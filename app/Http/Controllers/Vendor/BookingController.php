<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Employee;
use App\Services\BookingNotifier;
use App\Services\ShiftService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingNotifier $notifier)
    {
    }

    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $bookings = Booking::where('vendor_id', $vendor->id)
            /*
            | Legacy slots held for a customer who never completed a payment are
            | not appointments and must not appear on the shop's sheet — seeing
            | one reads as a booking that has been placed, which is exactly what
            | it is not. Bookings made under the current flow are confirmed on
            | arrival and all pass through this filter untouched.
            */
            ->visibleToShop()
            // `vendor` is eager-loaded for the appointment_at accessor, which
            // needs the opening hours to place after-midnight slots on the
            // right calendar day.
            ->with(['employee', 'vendor'])
            ->when($request->status, function ($q) use ($request) {
            return $q->where('status', $request->status);
        })
            ->latest()
            ->paginate(5);

        return view('vendor.bookings.index', compact('bookings'));
    }

    public function store(Request $request, ShiftService $shifts)
    {
        $vendor = auth()->user()->vendor;

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'slot_start' => 'required|date_format:H:i',
            'slot_end' => 'required|date_format:H:i',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // Ensure employee belongs to vendor
        if ($employee->vendor_id !== $vendor->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking = Booking::create([
            'vendor_id' => $vendor->id,
            'employee_id' => $request->employee_id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            // The shift currently being worked, so a manual booking taken after
            // midnight joins the same sheet as the rest of that night's queue.
            'booking_date' => $shifts->businessDate($vendor),
            'slot_start_time' => $request->slot_start,
            'slot_end_time' => $request->slot_end,
            'booking_type' => 'vendor',
            'status' => 'confirmed',
            'vendor_booked' => true,
        ]);

        // 'vendor': the shop keyed this in itself, so it gets no "new booking"
        // push back — but the specialist and every open dashboard still do.
        $this->notifier->created($booking, 'vendor');

        return back()->with('success', 'Manual booking created successfully!');
    }

    public function complete(Booking $booking)
    {
        $vendor = auth()->user()->vendor;

        if ($booking->vendor_id !== $vendor->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking->update(['status' => 'completed']);
        $this->advanceNowServing($booking);

        // Tells the customer their appointment is done, redraws both dashboards,
        // and pings whoever is now at the front of the queue.
        $this->notifier->completed($booking, 'vendor');
        $this->notifier->queueAdvanced($booking->employee);

        return back()->with('success', 'Booking marked as completed.');
    }

    public function destroy(Booking $booking)
    {
        $vendor = auth()->user()->vendor;

        if ($booking->vendor_id !== $vendor->id) {
            return back()->with('error', 'Unauthorized.');
        }

        // Announced BEFORE the delete — afterwards there is no row left to
        // describe, and the customer would simply find their booking gone.
        $employee = $booking->employee;
        $this->notifier->removed($booking, 'vendor');

        $booking->delete();

        $this->notifier->queueAdvanced($employee);

        return back()->with('success', 'Booking deleted successfully.');
    }

    public function nextToken(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $request->validate(['employee_id' => 'required|exists:employees,id']);

        $employee = Employee::findOrFail($request->employee_id);
        if ($employee->vendor_id !== $vendor->id) {
            abort(403);
        }

        $employee->increment('now_serving_token');

        // Every queue on screen — dashboards and customer pages alike — follows
        // this, and the customers now at the front get their "you're up" push.
        $this->notifier->queueAdvanced($employee);

        return back()->with('success', "{$employee->name} now serving #" . $employee->fresh()->now_serving_token);
    }

    /**
     * Pass over a customer the shop cannot serve. Closes the booking and moves
     * the queue on exactly as a cancellation does — the difference is what the
     * customer hears: skipped for non-availability, please rebook or call us.
     */
    public function skipToken(Booking $booking)
    {
        $vendor = auth()->user()->vendor;
        if ($booking->vendor_id !== $vendor->id) {
            abort(403);
        }

        // A booking that has already been completed, cancelled or expired is
        // done with; skipping it again would re-notify a customer who has long
        // since left and drag now_serving backwards behind the live queue.
        if ($booking->status !== 'confirmed' && $booking->status !== 'pending') {
            return back()->with('error', 'That appointment is already closed.');
        }

        $booking->update(['status' => 'skipped']);
        $this->advanceNowServing($booking);

        // The skipped customer is told — they may still be sitting there waiting
        // for a turn that has already gone past them.
        $this->notifier->skipped($booking, 'vendor');
        $this->notifier->queueAdvanced($booking->employee);

        $label = $booking->token_number
            ? 'Token #' . $booking->token_number
            : $booking->customer_name . "'s appointment";

        return back()->with('success', $label . ' skipped — the customer has been asked to rebook.');
    }

    /**
     * Advance the employee's now_serving_token to the token just handled
     * (completed or skipped). No-op for time-slot bookings (null token).
     */
    private function advanceNowServing(Booking $booking): void
    {
        if (!$booking->token_number) {
            return;
        }

        $employee = $booking->employee;
        if ($employee && $booking->token_number > $employee->now_serving_token) {
            $employee->update(['now_serving_token' => $booking->token_number]);
        }
    }
}