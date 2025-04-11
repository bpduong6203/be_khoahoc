<?php

namespace App\Services;

use App\Models\Progress;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProgressService
{
    /**
     * Get progress for a specific enrollment
     */
    public function getEnrollmentProgress($enrollmentId, $userId)
    {
        // Verify the enrollment belongs to the user
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', $userId)
            ->firstOrFail();
            
        // Get all lessons for the course
        $lessons = Lesson::where('course_id', $enrollment->course_id)
            ->orderBy('order_number')
            ->get();
            
        // Get progress records for this enrollment
        $progressRecords = Progress::where('enrollment_id', $enrollmentId)->get()
            ->keyBy('lesson_id');
            
        // Calculate overall completion percentage
        $totalLessons = $lessons->count();
        $completedLessons = $progressRecords->where('status', 'Completed')->count();
        $inProgressLessons = $progressRecords->where('status', 'In Progress')->count();
        
        $completionPercentage = $totalLessons > 0 
            ? round(($completedLessons / $totalLessons) * 100) 
            : 0;
            
        // Format lesson progress data
        $lessonsWithProgress = $lessons->map(function ($lesson) use ($progressRecords) {
            $progress = $progressRecords->get($lesson->id);
            
            return [
                'lesson_id' => $lesson->id,
                'title' => $lesson->title,
                'order_number' => $lesson->order_number,
                'duration' => $lesson->duration,
                'status' => $progress ? $progress->status : 'Not Started',
                'start_date' => $progress ? $progress->start_date : null,
                'completion_date' => $progress ? $progress->completion_date : null,
                'last_access_date' => $progress ? $progress->last_access_date : null,
                'time_spent' => $progress ? $progress->time_spent : 0,
            ];
        });
        
        return [
            'enrollment_id' => $enrollmentId,
            'course_id' => $enrollment->course_id,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'in_progress_lessons' => $inProgressLessons,
            'completion_percentage' => $completionPercentage,
            'lessons' => $lessonsWithProgress,
        ];
    }
    
    /**
     * Update progress for a specific lesson
     */
    public function updateLessonProgress($enrollmentId, $lessonId, $userId, $data)
    {
        // Verify the enrollment belongs to the user
        $enrollment = Enrollment::where('id', $enrollmentId)
            ->where('user_id', $userId)
            ->where('status', 'Active')
            ->firstOrFail();
            
        // Verify the lesson belongs to the course
        $lesson = Lesson::where('id', $lessonId)
            ->where('course_id', $enrollment->course_id)
            ->firstOrFail();
            
        // Find or create progress record
        $progress = Progress::firstOrNew([
            'enrollment_id' => $enrollmentId,
            'lesson_id' => $lessonId,
        ]);
        
        // If new record, set default values
        if (!$progress->exists) {
            $progress->id = Str::uuid();
            $progress->status = 'Not Started';
            $progress->time_spent = 0;
        }
        
        // Update status if provided
        if (isset($data['status']) && in_array($data['status'], ['Not Started', 'In Progress', 'Completed'])) {
            $progress->status = $data['status'];
            
            // Set appropriate dates based on status
            if ($data['status'] === 'In Progress' && !$progress->start_date) {
                $progress->start_date = now();
            } elseif ($data['status'] === 'Completed' && !$progress->completion_date) {
                $progress->completion_date = now();
            }
        }
        
        // Update time spent if provided
        if (isset($data['time_spent']) && is_numeric($data['time_spent'])) {
            $progress->time_spent = $data['time_spent'];
        }
        
        // Always update last access date
        $progress->last_access_date = now();
        
        $progress->save();
        
        // Check if all lessons are completed
        $this->checkCourseCompletion($enrollmentId);
        
        return $progress;
    }
    
    /**
     * Check if all lessons in a course are completed and update enrollment if needed
     */
    private function checkCourseCompletion($enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $course = $enrollment->course;
        
        // Count total and completed lessons
        $totalLessons = Lesson::where('course_id', $course->id)->count();
        $completedLessons = Progress::where('enrollment_id', $enrollmentId)
            ->where('status', 'Completed')
            ->count();
            
        // If all lessons completed, mark enrollment as completed
        if ($totalLessons > 0 && $totalLessons === $completedLessons) {
            $enrollment->status = 'Completed';
            $enrollment->completion_date = now();
            $enrollment->save();
        }
    }
    
    /**
     * Mark a lesson as started
     */
    public function startLesson($enrollmentId, $lessonId, $userId)
    {
        return $this->updateLessonProgress($enrollmentId, $lessonId, $userId, [
            'status' => 'In Progress'
        ]);
    }
    
    /**
     * Mark a lesson as completed
     */
    public function completeLesson($enrollmentId, $lessonId, $userId)
    {
        return $this->updateLessonProgress($enrollmentId, $lessonId, $userId, [
            'status' => 'Completed'
        ]);
    }
    
    /**
     * Get course summary progress for a user
     */
    public function getUserCoursesProgress($userId)
    {
        // Get all active enrollments for user
        $enrollments = Enrollment::with(['course'])
            ->where('user_id', $userId)
            ->where('status', 'Active')
            ->get();
            
        $coursesProgress = [];
        
        foreach ($enrollments as $enrollment) {
            // Get total lessons
            $totalLessons = Lesson::where('course_id', $enrollment->course_id)->count();
            
            // Get progress records
            $progressRecords = Progress::where('enrollment_id', $enrollment->id)->get();
            $completedLessons = $progressRecords->where('status', 'Completed')->count();
            
            // Calculate percentage
            $completionPercentage = $totalLessons > 0 
                ? round(($completedLessons / $totalLessons) * 100) 
                : 0;
                
            $coursesProgress[] = [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'course_title' => $enrollment->course->title,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'completion_percentage' => $completionPercentage,
                'last_accessed' => $progressRecords->max('last_access_date'),
            ];
        }
        
        return $coursesProgress;
    }
}