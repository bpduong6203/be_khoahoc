<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Enrollment extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'course_id',
        'expiry_date',
        'payment_status',
        'payment_method',
        'transaction_id',
        'price',
        'status',
        'completion_date',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'completion_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
