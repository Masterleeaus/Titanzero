<?php

namespace Modules\TitanZero\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\TitanZero\Evaluation\AgentEvaluator;

/**
 * Unit tests for AgentEvaluator — verifies all scoring methods in isolation.
 */
class AgentEvaluatorTest extends TestCase
{
    private AgentEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new AgentEvaluator();
    }

    // -------------------------------------------------------------------------
    // Task completion scoring
    // -------------------------------------------------------------------------

    public function test_empty_response_scores_zero_task_completion(): void
    {
        $score = $this->evaluator->scoreTaskCompletion([], []);
        $this->assertSame(0, $score);
    }

    public function test_non_empty_response_scores_at_least_60(): void
    {
        $score = $this->evaluator->scoreTaskCompletion(['message' => 'Hello'], []);
        $this->assertGreaterThanOrEqual(60, $score);
    }

    public function test_response_with_result_key_scores_at_least_80(): void
    {
        $score = $this->evaluator->scoreTaskCompletion(['result' => 'done'], []);
        $this->assertGreaterThanOrEqual(80, $score);
    }

    public function test_all_expected_keys_present_scores_100(): void
    {
        $response = ['result' => 'ok', 'foo' => 1, 'bar' => 2];
        $expected = ['expected_keys' => ['foo', 'bar']];
        $score = $this->evaluator->scoreTaskCompletion($response, $expected);
        $this->assertSame(100, $score);
    }

    public function test_partial_expected_keys_reduces_score(): void
    {
        $response = ['result' => 'ok', 'foo' => 1];
        $expected = ['expected_keys' => ['foo', 'bar', 'baz']];
        $score = $this->evaluator->scoreTaskCompletion($response, $expected);
        $this->assertLessThan(100, $score);
        $this->assertGreaterThan(0, $score);
    }

    // -------------------------------------------------------------------------
    // Hallucination detection
    // -------------------------------------------------------------------------

    public function test_clean_response_returns_no_hallucination(): void
    {
        $flag = $this->evaluator->detectHallucination(['result' => 'Cleaning schedule done.'], []);
        $this->assertSame(0, $flag);
    }

    public function test_forbidden_string_triggers_hallucination_flag(): void
    {
        $flag = $this->evaluator->detectHallucination(
            ['result' => 'The invoice amount is $9999.'],
            ['forbidden_strings' => ['$9999']],
        );
        $this->assertSame(1, $flag);
    }

    public function test_ai_disclaimer_in_tool_result_triggers_hallucination_flag(): void
    {
        $flag = $this->evaluator->detectHallucination(
            ['result' => ['message' => 'As an AI I cannot access live data.']],
            [],
        );
        $this->assertSame(1, $flag);
    }

    // -------------------------------------------------------------------------
    // Tool accuracy scoring
    // -------------------------------------------------------------------------

    public function test_no_expected_tools_scores_100(): void
    {
        $score = $this->evaluator->scoreToolAccuracy(['tool_calls' => ['foo']], []);
        $this->assertSame(100, $score);
    }

    public function test_all_expected_tools_used_scores_100(): void
    {
        $response = ['tool_calls' => ['search_kb', 'draft_document']];
        $expected = ['expected_tools' => ['search_kb', 'draft_document']];
        $score = $this->evaluator->scoreToolAccuracy($response, $expected);
        $this->assertSame(100, $score);
    }

    public function test_missing_tool_reduces_score(): void
    {
        $response = ['tool_calls' => ['search_kb']];
        $expected = ['expected_tools' => ['search_kb', 'draft_document']];
        $score = $this->evaluator->scoreToolAccuracy($response, $expected);
        $this->assertSame(50, $score);
    }

    // -------------------------------------------------------------------------
    // Composite score
    // -------------------------------------------------------------------------

    public function test_composite_score_with_no_hallucination(): void
    {
        $composite = $this->evaluator->compositeScore(100, 0, 100);
        $this->assertSame(100.0, $composite);
    }

    public function test_hallucination_reduces_composite_score(): void
    {
        $without = $this->evaluator->compositeScore(100, 0, 100);
        $with    = $this->evaluator->compositeScore(100, 1, 100);
        $this->assertGreaterThan($with, $without);
    }

    public function test_composite_score_is_bounded_between_0_and_100(): void
    {
        $score = $this->evaluator->compositeScore(0, 1, 0);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}
