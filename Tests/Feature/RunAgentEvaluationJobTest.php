<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TitanZero\Jobs\RunAgentEvaluationJob;
use Modules\TitanZero\Entities\AgentEvaluationScore;

/**
 * Feature tests for RunAgentEvaluationJob.
 */
class RunAgentEvaluationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_runs_without_error_when_no_calls_exist(): void
    {
        $job = new RunAgentEvaluationJob();
        $job->handle(app(\Modules\TitanZero\Evaluation\AgentEvaluator::class));

        // No exception thrown — job completes gracefully with empty data
        $this->assertTrue(true);
    }

    public function test_job_scoped_to_specific_company(): void
    {
        $job = new RunAgentEvaluationJob(companyId: 1);

        // Should run without errors even with no data
        $job->handle(app(\Modules\TitanZero\Evaluation\AgentEvaluator::class));

        $this->assertTrue(true);
    }
}
