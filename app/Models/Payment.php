<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'vendor_id', 'customer_id', 'amount',
        'payment_type', 'razorpay_order_id', 'razorpay_payment_id',
        'razorpay_signature', 'status', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
