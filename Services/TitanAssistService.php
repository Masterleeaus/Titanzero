<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\TitanCore\Services\TitanCoreAIService;
use Modules\TitanEchoAssist\Services\GeneratorBridge;
use Modules\TitanZero\Services\Context\TitanZeroContextLoader;
use Modules\TitanEchoAssist\Services\WorkcorePortalDataService;
use Modules\TitanZero\Entities\TitanZeroUsage;
use Modules\TitanCore\Services\UsageCostLogger;

class TitanAssistService
{
    private const MAX_PORTAL_SNAPSHOT_BYTES = 5000;
    private const UI_CONTEXT_MESSAGE_LIMIT = 6;

    /**
     * Titan Zero service talks to Titan Core on behalf of the module.
     */
    public function __construct(protected UsageCostLogger $usage, protected TitanCoreAIService $titanCoreAIService, protected TitanZeroContextLoader $contextLoader)
    {
    }

    /**
     * Generate AI content for a given prompt.
     *
     * The model is selected at Super Admin level only via config('aiassistant.model').
     *
     * @param  string  $prompt
     * @param  string  $language
     * @param  int     $maxTokens
     * @param  float   $temperature
     * @param  int     $maxResults
     * @return array{success: bool, text?: string, tokens?: int|null, message?: string}
     */
    public function generate(
        string $prompt,
        string $language,
        int $maxTokens,
        float $temperature,
        int $maxResults = 1,
        ?int $userId = null,
        ?int $companyId = null,
        ?int $templateId = null
    ): array
    {
        $langText = "Provide response in {$language} language.\n\n ";

        $model = config('aiassistant.model', 'gpt-4o-mini'); // Default to a TitanCore supported model

        try {
            $messages = [
                [
                    'role'    => 'user',
                    'content' => $prompt . ' ' . $langText,
                ],
            ];
            $result = $this->titanCoreAIService->generate($prompt, $messages, [], 'openai', $model);
        } catch (\Throwable $e) {
            Log::error('TitanAssistService: Error during AI generation.', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => __('Text was not generated due to an AI service error.'),
            ];
        }

        // TitanCoreAIService::generate returns a string, so we don't need to decode it.
        // We also don't get 'choices' or 'usage' directly, so we'll simulate for now.
        $response = ['choices' => [['message' => ['content' => $result]]], 'usage' => ['completion_tokens' => 0, 'total_tokens' => 0]];

        if (! is_array($response) || ! isset($response['choices'])) {
            return [
                'success' => false,
                'message' => __('Text was not generated due to Invalid API Key'),
            ];
        }

        $text = $response['choices'][0]['message']['content'] ?? '';

        $tokens = 0; // Token usage is not directly available from TitanCoreAIService->generate() yet


        // Cost + token telemetry (tenant scoped).
        try {
            $this->usage->logFromOpenAIResponse('chat', $response, [
                'tenant_id' => $companyId, // Worksuite often uses company_id as tenant proxy here
                'user_id' => $userId,
                'agent_slug' => 'titan_zero',
                'provider' => 'openai',
                'model' => $model,
                'template_id' => $templateId,
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        // Log lightweight usage row for reporting/limits.
        try {
            TitanZeroUsage::create([
                'user_id'        => $userId,
                'company_id'     => $companyId,
                'template_id'    => $templateId,
                'tokens_used'    => $tokens ?? 0,
                'requests_count' => 1,
            ]);
        } catch (\Throwable $e) {
            // Failing to log should not break user flow.
        }

        return [
            'success' => true,
            'text'    => $text,
            'tokens'  => $tokens,
        ];
    }

    /**
     * Build a Business OS assistant response with normalized widgets.
     *
     * @param  array<int, array<string, mixed>>  $history
     * @param  array<string, mixed>  $context
     * @return array{message: string, reply: string, parts: list<array<string, mixed>>, widgets: list<array<string, mixed>>}
     */
    public function respond(string $message, array $history = [], array $context = []): array
    {
        $message = trim($message);
        $companyId = (int) ($context['companyId'] ?? $context['company_id'] ?? $context['organization_id'] ?? 0);
        $userId = (int) ($context['userId'] ?? $context['user_id'] ?? 0);
        $source = (string) ($context['source'] ?? 'unknown');

        // Load vertical context pack
        $contextPack   = $this->contextLoader->load($companyId, $context);
        $systemContext = $this->contextLoader->toSystemPrompt($contextPack);

        $prompt = $this->buildBusinessOsPrompt(
            $context['appKey'] ?? 'default',
            $context['page'] ?? '',
            $this->portalSnapshot($context['appKey'] ?? 'default', $context, $companyId)
        );

        $prompt = $this->buildBusinessOsPrompt(
            $appKey,
            $page,
            $this->portalSnapshot($appKey, $context, $companyId)
        );

        [$reply, $parts] = $this->generateUiResponse($message, $prompt, $history);
        $widgets = app(WidgetFactory::class)->fromResponse($message, $reply, $parts);

        return [
            'message' => $reply,
            'reply' => $reply,
            'parts' => $widgets,
            'widgets' => $widgets,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $history
     * @return array{0: string, 1: list<array<string, mixed>>}
     */
    private function generateUiResponse(string $message, string $systemPrompt, array $history): array
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($this->mapHistoryMessages($history) as $item) {
            $messages[] = $item;
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $rawContent = $this->titanCoreAIService->generate($message, $messages, [], 'openai', config('titancore.ai.openai.model', 'gpt-4o-mini'));
            return $this->parseGeneratedResponse(
                $rawContent,
                $message
            );
        } catch (\Throwable $e) {
            Log::warning('TitanAssistService: TitanCoreAIService generate-ui call threw.', ['error' => $e->getMessage()]);
        }

        return $this->fallbackUiResponse($message);
    }

    /**
     * @return array{0: string, 1: list<array<string, mixed>>}
     */
    private function parseGeneratedResponse(string $raw, string $originalMessage): array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;

        $decoded = json_decode(trim($raw), true);

        if (is_array($decoded) && isset($decoded['message'])) {
            return [
                (string) ($decoded['message'] ?? $originalMessage),
                app(WidgetFactory::class)->normalize($decoded['parts'] ?? [], $originalMessage),
            ];
        }

        return [$raw !== '' ? $raw : 'Titan Zero is connected to the Business OS.', []];
    }
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;

        $decoded = json_decode(trim($raw), true);

        if (is_array($decoded) && isset($decoded['message'])) {
            return [
                (string) ($decoded['message'] ?? $originalMessage),
                app(WidgetFactory::class)->normalize($decoded['parts'] ?? [], $originalMessage),
            ];
        }

        return [$raw !== '' ? $raw : 'Titan Zero is connected to the Business OS.', []];
    }

    /**
     * @return array{0: string, 1: list<array<string, mixed>>}
     */
    private function fallbackUiResponse(string $message): array
    {
        $reply = 'Titan Zero is connected to the Business OS. How can I help you today?';

        return [
            $reply,
            [app(WidgetFactory::class)->makeFromIntent($message !== '' ? $message : 'status', $reply)],
        ];
    }
    {
        $reply = 'Titan Zero is connected to the Business OS. How can I help you today?';

        return [
            $reply,
            [app(WidgetFactory::class)->makeFromIntent($message !== '' ? $message : 'status', $reply)],
        ];
    }

    /**
     * @param  array<string, mixed>  $portalSnapshot
     */
    private function buildBusinessOsPrompt(string $appKey, string $page, array $portalSnapshot = []): string
    {
        $snapshotBlock = '';

        if ($appKey === 'portal' && $portalSnapshot !== []) {
            $snapshotBlock = "\nPortal customer data snapshot:\n" . json_encode($portalSnapshot, JSON_PRETTY_PRINT) . "\n";
        }

        return <<<PROMPT
You are Titan Zero, the intelligent AI assistant embedded in the Business OS platform.
Current context: app_key={$appKey}, page={$page}
{$snapshotBlock}

Respond ONLY with valid JSON matching this exact structure:
{
  "message": "<brief friendly summary (1-2 sentences)>",
  "parts": [
    {
      "id": "<unique-widget-id>",
      "kind": "<metric-card|line-chart|bar-chart|data-table|log-list|chat-thread|tool-call|settings-form|mcp-server-list|project-list>",
      "title": "<widget title>",
      "data": {}
    }
  ]
}

Rules:
- Always include at least one widget in "parts" relevant to the user's query.
- Use metric-card for single KPI values, data-table for lists, line-chart/bar-chart for trends.
- Keep "message" conversational and helpful.
- Do NOT include any text outside the JSON object.
PROMPT;
    }
    {
        $snapshotBlock = '';

        if ($appKey === 'portal' && $portalSnapshot !== []) {
            $snapshotBlock = "\nPortal customer data snapshot:\n" . json_encode($portalSnapshot, JSON_PRETTY_PRINT) . "\n";
        }

        return <<<PROMPT
You are Titan Zero, the intelligent AI assistant embedded in the Business OS platform.
Current context: app_key={$appKey}, page={$page}
{$snapshotBlock}

Respond ONLY with valid JSON matching this exact structure:
{
  "message": "<brief friendly summary (1-2 sentences)>",
  "parts": [
    {
      "id": "<unique-widget-id>",
      "kind": "<metric-card|line-chart|bar-chart|data-table|log-list|chat-thread|tool-call|settings-form|mcp-server-list|project-list>",
      "title": "<widget title>",
      "data": {}
    }
  ]
}

Rules:
- Always include at least one widget in "parts" relevant to the user's query.
- Use metric-card for single KPI values, data-table for lists, line-chart/bar-chart for trends.
- Keep "message" conversational and helpful.
- Do NOT include any text outside the JSON object.
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function portalSnapshot(string $appKey, array $context, int $defaultCompanyId): array
    {
        if ($appKey !== 'portal' || ! class_exists(WorkcorePortalDataService::class)) {
            return [];
        }

        $customerId = (int) ($context['customerId'] ?? $context['customer_id'] ?? 0);
        $companyId = (int) ($context['companyId'] ?? $context['company_id'] ?? $defaultCompanyId);

        if ($customerId <= 0 || $companyId <= 0) {
            return [];
        }

        try {
            /** @var WorkcorePortalDataService $workcore */
            $workcore = app(WorkcorePortalDataService::class);

            $snapshot = $workcore->getCustomerProfile($customerId, $companyId);

            if ($snapshot === []) {
                return [];
            }

            $snapshot['upcoming'] = $workcore->getUpcomingVisits($customerId, $companyId);
            $snapshot['balance'] = $workcore->getOutstandingBalance($customerId, $companyId);
            $snapshot['invoices'] = $workcore->getInvoices($customerId, $companyId);
            $snapshot['quotes'] = $workcore->getQuotes($customerId, $companyId);

            $snapshotJson = json_encode($snapshot);

            if (is_string($snapshotJson) && strlen($snapshotJson) > self::MAX_PORTAL_SNAPSHOT_BYTES) {
                $snapshot['upcoming'] = is_array($snapshot['upcoming'] ?? null)
                    ? array_slice($snapshot['upcoming'], 0, 3)
                    : [];
                $snapshot['invoices'] = is_array($snapshot['invoices'] ?? null)
                    ? array_slice($snapshot['invoices'], 0, 3)
                    : [];
                $snapshot['quotes'] = is_array($snapshot['quotes'] ?? null)
                    ? array_slice($snapshot['quotes'], 0, 3)
                    : [];

                Log::info('TitanZeroService: portal snapshot trimmed due to size', [
                    'customer_id' => $customerId,
                    'company_id' => $companyId,
                    'size' => strlen($snapshotJson),
                ]);
            }

            return $snapshot;
        } catch (\Throwable $e) {
            Log::warning('TitanZeroService: unable to build portal snapshot', ['error' => $e->getMessage()]);

            return [];
        }
    }
    {
        if ($appKey !== 'portal' || ! class_exists(WorkcorePortalDataService::class)) {
            return [];
        }

        $customerId = (int) ($context['customerId'] ?? $context['customer_id'] ?? 0);
        $companyId = (int) ($context['companyId'] ?? $context['company_id'] ?? $defaultCompanyId);

        if ($customerId <= 0 || $companyId <= 0) {
            return [];
        }

        try {
            /** @var WorkcorePortalDataService $workcore */
            $workcore = app(WorkcorePortalDataService::class);

            $snapshot = $workcore->getCustomerProfile($customerId, $companyId);

            if ($snapshot === []) {
                return [];
            }

            $snapshot['upcoming'] = $workcore->getUpcomingVisits($customerId, $companyId);
            $snapshot['balance'] = $workcore->getOutstandingBalance($customerId, $companyId);
            $snapshot['invoices'] = $workcore->getInvoices($customerId, $companyId);
            $snapshot['quotes'] = $workcore->getQuotes($customerId, $companyId);

            $snapshotJson = json_encode($snapshot);

            if (is_string($snapshotJson) && strlen($snapshotJson) > self::MAX_PORTAL_SNAPSHOT_BYTES) {
                $snapshot['upcoming'] = is_array($snapshot['upcoming'] ?? null)
                    ? array_slice($snapshot['upcoming'], 0, 3)
                    : [];
                $snapshot['invoices'] = is_array($snapshot['invoices'] ?? null)
                    ? array_slice($snapshot['invoices'], 0, 3)
                    : [];
                $snapshot['quotes'] = is_array($snapshot['quotes'] ?? null)
                    ? array_slice($snapshot['quotes'], 0, 3)
                    : [];

                Log::info('TitanZeroService: portal snapshot trimmed due to size', [
                    'customer_id' => $customerId,
                    'company_id' => $companyId,
                    'size' => strlen($snapshotJson),
                ]);
            }

            return $snapshot;
        } catch (\Throwable $e) {
            Log::warning('TitanZeroService: unable to build portal snapshot', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $history
     * @return array<int, array{role: string, content: string}>
     */
    private function mapHistoryMessages(array $history): array
    {
        return array_map(fn ($item) => [
            'role' => (string) ($item['role'] ?? 'user'),
            'content' => (string) ($item['content'] ?? ''),
        ], array_slice($history, -self::UI_CONTEXT_MESSAGE_LIMIT));
    }
    {
        return array_map(fn ($item) => [
            'role' => (string) ($item['role'] ?? 'user'),
            'content' => (string) ($item['content'] ?? ''),
        ], array_slice($history, -self::UI_CONTEXT_MESSAGE_LIMIT));
    }
}
