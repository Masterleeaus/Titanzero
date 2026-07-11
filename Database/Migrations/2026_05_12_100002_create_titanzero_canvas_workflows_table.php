<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canvas workflows and execution runs.
 * Workflows are visual automations built in the Canvas UI.
 * Runs record each execution with input/output and audit trail.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_canvas_workflows')) {
        Schema::create('titanzero_canvas_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->json('definition')->nullable();       // nodes + edges
            $table->string('trigger_type', 40)->default('manual'); // manual|signal|schedule
            $table->json('trigger_config')->nullable();
            $table->string('status', 20)->default('draft'); // draft|active|archived
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
        }

        if (!Schema::hasTable('titanzero_canvas_workflow_runs')) {
        Schema::create('titanzero_canvas_workflow_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('workflow_id')->index();
            $table->string('triggered_by', 120)->nullable();
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->string('status', 20)->default('running'); // running|completed|failed
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'workflow_id', 'created_at']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_canvas_workflow_runs');
        Schema::dropIfExists('titanzero_canvas_workflows');
    }
};
