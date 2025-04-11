<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'enrollment_id',
        'user_id',
        'invoice_code',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'billing_info',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_info' => 'json',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
