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
        'global_opening_time', 'global_closing_time', 'allow_booking_until_closing', 'show_contact_number',
        'require_customer_details', 'bookings_paused', 'is_verified',
        // Direct-to-vendor UPI advances. The money never reaches the platform;
        // these describe the shop's own bank destination. See UpiPaymentService.
        'is_direct_payment_enabled', 'upi_name', 'advance_amount',
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
        'require_customer_details' => 'boolean',
        'bookings_paused' => 'boolean',
        'is_verified' => 'boolean',
        'is_direct_payment_enabled' => 'boolean',
        'advance_amount' => 'decimal:2',
        'live_celebrated_at' => 'datetime',
    ];

    /**
     * Appended computed attributes.
     * is_currently_open is derived in real time from operating hours.
     * It correctly handles midnight-crossing windows (e.g. 22:00 → 02:00).
     */
    protected $appends = ['is_currently_open'];

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
            if ($vendor->wasChanged('status')) {
                $oldStatus = $vendor->getOriginal('status');
                $newStatus = $vendor->status;
                try {
                    app(\App\Services\NotificationService::class)->notifyVendorStatusChanged($vendor, $newStatus, $oldStatus);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send vendor status push notification: " . $e->getMessage());
                }
            }

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

    public function reviews()
    {
        return $this->hasMany(VendorReview::class);
    }

    /**
     * Average star rating across all reviews, rounded to one decimal.
     */
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * AI-generated fallback description based on vendor category and price,
     * used when the vendor hasn't provided their own description.
     */
    public function getDynamicDescriptionAttribute(): string
    {
        if (!empty($this->attributes['description'])) {
            return $this->attributes['description'];
        }

        $cat = strtolower($this->category?->slug ?? 'professional');
        $name = $this->business_name;
        $fee = number_format($this->service_fee);

        return match (true) {
            in_array($cat, ['salon', 'barber', 'beauty']) => 
                "Premium grooming and styling services at {$name}. Experience top-tier professional care starting at ₹{$fee}.",
            in_array($cat, ['clinic', 'doctor', 'health', 'dental']) => 
                "Trusted healthcare and medical consultations at {$name}. Professional care prioritizing your well-being, with visits starting at ₹{$fee}.",
            in_array($cat, ['sports', 'gym', 'fitness', 'turf']) => 
                "Top-class sports and fitness facilities at {$name}. Book your slot today starting at ₹{$fee}.",
            in_array($cat, ['training', 'consultant', 'coaching']) => 
                "Expert guidance and professional consultations at {$name}. Elevate your skills starting at ₹{$fee}.",
            default => 
                "Professional services offered at {$name}. Book your appointment today starting at ₹{$fee}."
        };
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

    /**
     * Has this shop pinned itself on the map?
     *
     * A stored 0 counts as unset, matching CustomerDiscoveryController::
     * coordinate() — the listing treats |coord| < 0.00001 as "no location", so
     * anything else here would call a shop located that the ranking ignores.
     */
    public function hasMapCoordinates(): bool
    {
        return abs((float) $this->latitude) >= 0.00001
            && abs((float) $this->longitude) >= 0.00001;
    }

    /**
     * Can a customer find this shop at all?
     *
     * Either way of answering counts. Map coordinates are the better one — they
     * drop a pin on the exact spot and are what the "N km away" chip is measured
     * from — but a typed address still gets somebody to the door, and shops
     * registered before coordinates existed only have that.
     */
    public function hasLocation(): bool
    {
        return $this->hasMapCoordinates() || filled($this->address);
    }

    /**
     * Where "open in maps" should send a customer, or null when this shop has
     * given us nothing to point at.
     *
     * Coordinates win over the address string: a text search lands wherever
     * Google decides to interpret it, which for a shop on an unnamed lane is
     * often the wrong end of the neighbourhood.
     */
    public function mapUrl(): ?string
    {
        $query = $this->hasMapCoordinates()
            ? ((float) $this->latitude) . ',' . ((float) $this->longitude)
            : trim((string) $this->address);

        if ($query === '') {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($query);
    }

    /**
     * What to print on the location row.
     *
     * The address when there is one. When there is not — the vendor pinned
     * themselves on the map and left the text box empty, which is now a
     * perfectly complete profile — the row becomes the action it actually is,
     * rather than a blank line or an invented "Professional District".
     *
     * Empty string when the shop has no location at all, so callers can drop
     * the row entirely instead of rendering a label that leads nowhere.
     */
    public function locationLabel(): string
    {
        $address = trim((string) $this->address);

        if ($address !== '') {
            return $address;
        }

        return $this->hasMapCoordinates() ? 'Go to Map' : '';
    }

    public function isProfileComplete()
    {
        return !empty($this->contact_number) && 
               !empty($this->vendor_type) && 
               // Location, by either route: a map pin OR a typed address. The
               // settings form asks for the pin and treats the address as
               // optional, so requiring the text would hold back a shop that
               // has told us exactly where it is.
               $this->hasLocation() &&
               !empty($this->appointment_mode) && 
               !empty($this->global_opening_time) && 
               !empty($this->global_closing_time);
    }

    /**
     * The half of the setup that lives on the settings page: the business
     * details and the map pin, and nothing else.
     *
     * Split out from getListingBlockers() because onboarding is staged. The
     * settings page has no staff section — that is a page of its own — so
     * listing a specialist among its chips asked the vendor for something the
     * form in front of them could not provide. A new shop is asked for these
     * first, alone; the specialist is asked for afterwards, in the section it
     * belongs to (see needsFirstEmployee on ProfileController::edit).
     */
    public function getProfileBlockers(): array
    {
        $blockers = [];

        /*
        | `address` is deliberately absent: a shop that has pinned itself on the
        | map has told us where it is, and the coordinate check below is the one
        | the listing actually depends on. Asking for the text as well would
        | keep a fully locatable shop off the listing over a formality.
        |
        | Each entry names the FIELD it lives in as well as the page: the setup
        | banner turns that into an anchor (#field-<name>) that scrolls to and
        | focuses the exact input, and into the list of inputs it watches to
        | hide the chip the moment the vendor fills them in.
        */
        $profileFields = [
            'contact_number'      => 'Contact number',
            'vendor_type'         => 'Business type',
            'appointment_mode'    => 'Appointment mode',
            'global_opening_time' => 'Opening time',
            'global_closing_time' => 'Closing time',
        ];
        foreach ($profileFields as $field => $label) {
            if (empty($this->$field)) {
                $blockers[] = ['label' => $label, 'route' => 'vendor.profile.edit', 'field' => $field];
            }
        }

        if (! $this->hasMapCoordinates()) {
            // 'map' is the anchor id; the chip watches latitude AND longitude.
            $blockers[] = ['label' => 'Shop location on map', 'route' => 'vendor.profile.edit', 'field' => 'map'];
        }

        return $blockers;
    }

    /**
     * At least one specialist a customer could actually book: on duty, priced,
     * and with a working window for the slot generator to walk.
     *
     * The second stage of setup, and the last thing between an approved shop
     * and a usable listing. An employee row on its own is not enough — an
     * inactive or unpriced one produces no slots — so the prompt, the blocker
     * and the going-live celebration all read this same check and can never
     * disagree about whether the shop is ready.
     */
    public function hasBookableEmployee(): bool
    {
        return $this->employees()
            ->where('is_active', true)
            ->where('service_fee_override', '>', 0)
            ->whereNotNull('working_start_time')
            ->whereNotNull('working_end_time')
            ->exists();
    }

    /**
     * Everything still standing between this shop and the public listing page —
     * both stages of setup at once.
     *
     * Mirrors the discovery query in CustomerDiscoveryController::discoverCandidates():
     * the settings-page fields and map coordinates (unlocated shops sink below
     * every located competitor and vanish for nearby searches), plus at least
     * one bookable specialist. Each entry is a human label and the panel route
     * where the vendor can fix it.
     *
     * This is the whole-account view — the dashboard checklist, and the gate on
     * the going-live celebration. The settings page deliberately shows only its
     * own half; see getProfileBlockers().
     */
    public function getListingBlockers(): array
    {
        $blockers = $this->getProfileBlockers();

        if (! $this->hasBookableEmployee()) {
            // Lives on another page entirely, so it carries no field to watch.
            $blockers[] = ['label' => 'One active specialist with a service fee & working hours', 'route' => 'vendor.employees.index', 'field' => null];
        }

        return $blockers;
    }

    /**
     * Whether the vendor has a paid/valid subscription window, regardless of
     * approval status. Used to gate dashboard access — a 'pending' vendor with
     * a valid window may still set up their shop while awaiting admin approval.
     */
    public function hasValidSubscriptionWindow(): bool
    {
        return $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isFuture();
    }

    /**
     * Whether this shop may reach the booking-reports section.
     *
     * Reports sit at the two ends of the plan ladder and nowhere in between:
     * a shop on the free trial gets them (so the feature is visible before
     * anyone pays for anything), and a shop that bought Premium gets them.
     * Basic and Standard do not — for those, reporting is the reason to
     * upgrade, so the section is hidden rather than shown locked.
     *
     * A vendor with no plan on file falls through to false; every gate in this
     * codebase treats a missing plan as no entitlement.
     */
    public function hasReportAccess(): bool
    {
        $plan = $this->subscriptionPlan;

        return $plan !== null && ($plan->isFree() || $plan->isPremium());
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

    /**
     * Whether the vendor qualifies for the "verified" badge shown next to their
     * name on the public profile. Granted to vendors on the top-tier Premium
     * plan (₹399) with an active subscription.
     */
    public function hasPremiumBadge(): bool
    {
        return $this->isSubscriptionActive()
            && optional($this->subscriptionPlan)->price >= 399;
    }


    /**
     * Computed attribute: is vendor truly open right now?
     * Respects the vendor's manual is_open intent flag AND checks
     * whether the current time falls within their operating window.
     * Midnight-crossing windows (e.g. open=22:00, close=02:00) are handled correctly.
     */
    public function getIsCurrentlyOpenAttribute(): bool
    {
        return $this->isEffectivelyOpen();
    }

    /**
     * Whether this shop collects a direct-to-vendor UPI advance before an
     * appointment is confirmed.
     *
     * Delegates rather than reimplements: the same three conditions decide
     * whether a payment screen can be rendered at all, and two copies of that
     * rule would eventually disagree — leaving a customer on a screen with no
     * payee or no amount.
     */
    public function acceptsDirectAdvance(): bool
    {
        return app(\App\Services\UpiPaymentService::class)->isEnabledFor($this);
    }

    public function isEffectivelyOpen()
    {
        // Vendor must have manually opened their shop
        if (!$this->is_open || $this->status !== 'active') {
            return false;
        }

        // Bookings paused = temporarily stopped but still "open" for display
        // We still show as open but booking button is disabled on frontend
        if (!$this->global_opening_time || !$this->global_closing_time) {
            return false;
        }

        $now   = now()->format('H:i:s');
        $open  = $this->global_opening_time;
        $close = $this->global_closing_time;

        if ($open < $close) {
            // Normal window: e.g. 09:00 → 18:00
            return ($now >= $open && $now <= $close);
        } else {
            // Midnight-crossing window: e.g. 22:00 → 02:00
            return ($now >= $open || $now <= $close);
        }
    }
}
