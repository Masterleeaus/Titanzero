<?php

namespace Modules\TitanZero\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\TitanZero\DTO\Citation;
use Modules\TitanZero\DTO\RetrievalResult;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentChunk;
use Modules\TitanZero\Services\Citations\CitationResolver;

/**
 * Verifies the citation resolver maps retrieval results to structured Citations.
 */
class CitationResolverTest extends TestCase
{
    use RefreshDatabase;

    private CitationResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new CitationResolver();
    }

    // -------------------------------------------------------------------------
    // resolveOne — single chunk look-up
    // -------------------------------------------------------------------------

    public function test_resolves_known_chunk_to_citation(): void
    {
        $doc   = $this->seedDocument('Safe Work Procedures', 'https://example.com/swp');
        $chunk = $this->seedChunk($doc->id, 0, 'Always wear PPE when handling chemicals.');

        $citation = $this->resolver->resolveOne("{$doc->id}:0");

        $this->assertInstanceOf(Citation::class, $citation);
        $this->assertSame($doc->id, $citation->document_id);
        $this->assertSame('Safe Work Procedures', $citation->title);
        $this->assertSame(0, $citation->chunk_index);
        $this->assertSame($chunk->content_hash, $citation->content_hash);
    }

    public function test_citation_has_source_url_when_source_is_url(): void
    {
        $doc = $this->seedDocument('Health Guidelines', 'https://health.gov/guidelines');
        $this->seedChunk($doc->id, 0, 'Follow all health guidelines provided.');

        $citation = $this->resolver->resolveOne("{$doc->id}:0");

        $this->assertSame('https://health.gov/guidelines', $citation->source_url);
        $this->assertNull($citation->module_reference);
    }

    public function test_citation_has_module_reference_when_source_is_not_url(): void
    {
        $doc = $this->seedDocument('Cleaning SOP', 'TitanZero/sops/cleaning');
        $this->seedChunk($doc->id, 0, 'Cleaning standard operating procedure.');

        $citation = $this->resolver->resolveOne("{$doc->id}:0");

        $this->assertNull($citation->source_url);
        $this->assertSame('TitanZero/sops/cleaning', $citation->module_reference);
    }

    public function test_citation_excerpt_is_first_200_characters(): void
    {
        $longContent = str_repeat('A', 300);
        $doc         = $this->seedDocument('Long Document', 'TitanZero/docs/long');
        $this->seedChunk($doc->id, 0, $longContent);

        $citation = $this->resolver->resolveOne("{$doc->id}:0");

        $this->assertSame(200, mb_strlen($citation->excerpt));
        $this->assertSame(str_repeat('A', 200), $citation->excerpt);
    }

    public function test_citation_excerpt_equals_full_content_when_under_200_chars(): void
    {
        $shortContent = 'Short content.';
        $doc          = $this->seedDocument('Short Document', 'TitanZero/docs/short');
        $this->seedChunk($doc->id, 0, $shortContent);

        $citation = $this->resolver->resolveOne("{$doc->id}:0");

        $this->assertSame('Short content.', $citation->excerpt);
    }

    // -------------------------------------------------------------------------
    // Missing chunk ID — must return null, not throw
    // -------------------------------------------------------------------------

    public function test_missing_chunk_id_returns_null_no_exception(): void
    {
        $citation = $this->resolver->resolveOne('99999:0');

        $this->assertNull($citation);
    }

    public function test_malformed_chunk_id_returns_null_no_exception(): void
    {
        foreach (['', 'no-colon', ':0', '123:', 'abc:def'] as $badId) {
            $this->assertNull(
                $this->resolver->resolveOne($badId),
                "Expected null for malformed chunk id: '{$badId}'"
            );
        }
    }

    // -------------------------------------------------------------------------
    // resolveMany — collection mapping
    // -------------------------------------------------------------------------

    public function test_resolve_many_returns_parallel_array_of_citations(): void
    {
        $doc1   = $this->seedDocument('Doc One', 'TitanZero/docs/one');
        $doc2   = $this->seedDocument('Doc Two', 'TitanZero/docs/two');
        $chunk1 = $this->seedChunk($doc1->id, 0, 'Content of document one.');
        $chunk2 = $this->seedChunk($doc2->id, 0, 'Content of document two.');

        $results = [
            $this->makeResult("{$doc1->id}:0", $doc1->id, 0, 'Content of document one.'),
            $this->makeResult("{$doc2->id}:0", $doc2->id, 0, 'Content of document two.'),
        ];

        $citations = $this->resolver->resolveMany($results);

        $this->assertCount(2, $citations);
        $this->assertInstanceOf(Citation::class, $citations[0]);
        $this->assertInstanceOf(Citation::class, $citations[1]);
        $this->assertSame('Doc One', $citations[0]->title);
        $this->assertSame('Doc Two', $citations[1]->title);
    }

    public function test_resolve_many_returns_null_for_missing_chunks(): void
    {
        $doc   = $this->seedDocument('Known Doc', 'TitanZero/docs/known');
        $chunk = $this->seedChunk($doc->id, 0, 'Known content.');

        $results = [
            $this->makeResult("{$doc->id}:0", $doc->id, 0, 'Known content.'),
            $this->makeResult('99999:0', 99999, 0, 'Ghost content.'),
        ];

        $citations = $this->resolver->resolveMany($results);

        $this->assertCount(2, $citations);
        $this->assertInstanceOf(Citation::class, $citations[0]);
        $this->assertNull($citations[1]);
    }

    public function test_resolve_many_empty_input_returns_empty_array(): void
    {
        $this->assertSame([], $this->resolver->resolveMany([]));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function seedDocument(string $title, string $source, int $companyId = 1): TitanZeroDocument
    {
        return TitanZeroDocument::create([
            'company_id' => $companyId,
            'title'      => $title,
            'source'     => $source,
        ]);
    }

    private function seedChunk(int $docId, int $index, string $content): TitanZeroDocumentChunk
    {
        return TitanZeroDocumentChunk::create([
            'document_id'  => $docId,
            'chunk_index'  => $index,
            'content'      => $content,
            'content_hash' => sha1($content),
        ]);
    }

    private function makeResult(string $chunkId, int $docId, int $chunkIndex, string $text): RetrievalResult
    {
        return new RetrievalResult(
            chunk_id:    $chunkId,
            document_id: $docId,
            chunk_index: $chunkIndex,
            text:        $text,
            metadata:    [],
            score:       1.0,
        );
    }
}
