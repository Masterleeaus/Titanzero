<?php

namespace Modules\TitanZero\Services;

use Illuminate\Support\Str;
use Modules\TitanCore\Services\AgentRegistryService;
use Modules\TitanCore\Services\KbCollectionService;
use Modules\TitanCore\Services\KnowledgeSearchService;
use Modules\TitanCore\Contracts\AI\ClientInterface;
use Modules\TitanEchoAssist\Services\AgentPlaybookService;

/**
 * Titan Zero Gateway Service
 *
 * Pass 4:
 * - Enforces specialist knowledge partitions (agent -> kb_collection_key).
 * - Performs retrieval from TitanCore KB engine.
 * - Generates grounded drafts via TitanCore AI client (if configured).
 *
 * Safety:
 * - If AI provider is not configured / errors, returns retrieval-only output with a clear message.
 */
class ZeroGateway
{
    public function __construct(
        protected AgentRegistryService $agents,
        protected KbCollectionService $kb,
        protected KnowledgeSearchService $search,
        protected ClientInterface $ai,
        protected AgentPlaybookService $playbooks,
    ) {}

    public function proposeDocument(array $envelope, ?int $tenantId = null): array
    {
        $auditId = (string) Str::uuid();

        $kbKey = $envelope['kb_collection_key'] ?? 'kb_general_cleaning';
        $collection = $this->kb->resolve($kbKey, $tenantId);

        $query = (string) ($envelope['query'] ?? ($envelope['title'] ?? '') ?: ($envelope['input'] ?? ''));
        $retrieved = $this->search->searchCollection($kbKey, $query ?: 'document', $tenantId, 6);

        $context = $this->formatContext($retrieved);
        $userAsk = $this->formatUserAsk($envelope);

        $messages = [
            ['role' => 'system', 'content' => "You are Titan Zero. Produce a helpful draft grounded in the provided context. If context is insufficient, ask for missing info. Always be specific and avoid inventing facts."],
            ['role' => 'system', 'content' => "KNOWLEDGE CONTEXT (use these facts; cite titles when relevant):\n".$context],
            ['role' => 'user', 'content' => $userAsk],
        ];

        $draft = null; $aiError = null;
        try {
            $resp = $this->ai->chat($messages, ['temperature' => 0.2]);
            if (!($resp['ok'] ?? false)) {
                $aiError = $resp['reason'] ?? 'AI provider error';
            } else {
                $draft = (string) ($resp['content'] ?? '');
            }
        } catch (\Throwable $e) {
            $aiError = $e->getMessage();
        }

        return [
            'status' => 'ok',
            'audit_id' => $auditId,
            'mode' => 'proposed',
            'kb_collection_key' => $kbKey,
            'kb_collection' => $collection,
            'draft_text' => $draft ?: "[PASS4] Retrieval ready. AI generation unavailable: ".($aiError ?: 'unknown'),
            'risk' => 'green',
            'citations' => $this->citations($retrieved),
            'retrieved' => $retrieved,
        ];
    }

