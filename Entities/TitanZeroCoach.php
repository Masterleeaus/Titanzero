<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroCoach extends Model
{
    protected $table = 'titanzero_coaches';

    protected $fillable = ['key','name','description','rules','is_enabled'];

    protected $casts = [
        'rules' => 'array',
        'is_enabled' => 'boolean',
    ];
}
