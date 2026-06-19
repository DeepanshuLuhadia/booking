<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor extends Model
{
    protected $fillable = [
        'user_id', 'vendor_category_id', 'business_name', 'slug', 'owner_name', 'contact_number',
        'shop_photo', 'address', 'latitude', 'longitude', 'is_open',
        'token_booking_enabled', 'token_amount', 'service_fee', 'emergency_fee',
        'subscription_plan_id', 'subscription_expires_at', 'qr_code_path', 'status', 'is_profile_complete',
        'referral_code', 'referred_by_id', 'referral_balance', 'referral_reward_paid',
        'upi_id', 'vendor_type', 'appointment_mode', 'avg_consultation_time',
        'global_opening_time', 'global_closing_time', 'allow_booking_until_closing', 'show_contact_number'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'token_booking_enabled' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'referral_balance' => 'decimal:2',
        'referral_reward_paid' => 'boolean',
        'is_profile_complete' => 'boolean',
        'allow_booking_until_closing' => 'boolean',
        'show_contact_number' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($vendor) {
            $vendor->slug = Str::slug($vendor->business_name) . '-' . Str::random(5);
            $vendor->referral_code = 'VND-' . strtoupper(Str::random(8));
        });

        static::saving(function ($vendor) {
            $vendor->is_profile_complete = $vendor->isProfileComplete();
        });

        // Award referral bonus ONLY when referred vendor purchases a PAID plan.
        // Uses a state-based check rather than wasChanged() to avoid missing edge
        // cases (demo-mode activation, double-submit, admin activation, etc.).
        // The `referral_reward_paid` guard above prevents duplicate payouts.
        static::updated(function ($vendor) {
            if (!$vendor->referred_by_id || $vendor->referral_reward_paid) {
                return;
            }

            if ($vendor->status !== 'active') {
                return;
            }

            $plan = \App\Models\SubscriptionPlan::find($vendor->subscription_plan_id);
            $isPaidPlan = $plan && $plan->price > 0;

            if (!$isPaidPlan) {
                return;
            }

            static::awardReferralBonus($vendor);
        });
    }

    /**
     * Award ₹50 referral bonus to the referrer and mark the reward as paid.
     * Triggered only when the referred vendor purchases a paid subscription plan.
     */
    private static function awardReferralBonus(Vendor $vendor): void
    {
        $referrer = $vendor->referrer;
        if ($referrer) {
            $referrer->increment('referral_balance', 50);
            // Mark as paid so they don't get rewards on every status toggle (suspend/activate)
            $vendor->updateQuietly(['referral_reward_paid' => true]);
        }
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
        if (!$this->isEffectivelyOpen()) {
            return false;
        }

        foreach ($this->employees as $employee) {
            if (!$employee->is_active) continue;
            
            $slots = app(\App\Services\SlotGenerationService::class)->generateSlots($employee);
            foreach ($slots as $slot) {
                if ($slot['available']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isProfileComplete()
    {
        return !empty($this->contact_number) && 
               !empty($this->vendor_type) && 
               !empty($this->address) && 
               !empty($this->appointment_mode) && 
               !empty($this->global_opening_time) && 
               !empty($this->global_closing_time);
    }

    public function isSubscriptionActive()
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return false;
        }

        return true;
    }


    public function isEffectivelyOpen()
    {
        if (!$this->is_open || $this->status !== 'active') {
            return false;
        }

        if (!$this->global_opening_time || !$this->global_closing_time) {
            return false;
        }

        $now = now()->format('H:i:s');
        $open = $this->global_opening_time;
        $close = $this->global_closing_time;
        if ($open < $close) {
            return ($now >= $open && $now <= $close);
        } else {
            return ($now >= $open || $now <= $close);
        }
    }
}
