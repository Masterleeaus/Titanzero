<?php

declare(strict_types=1);

namespace Modules\TitanZero\Services;

use Illuminate\Support\Facades\Log;
use Modules\TitanZero\Entities\AiChatSession;
use Modules\TitanCore\Services\TitanCoreAIService; // Hypothetical service
use Throwable;

/**
 * AIFileChatService — TitanZero port of AiChatProFileChat v1.1.0.
 *
 * Uploads a document (PDF / DOCX / TXT) to the OpenAI FileSearch API,
 * creates a per-session vector store and stores the resulting IDs on the
 * {@see AiChatSession} model so that subsequent chat messages can reference
 * the document content.
 *
 * Setting gate: `titanzero.file_chat.allowed` (defaults to `true`).
 */
class AIFileChatService
{
    private array $files;
    private ?string $sessionId;
    private FileSearchService $fileSearchService;

    /**
     * @param  string|null  $filePaths   Comma-separated list of file paths or URLs.
     * @param  string|null  $sessionId   ID of the {@see AiChatSession} to attach to.
     */
    public function __construct(FileSearchService $fileSearchService, ?string $filePaths = '', ?string $sessionId = null)
    {
        $this->fileSearchService = $fileSearchService;
        $this->files     = $this->filterValidFiles($filePaths);
        $this->sessionId = $sessionId;
    }

    /**
     * Validate files and, when the feature flag is enabled, upload and attach
     * the document to the session's vector store.
     *
     * @return bool  `true` when the session already has (or has just gained)
     *               an active vector store reference; `false` otherwise.
     */
    public function validateAndAnalyzeFile(): bool
    {
        if (empty($this->files)) {
            // No new file — check whether the existing session already has one.
            try {
                $session = AiChatSession::findOrFail($this->sessionId);

                $hasPdfInHistory = $session->messages()
                    ->latest()
                    ->take(10)
                    ->get()
                    // `pdfPath` is a dynamic attribute set by the upstream MagicAI extension
                    // on message records that were sent alongside a document.
                    ->contains(fn ($msg) => ! empty($msg->pdfPath));

                // No longer directly managing OpenAI IDs here
                return false;
            } catch (Throwable $e) {
                Log::error('[TitanZero] AIFileChatService — no-file branch error', [
                    'session_id' => $this->sessionId,
                    'error'      => $e->getMessage(),
                ]);

                return false;
            }
        }

        if (! config('titanzero.file_chat.allowed', true)) {
            return false;
        }

        return $this->storeDocForChatSession();
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Upload the first valid file, create a vector store and persist the
     * resulting IDs on the session record.
     */
    private function storeDocForChatSession(): bool
    {
        try {
            if (empty($this->sessionId)) {
                return false;
            }

            $session  = AiChatSession::findOrFail($this->sessionId);
            $filePath = $this->files[0];

            // Resolve to an absolute disk path when not a URL and not already absolute
            $isUrl      = filter_var($filePath, FILTER_VALIDATE_URL);
            $isAbsolute = ! $isUrl && (str_starts_with($filePath, '/') || preg_match('/^[a-zA-Z]:/', $filePath));
            $diskPath   = $isUrl || $isAbsolute ? $filePath : public_path($filePath);

            $fileSearchService = $this->fileSearchService;

            $fileId  = $fileSearchService->uploadFile($diskPath);
            $vectors = $fileSearchService->createVectorStore(basename($filePath), $fileId);

            // No longer directly managing OpenAI IDs here
            $session->update([
                'doc_name'         => basename($filePath),
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('[TitanZero] AIFileChatService error', [
                'session_id' => $this->sessionId,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Filter the raw comma-separated path string down to reachable entries.
     */
    private function filterValidFiles(?string $filePaths): array
    {
        if (empty($filePaths)) {
            return [];
        }

        $paths = array_map('trim', explode(',', $filePaths));

        return array_values(array_filter($paths, static function (string $path): bool {
            // Full URL — treat as valid
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                return true;
            }

            // Absolute path on disk
            if (str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:/', $path)) {
                return file_exists($path);
            }

            // Relative path — check under public/
            return file_exists(public_path($path));
        }));
    }
}
