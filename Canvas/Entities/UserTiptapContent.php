<?php

namespace Modules\TitanZero\Canvas\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserTiptapContent extends Model
{
    protected $table = 'user_tiptap_contents';

    protected $fillable = [
        'user_id',
        'save_contentable_id',
        'save_contentable_type',
        'title',
        'input',
        'output',
    ];

    public function saveContentable(): MorphTo
    {
        return $this->morphTo('saveContentable', 'save_contentable_type', 'save_contentable_id');
    }
}
