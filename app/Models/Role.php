<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Quan hệ: Một vai trò có nhiều người dùng
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
