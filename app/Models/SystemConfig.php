<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemConfig extends Model
{
    use HasFactory;

    protected $table = 'system_config';

    protected $fillable = [
        'config_key',
        'config_value',
        'description',
        'updated_by',
    ];

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
