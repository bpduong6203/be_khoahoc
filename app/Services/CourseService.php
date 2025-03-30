<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\DTO\CourseDTO;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    public function getCourses(array $filters = [], $perPage = 10)
    {
        $query = Course::query();

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $courses = $query->paginate($perPage);
        return $courses->through(fn($course) => CourseDTO::fromCourse($course));
    }

    public function createCourse(array $data, $userId)
    {
        $thumbnailUrl = null;
        if (isset($data['thumbnail']) && $data['thumbnail']) {
            $thumbnailUrl = $data['thumbnail']->store('thumbnails', 'public');
        }

        $course = Course::create([
            'id' => Str::uuid(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'user_id' => $userId,
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? null,
            'thumbnail_url' => $thumbnailUrl,
            'duration' => $data['duration'] ?? null,
            'level' => $data['level'] ?? 'All Levels',
            'requirements' => $data['requirements'] ?? null,
            'objectives' => $data['objectives'] ?? null,
            'status' => $data['status'] ?? 'Draft',
        ]);

        return CourseDTO::fromCourse($course);
    }

    public function getCourseById($id)
    {
        $course = Course::with(['category', 'user', 'lessons'])->findOrFail($id);
        return CourseDTO::fromCourse($course);
    }

    public function updateCourse($id, array $data)
    {
        $course = Course::findOrFail($id);

        if (isset($data['thumbnail']) && $data['thumbnail']) {
            if ($course->thumbnail_url) {
                Storage::disk('public')->delete($course->thumbnail_url);
            }
            $data['thumbnail_url'] = $data['thumbnail']->store('thumbnails', 'public');
        }
        unset($data['thumbnail']); // Xóa key thumbnail sau khi xử lý

        $course->update($data);
        return CourseDTO::fromCourse($course);
    }

    public function deleteCourse($id)
    {
        $course = Course::findOrFail($id);
        if ($course->thumbnail_url) {
            Storage::disk('public')->delete($course->thumbnail_url);
        }
        $course->delete();
    }

    public function enrollUser($courseId, $userId)
    {
        $course = Course::findOrFail($courseId);
        $existingEnrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingEnrollment) {
            throw new \Exception('You are already enrolled in this course', 400);
        }

        $enrollment = Enrollment::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'course_id' => $courseId,
            'price' => $course->discount_price ?? $course->price,
            'payment_status' => 'Pending',
            'status' => 'Active',
        ]);

        $course->increment('enrollment_count');
        return $enrollment;
    }

    public function getEnrolledCourses($userId)
    {
        return Enrollment::with('course')
            ->where('user_id', $userId)
            ->where('status', 'Active')
            ->get();
    }

    public function getUserCourses($userId)
    {
        $courses = Course::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        return $courses->map(fn($course) => CourseDTO::fromCourse($course));
    }
}