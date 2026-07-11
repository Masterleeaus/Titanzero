<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Facades\Auth;
use Modules\TitanCore\Services\TitanCoreAIService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\TitanZero\Entities\TitanZeroAiCall;
use Modules\TitanZero\Services\Aegis\AegisService;
use Modules\TitanZero\Services\Context\TitanZeroContextLoader;

/**
 * TitanZeroQueryService — central AI query router.
 *
 * This is the single entry point for all AI requests across the platform.
 * Every module, node, and surface calls this (via the TitanZero facade).
 *
 * Responsibilities:
 * - Rate limit check per company (tenant-scoped).
 * - Load vertical context pack via TitanZeroContextLoader.
 * - Resolve BYO API key or fall back to platform key.
 * - Send request to the correct AI provider.
 * - Run output through Aegis safety core before delivery.
 * - Write a full audit record for every call.
 *
 * All external AI calls are safe-by-default: if the provider is
 * unavailable or not configured, a structured fallback is returned.
 */
class TitanZeroQueryService
{
    public function __construct(
        protected AegisService $aegis,
        protected TitanZeroContextLoader $contextLoader,
        protected CompanyApiKeyService $keyService,
        protected TitanZeroRateLimitService $rateLimiter,
        protected TitanCoreAIService $titanCoreAIService,
    ) {}

