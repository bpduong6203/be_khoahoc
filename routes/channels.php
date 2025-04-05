<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ConversationMember;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return ConversationMember::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->where('status', 'Active')
        ->exists();
});