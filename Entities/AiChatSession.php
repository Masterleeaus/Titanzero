<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiChatSession extends Model
{
    protected $table = 'titanzero_ai_chat_sessions';

    protected $fillable = [
        'user_id',
        'company_id',
        'category_id',
        'title',
        'total_credits',
        'total_words',
        'is_guest',
        'is_pinned',
        'is_chatbot',
        'team_id',
        // AiChatProFileChat v1.1.0 — OpenAI FileSearch / vector store columns
        'openai_vector_id',
        'openai_file_id',
        'doc_name',
        'reference_url',
    ];

    protected $casts = [
        'is_guest'   => 'boolean',
        'is_pinned'  => 'boolean',
        'is_chatbot' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AiChatCategory::class, 'category_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'ai_chat_session_id');
    }
}
