<?php

namespace Modules\TitanZero\Services\Citations;

use Modules\TitanZero\DTO\Citation;
use Modules\TitanZero\DTO\RetrievalResult;
use Modules\TitanZero\Entities\TitanZeroDocumentChunk;

/**
 * CitationResolver — maps retrieval results to structured source citations.
 *
 * Given a `RetrievalResult[]` collection produced by ManifestDrivenRetrievalService,
 * this service looks up the corresponding document records and builds typed
 * `Citation` objects for use in AI responses and UI display.
 *
 * Contract:
 * - A missing or invalid chunk ID returns `null` — no exception is thrown.
 * - `source_url` is populated from `titanzero_documents.source` when it looks
 *   like a URL (starts with http/https); otherwise `module_reference` is set.
 * - `excerpt` is the first 200 characters of the chunk content.
 */
class CitationResolver
{
    private const EXCERPT_MAX_LENGTH = 200;

    /**
     * Resolve an array of RetrievalResults into Citation objects.
     *
     * @param  RetrievalResult[]  $results
     * @return array<int, Citation|null>  Indexed parallel to $results; null for unresolvable entries.
     */
    public function resolveMany(array $results): array
    {
        if (empty($results)) {
            return [];
        }

        // Pre-load all needed chunks in one query to avoid N+1.
        $chunkKeys = [];
        foreach ($results as $result) {
            $chunkKeys[] = [
                'document_id' => $result->document_id,
                'chunk_index' => $result->chunk_index,
            ];
        }

        $docIds = array_unique(array_column($chunkKeys, 'document_id'));

        $chunks = TitanZeroDocumentChunk::with('document')
            ->whereIn('document_id', $docIds)
            ->get()
            ->keyBy(fn ($c) => "{$c->document_id}:{$c->chunk_index}");

        $citations = [];
        foreach ($results as $result) {
            $citations[] = $this->buildFromChunk($result->chunk_id, $chunks);
        }

        return $citations;
    }

    /**
     * Resolve a single chunk ID string ("{document_id}:{chunk_index}") to a Citation.
     *
     * @param  string  $chunkId  Composite chunk identifier.
     * @return Citation|null     Null when the chunk is not found in the database.
     */
    public function resolveOne(string $chunkId): ?Citation
    {
        [$documentId, $chunkIndex] = $this->parseChunkId($chunkId);

        if ($documentId === null) {
            return null;
        }

        $chunk = TitanZeroDocumentChunk::with('document')
            ->where('document_id', $documentId)
            ->where('chunk_index', $chunkIndex)
            ->first();

        if ($chunk === null) {
            return null;
        }

        return $this->toCitation($chunk);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /** @param  \Illuminate\Support\Collection  $chunkMap  Keyed by "doc_id:chunk_index" */
    private function buildFromChunk(string $chunkId, \Illuminate\Support\Collection $chunkMap): ?Citation
    {
        $chunk = $chunkMap->get($chunkId);

        if ($chunk === null) {
            return null;
        }

        return $this->toCitation($chunk);
    }

    private function toCitation(TitanZeroDocumentChunk $chunk): Citation
    {
        $doc    = $chunk->document;
        $title  = $doc?->title ?? 'Unknown source';
        $source = $doc?->source ?? '';

        // Determine whether the stored source is a URL or a module path reference.
        $sourceUrl       = null;
        $moduleReference = null;

        if ($this->isUrl($source)) {
            $sourceUrl = $source;
        } elseif ($source !== '') {
            $moduleReference = $source;
        }

        $excerpt = mb_substr($chunk->content, 0, self::EXCERPT_MAX_LENGTH);

        return new Citation(
            document_id:       $chunk->document_id,
            title:             $title,
            chunk_index:       $chunk->chunk_index,
            content_hash:      $chunk->content_hash,
            source_url:        $sourceUrl,
            module_reference:  $moduleReference,
            excerpt:           $excerpt,
        );
    }

    /**
     * Parse a composite chunk identifier into its components.
     *
     * @return array{int|null, int}  [document_id, chunk_index]
     */
    private function parseChunkId(string $chunkId): array
    {
        $parts = explode(':', $chunkId, 2);

        if (count($parts) !== 2 || !ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
            return [null, 0];
        }

        return [(int) $parts[0], (int) $parts[1]];
    }

    /**
     * Determine whether a source string is a web URL.
     */
    private function isUrl(string $source): bool
    {
        return (bool) preg_match('/^https?:\/\//i', $source);
    }
}
