<?php

namespace Modules\TitanZero\Console\Commands;

use Illuminate\Console\Command;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentTag;
use Modules\TitanZero\Services\Docs\DocumentClassifier;

class TitanZeroClassifyDocs extends Command
{
    protected $signature = 'titan:classify-docs {--limit=500} {--dry-run}';
    protected $description = 'Auto-classify Titan Zero documents into tags/metadata using title + filename heuristics';

    public function handle(DocumentClassifier $classifier): int
    {
        $limit = (int)$this->option('limit');
        $dry = (bool)$this->option('dry-run');

        $tagsByKey = TitanZeroDocumentTag::query()->get()->keyBy('key');

        $docs = TitanZeroDocument::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $updated = 0;

        foreach ($docs as $doc) {
            $res = $classifier->classify($doc);

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

            $tagKeys = $res['tag_keys'] ?? [];
            $tagIds = [];
            foreach ($tagKeys as $key) {
                if (isset($tagsByKey[$key])) $tagIds[] = $tagsByKey[$key]->id;
            }

            if (count($tagIds) > 0) {
                $existing = $doc->tags()->pluck('id')->toArray();
                $merged = array_values(array_unique(array_merge($existing, $tagIds)));
                if (count($merged) !== count($existing)) {
                    $changes[] = 'tags';
                }
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
                $this->line("Doc {$doc->id}: ".implode(', ', $changes));
            }
        }

        $this->info(($dry ? '[DRY RUN] ' : '') . "Done. Updated {$updated} docs.");
        return self::SUCCESS;
    }
}
