<?php

namespace App\DTO;

use App\Models\Course;

class CourseDTO
{
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
        
        return $dto;
    }

    public function toArray()
    {
        return [
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
    }
}