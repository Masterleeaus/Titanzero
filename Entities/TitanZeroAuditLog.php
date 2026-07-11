<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroAuditLog extends Model
{
    protected $table = 'titanzero_audit_logs';

    protected $fillable = [
        'intent_run_id','event','payload_json','user_id'
    ];
}
