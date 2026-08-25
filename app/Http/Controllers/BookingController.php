<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\BookingNotifier;
use App\Services\CustomerBookingService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\ShiftService;
use App\Services\UpiPaymentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(
        Request $request,
        PaymentService $paymentService,
        NotificationService $notificationService,
        ShiftService $shifts,
        CustomerBookingService $customerBookings,
        BookingNotifier $bookingNotifier,
        UpiPaymentService $upi
    ) {
        try {
            $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'employee_id' => 'required|exists:employees,id',
                // Slot times only matter for time-slot mode; token-mode slot times
                // are computed server-side, so they are validated conditionally below.
                'slot_start' => 'nullable',
                'slot_end' => 'nullable',
                'booking_type' => 'required|in:normal,premium',
                'payment_id' => 'nullable|string'
            ]);

            $vendor = Vendor::with('user')->findOrFail($request->vendor_id);

            /*
            | Whether this shop asks who is booking.
            |
            | The setting is per *vendor*, so it covers every one of its
            | employees — a customer scanning a specialist's QR code is governed
            | by the shop's choice, not the specialist's. With it off, the
            | customer taps "book" and the appointment is made on the spot, so
            | name and phone must not be required here either; the details form
            | that would have supplied them was never shown.
            */
            $requireDetails = (bool) $vendor->require_customer_details;

            $request->validate([
                'customer_name'  => ($requireDetails ? 'required' : 'nullable') . '|string|max:50',
                'customer_phone' => ($requireDetails ? 'required' : 'nullable') . '|digits:10',
                'customer_email' => 'nullable|email|max:255',
            ]);

            /*
            | Fall back to the signed-in customer's own details when the form did
            | not collect them, so a logged-in visitor booking at a no-details
            | shop still appears on the vendor's sheet under their real name.
            |
            | These are the values everything MATCHES on — the throttle key, both
            | booking limits, and the phone the device is remembered by. A phone
            | we do not actually have must stay null here, never a placeholder:
            | a shared stand-in would make every anonymous customer look like the
            | same person and lock the second one out of the shop for the day.
            */
            $user           = auth()->user();
            $customerName   = $request->filled('customer_name')  ? $request->customer_name  : ($user?->name ?: null);
            $customerPhone  = $request->filled('customer_phone') ? $request->customer_phone : ($user?->mobile ?: null);
            $customerEmail  = $request->filled('customer_email') ? $request->customer_email : ($user?->email ?: null);

            /*
            | ...and these are the values that get WRITTEN. A shop that collects
            | nothing still wants a legible row on its sheet and its reports, so
            | the blanks are filled in with something that says plainly what
            | happened rather than leaving an empty cell that reads as a bug.
            */
            $recordedName  = $customerName  ?: 'Anonymous User';
            $recordedPhone = $customerPhone ?: 'Anonymous';

            /*
            | This device's identity, minted on the first booking it ever makes.
            | Without customer details there is no phone number to recognise the
            | customer by later, so every booking carries this instead — it is
            | what makes an anonymous booking visible on "my bookings", countable
            | against the limits below, and cancellable by whoever made it.
            */
            $guestKey = $customerBookings->ensureGuestKey($request);

            // 1. Throttling (3 bookings per day). Keyed on the phone number when
            // there is one, and on the device otherwise — an anonymous shop must
            // not become an unthrottled one.
            $throttleKey = $customerPhone
                ? 'booking-phone:' . $customerPhone
                : 'booking-guest:' . $guestKey;

            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
                return response()->json([
                    'success' => false,
                    'error'   => $customerPhone
                        ? 'Booking limit reached for this phone number today.'
                        : 'Booking limit reached for this device today.',
                ], 429);
            }

            /*
            | The shift this booking belongs to, NOT the calendar date. A shop
            | trading 22:00 → 02:00 is still working the same sheet at 00:30, so
            | filing that booking under "today" would restart its token sequence
            | mid-shift and hide it from the queue the vendor is looking at.
            */
            $bookingDate = $shifts->businessDate($vendor);

            /*
            | Everything that identifies the customer making this request. The
            | phone number is absent at a shop that does not ask for one, so the
            | device's guest key stands in — matching on the phone alone would
            | leave anonymous bookings uncounted by both limits below.
            */
            $identity = function ($q) use ($customerPhone, $guestKey) {
                $q->where(function ($inner) use ($customerPhone, $guestKey) {
                    if ($customerPhone) {
                        $inner->orWhere('customer_phone', $customerPhone);
                    }
                    $inner->orWhere('guest_key', $guestKey);
                });
            };

            // 2. Cross-Vendor Daily Limit (Max 3 active tokens). Spans the two
            // live business dates so an overnight shift keeps counting the
            // tokens a customer already holds instead of resetting at midnight.
            $activeBookingsToday = Booking::where($identity)
                ->whereIn('booking_date', $shifts->liveBusinessDates())
                ->whereIn('status', ['confirmed', 'pending'])
                ->count();

            if ($activeBookingsToday >= 3) {
                // Point at the page that lists them. The limit counts bookings
                // made with *other* vendors too, which the customer cannot see
                // from here — without the link this reads as an arbitrary refusal.
                return response()->json([
                    'success'      => false,
                    'error'        => 'You have reached the maximum number of active bookings for today.',
                    'bookings_url' => route('bookings.mine'),
                ], 429);
            }

            /*
            | 3. One Active Token Per Vendor (Duplicate Check), scoped to the shift.
            |
            | Only `pending` / `confirmed` block. The moment the vendor marks the
            | appointment completed — or it is cancelled, skipped, or expired by
            | the nightly reset — this vendor is bookable again for the customer.
            */
            $duplicate = Booking::where('vendor_id', $vendor->id)
                ->where('booking_date', $bookingDate)
                ->whereIn('status', ['confirmed', 'pending'])
                ->where(function ($q) use ($customerPhone, $guestKey) {
                    if ($customerPhone) {
                        $q->orWhere('customer_phone', $customerPhone);
                    }
                    $q->orWhere('guest_key', $guestKey);
                    if (auth()->check()) {
                        $q->orWhere('customer_id', auth()->id());
                    }
                })->exists();

            if ($duplicate) {
                return response()->json([
                    'success'      => false,
                    'error'        => 'You already have an active booking with this business. You can book again once it is completed.',
                    'bookings_url' => route('bookings.mine'),
                ], 422);
            }

            // Time-slot bookings carry a real customer-chosen slot; enforce its
            // format. Token bookings ignore the submitted slot entirely.
            if ($vendor->appointment_mode !== 'token') {
                $request->validate([
                    'slot_start' => 'required|date_format:H:i',
                    // NOTE: cannot use `after:slot_start` here. Overnight shops
                    // legitimately generate slots that wrap past midnight
                    // (e.g. 23:45 -> 00:00), and `after` compares both values on
                    // the SAME day, so it wrongly rejects the wrapped end time.
                    // The slot values are server-generated and chosen from a fixed
                    // list, so we only guard the format and that the two differ.
                    'slot_end'   => 'required|date_format:H:i|different:slot_start',
                ]);
            }

            // 4. Vendor Status Validation
            if ($vendor->status !== 'active' || !$vendor->is_open) {
                return response()->json(['success' => false, 'error' => 'This vendor is not currently accepting bookings.'], 403);
            }
            if ($vendor->bookings_paused) {
                return response()->json(['success' => false, 'error' => 'Bookings are currently paused by the vendor.'], 403);
            }
            if (!$vendor->isSubscriptionActive()) {
                return response()->json(['success' => false, 'error' => 'Booking is not allowed as the business subscription has expired or is inactive.'], 403);
            }

            $employee = Employee::findOrFail($request->employee_id);

            $baseServiceFee = $employee->service_fee_override ?? $vendor->service_fee;
            $premiumFee = $request->booking_type === 'premium' ? ($employee->premium_fee ?? 0) : 0;
            $tokenAmount = ($vendor->appointment_mode === 'token') ? $vendor->token_amount : 0;
            $totalToPay = $tokenAmount + $premiumFee;

            /*
            | Direct-to-vendor UPI advance.
            |
            | The figure is taken from the VENDOR's configuration and nowhere
            | else. Nothing the customer submits can influence it — there is no
            | amount field in this request to influence it with — so the amount
            | on the row, the amount encoded into the UPI deep link, and the
            | amount the shop verifies against its bank statement are all the
            | same server-side value. That is the whole of the amount lock; the
            | `mam` parameter on the link only makes the right amount the
            | default in the customer's UPI app, it does not enforce anything we
            | could rely on.
            |
            | It is also frozen here rather than read live: a shop that raises
            | its fee tomorrow must not change what a customer was quoted today.
            |
            | How much is due depends on whether the shop named an advance:
            | with one, that advance; without one, the FULL booking price and
            | the balance is nothing. `$fullBookingAmount` is deliberately the
            | same sum the customer was already shown as "Due Now" on the
            | booking screen (base service fee, plus the premium supplement when
            | they picked a premium slot) — being asked to transfer a number
            | larger than the one quoted a moment earlier is how a legitimate
            | charge comes to look like a scam.
            |
            | Note this uses $baseServiceFee, NOT $totalToPay: the latter is
            | token + premium and excludes the service fee entirely, so on the
            | many shops with token_amount = 0 it would ask for ₹0 and collect
            | nothing at all.
            */
            $fullBookingAmount = (float) $baseServiceFee + (float) $premiumFee;

            $advanceAmount = $upi->amountDueFor($vendor, $fullBookingAmount);
            $collectsAdvance = (float) $advanceAmount > 0;

            $avgTime = $vendor->avg_consultation_time ?: 15;

            // Time-slot mode persists the customer's chosen slot. Token-mode slot
            // times are derived from the token number inside the transaction (each
            // token gets a distinct estimated time — this also keeps the
            // (employee, date, slot_start_time) unique index collision-free).
            $requestedStart = $request->slot_start;
            $requestedEnd   = $request->slot_end;

            // 5 & 6. Transaction & Token Cap
            $booking = \Illuminate\Support\Facades\DB::transaction(function () use ($vendor, $employee, $request, $bookingDate, $avgTime, $requestedStart, $requestedEnd, $tokenAmount, $premiumFee, $totalToPay, $baseServiceFee, $recordedName, $recordedPhone, $customerEmail, $guestKey, $advanceAmount, $collectsAdvance) {
                $tokenNumber = null;

                if ($vendor->appointment_mode === 'token') {
                    // Token queue is per-employee. Lock the employee's rows for today
                    // so two concurrent requests cannot read the same MAX.
                    $lastToken = Booking::where('employee_id', $employee->id)
                        ->where('booking_date', $bookingDate)
                        ->whereNotNull('token_number')
                        ->lockForUpdate()
                        ->max('token_number') ?? 0;

                    // Optional daily cap set by the employee (null = unlimited).
                    if ($employee->max_daily_tokens && $lastToken >= $employee->max_daily_tokens) {
                        throw new \Exception('No more tokens available for this employee today.', 403);
                    }

                    $tokenNumber = $lastToken + 1;

                    // Estimated start = now + (position in queue) * avg service time.
                    $estStart  = Carbon::now()->addMinutes(($tokenNumber - 1) * $avgTime);
                    $slotStart = $estStart->format('H:i:s');
                    $slotEnd   = $estStart->copy()->addMinutes($avgTime)->format('H:i:s');
                } else {
                    $slotStart = $requestedStart;
                    $slotEnd   = $requestedEnd;

                    // Verify slot availability under pessimistic lock to prevent concurrent double booking
                    $alreadyBooked = Booking::where('employee_id', $employee->id)
                        ->where('booking_date', $bookingDate)
                        ->where('slot_start_time', $slotStart)
                        ->whereIn('status', ['confirmed', 'pending'])
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyBooked) {
                        throw new \Exception('This slot was just booked by another customer. Please select another time.', 409);
                    }
                }

                return Booking::create([
                    'vendor_id'            => $vendor->id,
                    'employee_id'          => $employee->id,
                    'customer_id'          => auth()->id(),
                    'customer_name'        => $recordedName,
                    'customer_phone'       => $recordedPhone,
                    'customer_email'       => $customerEmail,
                    // Who this device is, for the shops that never ask.
                    'guest_key'            => $guestKey,
                    /*
                    | The device to ping when this customer's token is called.
                    |
                    | Guests carry it in the session; a signed-in customer has it
                    | on their user record instead (FcmTokenController writes to
                    | one or the other), so both are consulted. Still null when
                    | they have not granted notifications yet — that case is
                    | repaired later by CustomerBookingService::attachDeviceToken,
                    | because permission is asked for only after this point.
                    */
                    'fcm_token'            => session('fcm_token')
                        ?? (auth()->check() ? auth()->user()->fcm_token : null),
                    'booking_date'         => $bookingDate,
                    'slot_start_time'      => $slotStart,
                    'slot_end_time'        => $slotEnd,
                    'booking_type'         => $request->booking_type,
                    'token_required'       => ($vendor->appointment_mode === 'token'),
                    'token_number'         => $tokenNumber,
                    'token_amount'         => $tokenAmount,
                    'emergency_fee'        => $premiumFee,
                    'online_paid_amount'   => $totalToPay,
                    /*
                    | Confirmed the moment it is made, advance or not.
                    |
                    | The advance is collected directly by the shop, outside
                    | this platform, and the platform performs no verification
                    | of it — so there is nothing for the booking to WAIT on.
                    | Holding the slot as `pending` until somebody ticked a box
                    | only produced two ways to lose an appointment that had
                    | already been paid for: a customer who never got round to
                    | uploading a screenshot, and a shop that never got round to
                    | looking at one. The shop checks the money at the counter,
                    | the same way it checks cash.
                    */
                    'status'               => 'confirmed',
                    /*
                    | 'paid' means "the customer was handed to their UPI app for
                    | this amount", not "the money is confirmed" — nothing on
                    | this platform can confirm that, because none of it passes
                    | through us. It is what puts the booking on the shop's
                    | payments list to be checked against their own UPI app.
                    */
                    'payment_status'       => $collectsAdvance ? 'paid' : 'pending',
                    'payment_submitted_at' => $collectsAdvance ? now() : null,
                    'payment_method'       => 'direct_upi',
                    // 0.00 at a shop that takes no advance — and a non-zero
                    // value here is what marks the booking as part of this flow.
                    'requested_amount'     => $advanceAmount,
                    'vendor_booked'        => false,
                    'razorpay_payment_id'  => $request->payment_id,
                    'notes'                => "Service Fee: ₹{$baseServiceFee}"
                ]);
            });

            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 86400);

            // Remember the guest so every booking page can recognise them on the
            // next visit and show their live token instead of the "book" button.
            // The service keeps *every* number booked from this device, not just
            // the latest, so booking for a second person no longer hides the
            // first person's booking. A shop that collects no details leaves
            // nothing to remember here — the guest key stamped on the row above
            // is already in the session and cookie and carries that case.
            // $customerPhone, not $booking->customer_phone — the row carries the
            // "Anonymous" placeholder, and remembering that as a phone number
            // would make this device match every other anonymous booking.
            if ($customerPhone) {
                $customerBookings->remember($customerPhone, $request);
            }

            // Invalidate discovery and slot caches so the next listing/slot request
            // reflects the newly confirmed booking immediately.
            \Illuminate\Support\Facades\Cache::forget('default_discovery_candidates');
            \Illuminate\Support\Facades\Cache::forget("slots:{$employee->id}:{$bookingDate}");

            $booking->setRelation('employee', $employee);
            $booking->setRelation('vendor', $vendor);

            /*
            | Every booking is announced here, advance or not.
            |
            | This announcement used to be withheld from bookings that owed an
            | advance and re-fired later, when the customer uploaded a proof —
            | which meant a shop learned about an appointment only if the
            | customer completed a second form after paying. The advance is no
            | longer something the platform waits on, so there is nothing left
            | to defer: the shop's dashboards redraw, the owner and the
            | specialist get their push, and the customer is told the booking is
            | confirmed, all at the moment it is made.
            */
            $bookingNotifier->created($booking, 'customer');

            /*
            | ...and, on top of that, the shop is told there is money to look
            | for. Separate from the booking announcement because it asks for a
            | different action — open your UPI app and check the credit — and it
            | is the only thing standing in for the verification step this flow
            | deliberately no longer has.
            */
            if ($collectsAdvance) {
                $bookingNotifier->directPaymentDue($booking);
            }

            $fcmToken = session('fcm_token');
            if ($fcmToken) {
                $dummyUser = new \App\Models\User(['fcm_token' => $fcmToken]);
                
                // Smart Notification Title Logic
                $hour = now()->hour;
                $greeting = 'Booking Confirmed!';
                
                if ($hour >= 5 && $hour < 12) {
                    $greeting = 'Start your day right! 🌅';
                } elseif ($hour >= 12 && $hour < 17) {
                    $greeting = 'Good Afternoon! ☀️';
                } elseif ($hour >= 17 && $hour < 22) {
                    $greeting = 'Evening plans set! 🌙';
                }

                $cat = strtolower($vendor->category?->slug ?? '');
                if (in_array($cat, ['salon', 'barber', 'beauty'])) {
                    $title = "{$greeting} Your grooming appointment is confirmed. ✂️";
                } elseif (in_array($cat, ['clinic', 'doctor', 'health'])) {
                    $title = "{$greeting} Your checkup at {$vendor->business_name} is booked. 🩺";
                } elseif (in_array($cat, ['sports', 'gym', 'turf'])) {
                    $title = "{$greeting} Game on! Your slot at {$vendor->business_name} is confirmed. ⚽";
                } else {
                    $title = "{$greeting} Your slot with {$employee->name} is confirmed.";
                }

                $notificationService->sendWebPush(
                    $dummyUser,
                    $title,
                    "Your appointment with {$employee->name} at {$vendor->business_name} is confirmed."
                );
            }

            // Built from the booking row's own frozen amount, so the link the
            // customer taps and the figure the shop looks for cannot disagree.
            $upiLink = $collectsAdvance ? $upi->deepLinkFor($booking) : null;

            $nowServing = $employee->now_serving_token ?? 0;

            $queueVelocityService = new \App\Services\QueueVelocityService();

            // Counted, not `token - now_serving`. now_serving_token records the
            // last token *handled*, so subtracting it counts the customer who has
            // just been finished with as somebody still in front of you — the
            // confirmation screen said "1 ahead" when the queue was clear.
            $peopleAhead = $queueVelocityService->peopleAheadOf($employee, $booking->token_number ?? 0);
            $approxWait  = $queueVelocityService->calculateEstimatedWait($vendor, $employee, $booking->token_number ?? 0);
            $serving     = $queueVelocityService->servingState($employee);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully!',
                'booking' => [
                    'id'              => $booking->id,
                    'token_number'    => $booking->token_number,
                    /*
                    | Everything the confirmation screen needs to hand the
                    | customer straight to their UPI app, sent WITH the
                    | confirmation rather than as a URL to a second page.
                    |
                    | The deep link is what raises the device's own payment
                    | chooser — whichever UPI apps are installed — when the
                    | customer taps Pay on the confirmation screen. The QR is the
                    | desktop equivalent, where there is no app to hand off to.
                    | `null` when the shop takes no advance, which the caller
                    | simply falls through on.
                    */
                    'payment' => $collectsAdvance ? [
                        'amount'        => $advanceAmount,
                        'upi_link'      => $upiLink,
                        'qr_svg'        => $upi->qrSvg($upiLink, 200),
                        'payee'         => $upi->payeeName($vendor),
                        'vpa'           => $vendor->upi_id,
                        'employee_name' => $employee->name,
                        // "advance" vs "full amount" — different promises about
                        // what is left to settle at the counter.
                        'is_advance'    => $upi->chargesFixedAdvance($vendor),
                    ] : null,
                    'payment_required' => $collectsAdvance,
                    'requested_amount' => $collectsAdvance ? $advanceAmount : null,
                    'payment_status'   => $booking->payment_status,
                    'vendor_name'     => $vendor->business_name,
                    'now_serving'     => $nowServing,
                    'people_ahead'    => $peopleAhead,
                    'approx_wait_min' => $approxWait,
                    'serving_label'   => $serving['serving_label'],
                    'serving_display' => $serving['serving_display'],
                    // The date the customer actually turns up on. Differs from
                    // the business date for after-midnight slots, so the
                    // confirmation screen must use this and not "today".
                    'booking_date'    => $booking->appointment_date_label,
                    'slot_time'       => $booking->appointment_at?->format('h:i A'),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => collect($e->errors())->flatten()->first(),
            ], 422);

        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::warning('BookingController@store database conflict: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'This slot was just booked by another customer. Please choose a different time.',
            ], 409);

        } catch (\Throwable $e) {
            $code = ($e->getCode() >= 400 && $e->getCode() < 600) ? (int) $e->getCode() : 500;
            \Illuminate\Support\Facades\Log::error('BookingController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage() ?: 'Booking could not be completed. Please try again.',
            ], $code);
        }
    }
}
