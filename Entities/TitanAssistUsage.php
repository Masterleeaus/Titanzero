<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TitanZeroUsage extends Model
{
    use HasFactory;

    protected $table = 'titan_assist_usages';

    protected $fillable = [
        'user_id',
        'company_id',
        'template_id',
        'tokens_used',
        'requests_count',
    ];
}
