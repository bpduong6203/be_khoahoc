<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\DTO\CourseDTO;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index(Request $request)
    {
        $query = Course::query();
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }
        
        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        // Pagination
        $perPage = $request->get('per_page', 10);
        $courses = $query->paginate($perPage);
        
        return response()->json([
            'data' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created course.
     */
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
        
        // Handle file upload if thumbnail is provided
        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $request->file('thumbnail')->store('thumbnails', 'public');
        }
        
        $course = Course::create([
            'id' => Str::uuid(),
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? null,
            'category_id' => $validatedData['category_id'] ?? null,
            'user_id' => $request->user()->id,
            'price' => $validatedData['price'],
            'discount_price' => $validatedData['discount_price'] ?? null,
            'thumbnail_url' => $thumbnailUrl,
            'duration' => $validatedData['duration'] ?? null,
            'level' => $validatedData['level'] ?? 'All Levels',
            'requirements' => $validatedData['requirements'] ?? null,
            'objectives' => $validatedData['objectives'] ?? null,
            'status' => $validatedData['status'] ?? 'Draft',
        ]);
        
        return response()->json([
            'data' => $course,
            'message' => 'Course created successfully'
        ], 201);
    }

    /**
     * Display the specified course.
     */
    public function show($id)
    {
        $course = Course::with(['category', 'user', 'lessons'])->findOrFail($id);
        
        return response()->json([
            'data' => $course,
            'message' => 'Course retrieved successfully'
        ]);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        // Check if the user is the owner of the course
        if ($request->user()->id !== $course->user_id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'You are not authorized to update this course'
            ], 403);
        }
        
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
        
        // Handle file upload if thumbnail is provided
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            
            $validatedData['thumbnail_url'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }
        
        $course->update($validatedData);
        
        return response()->json([
            'data' => $course,
            'message' => 'Course updated successfully'
        ]);
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        // Check if the user is the owner of the course
        if ($request->user()->id !== $course->user_id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'message' => 'You are not authorized to delete this course'
            ], 403);
        }
        
        // Delete thumbnail if exists
        if ($course->thumbnail_url) {
            Storage::disk('public')->delete($course->thumbnail_url);
        }
        
        $course->delete();
        
        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }
    
    /**
     * Enroll a user in a course.
     */
    public function enroll(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $userId = $request->user()->id;
        
        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingEnrollment) {
            return response()->json([
                'message' => 'You are already enrolled in this course'
            ], 400);
        }
        
        // Create enrollment
        $enrollment = Enrollment::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'course_id' => $courseId,
            'price' => $course->discount_price ?? $course->price,
            'payment_status' => 'Pending',
            'status' => 'Active',
        ]);
        
        // Increment enrollment count
        $course->increment('enrollment_count');
        
        return response()->json([
            'data' => $enrollment,
            'message' => 'Successfully enrolled in course'
        ], 201);
    }
    
    /**
     * Get enrolled courses for the authenticated user.
     */
    public function myEnrolledCourses(Request $request)
    {
        $userId = $request->user()->id;
        
        $enrollments = Enrollment::with('course')
            ->where('user_id', $userId)
            ->where('status', 'Active')
            ->get();
            
        return response()->json([
            'data' => $enrollments,
            'message' => 'Enrolled courses retrieved successfully'
        ]);
    }
    
    /**
     * Get courses created by the authenticated user.
     */
    public function myCourses(Request $request)
    {
        $userId = $request->user()->id;
        
        $courses = Course::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'data' => $courses,
            'message' => 'Your courses retrieved successfully'
        ]);
    }
}