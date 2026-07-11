<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI calls audit table — every call through TitanZero::query() is recorded here.
 * Requirement: every AI call logged with context, prompt, response, model, latency.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_ai_calls')) {
        Schema::create('titanzero_ai_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('provider', 32)->nullable()->index(); // openai | anthropic | gemini
            $table->string('model', 80)->nullable();
            $table->text('context_summary')->nullable();    // brief context description
            $table->text('prompt');
            $table->longText('response')->nullable();
            $table->string('aegis_verdict', 16)->default('green'); // green | yellow | blocked
            $table->json('aegis_flags')->nullable();                // reasons if flagged
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('source', 80)->nullable();   // module or surface that triggered the call
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_ai_calls');
    }
};
