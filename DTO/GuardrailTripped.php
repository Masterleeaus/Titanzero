<?php

namespace Modules\TitanZero\DTO;

/**
 * GuardrailTripped — result returned when a blocked-term guardrail fires.
 *
 * A non-null GuardrailTripped signals that the text must be rejected
 * before the AI handler runs (input) or before the response is delivered
 * (output).
 */
class GuardrailTripped
{
    /**
     * @param  string  $term     The blocked term that was matched (lower-cased)
     * @param  string  $context  Screening context: 'input' or 'output'
     * @param  string  $message  Human-readable explanation of the block
     */
    public function __construct(
        public readonly string $term,
        public readonly string $context,
        public readonly string $message,
    ) {}
}
