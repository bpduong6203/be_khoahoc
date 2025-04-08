<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Material extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'id',
        'lesson_id',
        'title',
        'file_url',
        'file_type',
        'file_size',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($material) {
            $material->id = (string) Str::uuid();
        });
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}