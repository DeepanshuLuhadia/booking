<?php

namespace App\Models;

use Carbon\Carbon;
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