    /**
     * Primary entry point used by all platform nodes.
     *
     * @param  array   $context  Tenant + domain context:
     *                           company_id, user_id, source, job_id, client_id, site_id, verticals, ...
     * @param  string  $prompt   The user or system prompt.
     * @return array{
     *   ok: bool,
     *   content: string,
     *   aegis_verdict: string,
     *   aegis_flags: string[],
     *   escalation_required: bool,
     *   model: string|null,
     *   latency_ms: int,
     *   error: string|null,
     * }
     */
    public function query(array $context, string $prompt): array
    {
        $companyId = isset($context['company_id']) ? (int) $context['company_id'] : null;
        $userId    = $context['user_id'] ?? (Auth::id() ?? null);
        $source    = $context['source'] ?? 'unknown';
        $startMs   = (int) round(microtime(true) * 1000);

        // 1. Rate limit check
        if ($companyId) {
            $rl = $this->rateLimiter->check($companyId);
            if (!$rl['allowed']) {
                return $this->errorResponse('rate_limited', $rl['reason'] ?? 'rate_limit', $context, $prompt, $userId, $source);
            }
        }

        // 2. Load vertical context pack
        $contextPack   = $this->contextLoader->load($companyId, $context);
        $systemContext = $this->contextLoader->toSystemPrompt($contextPack);

        // 3. Resolve provider and model
        $provider = $context["provider"] ?? config("titancore.ai.default_provider", "openai");
        $model = $context["model"] ?? config("titancore.ai.{$provider}.model");

        if (!$this->titanCoreAIService->isProviderAvailable($provider)) {
            return $this->errorResponse("no_api_key", "AI provider {$provider} is not configured. Please add an API key in your AI settings.", $context, $prompt, $userId, $source);
        }

        // 4. Build messages
        $messages = [];
        if ($systemContext) {
            $messages[] = ['role' => 'system', 'content' => $systemContext];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // 5. Call provider
        $rawContent = null;
        $inputTokens = null;
        $outputTokens = null;
        $callError = null;

        try {
            $rawContent = $this->titanCoreAIService->generate($prompt, $messages, [], $provider, $model);
            // For now, we don't have token usage from TitanCoreAIService->generate(), so set to null
            $inputTokens = null;
            $outputTokens = null;
        } catch (\Throwable $e) {
            $callError = $e->getMessage();
            Log::error('[TitanZero] Provider call failed', [
                'provider'   => $provider,
                'company_id' => $companyId,
                'error'      => $callError,
            ]);
        }

        $latencyMs = (int) round(microtime(true) * 1000) - $startMs;

        if ($callError || $rawContent === null) {
            $this->writeAudit([
                'company_id'      => $companyId,
                'user_id'         => $userId,
                'provider'        => $provider,
                'model'           => $model,
                'context_summary' => substr($systemContext, 0, 200),
                'prompt'          => $prompt,
                'response'        => null,
                'aegis_verdict'   => 'green',
                'aegis_flags'     => [],
                'input_tokens'    => $inputTokens,
                'output_tokens'   => $outputTokens,
                'latency_ms'      => $latencyMs,
                'source'          => $source,
                'success'         => false,
                'error_message'   => $callError,
            ]);

            return [
                'ok'                  => false,
                'content'             => 'AI provider is currently unavailable. Please try again.',
                'aegis_verdict'       => 'green',
                'aegis_flags'         => [],
                'escalation_required' => false,
                'model'               => $model,
                'latency_ms'          => $latencyMs,
                'error'               => $callError,
            ];
        }

        // 6. Aegis safety gate
        $aegisResult = $this->aegis->evaluate($rawContent, array_merge($context, ['source' => $source]));

        // 7. Rate limit counters
        if ($companyId) {
            $this->rateLimiter->increment($companyId, ($inputTokens ?? 0) + ($outputTokens ?? 0));
        }

        // 8. Write audit record
        $this->writeAudit([
            'company_id'      => $companyId,
            'user_id'         => $userId,
            'provider'        => $provider,
            'model'           => $model,
            'context_summary' => substr($systemContext, 0, 200),
            'prompt'          => $prompt,
            'response'        => $aegisResult['safe_output'],
            'aegis_verdict'   => $aegisResult['verdict'],
            'aegis_flags'     => $aegisResult['flags'],
            'input_tokens'    => $inputTokens,
            'output_tokens'   => $outputTokens,
            'latency_ms'      => $latencyMs,
            'source'          => $source,
            'success'         => true,
            'error_message'   => null,
        ]);

        return [
            'ok'                  => true,
            'content'             => $aegisResult['safe_output'],
            'aegis_verdict'       => $aegisResult['verdict'],
            'aegis_flags'         => $aegisResult['flags'],
            'escalation_required' => $aegisResult['escalation_required'],
            'model'               => $model,
            'latency_ms'          => $latencyMs,
            'error'               => null,
        ];
    }

    /**
     * Resolve provider name, API key, and model for the company.
     * Priority: company BYO key → platform env key.
     *
     * @return array{string, string|null, string|null}  [provider, api_key, model]
     */
    // Removed resolveProvider, callProvider, callOpenAI, and callAnthropic methods.
    // All AI requests now flow through TitanCoreAIService.

    private function writeAudit(array $data): void
    {
        try {
            if (\Illuminate\Support\Facades\DB::getSchemaBuilder()->hasTable('titanzero_ai_calls')) {
                TitanZeroAiCall::create($data);
            }
        } catch (\Throwable $e) {
            Log::warning('[TitanZero] Could not write AI call audit', ['error' => $e->getMessage()]);
        }
    }

    private function errorResponse(string $type, string $message, array $context, string $prompt, mixed $userId, string $source): array
    {
        $this->writeAudit([
            'company_id'      => $context['company_id'] ?? null,
            'user_id'         => $userId,
            'provider'        => $context['provider'] ?? 'unknown',
            'model'           => null,
            'context_summary' => null,
            'prompt'          => $prompt,
            'response'        => null,
            'aegis_verdict'   => 'green',
            'aegis_flags'     => [],
            'input_tokens'    => null,
            'output_tokens'   => null,
            'latency_ms'      => 0,
            'source'          => $source,
            'success'         => false,
            'error_message'   => "{$type}: {$message}",
        ]);

        return [
            'ok'                  => false,
            'content'             => $message,
            'aegis_verdict'       => 'green',
            'aegis_flags'         => [],
            'escalation_required' => false,
            'model'               => null,
            'latency_ms'          => 0,
            'error'               => $message,
        ];
    }
}
