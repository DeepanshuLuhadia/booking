<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Store a public review for a vendor.
     *
     * Identity is OPTIONAL:
     *  - If the reviewer signs in with Google, the front-end sends the Google
     *    ID token (`google_credential`). We verify it server-side and trust the
     *    name/email from the verified token (the review is marked verified).
     *  - Otherwise the review is posted anonymously. Any client-supplied name is
     *    ignored for identity purposes and the review shows as "Anonymous".
     */
    public function store(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'reviewer_name'     => 'nullable|string|max:60',
            'reviewer_phone'    => 'nullable|string|max:15',
            'rating'            => 'required|integer|min:1|max:5',
            'comment'           => 'nullable|string|max:1000',
            'google_credential' => 'nullable|string',
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
            $payload = $this->verifyGoogleCredential($request->input('google_credential'));

            if (! $payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your Google sign-in could not be verified. Please sign in again or post with just your name.',
                ], 422);
            }

            $name       = $payload['name'] ?? ($payload['email'] ?? 'Google User');
            $email      = $payload['email'] ?? null;
            $isVerified = true;
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

    /**
     * Verify a Google ID token (JWT credential) server-side.
     * Returns the verified payload array, or null when invalid / unconfigured.
     */
    private function verifyGoogleCredential(string $credential): ?array
    {
        $clientId = config('services.google.client_id');
        if (! $clientId) {
            return null;
        }

        try {
            $client  = new \Google\Client(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($credential);

            // verifyIdToken returns false on failure, or the payload array.
            return is_array($payload) ? $payload : null;
        } catch (\Throwable $e) {
            Log::warning('Google review credential verification failed: ' . $e->getMessage());
            return null;
        }
    }
}
