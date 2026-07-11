<?php

namespace Modules\TitanZero\Actions\Handlers;

use Modules\TitanZero\Contracts\Actions\ActionHandlerInterface;
use Modules\TitanZero\ValueObjects\IntentObject;

class HelpFillFormHandler implements ActionHandlerInterface
{
    public function supports(string $intent): bool
    {
        return $intent === 'help_fill_form';
    }

    public function validate(IntentObject $intent, array $context = []): array
    {
        return ['ok' => true, 'errors' => []];
    }

    public function execute(IntentObject $intent, array $context = []): array
    {
        return [
            'type' => 'help_fill_form',
            'summary' => 'This action will propose safe field values (no writes) and ask for confirmation (next pass).',
        ];
    }
}
