<?php

namespace Modules\TitanZero\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched every time a Canvas workflow completes execution (success or failure).
 */
class CanvasWorkflowExecuted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $companyId,
        public readonly int    $workflowId,
        public readonly int    $runId,
        public readonly string $status,   // completed | failed
        public readonly array  $output,
    ) {}
}
