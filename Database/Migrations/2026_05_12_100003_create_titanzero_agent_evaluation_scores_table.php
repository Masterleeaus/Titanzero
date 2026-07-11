<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent evaluation scores — stores results of every AgentEvaluator run.
 * Always scoped to company_id.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_agent_evaluation_scores')) {
        Schema::create('titanzero_agent_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('agent_name', 120)->index();
            $table->string('session_id', 80)->nullable()->index();
            $table->unsignedTinyInteger('task_completion_rate')->default(0);  // 0–100
            $table->boolean('hallucination_flag')->default(false);
            $table->unsignedTinyInteger('tool_accuracy')->default(100);       // 0–100
            $table->unsignedInteger('response_latency_ms')->nullable();
            $table->decimal('composite_score', 5, 2)->default(0);
            $table->longText('response_snapshot')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'agent_name', 'created_at']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_agent_evaluation_scores');
    }
};
