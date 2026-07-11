<?php

namespace Modules\TitanZero\Contracts\Actions;

use Modules\TitanZero\ValueObjects\IntentObject;

interface ActionHandlerInterface
{
    public function supports(string $intent): bool;

    /**
     * Validate input + context. Return [ok=>bool, errors=>array]
     */
    public function validate(IntentObject $intent, array $context = []): array;

    /**
     * Execute the action (must be deterministic). Return payload.
     */
    public function execute(IntentObject $intent, array $context = []): array;
}
