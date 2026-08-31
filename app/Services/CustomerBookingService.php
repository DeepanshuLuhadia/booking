<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * One place that answers "who is this customer, and what bookings do they
 * currently hold?" — across every vendor, not just the one being looked at.
 *
 * The rule the whole booking flow turns on: a booking that is still `pending`
 * or `confirmed` occupies the customer's one allowed slot at that vendor.
 * BookingController refuses a second one, so anything that is still live must
 * be *visible* to the customer — otherwise they meet the refusal as an
 * unexplained error on a form they were allowed to fill in. Once the vendor
 * marks it completed (or it is cancelled / skipped / expired by the nightly
 * reset) the vendor is free again and the customer may book there once more.
 *
 * Identity for a guest is the phone number recorded at booking time, which
 * BookingController writes to both the session and a 30-day cookie. Signed-in
 * customers are matched on their user id as well.
 *
 * A shop may switch the details form off entirely (Vendor::
 * $require_customer_details), in which case there is no phone number to
 * identify anyone by. Every booking therefore also carries a `guest_key` —
 * an opaque per-device id held in the same session/cookie pair — so an
 * anonymous booking still belongs to the device that made it.
 */
class CustomerBookingService
{
    /**
     * Statuses that still hold the customer's place at a vendor. Kept in step
     * with BookingController's duplicate check — the two must never drift, or
     * a booking gets blocked without being shown.
     */
    public const LIVE_STATUSES = ['pending', 'confirmed'];

    /**
     * Statuses that release the vendor for a fresh booking. Shown as history so
     * the customer can see the appointment finished and re-book.
     */
    public const CLOSED_STATUSES = ['completed', 'cancelled', 'skipped', 'expired'];

    public function __construct(private ShiftService $shifts)
    {
    }

    /**
     * How many booking phone numbers one device is remembered by. A household
     * sharing a phone books for a few people; beyond that the oldest is dropped
     * so the cookie stays small.
     */
    private const REMEMBERED_PHONES = 5;

    /**
     * Session/cookie name holding this device's opaque guest id.
     */
    private const GUEST_KEY = 'guest_key';

    /**
     * The identity this request carries: [phones, customerId, guestKey]. Any of
     * the three may be empty/null; when all are, the visitor has no bookings we
     * can find.
     *
     * Every number this device has booked with is kept, not just the last one.
     * Storing a single number meant that booking for a second person overwrote
     * the first, and the earlier booking became invisible on this device while
     * still being counted against it by the duplicate check.
     *
     * NOTE: read cookies via $request->cookie(). The global cookie() helper is
     * a factory that *makes* a cookie and always returns a truthy object.
     */
    public function identify(?Request $request = null, bool $allowQueryPhone = true): array
    {
        $request = $request ?? request();

        $phones = collect([
            // ?phone= lets a customer point a single vendor page at a booking
            // this device was not remembered by — the vendor page has always
            // accepted it. Callers that aggregate across vendors pass
            // $allowQueryPhone = false, so an arbitrary number typed into the
            // URL cannot be used to read out somebody's whole booking list.
            $allowQueryPhone ? $request->query('phone') : null,
            session('customer_phone'),
            $request->cookie('customer_phone'),
        ])
            ->merge($this->decodePhones(session('customer_phones')))
            ->merge($this->decodePhones($request->cookie('customer_phones')))
            ->filter(fn ($phone) => filled($phone))
            ->map(fn ($phone) => (string) $phone)
            /*
            | Only something shaped like a real phone number may be looked up.
            |
            | A booking made at a shop that collects no details is filed under
            | the literal "Anonymous" so the vendor's sheet reads sensibly — and
            | ?phone= is accepted from the URL. Without this filter, visiting
            | ?phone=Anonymous would match every anonymous booking on the
            | platform. Nothing legitimate is lost: every number that reaches
            | the session or cookie came through a `digits:10` rule.
            */
            ->filter(fn ($phone) => ctype_digit($phone) && strlen($phone) >= 6 && strlen($phone) <= 15)
            ->unique()
            ->values()
            ->all();

        return [
            $phones,
            auth()->check() ? auth()->id() : null,
            $this->guestKey($request),
        ];
    }

