<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'phone_number',
        'password',
        'full_name',
        'avatar',
        'is_email_verified',
        'is_2fa_enabled',
        'social_login_type',
        'social_login_id',
        'status',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_email_verified' => 'boolean',
        'is_2fa_enabled' => 'boolean',
        'last_login' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('status', 'assigned_by')
            ->withTimestamps();
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_members')
            ->withPivot('member_role', 'status')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function courseRecommendations()
    {
        return $this->hasMany(AICourseRecommendation::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)
            ->where('user_roles.status', 'Active')
            ->exists();
    }

    public function hasPermission($permissionName)
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName)
                    ->where('role_permissions.status', 'Active');
            })
            ->where('user_roles.status', 'Active')
            ->exists();
    }
}
