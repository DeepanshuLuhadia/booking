<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;

/**
 * Static, publicly reachable content pages (About / Terms / Privacy).
 *
 * The copy lives in `site_settings` so an admin can rewrite it from the panel;
 * the page structure lives in the Blade views.
 */
class PageController extends Controller
{
    public function about()
    {
        return view('pages.about', [
            'settings' => SiteSetting::all_settings(),
            'stats'    => $this->platformStats(),
        ]);
    }

    public function terms()
    {
        return view('pages.terms', ['settings' => SiteSetting::all_settings()]);
    }

    public function privacy()
    {
        return view('pages.privacy', ['settings' => SiteSetting::all_settings()]);
    }

    /**
     * Live headline numbers for the About page.
     *
     * Cached for an hour — these are decorative, and the vendor/booking tables
     * are the two busiest in the schema.
     */
    private function platformStats(): array
    {
        return Cache::remember('pages.about.stats', now()->addHour(), function () {
            return [
                'vendors'     => Vendor::where('status', 'active')->count(),
                'bookings'    => \App\Models\Booking::count(),
                'categories'  => \App\Models\VendorCategory::count(),
                'specialists' => \App\Models\Employee::count(),
            ];
        });
    }
}
