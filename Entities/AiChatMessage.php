<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\TitanZero\Canvas\Entities\UserTiptapContent;

class AiChatMessage extends Model
{
    protected $table = 'titanzero_ai_chat_messages';

    protected $fillable = [
        'ai_chat_session_id',
        'user_id',
        'input',
        'output',
        'response',
        'hash',
        'credits',
        'words',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'ai_chat_session_id');
    }

    /**
     * Canvas (TipTap) document linked to this message.
     */
    public function tiptapContent(): HasOne
    {
        return $this->hasOne(UserTiptapContent::class, 'save_contentable_id')
            ->where('save_contentable_type', self::class);
    }
}
