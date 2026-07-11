<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AiChatProFileChat v1.1.0 — add OpenAI FileSearch columns to TitanZero chat sessions.
 *
 * Stores the per-session vector store ID, file ID, document name and reference URL
 * so that the AIFileChatService can attach an uploaded document to the chat thread.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titanzero_ai_chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('titanzero_ai_chat_sessions', 'openai_vector_id')) {
                $table->string('openai_vector_id')->nullable()->after('is_chatbot');
            }
            if (!Schema::hasColumn('titanzero_ai_chat_sessions', 'openai_file_id')) {
                $table->string('openai_file_id')->nullable()->after('openai_vector_id');
            }
            if (!Schema::hasColumn('titanzero_ai_chat_sessions', 'doc_name')) {
                $table->string('doc_name')->nullable()->after('openai_file_id');
            }
            if (!Schema::hasColumn('titanzero_ai_chat_sessions', 'reference_url')) {
                $table->string('reference_url')->nullable()->after('doc_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titanzero_ai_chat_sessions', function (Blueprint $table) {
            $table->dropColumn(['openai_vector_id', 'openai_file_id', 'doc_name', 'reference_url']);
        });
    }
};
