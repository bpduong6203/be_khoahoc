<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SocialAccount extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['user_id', 'provider_name', 'provider_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
