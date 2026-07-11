<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitanZeroDocumentChunk extends Model
{
    protected $table = 'titanzero_document_chunks';

    protected $fillable = [
        'document_id','chunk_index','content','content_hash','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(TitanZeroDocument::class, 'document_id');
    }
}
