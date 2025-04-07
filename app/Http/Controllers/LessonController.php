<?php

namespace App\Http\Controllers;

use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller
{
    protected $lessonService;

    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
    }
    /**
     * Display a listing of lessons.
     */
    public function index(Request $request)
    {
        try {
            $lessons = $this->lessonService->getAllLessons($request->query());
            return response()->json(['lessons' => $lessons], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to retrieve lessons: ' . $e->getMessage()], 500);
        }
    }

    // Create a lesson
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'duration' => 'required|integer',
            'order_number' => 'required|integer',
            'status' => 'required|string|in:Draft,Published,Archived',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $lesson = $this->lessonService->createLesson($request->all());
            return response()->json(['lesson' => $lesson], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to create lesson: ' . $e->getMessage()], 400);
        }
    }
    /**
     * Display the specified lesson.
     */
    public function show($id)
    {
        try {
            $lesson = $this->lessonService->getLessonById($id);
            if (!$lesson) {
                return response()->json(['error' => 'Lesson not found'], 404);
            }
            return response()->json(['lesson' => $lesson], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to retrieve lesson: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'sometimes|exists:courses,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'content' => 'sometimes|string',
            'video_url' => 'nullable|url',
            'duration' => 'sometimes|integer',
            'order_number' => 'sometimes|integer',
            'status' => 'sometimes|string|in:Draft,Published,Archived',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            $lesson = $this->lessonService->updateLesson($id, $request->all());
            if (!$lesson) {
                return response()->json(['error' => 'Lesson not found'], 404);
            }
            return response()->json(['lesson' => $lesson], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to update lesson: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->lessonService->deleteLesson($id);
            if (!$deleted) {
                return response()->json(['error' => 'Lesson not found'], 404);
            }
            return response()->json(['message' => 'Lesson deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to delete lesson: ' . $e->getMessage()], 500);
        }
    }
}
