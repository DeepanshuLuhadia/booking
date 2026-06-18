<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $bookings = Booking::where('vendor_id', $vendor->id)
            ->with('employee')
            ->when($request->status, function ($q) use ($request) {
            return $q->where('status', $request->status);
        })
            ->latest()
            ->paginate(5);

        return view('vendor.bookings.index', compact('bookings'));
    }

    public function store(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'slot_start' => 'required|date_format:H:i',
            'slot_end' => 'required|date_format:H:i',
        ]);

        $employee = Employee::find($request->employee_id);

        // Ensure employee belongs to vendor
        if ($employee->vendor_id !== $vendor->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking = Booking::create([
            'vendor_id' => $vendor->id,
            'employee_id' => $request->employee_id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'booking_date' => Carbon::today()->toDateString(),
            'slot_start_time' => $request->slot_start,
            'slot_end_time' => $request->slot_end,
            'booking_type' => 'vendor',
            'status' => 'confirmed',
            'vendor_booked' => true,
        ]);

        if ($employee->user && $employee->user->fcm_token) {
            app(\App\Services\NotificationService::class)->sendWebPush(
                $employee->user,
                "📋 New Manual Appointment",
                "You have a new appointment with {$request->customer_name} at {$request->slot_start}."
            );
        }

        return back()->with('success', 'Manual booking created successfully!');
    }

    public function complete(Booking $booking)
    {
        $vendor = auth()->user()->vendor;

        if ($booking->vendor_id !== $vendor->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking->update(['status' => 'completed']);

        return back()->with('success', 'Booking marked as completed.');
    }

    public function destroy(Booking $booking)
    {
        $vendor = auth()->user()->vendor;

        if ($booking->vendor_id !== $vendor->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking->delete();

        return back()->with('success', 'Booking deleted successfully.');
    }
}