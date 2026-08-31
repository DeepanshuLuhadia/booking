<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'vendor_id', 'employee_id', 'customer_id', 'customer_name',
        'customer_phone', 'customer_email', 'guest_key',
        'booking_date', 'slot_start_time', 'slot_end_time',
        'booking_type', 'token_required', 'token_number', 'token_amount', 'emergency_fee',
        'online_paid_amount', 'status', 'payment_id', 'razorpay_order_id',
        'razorpay_payment_id', 'vendor_booked', 'notes',
        'fcm_token', 'next_notified_at', 'turn_notified_at',
        // Direct-to-vendor UPI advance. `requested_amount` is written from the
        // vendor's configured fee and never from customer input — it is the
        // amount lock. See UpiPaymentService.
        'payment_status', 'payment_method', 'requested_amount',
        'utr_number', 'payment_screenshot', 'payment_proof_deferred',
        'payment_submitted_at', 'payment_verified_at', 'payment_rejection_reason',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'token_required' => 'boolean',
        'vendor_booked' => 'boolean',
        'next_notified_at' => 'datetime',
        'turn_notified_at' => 'datetime',
        'requested_amount' => 'decimal:2',
        'payment_proof_deferred' => 'boolean',
        'payment_submitted_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Real Appointment Date & Time
    |--------------------------------------------------------------------------
    | `booking_date` is the *business date* — the calendar date the vendor's
    | shift started on — not necessarily the date the customer turns up. For a
    | shop trading 22:00 → 02:00, a 00:30 slot booked on the 2nd is filed under
    | booking_date = the 2nd but actually happens on the 3rd.
    |
    | Everything customer-facing must therefore render `appointment_at`, never
    | `booking_date`, or a midnight appointment reads as "today" when it is
    | tomorrow. The grouping column stays as it is — the queue, the token
    | sequence and the slot uniqueness index all depend on one shift sharing
    | one date.
    */

    /**
     * The moment the appointment actually starts, rolled onto the next day
     * when its time-of-day falls before the shift's opening time.
     */
    public function getAppointmentAtAttribute(): ?Carbon
    {
        return $this->resolveAppointmentTime($this->slot_start_time);
    }

    /**
     * The moment the appointment ends. An end time at or before the start has
     * wrapped past midnight (e.g. 23:45 → 00:00), so it lands a day later.
     */
    public function getAppointmentEndAtAttribute(): ?Carbon
    {
        $start = $this->appointment_at;
        $end   = $this->resolveAppointmentTime($this->slot_end_time);

        if (!$end) {
            return null;
        }

        if ($start && $end->lte($start)) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * Human date for the appointment ("Aug 03, 2026"). Falls back to the
     * business date for bookings that carry no slot time at all.
     */
    public function getAppointmentDateLabelAttribute(): string
    {
        return ($this->appointment_at ?? Carbon::parse($this->booking_date))->format('M d, Y');
    }

    /**
     * Anchor a slot time onto its business date, pushing it to the following
     * day when it lands before the vendor opened — i.e. after midnight.
     *
     * The vendor relation is consulted lazily; without opening hours on file
     * the time is taken at face value on the business date.
     */
    private function resolveAppointmentTime($time): ?Carbon
    {
        if (blank($time) || blank($this->booking_date)) {
            return null;
        }

        $date = Carbon::parse($this->booking_date)->toDateString();
        $at   = Carbon::parse($date . ' ' . $time);

        $opensAt = $this->vendor?->global_opening_time;

        if ($opensAt && $at->lt(Carbon::parse("$date $opensAt"))) {
            $at->addDay();
        }

        return $at;
    }

    /*
    |--------------------------------------------------------------------------
    | Direct-to-vendor UPI payment
    |--------------------------------------------------------------------------
    | The platform holds none of this money — it moves straight from the
    | customer's UPI app to the shop's bank account — and the platform verifies
    | none of it either. The booking row records only what was ASKED FOR and
    | whether the shop has since said it saw the credit.
    |
    | The states, and what each one actually means:
    |
    |   pending  — nothing was asked for (a shop that takes no payment), or a
    |              legacy booking made before this flow was simplified, whose
    |              customer never came back to pay.
    |   paid     — the customer was handed to their UPI app for this amount.
    |              NOT a confirmation of anything: it is our record that money
    |              should be arriving, and the reason the row appears on the
    |              shop's payments list.
    |   verified — the shop looked in its own UPI app and ticked it off.
    |              Bookkeeping; no booking depends on it.
    |
    | None of the three affects whether the appointment happens. `status` is
    | 'confirmed' from the moment the booking is made, whatever is owed.
    |
    | A non-zero `requested_amount` is what marks a booking as part of this
    | flow — bookings at shops that take no payment keep 0.00 — which is why
    | every check below tests the amount and not the status alone.
    |
    | `utr_number`, `payment_screenshot` and `payment_proof_deferred` are dead
    | columns kept for the rows written while the platform still collected
    | proof. Nothing writes them now.
    */

    /** Is an advance being collected on this booking at all? */
    public function collectsAdvance(): bool
    {
        return (float) $this->requested_amount > 0;
    }

    /**
     * The customer was never handed to their UPI app, or backed out of it.
     *
     * Only reachable on legacy rows now — a booking made today is marked 'paid'
     * the moment it is created. Kept because those rows still exist and their
     * owners can still be offered the payment link.
     */
    public function awaitsAdvancePayment(): bool
    {
        return $this->collectsAdvance() && $this->payment_status === 'pending';
    }

    /** Money the shop has been told to look for but has not ticked off yet. */
    public function awaitsPaymentVerification(): bool
    {
        return $this->collectsAdvance() && $this->payment_status === 'paid';
    }

    /** The shop confirmed it saw the credit in its own UPI app. */
    public function isAdvanceVerified(): bool
    {
        return $this->payment_status === 'verified';
    }

    /**
     * Public URL of a proof screenshot, on the legacy rows that carry one.
     * Vendor-facing only — the screenshot carries the customer's bank details.
     */
    public function getPaymentScreenshotUrlAttribute(): ?string
    {
        return $this->payment_screenshot
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->payment_screenshot)
            : null;
    }

    /** Payments on a shop's list that it has not ticked off yet. */
    public function scopeAwaitingPaymentVerification($query)
    {
        return $query->where('payment_status', 'paid')->where('requested_amount', '>', 0);
    }

    /**
     * Legacy held-but-never-paid rows.
     *
     * Nothing writes this state any more. It is the residue of the flow that
     * held a slot while the customer went off to pay and required them to come
     * back and prove it — the ones who never came back are these.
     */
    public function scopeAwaitingCustomerPayment($query)
    {
        return $query->where('requested_amount', '>', 0)->where('payment_status', 'pending');
    }

    /**
     * Everything the SHOP is entitled to see.
     *
     * Excludes the legacy held-but-never-paid rows above. A shop that saw them
     * would be looking at appointments nobody ever completed — it would staff
     * for them and count them in its day. Bookings made under the current flow
     * are never in that state, so this filter passes them all through.
     *
     * Deliberately a *display* filter and nothing more. Availability must still
     * count these rows (SlotGenerationService and the duplicate checks in
     * BookingController are left alone on purpose), because the whole reason
     * the row exists is to hold that slot. Hiding it from the shop and freeing
     * it for re-booking are two very different things, and only the first is
     * wanted here.
     *
     */
    public function scopeVisibleToShop($query)
    {
        return $query->where(function ($q) {
            $q->where('requested_amount', '<=', 0)
                ->orWhere('payment_status', '!=', 'pending');
        });
    }

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
