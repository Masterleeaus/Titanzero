<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed default WorkSuite AI chat categories for AIChatPro in TitanZero.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $categories = [
            [
                'name'             => 'General Assistant',
                'slug'             => 'tz-general-assistant',
                'role'             => 'default',
                'human_name'       => 'Titan',
                'plan'             => 'free',
                'helps_with'       => 'General questions and assistance',
                'is_enabled'       => true,
                'chat_completions'  => null,
                'company_id'       => null,
                'user_id'          => null,
                'chatbot_id'       => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'Project Assistant',
                'slug'             => 'tz-project-assistant',
                'role'             => 'assistant',
                'human_name'       => 'Titan',
                'plan'             => 'free',
                'helps_with'       => 'Project status summaries, Q&A, and planning',
                'is_enabled'       => true,
                'chat_completions'  => null,
                'company_id'       => null,
                'user_id'          => null,
                'chatbot_id'       => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'Invoice Drafter',
                'slug'             => 'tz-invoice-drafter',
                'role'             => 'assistant',
                'human_name'       => 'Titan',
                'plan'             => 'free',
                'helps_with'       => 'Drafting invoices, proposals, and financial documents',
                'is_enabled'       => true,
                'chat_completions'  => null,
                'company_id'       => null,
                'user_id'          => null,
                'chatbot_id'       => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'name'             => 'HR Policy Bot',
                'slug'             => 'tz-hr-policy-bot',
                'role'             => 'assistant',
                'human_name'       => 'Titan',
                'plan'             => 'free',
                'helps_with'       => 'HR policies, compliance questions, and employee onboarding',
                'is_enabled'       => true,
                'chat_completions'  => null,
                'company_id'       => null,
                'user_id'          => null,
                'chatbot_id'       => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($categories as $category) {
            $exists = DB::table('titanzero_ai_chat_categories')
                ->where('slug', $category['slug'])
                ->exists();

            if (! $exists) {
                DB::table('titanzero_ai_chat_categories')->insert($category);
            }
        }
    }

    public function down(): void
    {
        DB::table('titanzero_ai_chat_categories')
            ->whereIn('slug', [
                'tz-general-assistant',
                'tz-project-assistant',
                'tz-invoice-drafter',
                'tz-hr-policy-bot',
            ])
            ->delete();
    }
};
