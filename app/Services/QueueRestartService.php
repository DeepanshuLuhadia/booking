<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Vendor;

/**
 * Return a specialist's token queue to a clean slate mid-service.
 *
 * The on-demand twin of ResetDailyTokens, which only ever tidies up a shop
 * that has already closed. This is for the shop that is open and needs to
 * start the queue over — a machine went down, the specialist has to leave,
 * the counter drifted out of step with the room.
 *
 * It is deliberately destructive: everyone still waiting is cancelled and
 * told so. That is the whole point — a restart the customers were not told
 * about would leave a room full of people holding tokens the shop no longer
 * recognises. Callers are expected to confirm with the operator first.
 */
class QueueRestartService
{
    /** Statuses that still represent somebody waiting to be served. */
    private const LIVE = ['pending', 'confirmed'];

    public function __construct(
        private BookingNotifier $notifier,
        private ShiftService $shifts,
    ) {
    }

    /**
     * Clear one specialist's queue for the shift being worked right now.
     *
     * @param  string  $actor  'vendor' or 'employee' — the customer is told
     *                         which of the two cancelled on them.
     * @return int  how many waiting customers were cancelled
     */
    public function restart(Employee $employee, string $actor = 'vendor'): int
    {
        $vendor = $employee->vendor;

        // The shift being worked now, not the calendar day: an overnight rota
        // is still on yesterday's date at 00:30, which is where that night's
        // queue lives. Reading today() here would clear nothing at all.
        $date = $this->shifts->businessDate($vendor);

        /*
        | Read the rows BEFORE the update lands.
        |
        | Afterwards there is no way to tell which bookings were live a moment
        | ago, and every one of those customers is owed the message saying
        | their appointment is off — most of them are sitting in the shop.
        */
        $bookings = Booking::where('employee_id', $employee->id)
            ->where('booking_date', $date)
            ->whereIn('status', self::LIVE)
            ->with(['employee.vendor', 'vendor'])
            ->get();

        if ($bookings->isNotEmpty()) {
            Booking::whereIn('id', $bookings->pluck('id'))->update(['status' => 'cancelled']);

            foreach ($bookings as $booking) {
                // Re-read so the broadcast payload carries 'cancelled' rather
                // than the stale status it was loaded with.
                $booking->refresh();
                $this->notifier->cancelledByShop($booking, $actor);
            }
        }

        /*
        | Release the day's token numbers, AFTER the cancellations have gone
        | out (the messages quote the token, and a refresh() inside that loop
        | would have read a null back).
        |
        | This is the other half of the restart, and without it the restart
        | only half-happens. The next token is drawn as MAX(token_number) + 1
        | over the employee's bookings for the date — see the allocator in
        | BookingController and the queue index on the public page — so leaving
        | the retired numbers in place means the counter reads 0 while the very
        | next customer is handed #12, and every screen tells them eleven
        | people are ahead of them in a room that was just emptied.
        |
        | Every row for the shift goes, not only the ones just cancelled: the
        | tokens already completed hold their numbers under
        | unique_emp_token_per_day, and a queue restarting at #1 would collide
        | with them on the first booking.
        */
        Booking::where('employee_id', $employee->id)
            ->where('booking_date', $date)
            ->whereNotNull('token_number')
            ->update(['token_number' => null]);

        $employee->update([
            'now_serving_token' => 0,
            // A queue starting from nothing is not a paused queue, and leaving
            // the pause set would strand the restart behind a second click.
            'is_paused'         => false,
        ]);

        // Redraws the owner's dashboard and every customer screen watching this
        // specialist. Nobody is left holding a token by this point, so the
        // "you're up next" ping inside it has nobody to reach.
        $this->notifier->queueAdvanced($employee->fresh());

        return $bookings->count();
    }

    /**
     * Clear every queue in the shop — the "we are starting the day over"
     * button, rather than one specialist at a time.
     *
     * @return int  how many waiting customers were cancelled in total
     */
    public function restartShop(Vendor $vendor, string $actor = 'vendor'): int
    {
        $cancelled = 0;

        foreach ($vendor->employees()->with('vendor')->get() as $employee) {
            $cancelled += $this->restart($employee, $actor);
        }

        return $cancelled;
    }
}
