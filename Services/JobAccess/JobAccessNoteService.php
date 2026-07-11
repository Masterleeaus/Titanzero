<?php

namespace Modules\TitanZero\Services\JobAccess;

use Illuminate\Http\Request;
use Modules\TitanZero\Entities\JobAccessNote;
use Modules\TitanZero\Entities\JobAccessAuditLog;

/**
 * Server-side service for encrypted job access notes.
 *
 * Design constraints (Zero Knowledge):
 *  - This service NEVER handles plaintext.
 *  - It stores / retrieves opaque ciphertext blobs produced by the browser.
 *  - Key derivation and encryption/decryption happen entirely client-side.
 *  - The service is responsible only for persistence and audit logging.
 */
class JobAccessNoteService
{
    /**
     * Return all encrypted note envelopes for a job.
     * Callers should restrict this to the assigned cleaner or admins.
     */
    public function getNotesForJob(int $jobId): array
    {
        return JobAccessNote::where('job_id', $jobId)
            ->get(['field_name', 'ciphertext', 'iv_b64', 'assigned_user_id', 'version'])
            ->toArray();
    }

    /**
     * Store or update a single encrypted note field.
     * The caller supplies the ciphertext + IV produced by the browser.
     *
     * @param  int    $jobId
     * @param  string $fieldName   One of JobAccessNote::FIELDS
     * @param  string $ciphertext  Base64-encoded AES-GCM ciphertext
     * @param  string $ivB64       Base64-encoded 96-bit IV
     * @param  int    $assignedUserId  User who can decrypt
     * @param  int|null $companyId
     * @return JobAccessNote
     */
    public function storeNote(
        int $jobId,
        string $fieldName,
        string $ciphertext,
        string $ivB64,
        int $assignedUserId,
        ?int $companyId = null
    ): JobAccessNote {
        $note = JobAccessNote::firstOrNew([
            'job_id'     => $jobId,
            'field_name' => $fieldName,
        ]);

        $isNew = !$note->exists;

        $note->fill([
            'company_id'       => $companyId,
            'ciphertext'       => $ciphertext,
            'iv_b64'           => $ivB64,
            'assigned_user_id' => $assignedUserId,
            'version'          => $isNew ? 1 : ($note->version + 1),
        ]);
        $note->save();

        return $note;
    }

    /**
     * Re-encrypt a note for a new assignee.
     * Admin supplies the new ciphertext (re-encrypted client-side for the new cleaner).
     */
    public function reencryptNote(
        int $jobId,
        string $fieldName,
        string $newCiphertext,
        string $newIvB64,
        int $newAssignedUserId,
        ?int $companyId = null
    ): JobAccessNote {
        return $this->storeNote($jobId, $fieldName, $newCiphertext, $newIvB64, $newAssignedUserId, $companyId);
    }

    /**
     * Record an access audit event.
     */
    public function audit(
        int $jobId,
        string $action,
        ?int $userId,
        ?string $fieldName,
        Request $request,
        ?int $companyId = null
    ): void {
        JobAccessAuditLog::create([
            'company_id' => $companyId,
            'job_id'     => $jobId,
            'user_id'    => $userId,
            'action'     => $action,
            'field_name' => $fieldName,
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    /**
     * Retrieve the audit trail for a job (admin view).
     */
    public function getAuditForJob(int $jobId, int $perPage = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return JobAccessAuditLog::where('job_id', $jobId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
