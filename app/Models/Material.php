<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'file_url',
        'file_type',
        'file_size',
        'description',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
