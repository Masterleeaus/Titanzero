<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\TitanZero\Entities\CompanyAiKey;

/**
 * CompanyApiKeyService — resolves the active AI provider key for a company.
 *
 * BYO API key support: companies can store their own OpenAI, Anthropic,
 * or Gemini keys. This service resolves the correct key at query-time
 * and injects it into the TitanCore AI client configuration so that
 * callers can swap providers without changing call sites.
 */
class CompanyApiKeyService
{
    /**
     * Resolve the active provider and API key for a company.
     * Returns null if no company key is configured (platform key is used instead).
     *
     * @param  int     $companyId
     * @param  string  $provider  openai | anthropic | gemini
     * @return array{provider: string, api_key: string, model: string|null}|null
     */
    public function resolve(int $companyId, string $provider): ?array
    {
        $cacheKey = "company_ai_key.{$companyId}.{$provider}";

        return Cache::remember($cacheKey, 300, function () use ($companyId, $provider) {
            if (!DB::getSchemaBuilder()->hasTable('company_ai_keys')) {
                return null;
            }

            $record = CompanyAiKey::where('company_id', $companyId)
                ->where('provider', $provider)
                ->where('is_active', true)
                ->first();

            if (!$record) {
                return null;
            }

            try {
                return [
                    'provider' => $record->provider,
                    'api_key'  => $record->getDecryptedKey(),
                    'model'    => $record->model,
                ];
            } catch (\Throwable) {
                return null;
            }
        });
    }

    /**
     * Store or update a company's API key for a given provider.
     * The plain-text key is encrypted before persistence.
     */
    public function store(int $companyId, string $provider, string $plainKey, ?string $model = null, ?int $createdBy = null): void
    {
        if (!DB::getSchemaBuilder()->hasTable('company_ai_keys')) {
            return;
        }

        $record = CompanyAiKey::firstOrNew([
            'company_id' => $companyId,
            'provider'   => $provider,
        ]);

        $record->api_key              = $plainKey; // triggers setApiKeyAttribute
        $record->model                = $model;
        $record->is_active            = true;
        $record->created_by           = $createdBy;
        $record->save();

        Cache::forget("company_ai_key.{$companyId}.{$provider}");
    }

    /**
     * Deactivate a company's API key for a given provider.
     */
    public function revoke(int $companyId, string $provider): void
    {
        if (!DB::getSchemaBuilder()->hasTable('company_ai_keys')) {
            return;
        }

        CompanyAiKey::where('company_id', $companyId)
            ->where('provider', $provider)
            ->update(['is_active' => false]);

        Cache::forget("company_ai_key.{$companyId}.{$provider}");
    }
}
