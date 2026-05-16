<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'vendor_id', 'name', 'photo', 'working_start_time',
        'working_end_time', 'slot_duration', 'service_fee_override', 
        'premium_fee', 'premium_bookings_count', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
