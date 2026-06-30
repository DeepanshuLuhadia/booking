<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorReview extends Model
{
    protected $fillable = [
        'vendor_id',
        'reviewer_name',
        'reviewer_phone',
        'reviewer_email',
        'is_verified',
        'rating',
        'comment',
        'images',
        'is_reported',
        'report_reason',
        'reported_at',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'images'      => 'array',
        'is_verified' => 'boolean',
        'is_reported' => 'boolean',
        'reported_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
