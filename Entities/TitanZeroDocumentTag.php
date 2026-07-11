<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroDocumentTag extends Model
{
    protected $table = 'titanzero_document_tags';
    protected $fillable = ['key','name','group'];
}
