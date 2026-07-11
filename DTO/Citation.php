<?php

namespace Modules\TitanZero\DTO;

/**
 * Citation — structured reference to a knowledge source.
 *
 * Produced by CitationResolver from a RetrievalResult. Carries enough
 * information for UI display, API responses, and audit trails.
 */
class Citation
{
    /**
     * @param  int          $document_id       Primary key of the source document
     * @param  string       $title             Human-readable document title
     * @param  int          $chunk_index       Zero-based chunk position within the document
     * @param  string       $content_hash      Content fingerprint of the chunk (algorithm determined by indexer)
     * @param  string|null  $source_url        Public URL to the source (if web-accessible)
     * @param  string|null  $module_reference  Module-scoped path (e.g. "TitanZero/policies/example")
     * @param  string       $excerpt           Short text excerpt from the chunk for inline display
     */
    public function __construct(
        public readonly int     $document_id,
        public readonly string  $title,
        public readonly int     $chunk_index,
        public readonly string  $content_hash,
        public readonly ?string $source_url,
        public readonly ?string $module_reference,
        public readonly string  $excerpt,
    ) {}
}
