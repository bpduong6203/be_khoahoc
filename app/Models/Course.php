<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'teacher_id',
        'price',
        'discount_price',
        'thumbnail_url',
        'duration',
        'level',
        'requirements',
        'objectives',
        'status',
        'rating',
        'enrollment_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order_number');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Enrollment::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function recommendations()
    {
        return $this->hasMany(AICourseRecommendation::class);
    }
}
