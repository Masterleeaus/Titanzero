<?php

use Illuminate\Database\Migrations\Migration;
use Modules\TitanZero\Entities\AiChatCategory;

return new class extends Migration
{
    public function up(): void
    {
        if (! class_exists(AiChatCategory::class)) {
            return;
        }

        AiChatCategory::firstOrCreate(
            ['slug' => 'tz-ai-web-chat'],
            [
                'name'             => 'AI Web Chat',
                'role'             => 'Web Analyzer',
                'human_name'       => 'Web Analyzer',
                'helps_with'       => 'I can analyze any public website and answer questions about its content.',
                'chat_completions'  => json_encode([
                    ['role' => 'system', 'content' => 'You are a web page analyzer. Answer questions based on the crawled content only.'],
                ]),
                'plan'       => 'free',
                'is_enabled' => true,
            ]
        );
    }

    public function down(): void
    {
        if (class_exists(AiChatCategory::class)) {
            AiChatCategory::where('slug', 'tz-ai-web-chat')->delete();
        }
    }
};
