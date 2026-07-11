<?php

namespace Modules\TitanZero\Services\Assist;

class AssistRouter
{
    public function handle(string $action, array $context = [], string $input = ''): array
    {
        switch ($action) {
            case 'explain_page':
                return (new ExplainPageAction())->run($context, $input);
            case 'fill_form':
                return (new FillFormAction())->run($context, $input);
            case 'check_missing':
                return (new CheckMissingAction())->run($context, $input);
            case 'generate_notes':
                return (new GenerateNotesAction())->run($context, $input);
            default:
                return ['ok' => false, 'error' => 'Unknown action'];
        }
    }
}
