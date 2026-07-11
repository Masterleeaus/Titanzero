<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroChannel extends Model
{
    protected $table = 'titanzero_channels';

    protected $casts = [
        'config' => 'array',
        'health' => 'array',
        'enabled' => 'boolean',
    ];

    protected $fillable = [
        'company_id',
        'user_id',
        'key',
        'label',
        'enabled',
        'config',
        'health',
        'last_checked_at',
    ];
}
