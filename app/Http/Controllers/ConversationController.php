<?php

namespace App\Http\Controllers;

use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConversationController extends Controller
{
    protected $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    /**
     * Lấy danh sách cuộc trò chuyện của người dùng
     */
    public function index(Request $request)
    {
        try {
            $conversations = $this->conversationService->getUserConversations($request->user()->id);
            return response()->json([
                'data' => $conversations,
                'message' => 'Danh sách cuộc trò chuyện'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Tạo cuộc trò chuyện mới
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:Course,Private,Group',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        try {
            $conversation = $this->conversationService->createConversation($validatedData, $request->user()->id);
            return response()->json([
                'data' => $conversation,
                'message' => 'Cuộc trò chuyện đã được tạo'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Xem thông tin chi tiết cuộc trò chuyện
     */
    public function show(Request $request, $id)
    {
        try {
            $conversation = $this->conversationService->getConversation($id, $request->user()->id);
            return response()->json([
                'data' => $conversation,
                'message' => 'Thông tin cuộc trò chuyện'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy cuộc trò chuyện'], 404);
        }
    }

    /**
     * Tạo cuộc trò chuyện với giảng viên
     */
    public function createTeacherConversation(Request $request, $courseId)
    {
        try {
            $conversation = $this->conversationService->createTeacherConversation($courseId, $request->user()->id);
            return response()->json([
                'data' => $conversation,
                'message' => 'Cuộc trò chuyện với giảng viên đã được tạo'
            ], 201);
        } catch (\Exception $e) {
            // Only use valid HTTP status codes (100-599)
            $code = $e->getCode();
            if (!is_int($code) || $code < 100 || $code > 599) {
                $code = 500; // Default to 500 Internal Server Error
            }
            return response()->json(['message' => $e->getMessage()], $code);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy khóa học'], 404);
        }
    }

    /**
     * Thêm thành viên vào cuộc trò chuyện
     */
    public function addMember(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $member = $this->conversationService->addMember($id, $validatedData['user_id'], $request->user()->id);
            return response()->json([
                'data' => $member,
                'message' => 'Thành viên đã được thêm vào cuộc trò chuyện'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy cuộc trò chuyện'], 404);
        }
    }

    /**
     * Rời khỏi cuộc trò chuyện
     */
    public function leaveConversation(Request $request, $id)
    {
        try {
            $this->conversationService->leaveConversation($id, $request->user()->id);
            return response()->json([
                'message' => 'Bạn đã rời khỏi cuộc trò chuyện'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy cuộc trò chuyện hoặc bạn không phải thành viên'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}