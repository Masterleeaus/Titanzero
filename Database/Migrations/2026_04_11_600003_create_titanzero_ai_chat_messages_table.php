<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_ai_chat_messages')) {
        Schema::create('titanzero_ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ai_chat_session_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->string('response')->nullable();
            $table->string('hash', 512)->nullable();
            $table->unsignedInteger('credits')->default(0);
            $table->unsignedInteger('words')->default(0);
            $table->timestamps();

            $table->foreign('ai_chat_session_id')
                ->references('id')
                ->on('titanzero_ai_chat_sessions')
                ->cascadeOnDelete();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_ai_chat_messages');
    }
};
