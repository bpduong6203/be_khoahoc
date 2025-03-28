<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'avatar',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }


    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });

        static::created(function ($user) {
            if ($user->exists && !$user->roles()->exists()) {
                $role = Role::where('name', 'user')->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }
        });
    }
}
