<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;

        abort_unless($vendor, 403);

        $reviews = $vendor->reviews()
            ->latest()
            ->paginate(10);

        $stats = [
            'total'    => $vendor->reviews()->count(),
            'reported' => $vendor->reviews()->where('is_reported', true)->count(),
            'average'  => round((float) $vendor->reviews()->avg('rating'), 1),
        ];

        return view('vendor.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Flag a review on this vendor's profile for admin moderation.
     */
    public function report(Request $request, VendorReview $review)
    {
        $vendor = auth()->user()->vendor;

        // A vendor may only report reviews left on their own profile.
        abort_unless($vendor && $review->vendor_id === $vendor->id, 403);

        $data = $request->validate([
            'report_reason' => 'nullable|string|max:500',
        ]);

        $review->update([
            'is_reported'   => true,
            'report_reason' => $data['report_reason'] ?? null,
            'reported_at'   => now(),
        ]);

        return back()->with('success', 'Review reported. Our team will review it shortly.');
    }
}
