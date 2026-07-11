<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_ai_chat_categories')) {
        Schema::create('titanzero_ai_chat_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role')->default('default');
            $table->string('human_name')->nullable();
            $table->text('chat_completions')->nullable();
            $table->string('plan')->default('free');
            $table->text('helps_with')->nullable();
            $table->unsignedBigInteger('chatbot_id')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_ai_chat_categories');
    }
};
