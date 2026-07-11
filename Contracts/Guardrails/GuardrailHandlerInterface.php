<?php

namespace Modules\TitanZero\Contracts\Guardrails;

use Modules\TitanZero\DTO\GuardrailTripped;

/**
 * GuardrailHandlerInterface — contract for custom blocked-term guardrail handlers.
 *
 * Declare the fully-qualified class name of an implementation in the module's
 * `guardrails.json` under the `handler_class` key to override the default
 * term-matching logic of BlockedTermGuardrailService.
 *
 * Example manifest entry:
 *   "handler_class": "Modules\\MyModule\\Guardrails\\MyCustomHandler"
 */
interface GuardrailHandlerInterface
{
    /**
     * Inspect $text and return a GuardrailTripped DTO if the content should be blocked,
     * or null to allow it through.
     *
     * @param  string  $text     The input or output text to screen.
     * @param  string  $context  'input' or 'output'.
     * @return GuardrailTripped|null
     */
    public function check(string $text, string $context): ?GuardrailTripped;
}