    public function isIdentified(?Request $request = null, bool $allowQueryPhone = true): bool
    {
        [$phones, $customerId, $guestKey] = $this->identify($request, $allowQueryPhone);

        return $phones !== [] || $customerId !== null || $guestKey !== null;
    }

    /**
     * This device's guest id, or null if it has never booked.
     *
     * Unlike the phone list this is never accepted from the URL — it is the
     * whole of an anonymous customer's identity, so it may only come from
     * somewhere the visitor cannot type into.
     */
    public function guestKey(?Request $request = null): ?string
    {
        $request = $request ?? request();

        $key = $this->sessionFor($request)?->get(self::GUEST_KEY)
            ?? $request->cookie(self::GUEST_KEY);

        return filled($key) ? (string) $key : null;
    }

    /**
     * The guest id to stamp on a booking being made right now, minting one if
     * this device does not have it yet. Written to the session (this visit) and
     * a 30-day cookie (the next one), exactly as remember() does for phones.
     */
    public function ensureGuestKey(?Request $request = null): string
    {
        $request = $request ?? request();

        $key = $this->guestKey($request) ?? (string) \Illuminate\Support\Str::uuid();

        $this->sessionFor($request)?->put(self::GUEST_KEY, $key);
        \Illuminate\Support\Facades\Cookie::queue(self::GUEST_KEY, $key, 60 * 24 * 30);

        return $key;
    }

    /**
     * The session belonging to THIS request, or null if it has none.
     *
     * Read through the request rather than the session() helper: the helper
     * resolves a process-wide singleton, so two requests handled in one process
     * would share a guest key — and the guest key is the whole of an anonymous
     * customer's identity, so sharing it would hand one visitor another's
     * bookings.
     */
    private function sessionFor(Request $request): ?\Illuminate\Contracts\Session\Session
    {
        return $request->hasSession() ? $request->session() : null;
    }

    /**
     * Record a number this device has booked with, so its bookings stay visible
     * here. Written to the session (this visit) and a 30-day cookie (the next
     * one) — a guest has no account, so this is the whole of their identity.
     */
    public function remember(string $phone, ?Request $request = null): void
    {
        [$known] = $this->identify($request);

        $phones = collect($known)
            ->prepend($phone)
            ->unique()
            ->take(self::REMEMBERED_PHONES)
            ->values()
            ->all();

        // `customer_phone` (singular) is kept in step for the older readers that
        // still expect one number.
        session([
            'customer_phone'  => $phone,
            'customer_phones' => $phones,
        ]);

        \Illuminate\Support\Facades\Cookie::queue('customer_phone', $phone, 60 * 24 * 30);
        \Illuminate\Support\Facades\Cookie::queue('customer_phones', json_encode($phones), 60 * 24 * 30);
    }

