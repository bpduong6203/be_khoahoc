<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;

    protected $keyType = 'string';  
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'course_id',
        'type',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'conversation_members')
            ->withPivot('member_role', 'status')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
