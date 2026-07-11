<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroArtifact extends Model
{
    protected $table = 'titanzero_artifacts';

    protected $fillable = [
        'record_type','record_id','artifact_type','title',
        'content_json','storage_path','created_by'
    ];
}
