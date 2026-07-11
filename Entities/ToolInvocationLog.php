<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * ToolInvocationLog — immutable audit record for every AI tool call.
 * company_id is always set (tenant boundary).
 */
class ToolInvocationLog extends Model
{
    protected $table = 'titanzero_tool_invocation_logs';

    protected $fillable = [
        'company_id',
        'agent_name',
        'tool_name',
        'params_hash',
        'result_hash',
        'duration_ms',
        'success',
        'error_message',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];
}
