<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-company AI rate limit configuration.
 * Titan Zero reads this to enforce tenant-scoped throttling.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('company_ai_rate_limits')) {
        Schema::create('company_ai_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique()->index();
            $table->unsignedInteger('requests_per_minute')->default(20);
            $table->unsignedInteger('requests_per_day')->default(1000);
            $table->unsignedInteger('tokens_per_day')->default(500000);
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_ai_rate_limits');
    }
};
