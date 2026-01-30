<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request, PaymentService $paymentService, NotificationService $notificationService)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'employee_id' => 'required|exists:employees,id',
            'slot_start' => 'required',
            'slot_end' => 'required',
            'booking_type' => 'required|in:normal,emergency',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|size:10',
            'payment_id' => 'nullable|string' // In real app, this would be required if token > 0
        ]);

        $vendor = Vendor::find($request->vendor_id);
        $employee = Employee::find($request->employee_id);

        // Pricing Logic: Use employee override if set, otherwise vendor default
        $baseServiceFee = $employee->service_fee_override ?? $vendor->service_fee;
        
        // Emergency/Premium Fee from Vendor setting
        $emergencyFee = $request->booking_type === 'emergency' ? $vendor->emergency_fee : 0;
        
        $tokenAmount = $vendor->token_booking_enabled ? $vendor->token_amount : 0;
        
        // Final amount customer pays online
        // Usually it's Token + Emergency Fee
        $totalToPay = $tokenAmount + $emergencyFee;

        // Create booking
        $booking = Booking::create([
            'vendor_id' => $vendor->id,
            'employee_id' => $employee->id,
            'customer_id' => null, 
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'booking_date' => Carbon::today()->toDateString(),
            'slot_start_time' => $request->slot_start,
            'slot_end_time' => $request->slot_end,
            'booking_type' => $request->booking_type,
            'token_required' => $vendor->token_booking_enabled,
            'token_amount' => $tokenAmount,
            'emergency_fee' => $emergencyFee,
            'online_paid_amount' => $totalToPay,
            'status' => 'confirmed',
            'vendor_booked' => false,
            'razorpay_payment_id' => $request->payment_id,
            'notes' => "Service Fee: ₹{$baseServiceFee}"
        ]);

        // Notify Vendor
        $notificationService->notifyVendorNewBooking($vendor, $booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully!',
            'booking' => $booking
        ]);
    }
}
