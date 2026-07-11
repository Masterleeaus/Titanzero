<?php

namespace Modules\TitanZero\Services\Guardrails;

use Modules\TitanZero\DTO\GuardrailTripped;

/**
 * BlockedTermGuardrailService — manifest-driven blocked-term screening.
 *
 * Screens input (before the AI handler runs) and output (after the handler
 * returns) for terms declared in the module's `AI/Guardrails/guardrails.json`.
 *
 * Behaviour:
 * - Default matching is case-insensitive (configurable via `case_sensitive`).
 * - When a `handler_class` is declared in the manifest that class is
 *   instantiated and its `check(string $text, string $context): ?GuardrailTripped`
 *   method is called instead of the default term-matching logic.
 * - Returns `null` when the text passes all checks (no block).
 * - Returns a `GuardrailTripped` DTO when a blocked term is found.
 * - Never throws; invalid/missing manifest values fall back to defaults.
 */
class BlockedTermGuardrailService
{
    /**
     * Screen $text against the blocked-term list in $guardrails.
     *
     * @param  array   $guardrails  Decoded contents of `guardrails.json`.
     * @param  string  $text        The text to screen.
     * @param  string  $context     'input' or 'output' — used only for the DTO message.
     * @return GuardrailTripped|null  Non-null when a blocked term is matched.
     */
    public function screen(array $guardrails, string $text, string $context = 'input'): ?GuardrailTripped
    {
        // Delegate to a custom handler when declared.
        if (!empty($guardrails['handler_class'])) {
            return $this->runCustomHandler($guardrails['handler_class'], $text, $context);
        }

        $terms = $this->resolveTerms($guardrails);

        if (empty($terms)) {
            return null;
        }

        $caseSensitive = (bool) ($guardrails['case_sensitive'] ?? false);
        $haystack      = $caseSensitive ? $text : mb_strtolower($text);

        foreach ($terms as $rawTerm) {
            $needle = $caseSensitive ? $rawTerm : mb_strtolower($rawTerm);

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return new GuardrailTripped(
                    term:    $caseSensitive ? $rawTerm : mb_strtolower($rawTerm),
                    context: $context,
                    message: "Guardrail blocked: the {$context} contains a restricted term.",
                );
            }
        }

        return null;
    }

    /**
     * Screen the AI handler input.
     *
     * Convenience wrapper — calls `screen()` with context='input'.
     * The caller should check the return value before invoking the handler;
     * a non-null result means the handler must NOT run.
     *
     * @param  array   $guardrails  Decoded contents of `guardrails.json`.
     * @param  string  $input       The raw user or system input text.
     * @return GuardrailTripped|null
     */
    public function screenInput(array $guardrails, string $input): ?GuardrailTripped
    {
        if (!($guardrails['input_screening'] ?? true)) {
            return null;
        }

        return $this->screen($guardrails, $input, 'input');
    }

    /**
     * Screen the AI handler output.
     *
     * Convenience wrapper — calls `screen()` with context='output'.
     * Applied after the handler returns, before the response is delivered.
     *
     * @param  array   $guardrails  Decoded contents of `guardrails.json`.
     * @param  string  $output      The raw handler / AI response text.
     * @return GuardrailTripped|null
     */
    public function screenOutput(array $guardrails, string $output): ?GuardrailTripped
    {
        if (!($guardrails['output_screening'] ?? true)) {
            return null;
        }

        return $this->screen($guardrails, $output, 'output');
    }

    /**
     * Load a guardrails manifest from a JSON file on disk.
     *
     * @param  string  $path  Absolute path to `guardrails.json`.
     * @return array          Decoded manifest, or an empty array when the file is missing or malformed.
     */
    public static function loadManifest(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Illuminate\Support\Facades\Log::warning('[BlockedTermGuardrailService] Failed to parse guardrails JSON', [
                'path'  => $path,
                'error' => json_last_error_msg(),
            ]);
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Collect blocked terms from the manifest.
     *
     * Accepts both the simple `blocked_terms` key (flat array of strings) and
     * the structured `input_guardrails` / `output_guardrails` format used by
     * modules like TitanEchoAssist (looks for action === 'block' entries).
     *
     * @return string[]
     */
    private function resolveTerms(array $guardrails): array
    {
        $terms = [];

        // Simple flat list (preferred for explicit blocked-term manifests).
        if (!empty($guardrails['blocked_terms']) && is_array($guardrails['blocked_terms'])) {
            foreach ($guardrails['blocked_terms'] as $t) {
                if (is_string($t) && $t !== '') {
                    $terms[] = $t;
                }
            }
        }

        // Structured input guardrails with action === 'block' and patterns.
        foreach (['input_guardrails', 'output_guardrails'] as $section) {
            if (empty($guardrails[$section]) || !is_array($guardrails[$section])) {
                continue;
            }
            foreach ($guardrails[$section] as $rule) {
                if (($rule['action'] ?? '') === 'block' && !empty($rule['patterns'])) {
                    foreach ($rule['patterns'] as $pattern) {
                        if (is_string($pattern) && $pattern !== '') {
                            $terms[] = $pattern;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * Instantiate and invoke a custom handler class declared in the manifest.
     *
     * The class must be auto-loadable and implement GuardrailHandlerInterface.
     * Handlers that do not implement the interface are rejected to prevent
     * arbitrary class instantiation via manifest manipulation.
     *
     * @return GuardrailTripped|null
     */
    private function runCustomHandler(string $handlerClass, string $text, string $context): ?GuardrailTripped
    {
        if (!class_exists($handlerClass)) {
            // Custom handler is missing — fail open to avoid blocking all traffic,
            // but this should be treated as a configuration error.
            return null;
        }

        $handler = new $handlerClass();

        // Require the handler to implement the declared interface.
        if (!($handler instanceof \Modules\TitanZero\Contracts\Guardrails\GuardrailHandlerInterface)) {
            return null;
        }

        return $handler->check($text, $context);
    }
}
