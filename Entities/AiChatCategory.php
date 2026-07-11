<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChatCategory extends Model
{
    protected $table = 'titanzero_ai_chat_categories';

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'slug',
        'role',
        'human_name',
        'chat_completions',
        'plan',
        'helps_with',
        'chatbot_id',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(AiChatSession::class, 'category_id');
    }
}
