<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\GoogleIdentityService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a public review for a vendor.
     *
     * Identity is OPTIONAL:
     *  - If the reviewer signs in with Google, the front-end sends the Google
     *    ID token (`google_credential`). We verify it server-side and trust the
     *    name/email from the verified token (the review is marked verified).
     *  - Failing that, a signed-in customer's own account is the identity — a
     *    Google-linked one carries the same confirmed address, so it earns the
     *    same verified badge without making them re-authenticate.
     *  - Otherwise the review is posted anonymously. Any client-supplied name is
     *    ignored for identity purposes and the review shows as "Anonymous". A
     *    signed-in customer can force this with `anonymous` — being logged in
     *    must not take away the option of reviewing a shop unnamed.
     */
    public function store(Request $request, Vendor $vendor, GoogleIdentityService $google)
    {
        $data = $request->validate([
            'reviewer_name'     => 'nullable|string|max:60',
            'reviewer_phone'    => 'nullable|string|max:15',
            'rating'            => 'required|integer|min:1|max:5',
            'comment'           => 'nullable|string|max:1000',
            'google_credential' => 'nullable|string',
            // A signed-in reviewer opting out of being named on this one review.
            'anonymous'         => 'nullable|boolean',
            // Low ratings (under 2 stars) must be backed by photo evidence.
            'images'            => 'array|max:5|required_if:rating,1',
            'images.*'          => 'image|mimes:jpeg,jpg,png,webp|max:4096',
        ], [
            'images.required_if' => 'Please attach at least one photo to support a 1-star rating.',
        ]);

        // Resolve reviewer identity. Default: whatever name they typed (may be blank).
        $name       = trim((string) ($data['reviewer_name'] ?? '')) ?: null;
        $email      = null;
        $isVerified = false;

        // If they signed in with Google, the verified token is authoritative.
        if ($request->filled('google_credential')) {
            $payload = $google->verify($request->input('google_credential'), 'review.store');

            if (! $payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your Google sign-in could not be verified. Please sign in again or post with just your name.',
                ], 422);
            }

            $name       = $google->displayName($payload);
            $email      = $payload['email'] ?? null;
            $isVerified = true;
        } elseif (! $request->boolean('anonymous') && ($user = $request->user()) && $user->isCustomer()) {
            /*
            | No token on this request, but they are already signed in.
            |
            | This is the ordinary case once "Continue with Google" has run: the
            | credential was spent creating the session, and the account now
            | holds the same details. Only a Google-linked account is badged
            | verified — a password registration proves nothing about the
            | address on it, so it just contributes a name and email.
            */
            $email = $user->email;

            if ($user->usesGoogleSignIn()) {
                $name       = $user->name;
                $isVerified = true;
            } else {
                $name = $name ?: $user->name;
            }
        }

        $paths = [];
        foreach ($request->file('images', []) as $file) {
            $paths[] = $file->store('reviews', 'public');
        }

        $review = $vendor->reviews()->create([
            'reviewer_name'  => $name ?: 'Anonymous',
            'reviewer_phone' => $data['reviewer_phone'] ?? null,
            'reviewer_email' => $email,
            'is_verified'    => $isVerified,
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
            'images'         => $paths ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your review has been posted.',
            'review'  => [
                'name'          => $review->reviewer_name,
                'rating'        => $review->rating,
                'comment'       => $review->comment,
                'verified'      => $review->is_verified,
                'images'        => collect($paths)->map(fn ($p) => asset('storage/' . $p))->all(),
                'created_human' => 'Just now',
            ],
            'average_rating' => round((float) $vendor->reviews()->avg('rating'), 1),
            'reviews_count'  => $vendor->reviews()->count(),
        ]);
    }
}
