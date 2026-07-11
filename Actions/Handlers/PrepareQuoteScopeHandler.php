<?php

namespace Modules\TitanZero\Actions\Handlers;

use Modules\TitanZero\Contracts\Actions\ActionHandlerInterface;
use Modules\TitanZero\ValueObjects\IntentObject;

/**
 * Stub handler (PrepareQuoteScopeHandler) — implemented in next passes.
 */
class PrepareQuoteScopeHandler implements ActionHandlerInterface
{
    public function supports(string $intent): bool
    {
        return false;
    }

    public function validate(IntentObject $intent, array $context = []): array
    {
        return ['ok' => false, 'errors' => ['Not implemented']];
    }

    public function execute(IntentObject $intent, array $context = []): array
    {
        return ['ok' => false, 'error' => 'Not implemented'];
    }
}
