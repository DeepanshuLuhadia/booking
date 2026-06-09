<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'vendor_id', 'user_id', 'name', 'photo', 'working_start_time',
        'working_end_time', 'slot_duration', 'service_fee_override', 
        'premium_fee', 'premium_bookings_count', 'is_active', 'is_paused'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_paused' => 'boolean',
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
