<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomerBookingService;
use App\Services\GoogleIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * "Continue with Google" for customers, driven from the review modal.
 *
 * The review form already asked people to prove who they were with Google, but
 * threw that identity away the moment the review was posted. This turns the
 * same credential into a real session: the visitor ends up signed in as a
 * customer, with every detail Google gives us (name, address, picture) on the
 * account, and this device's FCM token stamped onto it so the shop's
 * confirmed / cancelled / your-turn pushes reach them.
 *
 * Deliberately customer-only. This endpoint is public and passwordless, so an
 * address that already belongs to an admin, vendor or employee is refused and
 * sent to the ordinary login screen — a public route must never be able to
 * open a staff panel.
 */
class GoogleAuthController extends Controller
{
    public function store(
        Request $request,
        GoogleIdentityService $google,
        CustomerBookingService $bookings
    ) {
        $request->validate([
            'credential' => 'required|string',
            // The browser's push token, when it already has one. Optional: the
            // visitor may not have granted notification permission at all.
            'fcm_token'  => 'nullable|string',
        ]);

        if (! $google->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in is not available right now.',
            ], 503);
        }

        $payload = $google->verify($request->input('credential'), 'auth.google');

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Your Google sign-in could not be verified. Please try again.',
            ], 422);
        }

        // No address, or one Google itself has not confirmed, is not an identity
        // we can key an account on — someone else may hold the real mailbox.
        if (empty($payload['email']) || ! $payload['email_verified']) {
            return response()->json([
                'success' => false,
                'message' => 'Your Google account has no confirmed email address, so we cannot sign you in with it.',
            ], 422);
        }

        $user = User::where('google_id', $payload['sub'])->first()
            ?: User::where('email', $payload['email'])->first();

        if ($user && ! $user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is registered to a business account. Please use the login page to sign in.',
            ], 403);
        }

        if ($user && $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active. Please contact support.',
            ], 403);
        }

        $isNew = $user === null;

        $user = $isNew
            ? $this->createCustomer($payload, $google)
            : $this->linkExisting($user, $payload);

        // Carry this device's push address onto the account. Same rule as
        // FcmTokenController: a token addresses exactly one device, so anybody
        // else holding it is stale and must be cleared or they keep receiving
        // notifications meant for this person.
        $fcmToken = $request->input('fcm_token') ?: session('fcm_token');

        if ($fcmToken) {
            User::where('fcm_token', $fcmToken)
                ->where('id', '!=', $user->id)
                ->update(['fcm_token' => null]);

            $user->forceFill(['fcm_token' => $fcmToken])->save();

            // Left in the session as well, exactly as the guest flow had it:
            // BookingController still reads it first when stamping a booking.
            session(['fcm_token' => $fcmToken]);
        }

        Auth::login($user, true);

        // Fresh session id on privilege change. Note this also rotates the CSRF
        // token, which is why the new one goes back in the response — the page
        // that called us is still holding the old one in its meta tag.
        $request->session()->regenerate();

        /*
        | Backfill the bookings this visitor is already holding.
        |
        | They may have booked as a guest minutes ago, before any of this. Now
        | that the device has a token and an account, stamp it on everything
        | still live so a shop completing or cancelling one reaches them.
        */
        $attached = $fcmToken ? $bookings->attachDeviceToken($fcmToken, $request) : 0;

        return response()->json([
            'success'           => true,
            'is_new'            => $isNew,
            'message'           => $isNew
                ? 'Welcome, ' . $user->name . '! Your account is ready.'
                : 'Signed in as ' . $user->name . '.',
            'user'              => [
                'name'     => $user->name,
                'email'    => $user->email,
                'picture'  => $user->avatar,
                'phone'    => $user->mobile,
                'verified' => true,
            ],
            'bookings_attached' => $attached,
            'csrf_token'        => csrf_token(),
        ]);
    }

    /**
     * A first-time visitor. Everything Google hands over is kept, so the
     * account is complete without the person filling in a registration form.
     *
     * `mobile` is the one field Google cannot give us — it stays null (the
     * column is nullable) and the booking form still asks for it.
     */
    private function createCustomer(array $payload, GoogleIdentityService $google): User
    {
        $user = new User();

        // forceFill, not create(): `google_id` and `email_verified_at` are
        // identity, and deliberately stay out of $fillable so no mass
        // assignment anywhere else can ever set them. Everything here is
        // server-side data off a token we verified ourselves.
        $user->forceFill([
            'name'              => $google->displayName($payload),
            'email'             => $payload['email'],
            'google_id'         => $payload['sub'],
            // A full https URL from Google rather than a stored path. Nothing
            // renders `users.avatar` through the storage disk, so this is safe.
            'avatar'            => $payload['picture'],
            'role'              => 'customer',
            'status'            => 'active',
            'email_verified_at' => now(),
            // The account has no password by design — sign-in is through Google.
            // An unguessable one is stored because the column is NOT NULL, and
            // it leaves "forgot password" available if they ever want one.
            'password'          => Str::random(48),
        ])->save();

        return $user;
    }

    /**
     * A returning visitor, or someone who registered with this address before
     * Google sign-in existed. Only blanks are filled in: a name or picture they
     * already have is theirs, and must not be overwritten by the Google copy.
     */
    private function linkExisting(User $user, array $payload): User
    {
        $fill = ['google_id' => $payload['sub']];

        if (blank($user->avatar) && filled($payload['picture'])) {
            $fill['avatar'] = $payload['picture'];
        }

        // Google has confirmed the address they registered with, so the account
        // counts as verified from here on.
        if ($user->email_verified_at === null) {
            $fill['email_verified_at'] = now();
        }

        $user->forceFill($fill)->save();

        return $user;
    }
}
