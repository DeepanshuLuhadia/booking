<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Services\GoogleIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * "Sign up / Log in with Google" for BUSINESS accounts.
 *
 * Deliberately separate from Auth\GoogleAuthController, which is the public,
 * customer-only endpoint behind the review modal. The two have opposite rules
 * and must never be able to stand in for one another:
 *
 *   - GoogleAuthController  — customers only, refuses every staff account.
 *   - this one              — vendors only, refuses customers and staff.
 *
 * Both endpoints are unauthenticated and passwordless, so the role check is
 * the only thing standing between a Google address and a panel. Keeping them
 * apart means neither check can be loosened "for the other case" by accident.
 *
 * Both actions answer JSON with a `redirect` the browser then follows, because
 * they are called by fetch() from the login / register pages rather than by a
 * form post.
 */
class VendorGoogleController extends Controller
{
    /**
     * Sign an EXISTING vendor in.
     *
     * Registration never happens here: an address with no vendor account is
     * turned away and pointed at the register page. That is the "no guest user
     * login" rule — a customer, an employee or an admin address is refused as
     * well, because this button exists for shop owners.
     */
    public function login(Request $request, GoogleIdentityService $google)
    {
        $request->validate([
            'credential' => 'required|string',
            'fcm_token'  => 'nullable|string',
        ]);

        $payload = $this->verified($request, $google, 'auth.google.vendor.login');

        if (! is_array($payload)) {
            return $payload; // already a JSON error response
        }

        $user = User::where('google_id', $payload['sub'])->first()
            ?: User::where('email', $payload['email'])->first();

        // Nobody here. Registration is a separate, deliberate act — it collects
        // a phone number and a terms acceptance this endpoint never sees — so
        // the visitor is sent there rather than silently given an account.
        if (! $user) {
            return response()->json([
                'success'  => false,
                'message'  => 'No business account is registered with ' . $payload['email']
                    . '. Please sign up with Google first.',
                'redirect' => route('register.vendor'),
            ], 404);
        }

        if (! $user->isVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in on this page is for registered businesses only. '
                    . 'Please sign in with your email and password instead.',
            ], 403);
        }

        // 'inactive' is what rejecting a vendor leaves behind, so this is the
        // rejected shop's door as well as the disabled account's.
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not active. Please contact support.',
            ], 403);
        }

        $this->linkGoogleIdentity($user, $payload);
        $this->attachDeviceToken($user, $request);

        Auth::login($user, true);
        $request->session()->regenerate();

        /*
        | Always the dashboard, never a computed destination.
        |
        | Where a vendor actually lands is decided by EnsureSubscriptionActive:
        | the approval-pending screen while an admin has not approved them, the
        | payment screen without a subscription window, the settings screen
        | until their business details are complete. Repeating that ladder here
        | would give it a second copy to drift out of step with.
        */
        return response()->json([
            'success'  => true,
            'message'  => 'Signed in as ' . $user->name . '.',
            'redirect' => route('vendor.dashboard'),
        ]);
    }

    /**
     * Create a NEW vendor account from a Google identity.
     *
     * Google gives us a confirmed email address, a name and a picture. It
     * cannot give us a phone number, and the phone number is the one detail an
     * admin needs to approve the business — so the page collects it in a modal
     * and posts it here alongside the credential. Everything else about the
     * shop (address, hours, appointment mode, staff) is collected by the
     * settings screen once the account is approved.
     */
    public function register(Request $request, GoogleIdentityService $google)
    {
        $request->validate([
            'credential'    => 'required|string',
            'business_name' => 'required|string|min:5|max:255',
            'vendor_type'   => 'required|exists:vendor_categories,slug',
            /*
            | Unique against BOTH columns a phone number lives in.
            |
            | users.mobile is what the login and OTP flows key on, and
            | vendors.contact_number is held unique by the settings screen —
            | letting a duplicate in here would create an account that can
            | never save its own profile.
            */
            'mobile'        => 'required|string|min:10|max:10|unique:users,mobile|unique:vendors,contact_number',
            'referral_code' => 'nullable|exists:vendors,referral_code',
            'terms'         => 'accepted',
            'fcm_token'     => 'nullable|string',
        ], [
            'terms.accepted'   => 'Please accept the Terms and Conditions and the Privacy Policy to continue.',
            'mobile.unique'    => 'That mobile number is already registered with us.',
            'mobile.min'       => 'Enter your 10-digit mobile number.',
            'mobile.max'       => 'Enter your 10-digit mobile number.',
            'business_name.min' => 'Please enter your full business name (at least 5 characters).',
        ]);

        $payload = $this->verified($request, $google, 'auth.google.vendor.register');

        if (! is_array($payload)) {
            return $payload;
        }

        $existing = User::where('google_id', $payload['sub'])->first()
            ?: User::where('email', $payload['email'])->first();

        if ($existing) {
            // A shop owner who already signed up. Not an error worth a dead
            // end — point them at the door they should have used.
            if ($existing->isVendor()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'A business account already exists for ' . $payload['email']
                        . '. Please use "Continue with Google" on the login page.',
                    'redirect' => route('login'),
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => $payload['email'] . ' is already registered on this platform under a '
                    . 'different account type. Please sign up with another Google account.',
            ], 409);
        }

        /*
        | The plan a Google sign-up starts on.
        |
        | The register form asks for one; this flow deliberately does not — the
        | whole point of the Google tab is that it takes seconds. The free trial
        | is the honest default (a month of everything, nothing to pay), and the
        | vendor can upgrade from Settings → Plans whenever they like.
        |
        | If this install has no free plan configured we fall back to the
        | cheapest active one; that leaves `subscription_expires_at` null, and
        | EnsureSubscriptionActive walks them to the payment screen exactly as
        | it does for a paid sign-up through the form.
        */
        $plan = SubscriptionPlan::where('is_active', true)->orderBy('price')->first();

        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'Registration is not available right now. Please contact support.',
            ], 503);
        }

        $isFreePlan = ((float) $plan->price) <= 0;
        $referrer   = $request->filled('referral_code')
            ? Vendor::where('referral_code', $request->input('referral_code'))->first()
            : null;

        $ownerName = $google->displayName($payload);

        /*
        | One transaction: a user row without its vendor row is an account that
        | can sign in and reach nothing, and the panel would fatal on it.
        |
        | Wrapped in a catch for the unique indexes on users.email,
        | users.google_id, users.mobile and vendors.contact_number. The checks
        | above already cover every ordinary case; what is left is the race —
        | two submits of the same account or the same number arriving together,
        | both passing the lookup, one losing at the INSERT. The database is the
        | thing that actually decides, and the loser should hear the same
        | sentence as everyone else rather than a generic 500.
        */
        $create = function () use ($request, $payload, $google, $plan, $isFreePlan, $referrer, $ownerName) {
            $user = new User();

            // forceFill, not create(): `google_id`, `email_verified_at` and
            // `password_set_at` are identity and are kept out of $fillable.
            $user->forceFill([
                'name'              => $ownerName,
                'email'             => $payload['email'],
                'google_id'         => $payload['sub'],
                'avatar'            => $payload['picture'],
                'mobile'            => $request->input('mobile'),
                'role'              => 'vendor',
                // Active so they can sign back in and finish setup; the vendor
                // row carries the 'pending' approval state, exactly as the form
                // registration does.
                'status'            => 'active',
                'email_verified_at' => now(),
                /*
                | No password by design — this account signs in with Google. An
                | unguessable one is stored because the column is NOT NULL, and
                | `password_set_at` stays null so the settings screen knows not
                | to ask for a current password before they choose their own
                | (the two-way login).
                */
                'password'          => Str::random(48),
                'password_set_at'   => null,
            ])->save();

            Vendor::create([
                'user_id'                 => $user->id,
                'vendor_type'             => $request->input('vendor_type'),
                'business_name'           => $request->input('business_name'),
                'owner_name'              => $ownerName,
                'contact_number'          => $request->input('mobile'),
                'subscription_plan_id'    => $plan->id,
                'referred_by_id'          => $referrer?->id,
                'status'                  => 'pending',
                'subscription_expires_at' => $isFreePlan ? now()->addMonth() : null,
            ]);

            return $user;
        };

        try {
            $user = DB::transaction($create);
        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'That Google account or mobile number is already registered. '
                    . 'Please sign in instead, or use a different mobile number.',
                'redirect' => route('login'),
            ], 409);
        }

        // The same alert the form registration sends — an admin has no way of
        // telling the two apart, and should not have to.
        try {
            app(\App\Services\NotificationService::class)->notifyAdminsNewVendor($user->vendor);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('New-vendor admin alert failed: ' . $e->getMessage());
        }

        $this->attachDeviceToken($user, $request);

        Auth::login($user, true);
        $request->session()->regenerate();

        // Same gate as the form registration: with OTP switched off, admin
        // approval is the entry check and the phone counts as taken on trust.
        if (! config('otp.enabled')) {
            $user->update(['mobile_verified_at' => now()]);

            return response()->json([
                'success'  => true,
                'message'  => 'Registration received! Your account is pending admin approval.',
                'redirect' => route('vendor.approval.pending'),
            ]);
        }

        $otp = rand(100000, 999999);
        Otp::create([
            'identifier' => $user->mobile,
            'otp'        => $otp,
            'type'       => 'verification',
            'expires_at' => now()->addMinutes(5),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => "Verify your mobile to finish setting up. (Simulated OTP: {$otp})",
            'redirect' => route('otp.verify'),
        ]);
    }

    /**
     * Turn the posted credential into a trusted profile, or into the JSON
     * error the caller should return as-is.
     *
     * @return array<string,mixed>|\Illuminate\Http\JsonResponse
     */
    private function verified(Request $request, GoogleIdentityService $google, string $context)
    {
        if (! $google->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in is not available right now.',
            ], 503);
        }

        $payload = $google->verify($request->input('credential'), $context);

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'Your Google sign-in could not be verified. Please try again.',
            ], 422);
        }

        // An address Google itself has not confirmed is not an identity we can
        // key an account on — someone else may hold the real mailbox.
        if (empty($payload['email']) || ! $payload['email_verified']) {
            return response()->json([
                'success' => false,
                'message' => 'Your Google account has no confirmed email address, so we cannot sign you in with it.',
            ], 422);
        }

        return $payload;
    }

    /**
     * Attach the Google identity to an account that was registered through the
     * form. This is what makes the second door work: the vendor who signed up
     * with an email and password can, from then on, use either.
     *
     * Only blanks are filled in — a picture they uploaded themselves is theirs.
     */
    private function linkGoogleIdentity(User $user, array $payload): void
    {
        $fill = [];

        if (blank($user->google_id)) {
            $fill['google_id'] = $payload['sub'];
        }

        if (blank($user->avatar) && filled($payload['picture'])) {
            $fill['avatar'] = $payload['picture'];
        }

        if ($user->email_verified_at === null) {
            $fill['email_verified_at'] = now();
        }

        if ($fill) {
            $user->forceFill($fill)->save();
        }
    }

    /**
     * Carry this device's push address onto the account, so the platform's
     * notifications reach the phone actually in the owner's hand.
     *
     * Same rule as FcmTokenController: a token addresses exactly one device, so
     * anybody else holding it is stale and must be cleared or they keep getting
     * notifications meant for this person.
     */
    private function attachDeviceToken(User $user, Request $request): void
    {
        $token = $request->input('fcm_token') ?: session('fcm_token');

        if (! $token) {
            return;
        }

        User::where('fcm_token', $token)
            ->where('id', '!=', $user->id)
            ->update(['fcm_token' => null]);

        $user->forceFill(['fcm_token' => $token])->save();
        session(['fcm_token' => $token]);
    }
}
