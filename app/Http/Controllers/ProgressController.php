<?php

namespace App\Http\Controllers;

use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProgressController extends Controller
{
    protected $progressService;

    public function __construct(ProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Get progress for all enrolled courses
     */
    public function index(Request $request)
    {
        try {
            $coursesProgress = $this->progressService->getUserCoursesProgress($request->user()->id);
            
            return response()->json([
                'data' => $coursesProgress,
                'message' => 'Danh sách tiến độ khóa học'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get detailed progress for a specific enrollment
     */
    public function show(Request $request, $enrollmentId)
    {
        try {
            $progress = $this->progressService->getEnrollmentProgress(
                $enrollmentId, 
                $request->user()->id
            );
            
            return response()->json([
                'data' => $progress,
                'message' => 'Chi tiết tiến độ khóa học'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký hoặc bạn không có quyền truy cập'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update progress for a specific lesson
     */
    public function updateLessonProgress(Request $request, $enrollmentId, $lessonId)
    {
        $validatedData = $request->validate([
            'status' => 'nullable|in:Not Started,In Progress,Completed',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        try {
            $progress = $this->progressService->updateLessonProgress(
                $enrollmentId,
                $lessonId, 
                $request->user()->id,
                $validatedData
            );
            
            return response()->json([
                'data' => $progress,
                'message' => 'Đã cập nhật tiến độ bài học'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký hoặc bài học'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a lesson as started
     */
    public function startLesson(Request $request, $enrollmentId, $lessonId)
    {
        try {
            $progress = $this->progressService->startLesson(
                $enrollmentId,
                $lessonId, 
                $request->user()->id
            );
            
            return response()->json([
                'data' => $progress,
                'message' => 'Đã bắt đầu bài học'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký hoặc bài học'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a lesson as completed
     */
    public function completeLesson(Request $request, $enrollmentId, $lessonId)
    {
        try {
            $progress = $this->progressService->completeLesson(
                $enrollmentId,
                $lessonId, 
                $request->user()->id
            );
            
            return response()->json([
                'data' => $progress,
                'message' => 'Đã hoàn thành bài học'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký hoặc bài học'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}