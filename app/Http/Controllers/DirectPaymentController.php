<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\CustomerBookingService;
use App\Services\UpiPaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The "pay the shop again" screen.
 *
 * NOT a step in the booking flow. A booking at a direct-payment shop is
 * confirmed the moment it is made, and the customer is handed to their UPI app
 * from the confirmation screen itself — see BookingController and the booking
 * pages' `launchUpi()`. There is no proof to submit, nothing for the platform
 * to verify, and no second page between "Pay & Book" and "Confirmed".
 *
 * What survives here is the fallback for the one thing that genuinely goes
 * wrong: the customer dismissed the UPI chooser without paying, or paid from
 * another phone, or is coming back a day later from "My Bookings" and wants the
 * QR again. It shows the amount and the same amount-locked link, and nothing
 * else. The appointment does not depend on it.
 *
 * Guest-accessible by necessity. Bookings on this platform are made without an
 * account — a shop can even switch the details form off entirely — so requiring
 * a login would lock out most of the people who want to pay. Ownership is
 * proved the same way cancellation proves it: through
 * CustomerBookingService::owns(), which matches on the phone this device
 * actually booked with, its guest key, or the signed-in customer's id, and
 * which deliberately refuses a ?phone= typed into the URL.
 */
class DirectPaymentController extends Controller
{
    public function __construct(
        private CustomerBookingService $customerBookings,
        private UpiPaymentService $upi
    ) {
    }

    public function show(Request $request, Booking $booking)
    {
        // 404 rather than 403 on purpose: a 403 confirms that booking #N
        // exists, and these ids are sequential.
        if (! $this->customerBookings->owns($booking, $request)) {
            throw new NotFoundHttpException();
        }

        $booking->loadMissing(['vendor', 'employee']);

        if (! $booking->collectsAdvance()) {
            // Either the shop never asked for a payment or it switched the
            // setting off after this booking was made. Either way the
            // appointment stands and there is no screen to show.
            return redirect()->route('bookings.mine')
                ->with('info', 'No online payment is required for this booking.');
        }

        $deepLink = $this->upi->deepLinkFor($booking);

        if (! $deepLink) {
            // The shop's VPA has gone missing since the booking was taken. Do
            // not render a payment screen with no payee — there is no way for
            // the customer to act on it and they would blame themselves.
            return redirect()->route('bookings.mine')
                ->with('error', 'This business has not finished setting up its payment details. Please pay them at the counter.');
        }

        return view('customer.payment', [
            'booking'  => $booking,
            'vendor'   => $booking->vendor,
            'deepLink' => $deepLink,
            'qrSvg'    => $this->upi->qrSvg($deepLink, 280),
            'amount'   => $this->upi->formatAmount($booking->requested_amount),
        ]);
    }
}
