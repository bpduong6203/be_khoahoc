<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Models\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Events\MessageSent;

class MessageService
{
    /**
     * Lấy danh sách tin nhắn của cuộc trò chuyện
     */
    public function getMessages($conversationId, $userId, $perPage = 20)
    {
        // Kiểm tra người dùng có trong cuộc trò chuyện không
        $isMember = ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();
            
        if (!$isMember) {
            throw new \Exception('Bạn không có quyền truy cập cuộc trò chuyện này', 403);
        }
        
        // Lấy tin nhắn theo trang
        $messages = Message::where('conversation_id', $conversationId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        // Đánh dấu tin nhắn là đã đọc
        Message::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'status' => 'Read']);
            
        return $messages;
    }

    /**
     * Gửi tin nhắn mới
     */
    public function sendMessage($conversationId, $userId, $data)
    {
        // Kiểm tra người dùng có trong cuộc trò chuyện không
        $member = ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->where('status', 'Active')
            ->first();
            
        if (!$member) {
            throw new \Exception('Bạn không có quyền gửi tin nhắn trong cuộc trò chuyện này', 403);
        }
        
        // Xử lý file đính kèm
        $attachmentUrl = null;
        $attachmentType = null;
        
        if (isset($data['attachment']) && $data['attachment']) {
            $file = $data['attachment'];
            $attachmentUrl = $file->store('attachments', 'public');
            $attachmentType = $file->getClientMimeType();
        }
        
        // Tạo tin nhắn mới
        $message = Message::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'content' => $data['content'] ?? null,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => $attachmentType,
            'is_read' => false,
            'status' => 'Sent'
        ]);
        
        // Cập nhật thời gian của cuộc trò chuyện
        Conversation::where('id', $conversationId)
            ->update(['updated_at' => now()]);
            
        $message->load('user');
        
        // Broadcast sự kiện
        broadcast(new MessageSent($message))->toOthers();
        
        return $message;
    }

    /**
     * Đánh dấu tin nhắn đã đọc
     */
    public function markAsRead($conversationId, $userId)
    {
        // Kiểm tra người dùng có trong cuộc trò chuyện không
        $isMember = ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();
            
        if (!$isMember) {
            throw new \Exception('Bạn không có quyền truy cập cuộc trò chuyện này', 403);
        }
        
        // Đánh dấu tất cả tin nhắn là đã đọc
        $count = Message::where('conversation_id', $conversationId)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'status' => 'Read']);
            
        return $count;
    }

    /**
     * Xóa tin nhắn
     */
    public function deleteMessage($messageId, $userId)
    {
        $message = Message::with('conversation')->findOrFail($messageId);
        
        // Kiểm tra quyền xóa tin nhắn
        if ($message->user_id != $userId) {
            $isAdmin = ConversationMember::where('conversation_id', $message->conversation_id)
                ->where('user_id', $userId)
                ->where('member_role', 'Admin')
                ->exists();
                
            if (!$isAdmin) {
                throw new \Exception('Bạn không có quyền xóa tin nhắn này', 403);
            }
        }
        
        // Xóa file đính kèm nếu có
        if ($message->attachment_url) {
            Storage::disk('public')->delete($message->attachment_url);
        }
        
        // Đánh dấu tin nhắn là đã xóa
        $message->status = 'Deleted';
        $message->save();
        
        return $message;
    }
}