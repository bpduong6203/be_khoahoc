<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('status', 'assigned_by')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withPivot('status', 'assigned_by')
            ->withTimestamps();
    }
}
