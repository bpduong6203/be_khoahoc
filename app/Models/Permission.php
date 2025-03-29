<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'status',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withPivot('status', 'assigned_by')
            ->withTimestamps();
    }
}
