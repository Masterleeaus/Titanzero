<?php

namespace Modules\TitanZero\Services\Context;

/**
 * TitanZeroContextLoader — assembles vertical knowledge context packs.
 *
 * Responsibilities:
 * - Load the vertical knowledge pack for a given company / job context.
 * - Support knowledge pack versioning and hot-reload (no deploy required).
 * - Context pack structure: knowledge, terminology, compliance,
 *   checklist intelligence, artefact intelligence, pricing intelligence.
 * - Multi-vertical context merging for companies with multiple active verticals.
 *
 * The loader is safe-by-default: if no pack is found it returns a minimal
 * general-cleaning fallback so callers never receive an empty context.
 */
class TitanZeroContextLoader
{
    /**
     * Built-in vertical packs.
     * Companies can override / extend these via the titan_ai_settings store.
     */
    private array $builtInPacks = [
        'cleaning' => [
            'vertical'    => 'cleaning',
            'version'     => '1.0.0',
            'knowledge'   => 'Professional cleaning industry operational knowledge including surface types, cleaning agents, equipment, and service standards.',
            'terminology' => 'Booking, site, job, cleaner, shift, checklist, inspection, quality check, re-clean, scope of works.',
            'compliance'  => 'WHS, NDIS cleaning standards, infection control, chemical handling SDS, safe work method statements.',
            'checklist'   => 'Pre-clean, during-clean, post-clean, quality inspection, client sign-off.',
            'artefact'    => 'Service report, completion certificate, inspection report, quote, invoice.',
            'pricing'     => 'Hourly rate, fixed-price, materials markup, travel allowance, weekend penalty rates.',
        ],
        'ndis' => [
            'vertical'    => 'ndis',
            'version'     => '1.0.0',
            'knowledge'   => 'NDIS household tasks and community access support worker obligations.',
            'terminology' => 'Participant, support plan, NDIS number, SIL, SDA, support coordinator.',
            'compliance'  => 'NDIS Practice Standards, Privacy Act, mandatory reporting, incident reporting, restrictive practices.',
            'checklist'   => 'Support delivery checklist, participant consent, incident review.',
            'artefact'    => 'Support note, incident report, participant agreement, service booking.',
            'pricing'     => 'NDIS price guide, support category codes, claim types.',
        ],
    ];

    /**
     * Load the context pack(s) for a given company and optional job context.
     *
     * @param  int|null    $companyId   Tenant boundary. Null returns generic pack.
     * @param  array       $context     Optional context hints: job_id, client_id, site_id, verticals.
     * @return array  Merged context pack ready for prompt injection.
     */
    public function load(?int $companyId, array $context = []): array
    {
        $verticals = $this->resolveVerticals($companyId, $context);

        // Merge packs for all active verticals
        $merged = $this->merge($verticals);

        // Overlay company-specific customisations from settings store
        $merged = $this->applyCompanyOverrides($merged, $companyId);

        // Attach runtime context
        $merged['runtime'] = [
            'company_id' => $companyId,
            'job_id'     => $context['job_id'] ?? null,
            'client_id'  => $context['client_id'] ?? null,
            'site_id'    => $context['site_id'] ?? null,
        ];

        return $merged;
    }

    /**
     * Return a formatted system prompt string from a context pack.
     * Suitable for prepending to TitanCore AI messages.
     */
    public function toSystemPrompt(array $pack): string
    {
        $lines = [];

        if (!empty($pack['knowledge'])) {
            $lines[] = "INDUSTRY KNOWLEDGE: " . $pack['knowledge'];
        }
        if (!empty($pack['terminology'])) {
            $lines[] = "KEY TERMINOLOGY: " . $pack['terminology'];
        }
        if (!empty($pack['compliance'])) {
            $lines[] = "COMPLIANCE & STANDARDS: " . $pack['compliance'];
        }
        if (!empty($pack['checklist'])) {
            $lines[] = "CHECKLIST INTELLIGENCE: " . $pack['checklist'];
        }
        if (!empty($pack['artefact'])) {
            $lines[] = "ARTEFACT TYPES: " . $pack['artefact'];
        }
        if (!empty($pack['pricing'])) {
            $lines[] = "PRICING INTELLIGENCE: " . $pack['pricing'];
        }

        return implode("\n\n", $lines);
    }

    /**
     * Resolve which verticals are active for this company.
     * Falls back to ['cleaning'] if nothing is configured.
     */
    private function resolveVerticals(?int $companyId, array $context): array
    {
        // Allow explicit override via context
        if (!empty($context['verticals']) && is_array($context['verticals'])) {
            return $context['verticals'];
        }

        // Read from settings store if TitanCore settings service is available
        if ($companyId && class_exists(\Modules\TitanCore\Services\TitanAISettingsService::class)) {
            /** @var \Modules\TitanCore\Services\TitanAISettingsService $settings */
            $settings = app(\Modules\TitanCore\Services\TitanAISettingsService::class);
            $configured = $settings->get('active_verticals', $companyId);
            if (!empty($configured) && is_array($configured)) {
                return $configured;
            }
        }

        return ['cleaning'];
    }

    /**
     * Merge multiple vertical packs into a single context pack.
     */
    private function merge(array $verticals): array
    {
        if (empty($verticals)) {
            return $this->builtInPacks['cleaning'];
        }

        $base = [];
        foreach ($verticals as $vertical) {
            $pack = $this->builtInPacks[$vertical] ?? null;
            if (!$pack) {
                continue;
            }
            foreach (['knowledge', 'terminology', 'compliance', 'checklist', 'artefact', 'pricing'] as $key) {
                if (!empty($pack[$key])) {
                    $base[$key] = isset($base[$key])
                        ? $base[$key] . ' | ' . $pack[$key]
                        : $pack[$key];
                }
            }
        }

        if (empty($base)) {
            return $this->builtInPacks['cleaning'];
        }

        $base['verticals'] = $verticals;
        $base['version']   = '1.0.0';

        return $base;
    }

    /**
     * Apply company-specific overrides from the settings store.
     * Each key in the override replaces or appends to the merged pack.
     */
    private function applyCompanyOverrides(array $pack, ?int $companyId): array
    {
        if (!$companyId || !class_exists(\Modules\TitanCore\Services\TitanAISettingsService::class)) {
            return $pack;
        }

        try {
            /** @var \Modules\TitanCore\Services\TitanAISettingsService $settings */
            $settings = app(\Modules\TitanCore\Services\TitanAISettingsService::class);
            $overrides = $settings->get('context_pack_overrides', $companyId);

            if (!empty($overrides) && is_array($overrides)) {
                foreach ($overrides as $key => $value) {
                    $pack[$key] = $value;
                }
            }
        } catch (\Throwable) {
            // Settings store not available — proceed with base pack
        }

        return $pack;
    }
}
