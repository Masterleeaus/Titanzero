<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('titan_assist_usages')) {
        Schema::create('titan_assist_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->unsignedInteger('requests_count')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'company_id']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titan_assist_usages');
    }
};
