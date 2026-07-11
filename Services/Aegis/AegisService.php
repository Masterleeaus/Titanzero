<?php

namespace Modules\TitanZero\Services\Aegis;

use Illuminate\Support\Facades\Log;

/**
 * AegisService — Titan Zero safety core.
 *
 * Filters AI output for compliance before delivery.
 * Enforces escalation detection and compliance gate per domain.
 *
 * Responsibilities:
 * - Output safety filtering (sensitive domains: biohazard, NDIS, etc.)
 * - Compliance gate enforcement (flag missing required fields)
 * - Escalation detection (mandatory reporting triggers)
 * - Failure logging → knowledge pack improvement queue
 */
class AegisService
{
    /**
     * Domains with strict content rules.
     * Pattern arrays are checked against the AI response text.
     */
    private array $sensitivePatterns = [
        'biohazard' => [
            '/\b(biohazard|hazardous\s+material|toxic\s+spill|chemical\s+exposure)\b/i',
        ],
        'ndis' => [
            '/\b(ndis|national\s+disability\s+insurance)\b/i',
        ],
        'mandatory_reporting' => [
            '/\b(abuse|neglect|assault|violence|harm\s+to\s+(a\s+)?child)\b/i',
            '/\b(mandatory\s+report|reportable\s+incident)\b/i',
        ],
        'financial_risk' => [
            '/\b(bank\s+account|credit\s+card\s+number|cvv|bsb\b|routing\s+number)\b/i',
        ],
    ];

    /**
     * Evaluate the AI-generated output.
     *
     * @param  string      $output      The raw AI response text.
     * @param  array       $context     Context used to build the prompt (company_id, domain, etc.).
     * @return array{
     *   verdict: 'green'|'yellow'|'blocked',
     *   flags: string[],
     *   safe_output: string,
     *   escalation_required: bool,
     * }
     */
    public function evaluate(string $output, array $context = []): array
    {
        $flags = [];
        $escalationRequired = false;

        foreach ($this->sensitivePatterns as $domain => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $output)) {
                    $flags[] = $domain;
                    if ($domain === 'mandatory_reporting') {
                        $escalationRequired = true;
                    }
                    break; // one flag per domain
                }
            }
        }

        // Determine verdict
        if ($escalationRequired) {
            $verdict = 'blocked';
        } elseif (!empty($flags)) {
            $verdict = 'yellow';
        } else {
            $verdict = 'green';
        }

        // If blocked, replace output with a compliance message
        $safeOutput = $output;
        if ($verdict === 'blocked') {
            $safeOutput = $this->buildBlockedMessage($flags, $context);
            $this->logFailure($flags, $context, $output);
        }

        return [
            'verdict'             => $verdict,
            'flags'               => array_unique($flags),
            'safe_output'         => $safeOutput,
            'escalation_required' => $escalationRequired,
        ];
    }

    /**
     * Compliance gate — check that required fields are present in the context.
     * Returns an array of missing field names (empty array = all present).
     */
    public function checkComplianceGate(array $context, array $requiredFields): array
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (empty($context[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    private function buildBlockedMessage(array $flags, array $context): string
    {
        $domain = implode(', ', $flags);
        return "This response has been blocked by Aegis safety controls ({$domain}). "
            . "If this relates to a mandatory reporting obligation or sensitive compliance matter, "
            . "please follow your organisation's escalation procedure.";
    }

    private function logFailure(array $flags, array $context, string $rawOutput): void
    {
        Log::warning('[Aegis] Response blocked', [
            'flags'      => $flags,
            'company_id' => $context['company_id'] ?? null,
            'source'     => $context['source'] ?? 'unknown',
        ]);
    }
}
