<?php

namespace Modules\TitanZero\Services\CircuitBreaker;

use Illuminate\Support\Facades\Cache;
use Modules\TitanZero\Events\CircuitBreakerTripped;

/**
 * CircuitBreakerService — per-company, per-tool circuit breaker.
 *
 * Tracks error rates in a rolling window (default 10 minutes).
 * When the error rate exceeds the threshold the circuit is opened
 * and `CircuitBreakerTripped` is dispatched once per open cycle.
 *
 * Configuration (from titanzero.circuit_breaker config):
 *   error_threshold_percent  — default 50 (%)
 *   window_minutes           — default 10
 *   min_calls                — default 5  (minimum calls before evaluation)
 *   open_duration_minutes    — default 5  (how long circuit stays open)
 */
class CircuitBreakerService
{
    private int   $threshold;        // error % to trip
    private int   $window;           // rolling window in minutes
    private int   $minCalls;         // minimum calls before evaluating
    private int   $openDuration;     // minutes the circuit stays open

    public function __construct()
    {
        $cfg = config('titanzero.circuit_breaker', []);
        $this->threshold    = (int) ($cfg['error_threshold_percent'] ?? 50);
        $this->window       = (int) ($cfg['window_minutes']          ?? 10);
        $this->minCalls     = (int) ($cfg['min_calls']               ?? 5);
        $this->openDuration = (int) ($cfg['open_duration_minutes']   ?? 5);
    }

    /**
     * Record a call outcome for a tool/company pair.
     * Returns true if the circuit was just tripped (caller may short-circuit).
     */
    public function recordCall(int $companyId, string $toolName, bool $success): bool
    {
        $bucket = $this->bucketKey($companyId, $toolName);
        $ttl    = ($this->window + 1) * 60;

        Cache::add("{$bucket}.total", 0, $ttl);
        Cache::add("{$bucket}.errors", 0, $ttl);

        Cache::increment("{$bucket}.total");
        if (!$success) {
            Cache::increment("{$bucket}.errors");
        }

        return $this->evaluate($companyId, $toolName);
    }

    /**
     * Is the circuit currently open (tool should be disabled)?
     */
    public function isOpen(int $companyId, string $toolName): bool
    {
        return (bool) Cache::get($this->openKey($companyId, $toolName), false);
    }

    /**
     * Manually reset (close) a circuit.
     */
    public function reset(int $companyId, string $toolName): void
    {
        $bucket = $this->bucketKey($companyId, $toolName);
        Cache::forget("{$bucket}.total");
        Cache::forget("{$bucket}.errors");
        Cache::forget($this->openKey($companyId, $toolName));
    }

    /**
     * Evaluate whether the circuit should be tripped.
     * Returns true if the circuit was just opened this call.
     */
    private function evaluate(int $companyId, string $toolName): bool
    {
        // Already open — do not re-trip
        if ($this->isOpen($companyId, $toolName)) {
            return false;
        }

        $bucket = $this->bucketKey($companyId, $toolName);
        $total  = (int) Cache::get("{$bucket}.total", 0);
        $errors = (int) Cache::get("{$bucket}.errors", 0);

        if ($total < $this->minCalls) {
            return false;
        }

        $errorRate = ($errors / $total) * 100;

        if ($errorRate < $this->threshold) {
            return false;
        }

        // Open the circuit
        Cache::put($this->openKey($companyId, $toolName), true, $this->openDuration * 60);

        event(new CircuitBreakerTripped(
            companyId:     $companyId,
            toolName:      $toolName,
            errorRate:     $errorRate,
            windowMinutes: $this->window,
            errorCount:    $errors,
            totalCount:    $total,
        ));

        return true;
    }

    private function bucketKey(int $companyId, string $toolName): string
    {
        $slot = (int) floor(time() / ($this->window * 60));
        return "tz.cb.{$companyId}.{$toolName}.{$slot}";
    }

    private function openKey(int $companyId, string $toolName): string
    {
        return "tz.cb.open.{$companyId}.{$toolName}";
    }
}
