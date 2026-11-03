<?php
// app/Models/ChatMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = ['chat_conversation_id', 'role', 'content', 'links', 'is_crisis', 'rating', 'input_tokens', 'output_tokens'];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'is_crisis' => 'boolean',
            'rating' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }
}