    public function runAgent(array $envelope, ?int $tenantId = null): array
    {
        $auditId = (string) Str::uuid();

        $agentSlug = $envelope['agent_slug'] ?? 'unknown';
        $agent = $this->agents->resolve($agentSlug, $tenantId);

        // Specialist partition: agent decides KB key, not user input
        $kbKey = $agent['kb_collection_key'] ?? 'kb_general_cleaning';
        $collection = $this->kb->resolve($kbKey, $tenantId);

        $playbook = $this->playbooks->get($agentSlug);
        $system = (string)($playbook['system'] ?? "You are a specialist agent. Use only provided context.");
        $schema = $playbook['output_schema'] ?? null;
        $mustAsk = $playbook['must_ask'] ?? [];
        $forbidden = $playbook['forbidden'] ?? [];

        $input = $envelope['input'] ?? $envelope;

        $query = is_string($input) ? $input : json_encode($input);
        $retrieved = $this->search->searchCollection($kbKey, $query ?: $agentSlug, $tenantId, 6);
        $context = $this->formatContext($retrieved);

        $consConfiguret = '';
        if (!empty($mustAsk)) $consConfiguret .= "REQUIRED QUESTIONS if missing: ".implode(', ', $mustAsk)."\n";
        if (!empty($forbidden)) $consConfiguret .= "FORBIDDEN TOPICS: ".implode(', ', $forbidden)."\n";
        if ($schema) $consConfiguret .= "OUTPUT MUST MATCH THIS JSON SHAPE (keys only; values as appropriate):\n".json_encode($schema, JSON_PRETTY_PRINT)."\n";

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'system', 'content' => "KNOWLEDGE CONTEXT:\n".$context],
            ['role' => 'system', 'content' => $consConfiguret],
            ['role' => 'user', 'content' => "INPUT:\n".(is_string($input) ? $input : json_encode($input, JSON_PRETTY_PRINT))],
        ];

        $content = null; $aiError = null;
        try {
            $resp = $this->ai->chat($messages, ['temperature' => 0.2]);
            if (!($resp['ok'] ?? false)) {
                $aiError = $resp['reason'] ?? 'AI provider error';
            } else {
                $content = (string) ($resp['content'] ?? '');
            }
        } catch (\Throwable $e) {
            $aiError = $e->getMessage();
        }

        return [
            'status' => 'ok',
            'audit_id' => $auditId,
            'mode' => 'proposed',
            'agent' => $agent,
            'kb_collection_key' => $kbKey,
            'kb_collection' => $collection,
            'result' => [
                'message' => $content ?: "[PASS4] Retrieval ready. AI generation unavailable: ".($aiError ?: 'unknown'),
                'structured' => [
                    'agent_slug' => $agentSlug,
                    'input' => $input,
                ],
            ],
            'risk' => 'green',
            'citations' => $this->citations($retrieved),
            'retrieved' => $retrieved,
        ];
    }

    public function ingestSignal(array $envelope, ?int $tenantId = null): array
    {
        $auditId = (string) Str::uuid();

        $signalId = null;
        if (class_exists(\App\Extensions\TitanPulse\Services\SignalBus\SignalEmitter::class)) {
            try {
                $signalId = \App\Extensions\TitanPulse\Services\SignalBus\SignalEmitter::emit(
                    (string) ($envelope['type'] ?? 'unknown'),
                    (string) ($envelope['subject_type'] ?? 'unknown'),
                    isset($envelope['subject_id']) && is_numeric($envelope['subject_id']) ? (int) $envelope['subject_id'] : null,
                    is_array($envelope['payload'] ?? null) ? $envelope['payload'] : $envelope,
                    [
                        'team_id' => $tenantId,
                        'company_id' => $tenantId,
                        'source' => 'titanzero.gateway',
                        'idempotency_key' => $envelope['idempotency_key'] ?? null,
                    ],
                );
            } catch (\Throwable) {
                // Keep TitanZero ingestion safe-by-default if signal persistence is unavailable.
            }
        }

        return [
            'status' => 'ok',
            'audit_id' => $auditId,
            'mode' => 'ingested',
            'signal_id' => $signalId,
            'signal' => [
                'type' => $envelope['type'] ?? 'unknown',
                'payload' => $envelope['payload'] ?? $envelope,
                'tenant_id' => $tenantId,
            ],
        ];
    }

    private function formatContext(array $retrieved): string
    {
        if (empty($retrieved)) return "(no relevant knowledge found)";
        $out = [];
        foreach ($retrieved as $i => $r) {
            $title = $r['document_title'] ?? 'Untitled';
            $out[] = "### Source ".($i+1).": {$title}\n".$r['content'];
        }
        return implode("\n\n", $out);
    }

    private function citations(array $retrieved): array
    {
        $c = [];
        foreach ($retrieved as $r) {
            $c[] = [
                'document_id' => $r['document_id'] ?? null,
                'document_title' => $r['document_title'] ?? null,
                'chunk_id' => $r['chunk_id'] ?? null,
                'score' => $r['score'] ?? null,
            ];
        }
        return $c;
    }

    private function formatUserAsk(array $envelope): string
    {
        $title = $envelope['title'] ?? null;
        $type = $envelope['type'] ?? null;
        $input = $envelope['input'] ?? null;
        $q = $envelope['query'] ?? null;

        $parts = [];
        if ($type) $parts[] = "DOC TYPE: {$type}";
        if ($title) $parts[] = "TITLE: {$title}";
        if ($q) $parts[] = "REQUEST: {$q}";
        if ($input) $parts[] = "INPUT:\n".(is_string($input) ? $input : json_encode($input, JSON_PRETTY_PRINT));

        return implode("\n\n", $parts ?: ["Draft a document based on the provided input."]);
    }
}
