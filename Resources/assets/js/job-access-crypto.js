/**
 * TitanZero – Job Access Note Encryption
 *
 * Zero-knowledge client-side encryption for sensitive site access notes:
 *   - Door access codes
 *   - Alarm instructions
 *   - Key safe locations
 *   - General access notes
 *
 * Algorithm:  AES-GCM 256-bit
 * Key deriv:  PBKDF2-SHA-256 (310 000 iterations)
 * Salt:       jobId (UTF-8) appended to a static module prefix
 *
 * IMPORTANT: Plaintext NEVER leaves the browser. Only ciphertext + IV are
 * transmitted to and stored by the server.
 */

'use strict';

const TitanZeroJobCrypto = (() => {

    // ─── Constants ────────────────────────────────────────────────────────────

    const PBKDF2_ITERATIONS = 310_000;  // OWASP recommended minimum for PBKDF2-SHA-256 (2023)
    const PBKDF2_HASH       = 'SHA-256';
    const AES_KEY_BITS      = 256;
    const AES_ALGO          = 'AES-GCM';
    const IV_BYTES          = 12; // 96-bit IV recommended for AES-GCM
    const SALT_PREFIX       = 'titanzero:job-access:v1:';

    // ─── Utility helpers ──────────────────────────────────────────────────────

    /** Encode a string as UTF-8 bytes. */
    function toBytes(str) {
        return new TextEncoder().encode(str);
    }

    /** Decode UTF-8 bytes to a string. */
    function fromBytes(buf) {
        return new TextDecoder().decode(buf);
    }

    /** ArrayBuffer → base64 string. */
    function bufToB64(buf) {
        return btoa(String.fromCharCode(...new Uint8Array(buf)));
    }

    /** Base64 string → Uint8Array. */
    function b64ToBuf(b64) {
        const bin = atob(b64);
        const buf = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
        return buf;
    }

    /** Generate a cryptographically random IV. */
    function randomIv() {
        return crypto.getRandomValues(new Uint8Array(IV_BYTES));
    }

    // ─── Key derivation ───────────────────────────────────────────────────────

    /**
     * Derive an AES-GCM key from a session token + job ID.
     *
     * @param {string} sessionToken  User's session token (treated as a password).
     * @param {string|number} jobId  Job ID (used as the PBKDF2 salt component).
     * @returns {Promise<CryptoKey>}
     */
    async function deriveKey(sessionToken, jobId) {
        const keyMaterial = await crypto.subtle.importKey(
            'raw',
            toBytes(sessionToken),
            { name: 'PBKDF2' },
            false,
            ['deriveKey']
        );

        const salt = toBytes(SALT_PREFIX + String(jobId));

        return crypto.subtle.deriveKey(
            {
                name:       'PBKDF2',
                salt:       salt,
                iterations: PBKDF2_ITERATIONS,
                hash:       PBKDF2_HASH,
            },
            keyMaterial,
            { name: AES_ALGO, length: AES_KEY_BITS },
            false,          // not exportable
            ['encrypt', 'decrypt']
        );
    }

    // ─── Encryption ───────────────────────────────────────────────────────────

    /**
     * Encrypt a plaintext string.
     *
     * @param {string}     plaintext
     * @param {CryptoKey}  key         Derived via deriveKey().
     * @returns {Promise<{ciphertext: string, iv_b64: string}>}
     *   ciphertext – base64(AES-GCM ciphertext including 16-byte auth tag)
     *   iv_b64     – base64(96-bit random IV)
     */
    async function encrypt(plaintext, key) {
        const iv         = randomIv();
        const cipherBuf  = await crypto.subtle.encrypt(
            { name: AES_ALGO, iv },
            key,
            toBytes(plaintext)
        );
        return {
            ciphertext: bufToB64(cipherBuf),
            iv_b64:     bufToB64(iv),
        };
    }

    // ─── Decryption ───────────────────────────────────────────────────────────

    /**
     * Decrypt a ciphertext envelope.
     *
     * @param {string}     ciphertext  base64-encoded ciphertext from the server.
     * @param {string}     ivB64       base64-encoded IV from the server.
     * @param {CryptoKey}  key         Derived via deriveKey().
     * @returns {Promise<string>}  The recovered plaintext.
     */
    async function decrypt(ciphertext, ivB64, key) {
        const cipherBuf  = b64ToBuf(ciphertext);
        const iv         = b64ToBuf(ivB64);
        const plainBuf   = await crypto.subtle.decrypt(
            { name: AES_ALGO, iv },
            key,
            cipherBuf
        );
        return fromBytes(plainBuf);
    }

    // ─── High-level UI API ────────────────────────────────────────────────────

    /**
     * Save an access note for a job.
     * Derives the key, encrypts the plaintext, then POSTs ciphertext to the server.
     *
     * @param {object} opts
     * @param {string}        opts.apiBase        e.g. '/api'
     * @param {string}        opts.csrfToken
     * @param {string}        opts.sessionToken   User's session/API token
     * @param {number|string} opts.jobId
     * @param {string}        opts.fieldName      One of the JobAccessNote::FIELDS
     * @param {string}        opts.plaintext      The sensitive note text
     * @param {number}        opts.assignedUserId User who will decrypt
     * @returns {Promise<{status: string, version: number}>}
     */
    async function saveNote({ apiBase, csrfToken, sessionToken, jobId, fieldName, plaintext, assignedUserId }) {
        const key = await deriveKey(sessionToken, jobId);
        const { ciphertext, iv_b64 } = await encrypt(plaintext, key);

        const res = await fetch(`${apiBase}/titan-zero/job-access/${jobId}/notes`, {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ field_name: fieldName, ciphertext, iv_b64, assigned_user_id: assignedUserId }),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.error || `HTTP ${res.status}`);
        }
        return res.json();
    }

    /**
     * Load and decrypt all access notes for a job.
     *
     * @param {object} opts
     * @param {string}        opts.apiBase
     * @param {string}        opts.csrfToken
     * @param {string}        opts.sessionToken
     * @param {number|string} opts.jobId
     * @returns {Promise<Array<{field_name: string, plaintext: string}>>}
     */
    async function loadNotes({ apiBase, csrfToken, sessionToken, jobId }) {
        const res = await fetch(`${apiBase}/titan-zero/job-access/${jobId}/notes`, {
            headers: {
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.error || `HTTP ${res.status}`);
        }

        const { notes } = await res.json();
        const key = await deriveKey(sessionToken, jobId);

        const results = [];
        for (const note of notes) {
            try {
                const plaintext = await decrypt(note.ciphertext, note.iv_b64, key);
                results.push({ field_name: note.field_name, plaintext, assigned_user_id: note.assigned_user_id });
            } catch {
                // Wrong key (note belongs to a different cleaner) – skip silently
                results.push({ field_name: note.field_name, plaintext: null, assigned_user_id: note.assigned_user_id });
            }
        }
        return results;
    }

    /**
     * Re-encrypt a note for a new assignee.
     * Admin must first decrypt (using old key) then re-encrypt for the new cleaner.
     *
     * @param {object} opts
     * @param {string}        opts.apiBase
     * @param {string}        opts.csrfToken
     * @param {string}        opts.oldSessionToken  Admin decrypts with old key
     * @param {string}        opts.newSessionToken  New cleaner's session token
     * @param {number|string} opts.jobId
     * @param {string}        opts.fieldName
     * @param {number}        opts.newAssignedUserId
     * @returns {Promise<{status: string, version: number}>}
     */
    async function reencryptNote({ apiBase, csrfToken, oldSessionToken, newSessionToken, jobId, fieldName, newAssignedUserId }) {
        // Fetch ciphertext
        const res = await fetch(`${apiBase}/titan-zero/job-access/${jobId}/notes`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const { notes } = await res.json();
        const envelope = notes.find(n => n.field_name === fieldName);
        if (!envelope) throw new Error(`No note found for field: ${fieldName}`);

        // Decrypt with old key
        const oldKey   = await deriveKey(oldSessionToken, jobId);
        const plaintext = await decrypt(envelope.ciphertext, envelope.iv_b64, oldKey);

        // Re-encrypt with new key
        const newKey  = await deriveKey(newSessionToken, jobId);
        const { ciphertext: newCiphertext, iv_b64: newIv } = await encrypt(plaintext, newKey);

        const postRes = await fetch(`${apiBase}/titan-zero/job-access/${jobId}/notes/reencrypt`, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                field_name:       fieldName,
                ciphertext:       newCiphertext,
                iv_b64:           newIv,
                assigned_user_id: newAssignedUserId,
            }),
        });

        if (!postRes.ok) {
            const err = await postRes.json().catch(() => ({}));
            throw new Error(err.error || `HTTP ${postRes.status}`);
        }
        return postRes.json();
    }

    // ─── Public API ───────────────────────────────────────────────────────────

    return { deriveKey, encrypt, decrypt, saveNote, loadNotes, reencryptNote };

})();

// Make available as a global in the browser (no bundler required)
if (typeof window !== 'undefined') {
    window.TitanZeroJobCrypto = TitanZeroJobCrypto;
}
