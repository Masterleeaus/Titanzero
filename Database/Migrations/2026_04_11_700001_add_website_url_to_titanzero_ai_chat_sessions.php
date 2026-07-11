<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('titanzero_ai_chat_sessions')) {
            return;
        }

        if (Schema::hasColumn('titanzero_ai_chat_sessions', 'website_url')) {
            return;
        }

        Schema::table('titanzero_ai_chat_sessions', function (Blueprint $table) {
            $table->string('website_url')->nullable()->after('is_chatbot');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('titanzero_ai_chat_sessions', 'website_url')) {
            Schema::table('titanzero_ai_chat_sessions', function (Blueprint $table) {
                $table->dropColumn('website_url');
            });
        }
    }
};
