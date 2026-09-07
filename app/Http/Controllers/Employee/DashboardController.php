<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\ShiftService;

class DashboardController extends Controller
{
    public function index(ShiftService $shifts)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect('/')->with('error', 'Employee profile not found.');
        }

        app(\App\Services\QRCodeService::class)->ensureForEmployee($employee);
        $employee->refresh();

        // The shift being worked right now. On an overnight rota this is still
        // yesterday's date at 00:30, which is where that night's queue lives —
        // reading Carbon::today() here emptied the screen at midnight.
        $today = $shifts->businessDate($employee->vendor);

        // The full confirmed queue for this shift, earliest slot first.
        $queue = Booking::where('employee_id', $employee->id)
            ->where('booking_date', $today)
            ->where('status', 'confirmed')
            ->with('vendor')
            ->orderBy('slot_start_time', 'asc')
            ->get();

        // The first confirmed booking is the one the employee is serving now.
        $currentBooking = $queue->first();

        // The next 5 after the current one — shown as a swipeable queue.
        $upcomingBookings = $queue->slice(1, 5)->values();

        $stats = [
            'completed' => Booking::where('employee_id', $employee->id)->where('booking_date', $today)->where('status', 'completed')->count(),
            'remaining' => $queue->count(),
        ];

        return view('employee.dashboard', compact('employee', 'currentBooking', 'upcomingBookings', 'stats'));
    }

    public function markDone(Request $request)
    {
        return $this->transition($request, 'completed', 'Appointment marked as done.');
    }

    public function cancel(Request $request)
    {
        return $this->transition($request, 'cancelled', 'Appointment cancelled.');
    }

    /**
     * Pass over a customer the specialist cannot serve right now. Mechanically
     * identical to a cancellation — the booking closes and the queue moves to
     * the next person — but the customer is told it was for non-availability
     * and that they need to rebook, rather than just that it is off.
     */
    public function skip(Request $request)
    {
        return $this->transition($request, 'skipped', 'Appointment skipped — the customer has been asked to rebook.');
    }

    /**
     * Move a booking owned by the current employee to the given status and
     * advance the token queue past it (a completed/cancelled/skipped customer
     * frees the counter for the next one).
     */
    private function transition(Request $request, string $status, string $message)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking = Booking::find($request->booking_id);

        if (!$booking || $booking->employee_id != $employee->id) {
            return back()->with('error', 'Invalid appointment.');
        }

        // Is this the customer currently being served (head of today's queue)?
        // Only the head advances the token counter — cancelling a booking further
        // down the queue must not skip the people waiting ahead of it. Resolve
        // this BEFORE the status update, while the booking is still 'confirmed'.
        $isHead = Booking::where('employee_id', $employee->id)
            ->where('booking_date', $booking->booking_date)
            ->where('status', 'confirmed')
            ->orderBy('slot_start_time', 'asc')
            ->value('id') === $booking->id;

        $booking->update(['status' => $status]);

        // Advance the token queue to the token just handled.
        if ($isHead && $booking->token_number && $booking->token_number > $employee->now_serving_token) {
            $employee->update(['now_serving_token' => $booking->token_number]);
        }

        /*
        | Tell the customer what just happened to their booking, redraw the
        | owner's dashboard and every customer queue watching this specialist,
        | and ping whoever is now at the front. Completing or cancelling used to
        | notify nobody but the next person in the queue — the customer whose
        | appointment it actually was heard nothing at all.
        */
        $notifier = app(\App\Services\BookingNotifier::class);

        match ($status) {
            'completed' => $notifier->completed($booking, 'employee'),
            'skipped'   => $notifier->skipped($booking, 'employee'),
            default     => $notifier->cancelledByShop($booking, 'employee'),
        };

        $notifier->queueAdvanced($employee);

        return back()->with('success', $message);
    }

    public function togglePause()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return back()->with('error', 'Unauthorized.');
        }

        $employee->update(['is_paused' => !$employee->is_paused]);

        // Customers waiting on this specialist see the pause immediately, and
        // those holding a live token are told — they are sitting there in person.
        app(\App\Services\BookingNotifier::class)->employeePauseChanged($employee);

        $status = $employee->is_paused ? 'Paused' : 'Resumed';
        return back()->with('success', "Appointments $status successfully.");
    }

    /**
     * Start this specialist's own queue over: everyone still waiting is
     * cancelled and told, and the token counter goes back to zero.
     *
     * Scoped to the signed-in employee — there is no employee_id to pass, so
     * one specialist can never clear another's queue. Destructive and
     * outward-facing; the confirmation lives in the dashboard UI.
     */
    public function restartQueue(\App\Services\QueueRestartService $queues)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return back()->with('error', 'Unauthorized.');
        }

        $cancelled = $queues->restart($employee, 'employee');

        if ($cancelled === 0) {
            return back()->with('success', 'Queue restarted. There was nobody waiting.');
        }

        return back()->with('success', "Queue restarted. {$cancelled} waiting "
            . ($cancelled === 1 ? 'customer was' : 'customers were')
            . ' cancelled and notified.');
    }
}
