<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('titanzero_web_chat_embeddings')) {
            return;
        }

        Schema::create('titanzero_web_chat_embeddings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_session_id')->index();
            $table->text('content');
            $table->longText('vector')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_web_chat_embeddings');
    }
};
