<?php

namespace Modules\TitanZero\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TitanZero\Canvas\Entities\CanvasWorkflow;
use Modules\TitanZero\Canvas\Entities\CanvasWorkflowRun;
use Modules\TitanZero\Canvas\Actions\ExecuteCanvasWorkflowAction;
use Modules\TitanZero\Events\CanvasWorkflowExecuted;
use Illuminate\Support\Facades\Event;

/**
 * Feature tests for Canvas workflow create and execute flow.
 */
class CanvasWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_canvas_workflow_can_be_created(): void
    {
        $workflow = CanvasWorkflow::create([
            'company_id'   => 1,
            'name'         => 'Test Workflow',
            'description'  => 'A test workflow',
            'definition'   => ['nodes' => [], 'edges' => []],
            'trigger_type' => 'manual',
            'status'       => 'active',
        ]);

        $this->assertDatabaseHas('titanzero_canvas_workflows', [
            'id'         => $workflow->id,
            'company_id' => 1,
            'name'       => 'Test Workflow',
        ]);
    }

    public function test_execute_workflow_creates_run_record(): void
    {
        Event::fake([CanvasWorkflowExecuted::class]);

        $workflow = CanvasWorkflow::create([
            'company_id'   => 1,
            'name'         => 'Run Test',
            'definition'   => ['nodes' => [], 'edges' => []],
            'trigger_type' => 'manual',
            'status'       => 'active',
        ]);

        $action = app(ExecuteCanvasWorkflowAction::class);
        $run    = $action->execute(1, $workflow->id, ['key' => 'value'], 'system');

        $this->assertInstanceOf(CanvasWorkflowRun::class, $run);
        $this->assertSame('completed', $run->status);
        $this->assertSame(1, (int) $run->company_id);
        $this->assertSame($workflow->id, (int) $run->workflow_id);

        $this->assertDatabaseHas('titanzero_canvas_workflow_runs', [
            'id'          => $run->id,
            'company_id'  => 1,
            'workflow_id' => $workflow->id,
            'status'      => 'completed',
        ]);
    }

    public function test_execute_workflow_dispatches_canvas_workflow_executed_event(): void
    {
        Event::fake([CanvasWorkflowExecuted::class]);

        $workflow = CanvasWorkflow::create([
            'company_id'   => 1,
            'name'         => 'Event Test',
            'definition'   => ['nodes' => [], 'edges' => []],
            'trigger_type' => 'manual',
            'status'       => 'active',
        ]);

        $action = app(ExecuteCanvasWorkflowAction::class);
        $action->execute(1, $workflow->id, [], 'system');

        Event::assertDispatched(CanvasWorkflowExecuted::class, function ($event) use ($workflow) {
            return $event->workflowId === $workflow->id
                && $event->companyId === 1
                && $event->status    === 'completed';
        });
    }

    public function test_execute_workflow_cross_tenant_throws_exception(): void
    {
        $workflow = CanvasWorkflow::create([
            'company_id'   => 5,
            'name'         => 'Cross-tenant test',
            'definition'   => [],
            'trigger_type' => 'manual',
            'status'       => 'active',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant violation/');

        $action = app(ExecuteCanvasWorkflowAction::class);
        $action->execute(1, $workflow->id); // company 1 trying to run company 5's workflow
    }

    public function test_workflow_run_has_steps_in_output_for_action_nodes(): void
    {
        Event::fake([CanvasWorkflowExecuted::class]);

        $definition = [
            'nodes' => [
                ['id' => 'start', 'type' => 'start'],
                ['id' => 'step1', 'type' => 'action', 'label' => 'Send Email'],
                ['id' => 'end', 'type' => 'end'],
            ],
            'edges' => [],
        ];

        $workflow = CanvasWorkflow::create([
            'company_id'   => 1,
            'name'         => 'Nodes Test',
            'definition'   => $definition,
            'trigger_type' => 'manual',
            'status'       => 'active',
        ]);

        $action = app(ExecuteCanvasWorkflowAction::class);
        $run    = $action->execute(1, $workflow->id);

        $this->assertNotEmpty($run->output['steps']);
        $this->assertSame('Send Email', $run->output['steps'][0]['label']);
    }
}
