<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'vendor_id', 'user_id', 'name', 'slug', 'qr_code_path', 'photo', 'working_start_time',
        'working_end_time', 'slot_duration', 'service_fee_override',
        'premium_fee', 'premium_bookings_count', 'is_active', 'is_paused',
        'now_serving_token', 'max_daily_tokens'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->slug)) {
                $baseSlug = \Illuminate\Support\Str::slug($employee->name);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $count++;
                }
                $employee->slug = $slug;
            }
        });
    }

    public function getPublicUrlAttribute()
    {
        return route('employee.public.show', $this->slug ?? $this->id);
    }

    protected $casts = [
        'is_active' => 'boolean',
        'is_paused' => 'boolean',
        'now_serving_token' => 'integer',
        'max_daily_tokens' => 'integer',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
