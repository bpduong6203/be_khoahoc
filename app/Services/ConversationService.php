<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConversationService
{
    /**
     * Lấy danh sách cuộc trò chuyện của người dùng
     */
    public function getUserConversations($userId)
    {
        return Conversation::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['members.user', 'messages' => function ($query) {
                $query->latest()->take(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Tạo cuộc trò chuyện mới
     */
    public function createConversation($data, $userId)
    {
        // Tạo cuộc trò chuyện
        $conversation = Conversation::create([
            'id' => Str::uuid(),
            'title' => $data['title'] ?? null,
            'course_id' => $data['course_id'] ?? null,
            'type' => $data['type'],
            'status' => 'Active'
        ]);

        // Thêm người tạo vào cuộc trò chuyện với vai trò Admin
        ConversationMember::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'member_role' => 'Admin',
            'status' => 'Active'
        ]);

        // Thêm các thành viên khác
        if (isset($data['members']) && is_array($data['members'])) {
            foreach ($data['members'] as $memberId) {
                if ($memberId != $userId) {
                    ConversationMember::create([
                        'id' => Str::uuid(),
                        'conversation_id' => $conversation->id,
                        'user_id' => $memberId,
                        'member_role' => 'Member',
                        'status' => 'Active'
                    ]);
                }
            }
        }

        return $conversation->load(['members']);
    }

    /**
     * Lấy thông tin cuộc trò chuyện
     */
    public function getConversation($id, $userId)
    {
        // Kiểm tra người dùng có trong cuộc trò chuyện không
        $isMember = ConversationMember::where('conversation_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if (!$isMember) {
            throw new \Exception('Bạn không có quyền truy cập cuộc trò chuyện này', 403);
        }

        return Conversation::with(['members.user'])
            ->findOrFail($id);
    }

    /**
     * Tạo cuộc trò chuyện với giảng viên
     */
    public function createTeacherConversation($courseId, $userId)
    {
        $course = Course::with('teacher')->findOrFail($courseId);

        // Kiểm tra người dùng đã đăng ký khóa học chưa
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            throw new \Exception('Bạn chưa đăng ký khóa học này', 403);
        }

        // Check payment status if needed
        if ($enrollment->payment_status !== 'Completed') {
            throw new \Exception('Bạn cần thanh toán khóa học để chat với giảng viên', 403);
        }

        // Kiểm tra đã có cuộc trò chuyện với giảng viên chưa
        $existingConversation = Conversation::where('course_id', $courseId)
            ->where('type', 'Private')
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('members', function ($query) use ($course) {
                $query->where('user_id', $course->user_id);
            })
            ->first();

        if ($existingConversation) {
            return $existingConversation->load('members.user');
        }

        // Tạo cuộc trò chuyện mới
        $title = "Hỗ trợ khóa học: " . $course->title;
        $conversation = Conversation::create([
            'id' => Str::uuid(),
            'title' => $title,
            'course_id' => $courseId,
            'type' => 'Private',
            'status' => 'Active'
        ]);

        // Thêm học viên vào cuộc trò chuyện
        ConversationMember::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'member_role' => 'Member',
            'status' => 'Active'
        ]);

        // Thêm giảng viên vào cuộc trò chuyện
        ConversationMember::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversation->id,
            'user_id' => $course->user_id,
            'member_role' => 'Admin',
            'status' => 'Active'
        ]);

        return $conversation->load('members.user');
    }

    /**
     * Thêm thành viên vào cuộc trò chuyện
     */
    public function addMember($conversationId, $memberId, $userId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Kiểm tra người thêm có quyền không
        $isAdmin = ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->where('member_role', 'Admin')
            ->exists();

        if (!$isAdmin) {
            throw new \Exception('Bạn không có quyền thêm thành viên vào cuộc trò chuyện này', 403);
        }

        // Kiểm tra thành viên đã tồn tại chưa
        $existingMember = ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $memberId)
            ->first();

        if ($existingMember) {
            if ($existingMember->status == 'Left') {
                $existingMember->status = 'Active';
                $existingMember->save();
                return $existingMember;
            }

            throw new \Exception('Thành viên đã tồn tại trong cuộc trò chuyện', 422);
        }

        // Thêm thành viên mới
        return ConversationMember::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversationId,
            'user_id' => $memberId,
            'member_role' => 'Member',
            'status' => 'Active'
        ]);
    }

    /**
     * Rời khỏi cuộc trò chuyện
     */
    public function leaveConversation($conversationId, $userId)
    {
        $member = ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->first();

        if (!$member) {
            throw new ModelNotFoundException('Bạn không phải là thành viên của cuộc trò chuyện này');
        }

        $member->status = 'Left';
        $member->save();

        return $member;
    }
}
