<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_ai_chat_sessions')) {
        Schema::create('titanzero_ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('title')->default('New Chat');
            $table->unsignedInteger('total_credits')->default(0);
            $table->unsignedInteger('total_words')->default(0);
            $table->boolean('is_guest')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_chatbot')->default(false);
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_ai_chat_sessions');
    }
};
