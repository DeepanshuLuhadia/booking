<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Services\ShiftService;

class DashboardController extends Controller
{
    public function index(QRCodeService $qrService, ShiftService $shifts)
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect('/')->with('error', 'Vendor profile not found.');
        }

        // The shift currently on the books. For a shop trading past midnight
        // this stays on the same date all night, so the counters don't reset
        // under the vendor while they are still serving.
        $today = $shifts->businessDate($vendor);
        
        $stats = [
            'today_bookings' => Booking::where('vendor_id', $vendor->id)->where('booking_date', $today)->count(),
            'active_employees' => Employee::where('vendor_id', $vendor->id)->where('is_active', true)->count(),
            'plan_limit' => $vendor->subscriptionPlan->max_employees ?? 0,
            'today_revenue' => Booking::where('vendor_id', $vendor->id)->where('booking_date', $today)->where('status', 'confirmed')->sum('online_paid_amount'),
        ];

        // Ensure QR code exists
        if (!$vendor->qr_code_path || !file_exists(storage_path('app/public/' . $vendor->qr_code_path))) {
            $vendor->qr_code_path = $qrService->generateForVendor($vendor);
            $vendor->save();
        }

        $recentBookings = Booking::where('vendor_id', $vendor->id)
            // `vendor` feeds the appointment_at accessor (after-midnight slots).
            ->with(['employee', 'vendor'])
            ->latest()
            ->take(5)
            ->get();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentBookings'));
    }
}
