{{--
    TitanZero – Encrypted Job Access Notes Panel
    =============================================
    Zero-knowledge: ciphertext is stored server-side; plaintext only exists
    in the browser for the duration of the session. Never sent to or stored by
    the server.

    Required variables (injected from the parent view):
      $order   – FSMOrder instance (must have ->id, ->person_id)
      $user    – authenticated User instance (auth()->user())
--}}

@php
    use Modules\TitanZero\Entities\JobAccessNote;

    $tzJobId         = (int) $order->id;
    $tzAssignedUser  = $order->person_id ? (int) $order->person_id : null;
    $tzCurrentUserId = auth()->id();
    $tzIsAdmin       = auth()->check() && (
        (method_exists(auth()->user(), 'hasRole') && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super admin')))
        || (isset(auth()->user()->is_superadmin) && auth()->user()->is_superadmin)
    );
    $tzCanAccess = $tzIsAdmin || ($tzAssignedUser !== null && $tzCurrentUserId === $tzAssignedUser);
    $tzFields = [
        'access_code'        => ['label' => '🔑 Access Code',        'placeholder' => 'e.g. front door pin 1234'],
        'alarm_instructions' => ['label' => '🚨 Alarm Instructions', 'placeholder' => 'e.g. disarm within 30 s, code 5678'],
        'key_safe'           => ['label' => '🗝 Key Safe',           'placeholder' => 'e.g. blue key safe, left gate post, code 0000'],
        'general_notes'      => ['label' => '📝 Access Notes',       'placeholder' => 'Any other entry instructions…'],
    ];
@endphp

<div class="card mb-3 border-warning" id="tz-job-access-panel">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center bg-warning bg-opacity-10">
        <span>🔒 Encrypted Site Access Notes
            <span class="badge bg-warning text-dark ms-1 small">Zero Knowledge</span>
        </span>
        <div class="d-flex align-items-center gap-2">
            @if($tzCanAccess)
                <button type="button" class="btn btn-sm btn-outline-warning"
                        id="tz-load-notes-btn"
                        data-job-id="{{ $tzJobId }}">
                    👁 View Notes
                </button>
            @endif
            @if($tzIsAdmin)
                <a href="#" class="btn btn-sm btn-outline-secondary"
                   id="tz-show-audit-btn"
                   data-job-id="{{ $tzJobId }}">
                    📋 Audit Log
                </a>
            @endif
        </div>
    </div>

    <div class="card-body">
        {{-- Access-denied notice for non-assigned users --}}
        @if(!$tzCanAccess)
            <div class="alert alert-secondary mb-0">
                <strong>🔒 Access restricted.</strong>
                Only the assigned cleaner for this job can view or edit encrypted access notes.
            </div>
        @else
            {{-- Token entry prompt (shown before notes are loaded) --}}
            <div id="tz-token-prompt">
                <p class="text-muted small mb-2">
                    Enter your session passphrase to derive the decryption key locally.
                    <strong>This value is never sent to the server.</strong>
                </p>
                <div class="input-group input-group-sm mb-2" style="max-width: 440px;">
                    <input type="password" class="form-control" id="tz-session-token"
                           placeholder="Your session passphrase…" autocomplete="current-password">
                    <button class="btn btn-warning" type="button" id="tz-unlock-btn">
                        🔓 Unlock
                    </button>
                </div>
                <div id="tz-unlock-error" class="text-danger small d-none"></div>
            </div>

            {{-- Notes panel (hidden until unlocked) --}}
            <div id="tz-notes-panel" class="d-none">
                <div class="row g-2 mb-3" id="tz-notes-display">
                    @foreach($tzFields as $fieldKey => $fieldMeta)
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold">{{ $fieldMeta['label'] }}</label>
                        <div class="input-group input-group-sm">
                            <textarea class="form-control tz-note-textarea"
                                      id="tz-field-{{ $fieldKey }}"
                                      data-field="{{ $fieldKey }}"
                                      rows="2"
                                      placeholder="{{ $fieldMeta['placeholder'] }}"
                            ></textarea>
                            <button class="btn btn-outline-success tz-save-field-btn"
                                    data-field="{{ $fieldKey }}"
                                    title="Encrypt & save">
                                💾
                            </button>
                        </div>
                        <div class="tz-field-status small text-muted mt-1" id="tz-status-{{ $fieldKey }}"></div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="tz-lock-btn">🔒 Lock</button>
                    <span class="text-muted small">Notes are AES-GCM 256-bit encrypted before leaving your browser.</span>
                </div>
            </div>

            {{-- Re-encryption panel (admin only) --}}
            @if($tzIsAdmin)
            <div id="tz-reencrypt-panel" class="mt-3 d-none border-top pt-3">
                <h6 class="fw-semibold text-danger">⚠ Re-encrypt for New Cleaner</h6>
                <p class="small text-muted">
                    When re-assigning a job, use this panel to re-encrypt all access notes
                    for the new cleaner. You need both the old and new session passphrases.
                </p>
                <div class="row g-2" style="max-width: 640px;">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">Old Cleaner's Passphrase</label>
                        <input type="password" class="form-control form-control-sm" id="tz-old-token"
                               placeholder="Old passphrase…" autocomplete="off">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">New Cleaner's Passphrase</label>
                        <input type="password" class="form-control form-control-sm" id="tz-new-token"
                               placeholder="New passphrase…" autocomplete="off">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small">New Assigned User ID</label>
                        <input type="number" class="form-control form-control-sm" id="tz-new-user-id"
                               placeholder="{{ $tzAssignedUser ?? 'user id…' }}"
                               value="{{ $tzAssignedUser ?? '' }}">
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-sm btn-danger" id="tz-reencrypt-btn">
                            🔄 Re-encrypt All Fields
                        </button>
                        <span id="tz-reencrypt-status" class="small ms-2"></span>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <button type="button" class="btn btn-link btn-sm p-0 text-muted"
                        id="tz-toggle-reencrypt">Show re-encryption panel</button>
            </div>
            @endif

            {{-- Audit log panel --}}
            <div id="tz-audit-panel" class="mt-3 d-none border-top pt-3">
                <h6 class="fw-semibold">📋 Access Audit Log</h6>
                <div id="tz-audit-content" class="small text-muted">Loading…</div>
            </div>
        @endif
    </div>
</div>

{{-- Inline script – no external bundler required --}}
<script>
(function () {
    'use strict';

    const JOB_ID          = {{ $tzJobId }};
    const ASSIGNED_USER   = {{ $tzAssignedUser ?? 'null' }};
    const IS_ADMIN        = {{ $tzIsAdmin ? 'true' : 'false' }};
    const FIELDS          = @json(array_keys($tzFields));
    const API_BASE        = '/api';
    const CSRF            = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    let _key = null; // Derived CryptoKey – cleared on lock

    // ── Helpers ──────────────────────────────────────────────────────────────

    function qs(id) { return document.getElementById(id); }
    function show(el) { el && el.classList.remove('d-none'); }
    function hide(el) { el && el.classList.add('d-none'); }

    function setStatus(fieldKey, msg, cls = 'text-muted') {
        const el = qs(`tz-status-${fieldKey}`);
        if (el) { el.textContent = msg; el.className = `tz-field-status small mt-1 ${cls}`; }
    }

    // ── Unlock (derive key + load notes) ─────────────────────────────────────

    async function unlock() {
        const tokenEl = qs('tz-session-token');
        const errEl   = qs('tz-unlock-error');
        errEl.classList.add('d-none');

        const token = (tokenEl.value || '').trim();
        if (!token) { errEl.textContent = 'Enter your passphrase first.'; errEl.classList.remove('d-none'); return; }

        try {
            _key = await TitanZeroJobCrypto.deriveKey(token, JOB_ID);
        } catch (e) {
            errEl.textContent = 'Key derivation failed: ' + e.message;
            errEl.classList.remove('d-none');
            return;
        }

        // Fetch ciphertext envelopes and decrypt
        try {
            const res = await fetch(`${API_BASE}/titan-zero/job-access/${JOB_ID}/notes`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const { notes } = await res.json();

            for (const note of notes) {
                const textarea = qs(`tz-field-${note.field_name}`);
                if (!textarea) continue;
                try {
                    const pt = await TitanZeroJobCrypto.decrypt(note.ciphertext, note.iv_b64, _key);
                    textarea.value = pt;
                    setStatus(note.field_name, '🔓 Decrypted', 'text-success');
                } catch {
                    textarea.value = '';
                    setStatus(note.field_name, '🔒 Encrypted for another cleaner', 'text-muted');
                }
            }
        } catch (e) {
            errEl.textContent = 'Failed to load notes: ' + e.message;
            errEl.classList.remove('d-none');
            return;
        }

        hide(qs('tz-token-prompt'));
        show(qs('tz-notes-panel'));
        tokenEl.value = ''; // clear passphrase from DOM immediately
    }

    // ── Save a single field ───────────────────────────────────────────────────

    async function saveField(fieldKey) {
        if (!_key) { alert('Please unlock notes first.'); return; }
        const textarea     = qs(`tz-field-${fieldKey}`);
        const plaintext    = textarea.value;
        const assignedUser = ASSIGNED_USER || parseInt(qs('tz-new-user-id')?.value || '0', 10);

        if (!assignedUser) { setStatus(fieldKey, '⚠ No assigned user', 'text-danger'); return; }

        try {
            setStatus(fieldKey, 'Saving…', 'text-info');
            const { ciphertext, iv_b64 } = await TitanZeroJobCrypto.encrypt(plaintext, _key);
            const res = await fetch(`${API_BASE}/titan-zero/job-access/${JOB_ID}/notes`, {
                method:  'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-TOKEN':     CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ field_name: fieldKey, ciphertext, iv_b64, assigned_user_id: assignedUser }),
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            setStatus(fieldKey, '✔ Saved (encrypted)', 'text-success');
        } catch (e) {
            setStatus(fieldKey, '✘ Save failed: ' + e.message, 'text-danger');
        }
    }

    // ── Lock (clear key + wipe fields) ────────────────────────────────────────

    function lock() {
        _key = null;
        FIELDS.forEach(f => {
            const el = qs(`tz-field-${f}`);
            if (el) el.value = '';
            setStatus(f, '', 'text-muted');
        });
        hide(qs('tz-notes-panel'));
        show(qs('tz-token-prompt'));
    }

    // ── Re-encryption ─────────────────────────────────────────────────────────

    async function reencryptAll() {
        if (!IS_ADMIN) return;
        const oldToken  = (qs('tz-old-token').value  || '').trim();
        const newToken  = (qs('tz-new-token').value  || '').trim();
        const newUserId = parseInt(qs('tz-new-user-id').value || '0', 10);
        const statusEl  = qs('tz-reencrypt-status');

        if (!oldToken || !newToken || !newUserId) {
            statusEl.textContent = '⚠ Fill all fields.';
            statusEl.className   = 'small ms-2 text-danger';
            return;
        }

        statusEl.textContent = 'Re-encrypting…';
        statusEl.className   = 'small ms-2 text-info';

        let ok = 0, fail = 0;
        for (const field of FIELDS) {
            try {
                await TitanZeroJobCrypto.reencryptNote({
                    apiBase:          API_BASE,
                    csrfToken:        CSRF,
                    oldSessionToken:  oldToken,
                    newSessionToken:  newToken,
                    jobId:            JOB_ID,
                    fieldName:        field,
                    newAssignedUserId: newUserId,
                });
                ok++;
            } catch (e) {
                // Note may not exist yet for this field – not an error
                if (!String(e.message).includes('No note found')) fail++;
            }
        }

        statusEl.textContent = fail
            ? `⚠ ${ok} re-encrypted, ${fail} failed.`
            : `✔ ${ok} note(s) re-encrypted for new cleaner.`;
        statusEl.className = `small ms-2 ${fail ? 'text-danger' : 'text-success'}`;

        // Clear passphrases
        qs('tz-old-token').value = '';
        qs('tz-new-token').value = '';
    }

    // ── Audit log ─────────────────────────────────────────────────────────────

    async function loadAudit() {
        const panel = qs('tz-audit-panel');
        const content = qs('tz-audit-content');
        show(panel);
        content.textContent = 'Loading…';

        try {
            const res = await fetch(`${API_BASE}/titan-zero/job-access/${JOB_ID}/audit`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            const rows = (data.data || []);
            if (!rows.length) { content.textContent = 'No audit entries yet.'; return; }

            const table = document.createElement('table');
            table.className = 'table table-sm mb-0';
            table.innerHTML = `<thead class="table-light"><tr><th>ID</th><th>User</th><th>Action</th><th>Field</th><th>IP</th><th>Time</th></tr></thead>`;
            const tbody = document.createElement('tbody');
            rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${r.id}</td><td>${r.user_id ?? '—'}</td><td><code>${r.action}</code></td><td>${r.field_name ?? '—'}</td><td>${r.ip ?? '—'}</td><td>${r.created_at}</td>`;
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            content.innerHTML = '';
            content.appendChild(table);
        } catch (e) {
            content.textContent = 'Failed to load audit: ' + e.message;
        }
    }

    // ── Event wiring ─────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        qs('tz-unlock-btn')?.addEventListener('click', unlock);
        qs('tz-lock-btn')?.addEventListener('click', lock);
        qs('tz-load-notes-btn')?.addEventListener('click', function() {
            show(qs('tz-token-prompt'));
            qs('tz-session-token')?.focus();
        });

        // Per-field save buttons
        document.querySelectorAll('.tz-save-field-btn').forEach(btn => {
            btn.addEventListener('click', () => saveField(btn.dataset.field));
        });

        // Admin: re-encryption panel toggle
        qs('tz-toggle-reencrypt')?.addEventListener('click', function(e) {
            e.preventDefault();
            const panel = qs('tz-reencrypt-panel');
            if (panel) {
                panel.classList.toggle('d-none');
                this.textContent = panel.classList.contains('d-none')
                    ? 'Show re-encryption panel'
                    : 'Hide re-encryption panel';
            }
        });

        qs('tz-reencrypt-btn')?.addEventListener('click', reencryptAll);
        qs('tz-show-audit-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            loadAudit();
        });
    });

    // Also allow Enter key in token field
    qs('tz-session-token')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') unlock();
    });

})();
</script>

{{-- Load the crypto library once (idempotent guard) --}}
@once
<script src="{{ asset('vendor/titanzero/job-access-crypto.js') }}?v={{ filemtime(public_path('vendor/titanzero/job-access-crypto.js')) ?: 1 }}" defer></script>
@endonce
