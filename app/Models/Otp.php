<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['identifier', 'otp', 'type', 'expires_at', 'verified'];
    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
    ];
}
