<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * TitanZeroAiCall — audit record for every AI call routed through Titan Zero.
 * Requirement: every AI call logged with context, prompt, response, model, latency.
 */
class TitanZeroAiCall extends Model
{
    protected $table = 'titanzero_ai_calls';

    protected $fillable = [
        'company_id',
        'user_id',
        'provider',
        'model',
        'context_summary',
        'prompt',
        'response',
        'aegis_verdict',
        'aegis_flags',
        'input_tokens',
        'output_tokens',
        'latency_ms',
        'source',
        'success',
        'error_message',
    ];

    protected $casts = [
        'aegis_flags' => 'array',
        'success'     => 'boolean',
    ];
}
