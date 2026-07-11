<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores client-side AES-GCM ciphertext for sensitive job access fields.
 * Plaintext is never transmitted to or stored on the server.
 */
class JobAccessNote extends Model
{
    protected $table = 'titanzero_job_access_notes';

    protected $fillable = [
        'company_id',
        'job_id',
        'field_name',
        'ciphertext',
        'iv_b64',
        'assigned_user_id',
        'version',
    ];

    protected $casts = [
        'company_id'       => 'integer',
        'job_id'           => 'integer',
        'assigned_user_id' => 'integer',
        'version'          => 'integer',
    ];

    /** The sensitive field names that may be encrypted. */
    public const FIELDS = [
        'access_code',
        'alarm_instructions',
        'key_safe',
        'general_notes',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_user_id');
    }
}
