<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all'); // all | reported

        $reviews = VendorReview::with('vendor')
            ->when($filter === 'reported', fn ($q) => $q->where('is_reported', true))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $reportedCount = VendorReview::where('is_reported', true)->count();

        return view('admin.reviews.index', compact('reviews', 'filter', 'reportedCount'));
    }

    /**
     * Permanently remove a review (abusive / fake / on vendor report).
     */
    public function destroy(VendorReview $review)
    {
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }

    /**
     * Clear a vendor's report flag, keeping the review live.
     */
    public function unreport(VendorReview $review)
    {
        $review->update([
            'is_reported'   => false,
            'report_reason' => null,
            'reported_at'   => null,
        ]);

        return back()->with('success', 'Report cleared. The review remains published.');
    }
}
