<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * AgentEvaluationScore — persisted result of a single AgentEvaluator run.
 * Always scoped to company_id (tenant boundary).
 */
class AgentEvaluationScore extends Model
{
    protected $table = 'titanzero_agent_evaluation_scores';

    protected $fillable = [
        'company_id',
        'agent_name',
        'session_id',
        'task_completion_rate',
        'hallucination_flag',
        'tool_accuracy',
        'response_latency_ms',
        'composite_score',
        'response_snapshot',
    ];

    protected $casts = [
        'hallucination_flag' => 'boolean',
    ];
}
