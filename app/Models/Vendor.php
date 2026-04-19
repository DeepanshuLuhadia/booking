<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor extends Model
{
    protected $fillable = [
        'user_id', 'business_name', 'slug', 'owner_name', 'contact_number',
        'shop_photo', 'address', 'latitude', 'longitude', 'is_open',
        'token_booking_enabled', 'token_amount', 'service_fee', 'emergency_fee',
        'subscription_plan_id', 'subscription_expires_at', 'qr_code_path', 'status',
        'referral_code', 'referred_by_id', 'referral_balance', 'referral_reward_paid',
        'upi_id', 'vendor_type', 'appointment_mode', 'avg_consultation_time',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'token_booking_enabled' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'referral_balance' => 'decimal:2',
        'referral_reward_paid' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($vendor) {
            $vendor->slug = Str::slug($vendor->business_name) . '-' . Str::random(5);
            $vendor->referral_code = 'VND-' . strtoupper(Str::random(8));
        });

        static::updated(function ($vendor) {
            // Reward referrer ONLY ONCE when vendor becomes active and has a referrer who hasn't been rewarded yet
            if ($vendor->wasChanged('status') && $vendor->status === 'active' && $vendor->referred_by_id && !$vendor->referral_reward_paid) {
                $referrer = $vendor->referrer;
                if ($referrer) {
                    $referrer->increment('referral_balance', 150);
                    // Mark as paid so they don't get rewards on every status toggle (suspend/activate)
                    $vendor->updateQuietly(['referral_reward_paid' => true]);
                }
            }
        });
    }

    public function referrer()
    {
        return $this->belongsTo(Vendor::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(Vendor::class, 'referred_by_id');
    }

    public function category()
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }

    public function hasAvailableSlotsToday()
    {
        if (!$this->is_open || $this->status !== 'active') {
            return false;
        }

        foreach ($this->employees as $employee) {
            if (!$employee->is_active) continue;
            
            $slots = app(\App\Services\SlotGenerationService::class)->generateSlots($employee);
            foreach ($slots as $slot) {
                if ($slot['available'] || $slot['requires_emergency']) {
                    return true;
                }
            }
        }

        return false;
    }
}
