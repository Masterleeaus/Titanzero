<?php

namespace Modules\TitanZero\Listeners;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\TitanZero\Events\TitanZeroContentGenerated;
use Modules\AIDocument\Entities\AiPromptHistory;
use Modules\AIDocument\Entities\AiPromptResponse;

class SendTitanZeroContentToDocs
{
    public function handle(TitanZeroContentGenerated $event): void
    {
        if (! Config::get('aiassistant.integrations.docs_enabled', true)) {
            return;
        }

        $content = trim($event->content ?? '');
        if ($content === '') {
            return;
        }

        try {
            $model      = Config::get('aiassistant.model', 'gpt-5-nano');
            $templateId = $event->templateId;

            $history = AiPromptHistory::create([
                'template_id'   => $templateId,
                'doc_name'      => 'Titan Zero ' . now()->format('Y-m-d H:i'),
                'model'         => $model,
                'creativity'    => 0.5,
                'max_tokens'    => 0,
                'max_results'   => 1,
                'prompt'        => 'Generated via Titan Zero integration.',
                'language'      => 'en',
                'prompt_fields' => null,
                'workspace'     => 'default',
                'created_by'    => $event->userId,
            ]);

            AiPromptResponse::create([
                'template_id'       => $templateId,
                'history_prompt_id' => $history->id,
                'used_words'        => str_word_count($content),
                'content'           => $content,
                'created_by'        => $event->userId,
            ]);
        } catch (\Throwable $e) {
            // Log but do not break user flow.
            Log::warning('Titan Zero: failed to send content to Docs', [
                'error'   => $e->getMessage(),
                'user_id' => $event->userId,
            ]);
        }
    }
}
