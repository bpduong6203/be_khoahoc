<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LessonService
{
    public function createLesson(array $data)
    {
        // Validate course existence
        $course = Course::find($data['course_id']);

        if (!$course) {
            throw new ModelNotFoundException('Course not found.');
        }

        // Create lesson for the course
        $lesson = Lesson::create([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'],
            'video_url' => $data['video_url'],
            'duration' => $data['duration'],
            'order_number' => $data['order_number'],
            'status' => $data['status'],
        ]);

        return $lesson;
    }
    /**
     * Retrieve all lessons with optional filters.
     */
    public function getAllLessons(array $filters = [])
    {
        $query = Lesson::query();

        // Apply filters if provided (e.g., course_id, status)
        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }
    /**
     * Retrieve a lesson by ID.
     */
    public function getLessonById($id)
    {
        return Lesson::find($id);
    }
    /**
     * Update an existing lesson.
     */
    public function updateLesson($id, array $data)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return null;
        }

        if (isset($data['course_id'])) {
            $course = Course::find($data['course_id']);
            if (!$course) {
                throw new ModelNotFoundException('Course not found.');
            }
        }

        $lesson->update(array_filter($data)); // Only update provided fields
        return $lesson->fresh(); // Return the updated model
    }

    /**
     * Delete a lesson.
     */
    public function deleteLesson($id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return false;
        }

        $lesson->delete();
        return true;
    }
}
