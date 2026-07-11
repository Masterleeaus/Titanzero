<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * TitanZeroRateLimitService — enforces per-company AI call rate limits.
 *
 * Tenant-scoped throttling: each company has independent rate limit counters
 * so one tenant's heavy usage cannot starve another.
 *
 * Defaults (overridable per company via `company_ai_rate_limits` table):
 *   - 20 requests/minute
 *   - 1000 requests/day
 *   - 500,000 tokens/day
 */
class TitanZeroRateLimitService
{
    private array $defaults = [
        'requests_per_minute' => 20,
        'requests_per_day'    => 1000,
        'tokens_per_day'      => 500000,
    ];

    /**
     * Check whether a company is within its rate limits.
     *
     * @param  int  $companyId
     * @param  int  $estimatedTokens  Estimated token count for the upcoming request.
     * @return array{allowed: bool, reason: string|null}
     */
    public function check(int $companyId, int $estimatedTokens = 0): array
    {
        $limits = $this->resolveLimit($companyId);

        // Minute bucket
        $minuteKey   = "tz.rl.{$companyId}.minute." . floor(time() / 60);
        $minuteCount = (int) Cache::get($minuteKey, 0);

        if ($minuteCount >= $limits['requests_per_minute']) {
            return ['allowed' => false, 'reason' => 'rate_limit_minute'];
        }

        // Day bucket
        $dayKey   = "tz.rl.{$companyId}.day." . date('Y-m-d');
        $dayCount = (int) Cache::get($dayKey, 0);

        if ($dayCount >= $limits['requests_per_day']) {
            return ['allowed' => false, 'reason' => 'rate_limit_day'];
        }

        // Token day bucket
        $tokenKey   = "tz.rl.{$companyId}.tokens." . date('Y-m-d');
        $tokenCount = (int) Cache::get($tokenKey, 0);

        if ($estimatedTokens > 0 && ($tokenCount + $estimatedTokens) > $limits['tokens_per_day']) {
            return ['allowed' => false, 'reason' => 'rate_limit_tokens'];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Increment counters after a successful or attempted request.
     */
    public function increment(int $companyId, int $tokensUsed = 0): void
    {
        $minuteKey = "tz.rl.{$companyId}.minute." . floor(time() / 60);
        $dayKey    = "tz.rl.{$companyId}.day." . date('Y-m-d');
        $tokenKey  = "tz.rl.{$companyId}.tokens." . date('Y-m-d');

        Cache::add($minuteKey, 0, 90);     // 90s TTL covers the bucket
        Cache::increment($minuteKey);

        Cache::add($dayKey, 0, 86500);     // just over 24h
        Cache::increment($dayKey);

        if ($tokensUsed > 0) {
            Cache::add($tokenKey, 0, 86500);
            Cache::increment($tokenKey, $tokensUsed);
        }
    }

    private function resolveLimit(int $companyId): array
    {
        if (!DB::getSchemaBuilder()->hasTable('company_ai_rate_limits')) {
            return $this->defaults;
        }

        $row = DB::table('company_ai_rate_limits')
            ->where('company_id', $companyId)
            ->first();

        if (!$row) {
            return $this->defaults;
        }

        return [
            'requests_per_minute' => (int) ($row->requests_per_minute ?? $this->defaults['requests_per_minute']),
            'requests_per_day'    => (int) ($row->requests_per_day ?? $this->defaults['requests_per_day']),
            'tokens_per_day'      => (int) ($row->tokens_per_day ?? $this->defaults['tokens_per_day']),
        ];
    }
}