    /**
     * Phone lists survive as a JSON string in the cookie and as an array in the
     * session; accept either, and never let malformed input become a filter.
     */
    private function decodePhones($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Every booking this customer holds across every vendor on the business
     * dates that are still live.
     *
     * The window is deliberately the same one BookingController's cross-vendor
     * limit counts — yesterday's business date included, because a shop trading
     * past midnight is still working a sheet filed under yesterday. Scoping the
     * display any tighter is what made a live booking invisible while it was
     * still being counted against the customer.
     */
    public function liveBookings(?Request $request = null): Collection
    {
        return $this->query(self::LIVE_STATUSES, $request, false)
            ->orderBy('booking_date')
            ->orderBy('slot_start_time')
            ->get();
    }

    /**
     * Bookings on the same live window that have already been closed out. These
     * no longer block anything — they are shown so the customer can see the
     * appointment is done and that the vendor is bookable again.
     */
    public function closedBookings(?Request $request = null): Collection
    {
        return $this->query(self::CLOSED_STATUSES, $request, false)
            ->latest('updated_at')
            ->limit(20)
            ->get();
    }

    /**
     * This customer's live booking at one particular vendor, if any. Used by
     * the vendor and employee pages to replace the booking form with the token
     * the customer already holds.
     */
    public function liveBookingFor(Vendor $vendor, ?Request $request = null): ?Booking
    {
        return $this->query(self::LIVE_STATUSES, $request)
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->first();
    }

    /**
     * The same lookup narrowed to a single employee, for the public
     * single-employee booking page.
     */
    public function liveBookingForEmployee(int $employeeId, ?Request $request = null): ?Booking
    {
        return $this->query(self::LIVE_STATUSES, $request)
            ->where('employee_id', $employeeId)
            ->latest()
            ->first();
    }

    /**
     * How many vendors this customer is currently holding a booking with —
     * drives the badge in the site navigation.
     */
    public function liveBookingCount(?Request $request = null): int
    {
        if (!$this->isIdentified($request, false)) {
            return 0;
        }

        return $this->query(self::LIVE_STATUSES, $request, false)->count();
    }

    /**
     * Stamp this device's push token onto the bookings it is already holding.
     *
     * The order of events is the problem this solves. A guest books first and is
     * only asked for notification permission *afterwards* — so the booking row is
     * written with no token, and by the time the browser registers one the row it
     * belongs to has already been saved. The shop could then complete or cancel
     * that appointment and the customer would never hear about it, because the
     * only address we had for them was blank.
     *
     * Called whenever a token is registered, so a customer who accepts
     * notifications at any point starts receiving updates for what they are
     * already waiting on. Existing tokens are overwritten: FCM rotates them, and
     * the stale one is dead weight.
     *
     * @return int rows updated
     */
    public function attachDeviceToken(string $fcmToken, ?Request $request = null): int
    {
        if (!$this->isIdentified($request, false)) {
            return 0;
        }

        return $this->query(self::LIVE_STATUSES, $request, false)
            ->where(function ($q) use ($fcmToken) {
                $q->whereNull('fcm_token')->orWhere('fcm_token', '!=', $fcmToken);
            })
            ->update(['fcm_token' => $fcmToken]);
    }

    /**
     * Is this device holding a live booking we would be unable to notify about?
     *
     * A booking is unreachable when it carries no device token AND its customer
     * has none on their account either — the state every guest booking starts in,
     * because permission is only asked for after the booking is made. Used to
     * re-offer notifications at the one moment they obviously matter, rather than
     * asking once on a first visit and never again.
     */
    public function hasLiveBookingsMissingPush(?Request $request = null): bool
    {
        if (!$this->isIdentified($request, false)) {
            return false;
        }

        return $this->query(self::LIVE_STATUSES, $request, false)
            ->whereNull('fcm_token')
            ->where(function ($q) {
                $q->whereNull('customer_id')
                    ->orWhereDoesntHave('customer', fn ($c) => $c->whereNotNull('fcm_token'));
            })
            ->exists();
    }

    /**
     * Does this device own the booking well enough to act on it?
     *
     * Deliberately stricter than the read path: a ?phone= in the URL is never
     * accepted here, so knowing (or guessing) somebody's number cannot be used
     * to cancel their appointment. Only a number this device actually booked
     * with — its guest id, or the signed-in customer's own id — counts.
     */
    public function owns(Booking $booking, ?Request $request = null): bool
    {
        [$phones, $customerId, $guestKey] = $this->identify($request, false);

        if ($customerId !== null && (int) $booking->customer_id === (int) $customerId) {
            return true;
        }

        // The only handle on a booking made without any customer details.
        if ($guestKey !== null && $booking->guest_key !== null
            && hash_equals((string) $booking->guest_key, $guestKey)) {
            return true;
        }

        return $booking->customer_phone !== null
            && in_array((string) $booking->customer_phone, $phones, true);
    }

    /**
     * Cancel a booking on the customer's own behalf.
     *
     * The token counter is deliberately left alone. Only the head of the queue
     * advances `now_serving_token` — that is the vendor's and the employee's
     * call — and a customer cancelling from three places back must not skip the
     * people waiting ahead of them.
     *
     * Returns false when the booking is no longer live, so a double-submit or a
     * stale page cannot re-cancel something the vendor has already completed.
     */
    public function cancel(Booking $booking): bool
    {
        if (!in_array($booking->status, self::LIVE_STATUSES, true)) {
            return false;
        }

        $booking->update(['status' => 'cancelled']);

        // Mirrors the write path in BookingController: the freed slot has to
        // reappear on the next listing and slot request, not up to a minute later.
        \Illuminate\Support\Facades\Cache::forget('default_discovery_candidates');
        \Illuminate\Support\Facades\Cache::forget(
            "slots:{$booking->employee_id}:" . Carbon::parse($booking->booking_date)->toDateString()
        );

        return true;
    }

    /**
     * Flatten a booking into everything the customer-facing screens render, so
     * the page and its polling endpoint cannot disagree about the queue.
     */
    public function present(Booking $booking): array
    {
        $employee   = $booking->employee;
        $vendor     = $booking->vendor;
        $nowServing = (int) ($employee?->now_serving_token ?? 0);
        $token      = (int) ($booking->token_number ?? 0);

        $peopleAhead = 0;
        $approxWait  = 0;
        $serving     = ['serving_label' => 'Now Serving', 'serving_display' => '—', 'is_serving' => false];

        if ($token > 0 && $employee) {
            $velocity = new QueueVelocityService();

            // Counted, not `token - now_serving`: a completed or cancelled token
            // below this one is no longer anybody standing in front of you.
            $peopleAhead = $velocity->peopleAheadOf($employee, $token);

            // Whether anyone is actually in the chair, so the screen does not keep
            // announcing a token that has already walked out.
            $serving = $velocity->servingState($employee);

            if ($vendor) {
                $approxWait = $velocity->calculateEstimatedWait($vendor, $employee, $token);
            }
        }

        return [
            'id'            => $booking->id,
            'vendor_id'     => $booking->vendor_id,
            'vendor_name'   => $vendor?->business_name ?? 'This business',
            'vendor_slug'   => $vendor?->slug,
            'employee_id'   => $booking->employee_id,
            'employee_name' => $employee?->name ?? 'your specialist',
            'token_number'  => $booking->token_number,
            'now_serving'   => $nowServing,
            'people_ahead'  => $peopleAhead,
            'approx_wait_min' => $approxWait,
            // "Now Serving #9" vs "Up Next #10" vs "—". See servingState().
            'serving_label'   => $serving['serving_label'],
            'serving_display' => $serving['serving_display'],
            'is_serving'      => $serving['is_serving'],
            // appointment_at, never booking_date: a 00:30 slot on an overnight
            // shift is filed under the previous day's sheet but happens the
            // next morning, and the customer must be told the day they turn up.
            'slot_time'     => $booking->appointment_at?->format('h:i A'),
            'booking_date'  => $booking->appointment_date_label,
            'status'        => $booking->status,
            'status_label'  => ucfirst($booking->status),
            'is_live'       => in_array($booking->status, self::LIVE_STATUSES, true),

            /*
            | Direct-to-vendor UPI payment, if this shop takes one.
            |
            | Carried on every booking payload so My Bookings can say whether
            | the money went out, and — for the customer who dismissed the
            | payment chooser without paying — offer the way back to it. None of
            | it affects the appointment, which is confirmed either way. Null
            | for shops that take no payment.
            */
            'payment_status'   => $booking->collectsAdvance() ? $booking->payment_status : null,
            'requested_amount' => $booking->collectsAdvance()
                ? number_format((float) $booking->requested_amount, 2, '.', '')
                : null,
            'payment_due'      => $booking->awaitsAdvancePayment(),
            'payment_url'      => $booking->collectsAdvance()
                ? route('payment.show', $booking)
                : null,
        ];
    }

    /**
     * Base query: this customer's bookings in the given statuses, on business
     * dates that have not yet been closed out.
     *
     * `vendor` is eager loaded because the appointment_at accessor needs the
     * opening hours to place an after-midnight slot on the right calendar day.
     */
    private function query(array $statuses, ?Request $request = null, bool $allowQueryPhone = true)
    {
        [$phones, $customerId, $guestKey] = $this->identify($request, $allowQueryPhone);

        $query = Booking::query()
            ->whereIn('status', $statuses)
            ->where('booking_date', '>=', $this->windowStart())
            ->with(['employee', 'vendor']);

        if ($phones === [] && $customerId === null && $guestKey === null) {
            // No identity: match nothing rather than everything.
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($phones, $customerId, $guestKey) {
            if ($phones !== []) {
                $q->whereIn('customer_phone', $phones);
            }
            if ($customerId !== null) {
                $q->orWhere('customer_id', $customerId);
            }
            if ($guestKey !== null) {
                $q->orWhere('guest_key', $guestKey);
            }
        });
    }

    /**
     * Earliest business date that can still hold a live booking — yesterday,
     * to keep an overnight shift's sheet in view.
     */
    private function windowStart(): string
    {
        return collect($this->shifts->liveBusinessDates())->min();
    }
}
