<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminBadgeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(AdminBadgeService $badges)
    {
        $stats = [
            'total_revenue' => \App\Models\Booking::where('status', 'confirmed')->sum('online_paid_amount'),
            'active_vendors' => \App\Models\Vendor::where('status', 'active')->count(),
            'total_bookings' => \App\Models\Booking::count(),
            'active_users' => \App\Models\User::where('role', 'customer')->count(),
        ];

        /*
        | The queues waiting on an admin, and where each one leads.
        |
        | The four figures above describe the platform; these describe the
        | admin's own inbox. They were the missing half: a business could
        | register and sit unapproved because the overview screen said nothing
        | about it. Same service the sidebar badges read, so the number on the
        | card and the number on the menu entry can never disagree.
        */
        $actionRequired = collect($badges->destinations())
            ->map(fn (array $destination, string $key) => $destination + [
                'key'   => $key,
                'count' => $badges->count($key),
            ])
            ->values()
            ->all();

        return view('admin.dashboard', [
            'stats'          => $stats,
            'actionRequired' => $actionRequired,
            'pendingTotal'   => $badges->total(),
            'unreadAlerts'   => $badges->unreadNotifications(),
        ]);
    }
}
