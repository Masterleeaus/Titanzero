<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_intent_runs')) {
        Schema::create('titanzero_intent_runs', function (Blueprint $table) {
            $table->id();
            $table->string('intent', 80)->index();
            $table->unsignedSmallInteger('confidence')->default(0)->index();
            $table->string('risk_level', 20)->default('low')->index();
            $table->string('execution_mode', 20)->default('clarify')->index();
            $table->longText('entities_json')->nullable();
            $table->longText('missing_entities_json')->nullable();
            $table->longText('page_context_json')->nullable();
            $table->longText('result_json')->nullable();
            $table->string('status', 30)->default('created')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('titanzero_audit_logs')) {
        Schema::create('titanzero_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intent_run_id')->index();
            $table->string('event', 80)->index();
            $table->longText('payload_json')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('titanzero_artifacts')) {
        Schema::create('titanzero_artifacts', function (Blueprint $table) {
            $table->id();
            $table->string('record_type', 80)->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->string('artifact_type', 80)->index();
            $table->string('title', 191)->nullable();
            $table->longText('content_json')->nullable();
            $table->string('storage_path', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        // safe down omitted
    }
};
