<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        
        if (!$employee) {
            return redirect('/')->with('error', 'Employee profile not found.');
        }

        $today = Carbon::today()->toDateString();
        
        $currentBooking = Booking::where('employee_id', $employee->id)
            ->where('booking_date', $today)
            ->where('status', 'confirmed')
            ->orderBy('slot_start_time', 'asc')
            ->first();

        $stats = [
            'completed' => Booking::where('employee_id', $employee->id)->where('booking_date', $today)->where('status', 'completed')->count(),
            'remaining' => Booking::where('employee_id', $employee->id)->where('booking_date', $today)->where('status', 'confirmed')->count(),
        ];

        return view('employee.dashboard', compact('employee', 'currentBooking', 'stats'));
    }

    public function markDone(Request $request)
    {
        $employee = auth()->user()->employee;
        
        if (!$employee) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking = Booking::find($request->booking_id);

        if ($booking && $booking->employee_id == $employee->id) {
            $booking->update(['status' => 'completed']);

            // Advance the token queue to the token just served.
            if ($booking->token_number && $booking->token_number > $employee->now_serving_token) {
                $employee->update(['now_serving_token' => $booking->token_number]);
            }

            return back()->with('success', 'Appointment marked as done.');
        }

        return back()->with('error', 'Invalid appointment.');
    }

    public function togglePause()
    {
        $employee = auth()->user()->employee;
        
        if (!$employee) {
            return back()->with('error', 'Unauthorized.');
        }

        $employee->update(['is_paused' => !$employee->is_paused]);
        
        $status = $employee->is_paused ? 'Paused' : 'Resumed';
        return back()->with('success', "Appointments $status successfully.");
    }
}
