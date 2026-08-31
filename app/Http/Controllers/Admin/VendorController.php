<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\BookingReportService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $vendors = Vendor::with('user', 'subscriptionPlan')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = Vendor::where('status', 'pending')->count();

        return view('admin.vendors.index', compact('vendors', 'status', 'pendingCount'));
    }

    public function show(Request $request, Vendor $vendor)
    {
        $vendor->load(['user', 'subscriptionPlan', 'employees', 'settlements' => function($query) {
            $query->latest()->limit(5);
        }]);

        $bookingStatus = (string) $request->query('booking_status', 'all');

        if (!\array_key_exists($bookingStatus, BookingReportService::STATUSES)) {
            $bookingStatus = 'all';
        }

        // This shop's bookings, newest first. Always paginated — an established
        // shop's history is thousands of rows, and this page carries the whole
        // vendor profile above it.
        //
        // `bookings` as the page name keeps the URL self-describing and leaves
        // the plain `page` key free for anything else added to this page later.
        $bookings = $vendor->bookings()
            ->with('employee')
            ->when($bookingStatus !== 'all', fn ($q) => $q->where('status', $bookingStatus))
            ->orderByDesc('booking_date')
            ->orderByDesc('slot_start_time')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'bookings')
            ->withQueryString()
            ->fragment('bookings');

        // The appointment_at accessor reads the shop's opening time to place
        // after-midnight slots; handing each row the vendor we already have
        // avoids one lazy load per booking.
        $bookings->getCollection()->each(fn ($booking) => $booking->setRelation('vendor', $vendor));

        return view('admin.vendors.show', [
            'vendor'        => $vendor,
            'bookings'      => $bookings,
            'bookingStatus' => $bookingStatus,
            'statuses'      => BookingReportService::STATUSES,
        ]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($request->only('status'));
        return back()->with('success', 'Vendor status updated');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return back()->with('success', 'Vendor deleted');
    }

    /**
     * Approve a pending vendor and take it live.
     * The verified badge is granted only to paid (premium) plan holders.
     */
    public function approve(Vendor $vendor)
    {
        $isPaidPlan = $vendor->subscriptionPlan && $vendor->subscriptionPlan->price > 0;

        $vendor->update([
            'status'      => 'active',
            'is_verified' => $isPaidPlan,
        ]);
        $vendor->user?->update(['status' => 'active']);

        return back()->with('success', "Vendor '{$vendor->business_name}' approved.");
    }

    public function reject(Vendor $vendor)
    {
        $vendor->update(['status' => 'rejected', 'is_open' => false]);
        $vendor->user?->update(['status' => 'inactive']);

        return back()->with('success', "Vendor '{$vendor->business_name}' rejected.");
    }

    public function suspend(Vendor $vendor)
    {
        $vendor->update(['status' => 'suspended', 'is_open' => false]);

        return back()->with('success', "Vendor '{$vendor->business_name}' suspended.");
    }

    public function reinstate(Vendor $vendor)
    {
        // Re-verify only if they hold a paid plan.
        $isPaidPlan = $vendor->subscriptionPlan && $vendor->subscriptionPlan->price > 0;

        $vendor->update(['status' => 'active', 'is_verified' => $isPaidPlan]);
        $vendor->user?->update(['status' => 'active']);

        return back()->with('success', "Vendor '{$vendor->business_name}' reinstated.");
    }
}
