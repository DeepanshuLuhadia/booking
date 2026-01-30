<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'price', 'max_employees', 'features', 'is_active'];
    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }
}
