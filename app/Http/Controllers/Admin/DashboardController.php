<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_revenue' => \App\Models\Booking::where('status', 'confirmed')->sum('online_paid_amount'),
            'active_vendors' => \App\Models\Vendor::where('status', 'active')->count(),
            'total_bookings' => \App\Models\Booking::count(),
            'active_users' => \App\Models\User::where('role', 'customer')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
