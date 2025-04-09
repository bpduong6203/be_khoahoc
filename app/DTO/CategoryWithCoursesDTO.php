<?php

namespace App\DTO;

class CategoryWithCoursesDTO
{
    public string $id;
    public string $name;
    public ?string $description;
    public ?string $parentId;
    public ?string $createdBy;
    public string $status;
    public string $createdAt;
    public string $updatedAt;
    public array $courses;

    public function __construct(
        string $id,
        string $name,
        ?string $description,
        ?string $parentId,
        ?string $createdBy,
        string $status,
        string $createdAt,
        string $updatedAt,
        array $courses
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->parentId = $parentId;
        $this->createdBy = $createdBy;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->courses = $courses;
    }

    public static function fromModel($category): self
    {
        $courses = $category->courses->map(function ($course) {
            return new CourseDTO(
                $course->id,
                $course->title,
                $course->description,
                $course->category_id,
                $course->user->name ?? 'Unknown', 
                (float) $course->price,
                $course->discount_price ? (float) $course->discount_price : null,
                $course->thumbnail_url,
                $course->duration,
                $course->level,
                $course->requirements,
                $course->objectives,
                $course->status,
                (float) $course->rating,
                (int) $course->enrollment_count,
                $course->created_at->toIso8601String(),
                $course->updated_at->toIso8601String()
            );
        })->toArray();

        return new self(
            $category->id,
            $category->name,
            $category->description,
            $category->parent_id,
            $category->created_by,
            $category->status,
            $category->created_at->toIso8601String(),
            $category->updated_at->toIso8601String(),
            $courses
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'courses' => array_map(fn($course) => $course->toArray(), $this->courses),
        ];
    }
}

class CourseDTO
{
    public string $id;
    public string $title;
    public ?string $description;
    public ?string $categoryId;
    public string $userName; 
    public float $price;
    public ?float $discountPrice;
    public ?string $thumbnailUrl;
    public ?int $duration;
    public ?string $level;
    public ?string $requirements;
    public ?string $objectives;
    public string $status;
    public float $rating;
    public int $enrollmentCount;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        string $id,
        string $title,
        ?string $description,
        ?string $categoryId,
        string $userName,
        float $price,
        ?float $discountPrice,
        ?string $thumbnailUrl,
        ?int $duration,
        ?string $level,
        ?string $requirements,
        ?string $objectives,
        string $status,
        float $rating,
        int $enrollmentCount,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->userName = $userName;
        $this->price = $price;
        $this->discountPrice = $discountPrice;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->duration = $duration;
        $this->level = $level;
        $this->requirements = $requirements;
        $this->objectives = $objectives;
        $this->status = $status;
        $this->rating = $rating;
        $this->enrollmentCount = $enrollmentCount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'user_name' => $this->userName, 
            'price' => $this->price,
            'discount_price' => $this->discountPrice,
            'thumbnail_url' => $this->thumbnailUrl,
            'duration' => $this->duration,
            'level' => $this->level,
            'requirements' => $this->requirements,
            'objectives' => $this->objectives,
            'status' => $this->status,
            'rating' => $this->rating,
            'enrollment_count' => $this->enrollmentCount,
        ];
    }
}