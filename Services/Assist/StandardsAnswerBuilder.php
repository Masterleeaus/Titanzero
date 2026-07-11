<?php

namespace Modules\TitanZero\Services\Assist;

use Modules\TitanZero\Services\Retrieval\RetrievalEngine;

/**
 * Pass 7: Deterministic "standards-grounded" helper without AI.
 * Takes user question + page context summary, pulls relevant chunks, returns structured cards.
 */
class StandardsAnswerBuilder
{
    public function __construct(protected RetrievalEngine $retrieval) {}

    public function build(string $question, array $pageContext = []): array
    {
        $results = $this->retrieval->search($question, 5);

        $summary = $this->summarizeDeterministically($question, $pageContext, $results);

        return [
            'ok' => true,
            'mode' => 'standards_grounded',
            'question' => $question,
            'cards' => [
                [
                    'type' => 'text',
                    'title' => 'Guidance (Standards-grounded)',
                    'content' => $summary,
                ],
                [
                    'type' => 'citations',
                    'title' => 'Citations (snippets)',
                    'items' => array_map(function($r){
                        return [
                            'document_id' => $r['document_id'],
                            'document_title' => $r['document_title'],
                            'chunk_index' => $r['chunk_index'],
                            'content_hash' => $r['content_hash'],
                            'preview' => mb_substr($r['content'], 0, 420),
                        ];
                    }, $results),
                ],
            ],
            'results' => $results,
        ];
    }

    protected function summarizeDeterministically(string $question, array $pageContext, array $results): string
    {
        $lines = [];
        $lines[] = "Question: ".$question;

        if (!empty($pageContext['url']) || !empty($pageContext['title'])) {
            $lines[] = "Page: ".trim(($pageContext['title'] ?? '').' '.($pageContext['url'] ?? ''));
        }

        if (empty($results)) {
            $lines[] = "No matching standards snippets were found in the current library for this query.";
            $lines[] = "Try importing the relevant standard/guideline PDF, or search with more specific keywords.";
            return implode("\n", $lines);
        }

        $lines[] = "Top matches were found in your standards library. Review the citations below and ensure your actions comply with the referenced requirements.";

        // Add a small "next steps" block based on presence of form fields
        if (!empty($pageContext['fields']) && is_array($pageContext['fields'])) {
            $missing = [];
            foreach ($pageContext['fields'] as $f) {
                if (!empty($f['required']) && (string)($f['value'] ?? '') === '') {
                    $missing[] = $f['label'] ?? $f['name'] ?? 'field';
                }
            }
            if ($missing) {
                $lines[] = "Form completeness hint: these required fields appear empty: ".implode(', ', array_slice($missing, 0, 8)).".";
            }
        }

        $lines[] = "Next step: open the relevant citation chunk(s) and apply the requirement to the current record (quote/job/task/etc).";
        return implode("\n", $lines);
    }


    public function buildWithFilters(string $question, array $pageContext, array $filters, array $coachMeta = []): array
    {
        $results = $this->retrieval->searchWithFilters($question, $filters, 5);

        $summary = $this->summarizeDeterministically($question, $pageContext, $results);

        return [
            'ok' => true,
            'mode' => 'coach_grounded',
            'coach' => [
                'key' => $coachMeta['key'] ?? ($filters['coach_key'] ?? null),
                'name' => $coachMeta['name'] ?? null,
            ],
            'question' => $question,
            'cards' => [
                [
                    'type' => 'text',
                    'title' => ($coachMeta['name'] ?? 'Coach') . ' Guidance',
                    'content' => $summary,
                ],
                [
                    'type' => 'citations',
                    'title' => 'Citations (snippets)',
                    'items' => array_map(function($r){
                        return [
                            'document_id' => $r['document_id'],
                            'document_title' => $r['document_title'],
                            'chunk_index' => $r['chunk_index'],
                            'content_hash' => $r['content_hash'],
                            'preview' => mb_substr($r['content'], 0, 420),
                            'doc_meta' => $r['doc'] ?? null,
                        ];
                    }, $results),
                ],
            ],
            'results' => $results,
        ];
    }
}
