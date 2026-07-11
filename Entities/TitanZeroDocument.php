<?php

namespace Modules\TitanZero\Entities;

use Modules\TitanZero\Entities\TitanZeroDocumentTag;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TitanZeroDocument extends Model
{
    protected $table = 'titanzero_documents';

    protected $fillable = [
        'company_id','title','source','storage_path','sha256','meta',
        'doc_type','authority_level','jurisdiction','is_superseded','preferred_weight','coach_override',
        'classification_confidence','classification_source','review_status','reviewed_by','reviewed_at',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function chunks(): HasMany
    {
        return $this->hasMany(TitanZeroDocumentChunk::class, 'document_id');
    }


    public function tags()
    {
        return $this->belongsToMany(TitanZeroDocumentTag::class, 'titanzero_document_tag', 'document_id', 'tag_id');
    }
}
