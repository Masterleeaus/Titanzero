<?php

namespace Modules\TitanZero\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\TitanZero\Evaluation\AgentEvaluator;

/**
 * RunAgentEvaluationJob — scheduled weekly to evaluate all active agents.
 *
 * For each company that has recent AI calls, the job samples the last N
 * completed calls per agent and produces evaluation scores.
 *
 * Schedule: weekly via TitanZero console kernel.
 */
class RunAgentEvaluationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Number of recent calls sampled per agent per company. */
    private const SAMPLE_SIZE = 10;

    public function __construct(
        public readonly ?int $companyId = null,
    ) {}

    public function handle(AgentEvaluator $evaluator): void
    {
        if (!DB::getSchemaBuilder()->hasTable('titanzero_ai_calls')) {
            return;
        }

        $query = DB::table('titanzero_ai_calls')
            ->select('company_id', 'source as agent_name', DB::raw('COUNT(*) as total'))
            ->where('success', true)
            ->where('created_at', '>=', now()->subWeek())
            ->whereNotNull('source')
            ->groupBy('company_id', 'source');

        if ($this->companyId !== null) {
            $query->where('company_id', $this->companyId);
        }

        $groups = $query->get();

        foreach ($groups as $group) {
            $calls = DB::table('titanzero_ai_calls')
                ->where('company_id', $group->company_id)
                ->where('source', $group->agent_name)
                ->where('success', true)
                ->where('created_at', '>=', now()->subWeek())
                ->orderByDesc('id')
                ->limit(self::SAMPLE_SIZE)
                ->get();

            foreach ($calls as $call) {
                $response = [
                    'message'       => $call->response,
                    'aegis_verdict' => $call->aegis_verdict,
                ];

                $evaluator->evaluate(
                    companyId:       (int) $call->company_id,
                    agentName:       (string) $group->agent_name,
                    sessionId:       (string) $call->id,
                    response:        $response,
                    expectedOutcome: [],
                    latencyMs:       (int) ($call->latency_ms ?? 0),
                );
            }
        }
    }
}
