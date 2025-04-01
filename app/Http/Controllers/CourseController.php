<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CourseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['category_id', 'status', 'level', 'min_price', 'max_price', 'search', 'sort_by', 'sort_direction']);
        $perPage = $request->get('per_page', 10);
        $courses = $this->courseService->getCourses($filters, $perPage);

        return response()->json([
            'data' => $courses,
            'message' => 'Courses retrieved successfully',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|image|max:2048',
            'duration' => 'nullable|integer|min:1',
            'level' => 'nullable|in:Beginner,Intermediate,Advanced,All Levels',
            'requirements' => 'nullable|string',
            'objectives' => 'nullable|string',
            'status' => 'nullable|in:Draft,Published,Archived',
        ]);

        $course = $this->courseService->createCourse($validatedData, $request->user()->id);

        return response()->json([
            'data' => $course,
            'message' => 'Course created successfully',
        ], 201);
    }

    public function show($id)
    {
        try {
            $course = $this->courseService->getCourseById($id);
            return response()->json([
                'data' => $course,
                'message' => 'Course retrieved successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Course not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|image|max:2048',
            'duration' => 'nullable|integer|min:1',
            'level' => 'nullable|in:Beginner,Intermediate,Advanced,All Levels',
            'requirements' => 'nullable|string',
            'objectives' => 'nullable|string',
            'status' => 'nullable|in:Draft,Published,Archived',
        ]);

        try {
            $course = $this->courseService->updateCourse($id, $validatedData);
            return response()->json([
                'data' => $course,
                'message' => 'Course updated successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Course not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->courseService->deleteCourse($id);
            return response()->json(['message' => 'Course deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Course not found'], 404);
        }
    }

    public function enroll(Request $request, $courseId)
    {
        try {
            $enrollment = $this->courseService->enrollUser($courseId, $request->user()->id);
            return response()->json([
                'data' => $enrollment,
                'message' => 'Successfully enrolled in course',
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function myEnrolledCourses(Request $request)
    {
        $enrollments = $this->courseService->getEnrolledCourses($request->user()->id);
        return response()->json([
            'data' => $enrollments,
            'message' => 'Enrolled courses retrieved successfully',
        ]);
    }

    public function myCourses(Request $request)
    {
        $courses = $this->courseService->getUserCourses($request->user()->id);
        return response()->json([
            'data' => $courses,
            'message' => 'Your courses retrieved successfully',
        ]);
    }
}