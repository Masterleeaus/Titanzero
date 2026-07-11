<?php

namespace Modules\TitanZero\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a tool's error rate breaches the circuit-breaker threshold.
 * Consumers may disable the tool, alert operators, or trigger diagnostics.
 */
class CircuitBreakerTripped
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $companyId,
        public readonly string $toolName,
        public readonly float  $errorRate,
        public readonly int    $windowMinutes,
        public readonly int    $errorCount,
        public readonly int    $totalCount,
    ) {}
}
