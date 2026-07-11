<?php

namespace Modules\TitanZero\Actions\Handlers;

use Modules\TitanZero\Contracts\Actions\ActionHandlerInterface;
use Modules\TitanZero\Services\Retrieval\RetrievalEngine;
use Modules\TitanZero\ValueObjects\IntentObject;

class SummarizeStandardHandler implements ActionHandlerInterface
{
    public function __construct(protected RetrievalEngine $retrieval) {}

    public function supports(string $intent): bool
    {
        return $intent === 'summarize_standard';
    }

    public function validate(IntentObject $intent, array $context = []): array
    {
        return ['ok' => true, 'errors' => []];
    }

    public function execute(IntentObject $intent, array $context = []): array
    {
        $query = trim((string)($context['text'] ?? ''));
        if ($query === '') $query = 'standard';

        $coachKey = 'standards_compliance';
        $hits = $this->retrieval->searchWithFilters($query, [
            'coach_key' => $coachKey,
            'include_tags' => ['standards','compliance'],
        ], 5);

        $citations = array_map(function($h){
            return [
                'document_id' => $h['document_id'],
                'document_title' => $h['document_title'],
                'chunk_index' => $h['chunk_index'],
                'content_hash' => $h['content_hash'],
                'score' => $h['score'],
            ];
        }, $hits);

        return [
            'type' => 'summarize_standard',
            'summary' => 'Foundation result: top relevant excerpts retrieved. Next pass will generate a structured summary with citations.',
            'query' => $query,
            'citations' => $citations,
            'excerpts' => array_map(fn($h) => [
                'title' => $h['document_title'],
                'chunk_index' => $h['chunk_index'],
                'content' => mb_substr((string)$h['content'], 0, 900),
            ], $hits),
        ];
    }
}
