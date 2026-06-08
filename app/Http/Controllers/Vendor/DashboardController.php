<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\QRCodeService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(QRCodeService $qrService)
    {
        $vendor = auth()->user()->vendor;
        
        if (!$vendor) {
            return redirect('/')->with('error', 'Vendor profile not found.');
        }

        $today = Carbon::today()->toDateString();
        
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
            ->with('employee')
            ->latest()
            ->take(5)
            ->get();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentBookings'));
    }
}
