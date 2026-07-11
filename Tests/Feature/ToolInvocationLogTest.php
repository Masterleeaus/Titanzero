<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TitanZero\Entities\ToolInvocationLog;
use Modules\TitanZero\Services\ToolInvocationLogger;

/**
 * Integration tests for ToolInvocationLogger — verifies call logging with tenant context.
 */
class ToolInvocationLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_tool_invocation_is_logged_with_company_id(): void
    {
        $logger = app(ToolInvocationLogger::class);

        $log = $logger->log(
            companyId:  1,
            agentName:  'titan_zero',
            toolName:   'search_kb',
            params:     ['query' => 'cleaning schedule'],
            result:     ['documents' => []],
            durationMs: 320,
            success:    true,
        );

        $this->assertInstanceOf(ToolInvocationLog::class, $log);
        $this->assertDatabaseHas('titanzero_tool_invocation_logs', [
            'company_id' => 1,
            'agent_name' => 'titan_zero',
            'tool_name'  => 'search_kb',
            'success'    => true,
        ]);
    }

    public function test_params_are_hashed_not_stored_in_clear(): void
    {
        $logger = app(ToolInvocationLogger::class);

        $log = $logger->log(
            companyId:  1,
            agentName:  'agent',
            toolName:   'tool',
            params:     ['secret' => 'password123'],
            result:     null,
            durationMs: 10,
        );

        $this->assertNotNull($log->params_hash);
        $this->assertSame(64, strlen($log->params_hash)); // SHA-256 hex = 64 chars
        $this->assertStringNotContainsString('password123', $log->params_hash);
    }

    public function test_failed_invocation_records_error(): void
    {
        $logger = app(ToolInvocationLogger::class);

        $log = $logger->log(
            companyId:  1,
            agentName:  'agent',
            toolName:   'failing_tool',
            params:     [],
            result:     null,
            durationMs: 5000,
            success:    false,
            error:      'Connection timed out',
        );

        $this->assertFalse($log->success);
        $this->assertSame('Connection timed out', $log->error_message);
    }

    public function test_logs_are_scoped_per_company(): void
    {
        $logger = app(ToolInvocationLogger::class);

        $logger->log(1, 'agent', 'tool', [], [], 100);
        $logger->log(2, 'agent', 'tool', [], [], 100);

        $this->assertSame(1, ToolInvocationLog::where('company_id', 1)->count());
        $this->assertSame(1, ToolInvocationLog::where('company_id', 2)->count());
    }
}
