<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroImport extends Model
{
    protected $table = 'titanzero_imports';

    protected $fillable = [
        'document_id','status','message','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
