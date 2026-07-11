<?php

namespace Modules\TitanZero\Actions\Handlers;

use Modules\TitanZero\Contracts\Actions\ActionHandlerInterface;
use Modules\TitanZero\ValueObjects\IntentObject;

class ExplainPageHandler implements ActionHandlerInterface
{
    public function supports(string $intent): bool
    {
        return $intent === 'explain_page';
    }

    public function validate(IntentObject $intent, array $context = []): array
    {
        return ['ok' => true, 'errors' => []];
    }

    public function execute(IntentObject $intent, array $context = []): array
    {
        // Foundation only: no AI call yet. Returns structured guidance stub.
        $page = $intent->evidence['page'] ?? 'current page';
        $fields = $intent->evidence['fields'] ?? [];

        return [
            'type' => 'explain_page',
            'page' => $page,
            'summary' => 'This action will explain the current page using structured hints + Titan Zero knowledge (next pass).',
            'fields_detected' => $fields,
        ];
    }
}
