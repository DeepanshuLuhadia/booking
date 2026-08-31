<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingNotifier;
use App\Services\CustomerBookingService;
use Illuminate\Http\Request;

/**
 * The customer's own bookings, across every vendor.
 *
 * Guests have no account, so until now a booking was only ever visible on the
 * page of the vendor it was made with: hold a token at two shops and the second
 * one hid the first. The customer then met the "you already have an active
 * booking" refusal with nothing on screen to explain it. This page is the one
 * place that shows every live booking they hold, and says plainly that a vendor
 * only reopens for them once that booking is completed.
 */
class MyBookingsController extends Controller
{
    public function __construct(private CustomerBookingService $bookings)
    {
    }

    public function index(Request $request)
    {
        $live = $this->bookings->liveBookings($request)
            ->map(fn ($booking) => $this->bookings->present($booking))
            ->values();

        $closed = $this->bookings->closedBookings($request)
            ->map(fn ($booking) => $this->bookings->present($booking))
            ->values();

        return view('customer.my-bookings', [
            'liveBookings'   => $live,
            'closedBookings' => $closed,
            // false: this page aggregates across vendors, so it trusts only the
            // numbers the device itself was remembered by — never a ?phone= in
            // the URL, which anyone could type.
            'isIdentified'   => $this->bookings->isIdentified($request, false),
        ]);
    }

    /**
     * Same payload as the page, for the poll that keeps "now serving" and the
     * queue position moving without a refresh.
     */
    public function status(Request $request)
    {
        return response()->json([
            'bookings' => $this->bookings->liveBookings($request)
                ->map(fn ($booking) => $this->bookings->present($booking))
                ->values(),
        ]);
    }

    /**
     * Let the customer drop their own booking, which releases that vendor for
     * a fresh one straight away.
     *
     * Ownership is checked against the numbers *this device* booked with — a
     * ?phone= in the URL is not accepted (see CustomerBookingService::owns), so
     * knowing someone's number is not enough to cancel their appointment.
     */
    public function cancel(Booking $booking, Request $request, BookingNotifier $notifier)
    {
        if (!$this->bookings->owns($booking, $request)) {
            return response()->json([
                'success' => false,
                'error'   => 'This booking is not on this device.',
            ], 403);
        }

        $booking->load(['employee', 'vendor.user']);

        // False means the vendor already closed it out — a stale page or a
        // double submit, not an error worth alarming the customer about.
        if (!$this->bookings->cancel($booking)) {
            return response()->json([
                'success' => false,
                'error'   => 'This booking is no longer active.',
                'status'  => $booking->status,
            ], 409);
        }

        // Redraws the shop's dashboards live and pushes to the owner and the
        // specialist in one step. See BookingNotifier.
        $notifier->cancelledByCustomer($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled. You can book with '
                . ($booking->vendor?->business_name ?? 'this business') . ' again now.',
            'bookings' => $this->bookings->liveBookings($request)
                ->map(fn ($live) => $this->bookings->present($live))
                ->values(),
        ]);
    }
}
