<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BYO API Key table — stores per-company AI provider keys.
 * Keys are encrypted at rest. Titan Zero resolves the active key
 * for a tenant before dispatching to TitanCore.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('company_ai_keys')) {
        Schema::create('company_ai_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('provider', 32)->index(); // openai | anthropic | gemini
            $table->text('api_key_encrypted');        // encrypted with app key
            $table->string('model', 80)->nullable();  // override model if set
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'provider']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_ai_keys');
    }
};
