<?php

namespace Modules\TitanZero\Http\Controllers\JobAccess;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Entities\JobAccessAuditLog;
use Modules\TitanZero\Entities\JobAccessNote;
use Modules\TitanZero\Services\JobAccess\JobAccessNoteService;

/**
 * REST endpoints for encrypted job access notes.
 *
 * Zero-knowledge contract:
 *   - POST /notes  – browser sends ciphertext + IV; server stores opaque blob.
 *   - GET  /notes  – server returns ciphertext + IV; browser decrypts locally.
 *   - POST /notes/reencrypt – admin re-encrypts for new assignee client-side,
 *                             then POSTs new ciphertext here.
 *   - GET  /audit  – admin-only paginated audit trail.
 *
 * Plaintext NEVER passes through these controllers.
 */
class JobAccessNoteController extends Controller
{
    public function __construct(protected JobAccessNoteService $service)
    {
    }

    /**
     * GET /api/titan-zero/job-access/{jobId}/notes
     * Returns ciphertext envelopes for the job.
     * Only the assigned cleaner or an admin may retrieve them.
     */
    public function index(Request $request, int $jobId): JsonResponse
    {
        $user = $request->user();

        if (!$this->canRead($user, $jobId)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $this->service->audit(
            $jobId,
            JobAccessAuditLog::ACTION_DECRYPT_REQUEST,
            $user?->id,
            null,
            $request,
            $this->companyId($user)
        );

        $notes = $this->service->getNotesForJob($jobId);

        return response()->json(['job_id' => $jobId, 'notes' => $notes]);
    }

    /**
     * POST /api/titan-zero/job-access/{jobId}/notes
     * Store a single encrypted note field.
     *
     * Body: { field_name, ciphertext, iv_b64, assigned_user_id }
     */
    public function store(Request $request, int $jobId): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'field_name'       => ['required', 'string', 'in:' . implode(',', JobAccessNote::FIELDS)],
            'ciphertext'       => ['required', 'string'],
            'iv_b64'           => ['required', 'string', 'max:64'],
            'assigned_user_id' => ['required', 'integer'],
        ]);

        $note = $this->service->storeNote(
            $jobId,
            $validated['field_name'],
            $validated['ciphertext'],
            $validated['iv_b64'],
            (int) $validated['assigned_user_id'],
            $this->companyId($user)
        );

        $this->service->audit(
            $jobId,
            JobAccessAuditLog::ACTION_ENCRYPT,
            $user?->id,
            $validated['field_name'],
            $request,
            $this->companyId($user)
        );

        return response()->json(['status' => 'ok', 'version' => $note->version], 201);
    }

    /**
     * POST /api/titan-zero/job-access/{jobId}/notes/reencrypt
     * Admin re-encrypts a note for a new assignee.
     *
     * Body: { field_name, ciphertext, iv_b64, assigned_user_id }
     */
    public function reencrypt(Request $request, int $jobId): JsonResponse
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['error' => 'Forbidden — admin only'], 403);
        }

        $validated = $request->validate([
            'field_name'       => ['required', 'string', 'in:' . implode(',', JobAccessNote::FIELDS)],
            'ciphertext'       => ['required', 'string'],
            'iv_b64'           => ['required', 'string', 'max:64'],
            'assigned_user_id' => ['required', 'integer'],
        ]);

        $note = $this->service->reencryptNote(
            $jobId,
            $validated['field_name'],
            $validated['ciphertext'],
            $validated['iv_b64'],
            (int) $validated['assigned_user_id'],
            $this->companyId($user)
        );

        $this->service->audit(
            $jobId,
            JobAccessAuditLog::ACTION_REENCRYPT,
            $user?->id,
            $validated['field_name'],
            $request,
            $this->companyId($user)
        );

        return response()->json(['status' => 'ok', 'version' => $note->version]);
    }

    /**
     * GET /api/titan-zero/job-access/{jobId}/audit
     * Paginated audit trail for a job (admin only).
     */
    public function audit(Request $request, int $jobId): JsonResponse
    {
        $user = $request->user();

        if (!$this->isAdmin($user)) {
            return response()->json(['error' => 'Forbidden — admin only'], 403);
        }

        $this->service->audit(
            $jobId,
            JobAccessAuditLog::ACTION_VIEW,
            $user?->id,
            null,
            $request,
            $this->companyId($user)
        );

        $paginator = $this->service->getAuditForJob($jobId);

        return response()->json($paginator);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function canRead(mixed $user, int $jobId): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        // Allow if the user is the assigned cleaner on any note for this job
        return JobAccessNote::where('job_id', $jobId)
            ->where('assigned_user_id', $user->id)
            ->exists();
    }

    private function isAdmin(mixed $user): bool
    {
        if ($user === null) {
            return false;
        }

        // Super-admin or account owner roles
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin') || $user->hasRole('super admin');
        }

        return isset($user->is_superadmin) && (bool) $user->is_superadmin;
    }

    private function companyId(mixed $user): ?int
    {
        if ($user === null) {
            return null;
        }
        return isset($user->company_id) ? (int) $user->company_id : null;
    }
}
