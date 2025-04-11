<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'id',
        'title',
        'description',
        'category_id',
        'user_id',
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

    protected $attributes = [
        'status' => 'draft',
        'rating' => 0.0,
        'enrollment_count' => 0,
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    // Custom accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . ' đ';
    }

    // Custom scopes
    public function scopeSearch($query, $keyword)
    {
        return $query->where('title', 'LIKE', "%$keyword%");
    }
}
