<?php

namespace Modules\TitanZero\Evaluation;

use Modules\TitanZero\Entities\AgentEvaluationScore;

/**
 * AgentEvaluator — scores agent response quality against four canonical metrics.
 *
 * Metrics (Blueprint 04-AI):
 *  - task_completion_rate   : 0–100, did the agent complete the requested task?
 *  - hallucination_flag     : 0 = clean, 1 = hallucination detected
 *  - tool_accuracy          : 0–100, were tool calls correct and relevant?
 *  - response_latency_ms    : raw latency in milliseconds
 *
 * The composite score is a weighted average of the three quality signals
 * (latency is recorded but excluded from the composite to avoid conflating
 * speed with correctness).
 */
class AgentEvaluator
{
    /**
     * Evaluate a single agent response and persist the result.
     *
     * @param  int     $companyId
     * @param  string  $agentName
     * @param  string  $sessionId        Correlation ID (e.g. audit_id, run_id).
     * @param  array   $response         The agent's raw response array.
     * @param  array   $expectedOutcome  Ground-truth or expected output hints.
     * @param  int     $latencyMs        Wall-clock latency recorded by the caller.
     * @return AgentEvaluationScore
     */
    public function evaluate(
        int    $companyId,
        string $agentName,
        string $sessionId,
        array  $response,
        array  $expectedOutcome = [],
        int    $latencyMs       = 0,
    ): AgentEvaluationScore {
        $taskCompletion = $this->scoreTaskCompletion($response, $expectedOutcome);
        $hallucinFlag   = $this->detectHallucination($response, $expectedOutcome);
        $toolAccuracy   = $this->scoreToolAccuracy($response, $expectedOutcome);
        $composite      = $this->compositeScore($taskCompletion, $hallucinFlag, $toolAccuracy);

        return AgentEvaluationScore::create([
            'company_id'           => $companyId,
            'agent_name'           => $agentName,
            'session_id'           => $sessionId,
            'task_completion_rate' => $taskCompletion,
            'hallucination_flag'   => $hallucinFlag,
            'tool_accuracy'        => $toolAccuracy,
            'response_latency_ms'  => $latencyMs,
            'composite_score'      => $composite,
            'response_snapshot'    => json_encode($response),
        ]);
    }

    /**
     * Score task completion (0–100).
     *
     * Heuristics used when no explicit expected_keys are declared:
     *   - Response is non-empty                      → +60
     *   - Response contains 'result' or 'message' key → +20
     *   - Caller-provided expected_keys all present   → +20
     */
    public function scoreTaskCompletion(array $response, array $expected): int
    {
        if (empty($response)) {
            return 0;
        }

        $score = 60;

        if (isset($response['result']) || isset($response['message'])) {
            $score += 20;
        }

        $expectedKeys = $expected['expected_keys'] ?? [];
        if (!empty($expectedKeys)) {
            $present = array_intersect_key($response, array_flip($expectedKeys));
            $score   += (int) round(20 * (count($present) / count($expectedKeys)));
        } else {
            $score += 20;
        }

        return min(100, $score);
    }

    /**
     * Detect hallucination: returns 1 (flagged) or 0 (clean).
     *
     * Checks:
     *  - Response claims facts the caller explicitly marks as wrong
     *    (expected['forbidden_strings'])
     *  - Response contains a known unsupported assertion pattern
     */
    public function detectHallucination(array $response, array $expected): int
    {
        $text     = strtolower(json_encode($response));
        $forbidden = $expected['forbidden_strings'] ?? [];

        foreach ($forbidden as $term) {
            if (str_contains($text, strtolower((string) $term))) {
                return 1;
            }
        }

        // Generic hallucination signal: "as an AI I cannot …" in a tool-result context
        if (isset($response['result']) && str_contains($text, 'as an ai')) {
            return 1;
        }

        return 0;
    }

    /**
     * Score tool accuracy (0–100).
     *
     * Uses caller-supplied expected_tools list vs actual tool calls recorded
     * in the response (response['tool_calls'] array of tool names).
     */
    public function scoreToolAccuracy(array $response, array $expected): int
    {
        $expectedTools = $expected['expected_tools'] ?? [];
        if (empty($expectedTools)) {
            return 100; // no expectation declared → assume correct
        }

        $actualTools = $response['tool_calls'] ?? [];

        $matched = count(array_intersect($expectedTools, $actualTools));
        return (int) round(100 * ($matched / count($expectedTools)));
    }

    /**
     * Weighted composite score (0–100).
     * Weights: task_completion=0.5, tool_accuracy=0.3, hallucination_penalty=0.2.
     */
    public function compositeScore(int $taskCompletion, int $hallucinFlag, int $toolAccuracy): float
    {
        $hallucinPenalty = $hallucinFlag === 1 ? 0 : 100;
        return round(
            ($taskCompletion * 0.5)
            + ($toolAccuracy * 0.3)
            + ($hallucinPenalty * 0.2),
            2
        );
    }
}
