<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The shop's record of the payments customers made straight into its account.
 *
 * A ledger, not a gate. Nothing on this screen decides whether an appointment
 * happens — bookings here are confirmed the moment they are made — and the
 * platform performs no verification of its own, because the money never passes
 * through it. The shop reads its own UPI app; this page just lists what to look
 * for, and lets the shop tick off the ones it has found so it can tell at a
 * glance which are still outstanding.
 */
class PaymentVerificationController extends Controller
{
    public function __construct(private BookingNotifier $notifier)
    {
    }

    /**
     * Payments the shop has not ticked off yet, oldest first.
     *
     * Oldest first rather than newest: every row is money that has already left
     * a customer's account, so the one that has waited longest is the one most
     * likely to be a credit the shop missed.
     */
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $pending = Booking::where('vendor_id', $vendor->id)
            ->awaitingPaymentVerification()
            // `vendor` for the appointment_at accessor, which needs the opening
            // hours to place an after-midnight slot on the right calendar day.
            ->with(['employee', 'vendor'])
            ->orderBy('payment_submitted_at')
            ->get();

        // Everything already ticked off, so a shop can find a payment it
        // confirmed earlier when a customer rings up about it.
        $settled = Booking::where('vendor_id', $vendor->id)
            ->where('payment_status', 'verified')
            ->where('requested_amount', '>', 0)
            ->with(['employee', 'vendor'])
            ->latest('payment_verified_at')
            ->paginate(10);

        return view('vendor.payments.index', compact('vendor', 'pending', 'settled'));
    }

    /**
     * The shop found the credit in its own UPI app and ticked it off.
     *
     * Purely bookkeeping. The booking's `status` is deliberately NOT touched:
     * it was confirmed when it was made and it stays confirmed whether or not
     * anybody ever presses this button. All this does is move the row out of
     * the "still looking for it" list and tell the customer their money was
     * seen, which is worth something to somebody who paid a UPI id they had
     * never sent money to before.
     */
    public function approve(Request $request, Booking $booking)
    {
        $vendor = $this->authorizeBooking($booking);

        // Ticked off already — a double-submit, or two staff on the same list.
        // Return quietly rather than re-notifying the customer.
        if ($booking->payment_status !== 'paid') {
            return back()->with('info', 'This payment has already been marked received.');
        }

        // Guarded so two dashboards clicking at the same moment produce one
        // confirmation and one notification, not two.
        $applied = DB::transaction(function () use ($booking) {
            return Booking::whereKey($booking->id)
                ->where('payment_status', 'paid')
                ->update([
                    'payment_status'      => 'verified',
                    'payment_verified_at' => now(),
                    'updated_at'          => now(),
                ]);
        });

        if (! $applied) {
            return back()->with('info', 'This payment has already been marked received.');
        }

        $booking->refresh();

        $this->notifier->paymentAcknowledged($booking);

        return back()->with('success', "Marked as received. Booking #{$booking->id} was already confirmed.");
    }

    /**
     * A shop may only tick off payments made to itself.
     *
     * 404, not 403: booking ids are sequential and a 403 would confirm which of
     * them exist to any signed-in vendor willing to walk the range.
     */
    private function authorizeBooking(Booking $booking)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor || (int) $booking->vendor_id !== (int) $vendor->id) {
            throw new NotFoundHttpException();
        }

        return $vendor;
    }
}
