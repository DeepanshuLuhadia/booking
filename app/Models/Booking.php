<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'vendor_id', 'employee_id', 'customer_id', 'customer_name',
        'customer_phone', 'booking_date', 'slot_start_time', 'slot_end_time',
        'booking_type', 'token_required', 'token_number', 'token_amount', 'emergency_fee',
        'online_paid_amount', 'status', 'payment_id', 'razorpay_order_id',
        'razorpay_payment_id', 'vendor_booked', 'notes',
        'fcm_token', 'next_notified_at', 'turn_notified_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'token_required' => 'boolean',
        'vendor_booked' => 'boolean',
        'next_notified_at' => 'datetime',
        'turn_notified_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
