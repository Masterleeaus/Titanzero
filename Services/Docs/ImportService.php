<?php

namespace Modules\TitanZero\Services\Docs;

use Illuminate\Support\Facades\Storage;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentChunk;
use Modules\TitanZero\Entities\TitanZeroImport;

class ImportService
{
    public function __construct(
        protected PdfTextExtractor $extractor,
        protected TextCleaner $cleaner,
        protected Chunker $chunker
    ) {}

    public function importPdf(string $pdfPath, string $title, array $meta = []): TitanZeroImport
    {
        $sha = hash_file('sha256', $pdfPath);

        $doc = TitanZeroDocument::query()->firstOrCreate(
            ['sha256' => $sha],
            ['title' => $title, 'source' => 'upload', 'meta' => $meta]
        );

        $import = TitanZeroImport::query()->create([
            'document_id' => $doc->id,
            'status' => 'processing',
            'meta' => $meta,
        ]);

        $result = $this->extractor->extract($pdfPath);

        if (!empty($result['error'])) {
            $import->update(['status' => 'failed', 'message' => $result['error']]);
            return $import;
        }

        $text = $this->cleaner->clean($result['text'] ?? '');
        if ($text === '') {
            $import->update(['status' => 'failed', 'message' => 'Extracted empty text from PDF.']);
            return $import;
        }

        $chunks = $this->chunker->chunk($text, 1200);

        // Store chunks (idempotent by hash)
        foreach ($chunks as $idx => $content) {
            $hash = hash('sha256', $content);
            TitanZeroDocumentChunk::query()->updateOrCreate(
                ['document_id' => $doc->id, 'chunk_index' => $idx],
                ['content' => $content, 'content_hash' => $hash, 'meta' => ['len' => mb_strlen($content)]]
            );
        }

        $import->update(['status' => 'done', 'message' => 'Imported '.count($chunks).' chunks.']);
        return $import;
    }
}
