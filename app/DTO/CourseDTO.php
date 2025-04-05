<?php
namespace App\DTO;

use App\Models\Course;

class CourseDTO {
    public $id;
    public $title;
    public $description;
    public $category_id;
    public $user_id;
    public $price;
    public $discount_price;
    public $thumbnail_url;
    public $duration;
    public $level;
    public $requirements;
    public $objectives;
    public $status;
    public $rating;
    public $enrollment_count;
    public $created_at;
    
    // Add relationship properties
    public $category;
    public $user;
    public $lessons;

    public static function fromCourse(Course $course, array $with = [])
    {
        $dto = new self();
        $dto->id = $course->id;
        $dto->title = $course->title;
        $dto->description = $course->description;
        $dto->category_id = $course->category_id;
        $dto->user_id = $course->user_id;
        $dto->price = $course->price;
        $dto->discount_price = $course->discount_price;
        $dto->thumbnail_url = $course->thumbnail_url;
        $dto->duration = $course->duration;
        $dto->level = $course->level;
        $dto->requirements = $course->requirements;
        $dto->objectives = $course->objectives;
        $dto->status = $course->status;
        $dto->rating = $course->rating;
        $dto->enrollment_count = $course->enrollment_count;
        $dto->created_at = $course->created_at;
        
        // Handle relationships
        if ($course->relationLoaded('category')) {
            $dto->category = $course->category ? [
                'id' => $course->category->id,
                'name' => $course->category->name,
                // Add other category fields you need
            ] : null;
        }
        
        if ($course->relationLoaded('user')) {
            $dto->user = $course->user ? [
                'id' => $course->user->id,
                'name' => $course->user->name,
                // Add other user fields you need
            ] : null;
        }
        
        if ($course->relationLoaded('lessons')) {
            $dto->lessons = $course->lessons->map(function($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'content' => $lesson->content,
                    'video_url' => $lesson->video_url,
                    'duration' => $lesson->duration,
                    'order_number' => $lesson->order_number,
                    'status' => $lesson->status,
                    // Add other lesson fields you need
                ];
            })->toArray();
        }
        
        return $dto;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'thumbnail_url' => $this->thumbnail_url,
            'duration' => $this->duration,
            'level' => $this->level,
            'requirements' => $this->requirements,
            'objectives' => $this->objectives,
            'status' => $this->status,
            'rating' => $this->rating,
            'enrollment_count' => $this->enrollment_count,
            'created_at' => $this->created_at,
        ];
        
        // Add relationship data if it exists
        if (isset($this->category)) {
            $array['category'] = $this->category;
        }
        
        if (isset($this->user)) {
            $array['user'] = $this->user;
        }
        
        if (isset($this->lessons)) {
            $array['lessons'] = $this->lessons;
        }
        
        return $array;
    }
}