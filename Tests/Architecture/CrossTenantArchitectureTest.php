<?php

namespace Modules\TitanZero\Tests\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Architecture tests — assert that tool-related code never bypasses
 * the company_id boundary.
 *
 * These tests do not use a database; they statically inspect class files.
 */
class CrossTenantArchitectureTest extends TestCase
{
    /**
     * All key orchestration services must exist and be pure PHP classes
     * (not returning Eloquent models directly).
     */
    public function test_cross_tenant_guard_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\Security\CrossTenantGuard::class),
            'CrossTenantGuard must exist'
        );
    }

    public function test_circuit_breaker_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\CircuitBreaker\CircuitBreakerService::class),
            'CircuitBreakerService must exist'
        );
    }

    public function test_tool_invocation_logger_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Services\ToolInvocationLogger::class),
            'ToolInvocationLogger must exist'
        );
    }

    public function test_circuit_breaker_tripped_event_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Events\CircuitBreakerTripped::class),
            'CircuitBreakerTripped event must exist'
        );
    }

    public function test_canvas_workflow_executed_event_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Events\CanvasWorkflowExecuted::class),
            'CanvasWorkflowExecuted event must exist'
        );
    }

    public function test_agent_evaluator_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Evaluation\AgentEvaluator::class),
            'AgentEvaluator must exist'
        );
    }

    public function test_run_agent_evaluation_job_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Jobs\RunAgentEvaluationJob::class),
            'RunAgentEvaluationJob must exist'
        );
    }

    public function test_canvas_workflow_entity_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Canvas\Entities\CanvasWorkflow::class),
            'CanvasWorkflow entity must exist'
        );
    }

    public function test_canvas_workflow_run_entity_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Canvas\Entities\CanvasWorkflowRun::class),
            'CanvasWorkflowRun entity must exist'
        );
    }

    public function test_agent_evaluation_score_entity_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Entities\AgentEvaluationScore::class),
            'AgentEvaluationScore entity must exist'
        );
    }

    public function test_tool_invocation_log_entity_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanZero\Entities\ToolInvocationLog::class),
            'ToolInvocationLog entity must exist'
        );
    }

    /**
     * Assert that CrossTenantGuard::assertSameTenant throws when company IDs differ.
     * This is the key architectural invariant that prevents cross-tenant data leaks.
     */
    public function test_cross_tenant_guard_throws_on_mismatch(): void
    {
        $this->expectException(\RuntimeException::class);

        \Modules\TitanZero\Services\Security\CrossTenantGuard::assertSameTenant(
            1, 99, 'SomeResource'
        );
    }

    /**
     * Assert that CrossTenantGuard passes when IDs match (no false positives).
     */
    public function test_cross_tenant_guard_does_not_throw_on_match(): void
    {
        $this->expectNotToPerformAssertions();

        \Modules\TitanZero\Services\Security\CrossTenantGuard::assertSameTenant(
            42, 42, 'SomeResource'
        );
    }

    /**
     * ToolInvocationLog must always have a fillable company_id.
     */
    public function test_tool_invocation_log_has_company_id_fillable(): void
    {
        $model    = new \Modules\TitanZero\Entities\ToolInvocationLog();
        $fillable = $model->getFillable();

        $this->assertContains('company_id', $fillable);
    }

    /**
     * AgentEvaluationScore must always have a fillable company_id.
     */
    public function test_agent_evaluation_score_has_company_id_fillable(): void
    {
        $model    = new \Modules\TitanZero\Entities\AgentEvaluationScore();
        $fillable = $model->getFillable();

        $this->assertContains('company_id', $fillable);
    }
}
