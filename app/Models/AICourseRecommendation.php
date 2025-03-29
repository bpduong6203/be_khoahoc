<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AICourseRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'score',
        'reasons',
        'is_shown',
        'is_clicked',
        'is_enrolled',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'is_shown' => 'boolean',
        'is_clicked' => 'boolean',
        'is_enrolled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
