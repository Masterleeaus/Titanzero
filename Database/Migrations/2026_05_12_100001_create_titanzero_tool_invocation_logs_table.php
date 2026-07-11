<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tool invocation audit log — every AI tool call logged with tenant context.
 * Blueprint 19 requirement: company_id, agent_name, tool_name, hashes, duration.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_tool_invocation_logs')) {
        Schema::create('titanzero_tool_invocation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('agent_name', 120);
            $table->string('tool_name', 120)->index();
            $table->char('params_hash', 64)->nullable();
            $table->char('result_hash', 64)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'tool_name', 'created_at']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_tool_invocation_logs');
    }
};
