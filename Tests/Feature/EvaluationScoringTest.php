<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TitanZero\Evaluation\AgentEvaluator;
use Modules\TitanZero\Entities\AgentEvaluationScore;

/**
 * Feature tests for evaluation scoring — validates persistence and tenant scope.
 */
class EvaluationScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluate_persists_score_to_database(): void
    {
        $evaluator = app(AgentEvaluator::class);

        $score = $evaluator->evaluate(
            companyId:       1,
            agentName:       'crm_agent',
            sessionId:       'test-session-001',
            response:        ['result' => 'Lead created successfully.'],
            expectedOutcome: [],
            latencyMs:       250,
        );

        $this->assertInstanceOf(AgentEvaluationScore::class, $score);
        $this->assertSame(1, (int) $score->company_id);
        $this->assertSame('crm_agent', $score->agent_name);
        $this->assertSame(250, (int) $score->response_latency_ms);

        $this->assertDatabaseHas('titanzero_agent_evaluation_scores', [
            'company_id' => 1,
            'agent_name' => 'crm_agent',
            'session_id' => 'test-session-001',
        ]);
    }

    public function test_scores_are_tenant_scoped(): void
    {
        $evaluator = app(AgentEvaluator::class);

        $evaluator->evaluate(1, 'agent_a', 'sess-1', ['result' => 'ok'], [], 100);
        $evaluator->evaluate(2, 'agent_a', 'sess-2', ['result' => 'ok'], [], 100);

        $company1Scores = AgentEvaluationScore::where('company_id', 1)->count();
        $company2Scores = AgentEvaluationScore::where('company_id', 2)->count();

        $this->assertSame(1, $company1Scores);
        $this->assertSame(1, $company2Scores);
    }

    public function test_hallucination_lowers_composite_score(): void
    {
        $evaluator = app(AgentEvaluator::class);

        $clean = $evaluator->evaluate(1, 'agent', 'clean', ['result' => 'correct answer'], [], 100);
        $dirty = $evaluator->evaluate(1, 'agent', 'dirty', ['result' => 'correct answer'], ['forbidden_strings' => ['correct']], 100);

        $this->assertGreaterThan((float) $dirty->composite_score, (float) $clean->composite_score);
    }

    public function test_composite_score_bounded_between_0_and_100(): void
    {
        $evaluator = app(AgentEvaluator::class);

        $score = $evaluator->evaluate(1, 'agent', 'sess', [], [], 0);

        $this->assertGreaterThanOrEqual(0, (float) $score->composite_score);
        $this->assertLessThanOrEqual(100, (float) $score->composite_score);
    }
}
