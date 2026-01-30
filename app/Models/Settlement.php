<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    protected $fillable = [
        'vendor_id', 'period_start', 'period_end', 'booking_count',
        'booking_amount', 'emergency_booking_amount', 'referral_amount',
        'total_amount', 'status', 'payout_date', 'upi_transaction_id', 'payment_details'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_details' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
