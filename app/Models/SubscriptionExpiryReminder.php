<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionExpiryReminder extends Model
{
    public $timestamps = false;

    protected $fillable = ['vendor_id', 'expires_on', 'days_before', 'sent_at'];

    protected $casts = [
        'expires_on' => 'date',
        'sent_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
