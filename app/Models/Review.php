<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Enrollment::class, 'id', 'id', 'enrollment_id', 'user_id');
    }

    public function course()
    {
        return $this->hasOneThrough(Course::class, Enrollment::class, 'id', 'id', 'enrollment_id', 'course_id');
    }
}
