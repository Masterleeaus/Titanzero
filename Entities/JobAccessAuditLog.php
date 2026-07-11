<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable audit trail for access to encrypted job notes.
 */
class JobAccessAuditLog extends Model
{
    protected $table = 'titanzero_job_access_audit';

    protected $fillable = [
        'company_id',
        'job_id',
        'user_id',
        'action',
        'field_name',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'job_id'     => 'integer',
        'user_id'    => 'integer',
    ];

    /** Action constants */
    public const ACTION_ENCRYPT        = 'encrypt';
    public const ACTION_DECRYPT_REQUEST = 'decrypt_request';
    public const ACTION_REENCRYPT      = 'reencrypt';
    public const ACTION_VIEW           = 'view';
}
