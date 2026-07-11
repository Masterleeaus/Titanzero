<?php

namespace Modules\TitanZero\Console\Commands;

use Illuminate\Console\Command;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentTag;
use Modules\TitanZero\Entities\TitanZeroDocumentChunk;
use Modules\TitanZero\Services\Docs\DocumentClassifierV2;

class TitanZeroClassifyDocsV2 extends Command
{
    protected $signature = 'titan:classify-docs-v2 {--limit=500} {--dry-run} {--min-confidence=35}';
    protected $description = 'Auto-classify docs using TOC/first pages/chunk evidence and set confidence + review queue';

    public function handle(DocumentClassifierV2 $classifier): int
    {
        $limit = (int)$this->option('limit');
        $dry = (bool)$this->option('dry-run');
        $min = (int)$this->option('min-confidence');

        $tagsByKey = TitanZeroDocumentTag::query()->get()->keyBy('key');

        $docs = TitanZeroDocument::query()->orderByDesc('id')->limit($limit)->get();
        $updated = 0;

        foreach ($docs as $doc) {
            // evidence text = first few chunks concatenated
            $chunks = TitanZeroDocumentChunk::query()
                ->where('document_id', $doc->id)
                ->orderBy('chunk_index')
                ->limit(5)
                ->pluck('content')
                ->toArray();
            $evidence = implode("\n\n", $chunks);

            $res = $classifier->classify($doc, $evidence);

            $changes = [];

            foreach (['doc_type','authority_level','jurisdiction'] as $k) {
                if (!empty($res[$k]) && empty($doc->{$k})) {
                    $doc->{$k} = $res[$k];
                    $changes[] = $k;
                }
            }

            if (!empty($res['is_superseded']) && !$doc->is_superseded) {
                $doc->is_superseded = true;
                $changes[] = 'is_superseded';
            }

            $confidence = (int)($res['confidence'] ?? 0);
            if ($confidence > 0) {
                $doc->classification_confidence = $confidence;
                $doc->classification_source = 'toc_v2';
                $changes[] = 'classification_confidence';
            }

            // review queue: pending if low confidence or no tags
            $doc->review_status = ($confidence >= $min && count(($res['tag_keys'] ?? [])) > 0) ? 'approved' : 'pending';
            $changes[] = 'review_status';

            $tagKeys = $res['tag_keys'] ?? [];
            $tagIds = [];
            foreach ($tagKeys as $key) {
                if (isset($tagsByKey[$key])) $tagIds[] = $tagsByKey[$key]->id;
            }

            if (count($changes) > 0) {
                if (!$dry) {
                    $doc->save();
                    if (count($tagIds) > 0) {
                        $existing = $doc->tags()->pluck('id')->toArray();
                        $merged = array_values(array_unique(array_merge($existing, $tagIds)));
                        $doc->tags()->sync($merged);
                    }
                }
                $updated++;
                $this->line("Doc {$doc->id}: conf={$confidence} status={$doc->review_status}");
            }
        }

        $this->info(($dry ? '[DRY RUN] ' : '') . "Done. Processed {$updated} docs.");
        return self::SUCCESS;
    }
}
