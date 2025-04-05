<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Lấy danh sách tin nhắn của cuộc trò chuyện
     */
    public function index(Request $request, $conversationId)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $messages = $this->messageService->getMessages($conversationId, $request->user()->id, $perPage);
            return response()->json([
                'data' => $messages,
                'message' => 'Danh sách tin nhắn'
            ]);
        } catch (\Exception $e) {
            $code = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    /**
     * Gửi tin nhắn mới
     */
    public function store(Request $request, $conversationId)
    {
        $validatedData = $request->validate([
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (empty($validatedData['content']) && !$request->hasFile('attachment')) {
            return response()->json([
                'message' => 'Tin nhắn không được để trống'
            ], 422);
        }

        try {
            $message = $this->messageService->sendMessage($conversationId, $request->user()->id, $validatedData);
            return response()->json([
                'data' => $message,
                'message' => 'Tin nhắn đã được gửi'
            ], 201);
        } catch (\Exception $e) {
            $code = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    /**
     * Đánh dấu tin nhắn đã đọc
     */
    public function markAsRead(Request $request, $conversationId)
    {
        try {
            $count = $this->messageService->markAsRead($conversationId, $request->user()->id);
            return response()->json([
                'count' => $count,
                'message' => 'Đã đánh dấu tất cả tin nhắn là đã đọc'
            ]);
        } catch (\Exception $e) {
            $code = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    /**
     * Xóa tin nhắn
     */
    public function destroy(Request $request, $conversationId, $messageId)
    {
        try {
            $this->messageService->deleteMessage($messageId, $request->user()->id);
            return response()->json([
                'message' => 'Tin nhắn đã được xóa'
            ]);
        } catch (\Exception $e) {
            $code = (is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $code);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy tin nhắn'], 404);
        }
    }
}