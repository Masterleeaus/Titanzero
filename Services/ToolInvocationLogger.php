<?php

namespace Modules\TitanZero\Services;

use Modules\TitanZero\Entities\ToolInvocationLog;

/**
 * ToolInvocationLogger — records every AI tool call with full tenant context.
 *
 * Blueprint 19 requirement: every tool invocation must be logged with:
 *   company_id, agent_name, tool_name, params_hash, result_hash, duration_ms
 */
class ToolInvocationLogger
{
    /**
     * Log a completed tool invocation.
     *
     * @param  int          $companyId
     * @param  string       $agentName
     * @param  string       $toolName
     * @param  array        $params      Raw params (will be hashed, never stored in clear)
     * @param  array|null   $result      Tool result (will be hashed)
     * @param  int          $durationMs  Wall-clock duration in milliseconds
     * @param  bool         $success
     * @param  string|null  $error
     */
    public function log(
        int     $companyId,
        string  $agentName,
        string  $toolName,
        array   $params,
        ?array  $result,
        int     $durationMs,
        bool    $success = true,
        ?string $error   = null,
    ): ToolInvocationLog {
        return ToolInvocationLog::create([
            'company_id'   => $companyId,
            'agent_name'   => $agentName,
            'tool_name'    => $toolName,
            'params_hash'  => hash('sha256', json_encode($params)),
            'result_hash'  => $result !== null ? hash('sha256', json_encode($result)) : null,
            'duration_ms'  => $durationMs,
            'success'      => $success,
            'error_message'=> $error,
        ]);
    }
}
