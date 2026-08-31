<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Services\GoogleIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * "Continue with Google" in the review modal.
 *
 * The button used to prove who a reviewer was and then throw that identity
 * away. It now ends in a real customer session, an account carrying everything
 * Google gives us, and this device's push token bound to it.
 *
 * The guarantees worth pinning down: only a credential Google actually signed
 * is accepted; the endpoint is public, so it must never open a staff account;
 * details a person already has are never overwritten by the Google copy; the
 * push token follows the account and stops addressing whoever held it before;
 * and none of it takes away the ability to review a shop unnamed.
 */
class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => 'test-client-id.apps.googleusercontent.com']);

        $plan = SubscriptionPlan::create([
            'name' => 'Test', 'price' => 0, 'max_employees' => 5,
            'features' => ['Test plan'], 'duration_days' => 30, 'is_active' => true,
        ]);

        $owner = User::create([
            'name' => 'Shop Owner', 'email' => 'owner@example.com',
            'mobile' => '9000000001', 'password' => bcrypt('secret'), 'role' => 'vendor',
        ]);

        $this->vendor = Vendor::create([
            'user_id' => $owner->id,
            'business_name' => 'City Dental Clinic',
            'owner_name' => 'Shop Owner',
            'contact_number' => '9000000001',
            'address' => '1 Test Road',
            'latitude' => 26.9124, 'longitude' => 75.7873,
            'is_open' => true, 'status' => 'active',
            'vendor_type' => 'doctor', 'appointment_mode' => 'token',
            'subscription_plan_id' => $plan->id,
            'subscription_expires_at' => now()->addDays(30),
            'global_opening_time' => '00:00', 'global_closing_time' => '23:59',
            'service_fee' => 500, 'token_amount' => 0,
            'require_customer_details' => true,
        ]);
    }

    /**
     * Swap in a Google verifier that trusts a fixed payload, so the tests never
     * reach out to Google's key endpoint. Everything else — displayName and the
     * rest — stays the real implementation.
     */
    private function fakeGoogle(?array $payload): void
    {
        $this->instance(GoogleIdentityService::class, new class($payload) extends GoogleIdentityService {
            public function __construct(private ?array $payload)
            {
            }

            public function isConfigured(): bool
            {
                return true;
            }

            public function verify(string $credential, string $context = 'google'): ?array
            {
                return $this->payload;
            }
        });
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'sub'            => '110000000000000000001',
            'email'          => 'aarav@gmail.com',
            'email_verified' => true,
            'name'           => 'Aarav Sharma',
            'given_name'     => 'Aarav',
            'family_name'    => 'Sharma',
            'picture'        => 'https://lh3.googleusercontent.com/a/aarav',
            'locale'         => 'en',
        ], $overrides);
    }

    /*
    |--------------------------------------------------------------------------
    | Signing in
    |--------------------------------------------------------------------------
    */

    /**
     * A first-time visitor ends up with a complete account and a session,
     * without ever meeting a registration form.
     */
    public function test_a_new_visitor_is_signed_in_and_every_detail_google_sends_is_kept(): void
    {
        $this->fakeGoogle($this->payload());

        $res = $this->postJson(route('auth.google'), [
            'credential' => 'signed-by-google',
            'fcm_token'  => 'device-token-abc',
        ]);

        $res->assertOk()
            ->assertJson([
                'success' => true,
                'is_new'  => true,
                'user'    => [
                    'name'    => 'Aarav Sharma',
                    'email'   => 'aarav@gmail.com',
                    'picture' => 'https://lh3.googleusercontent.com/a/aarav',
                ],
            ]);

        $user = User::where('email', 'aarav@gmail.com')->firstOrFail();

        $this->assertSame('110000000000000000001', $user->google_id);
        $this->assertSame('Aarav Sharma', $user->name);
        $this->assertSame('https://lh3.googleusercontent.com/a/aarav', $user->avatar);
        $this->assertSame('customer', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->email_verified_at, 'Google confirmed the address.');
        $this->assertSame('device-token-abc', $user->fcm_token);
        // Google never sends a phone number; the booking form still asks.
        $this->assertNull($user->mobile);

        $this->assertTrue(Auth::check());
        $this->assertSame($user->id, Auth::id());
    }

    /**
     * The session id is rotated on sign-in, which rotates the CSRF token with
     * it. The page that called us is still holding the old one, so the new one
     * has to come back or its next POST dies with a 419.
     */
    public function test_the_rotated_csrf_token_is_returned_to_the_caller(): void
    {
        $this->fakeGoogle($this->payload());

        $res = $this->postJson(route('auth.google'), ['credential' => 'signed-by-google']);

        $res->assertOk();
        $this->assertNotEmpty($res->json('csrf_token'));
        $this->assertSame(csrf_token(), $res->json('csrf_token'));
    }

    /**
     * Somebody who registered with a password before Google sign-in existed
     * links the two — but keeps the name and picture they chose. The Google
     * copy fills blanks only.
     */
    public function test_an_existing_customer_is_linked_without_their_own_details_being_overwritten(): void
    {
        $existing = User::create([
            'name' => 'A. Sharma', 'email' => 'aarav@gmail.com',
            'mobile' => '9000000042', 'password' => bcrypt('secret'),
            'role' => 'customer', 'status' => 'active', 'avatar' => 'avatars/mine.jpg',
        ]);

        $this->fakeGoogle($this->payload());

        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])
            ->assertOk()
            ->assertJson(['success' => true, 'is_new' => false]);

        $existing->refresh();

        $this->assertSame('110000000000000000001', $existing->google_id);
        $this->assertSame('A. Sharma', $existing->name, 'Their own name survives.');
        $this->assertSame('avatars/mine.jpg', $existing->avatar, 'Their own picture survives.');
        $this->assertSame('9000000042', $existing->mobile);
        $this->assertNotNull($existing->email_verified_at);
        $this->assertSame($existing->id, Auth::id());
        $this->assertSame(1, User::where('email', 'aarav@gmail.com')->count(), 'No duplicate account.');
    }

    /**
     * A person changed the address on their Google account. The `sub` claim is
     * the stable handle, so they land on the same account rather than a new one.
     */
    public function test_a_changed_google_address_still_finds_the_same_account(): void
    {
        $user = User::create([
            'name' => 'Aarav Sharma', 'email' => 'aarav@gmail.com',
            'password' => bcrypt('x'), 'role' => 'customer', 'status' => 'active',
        ]);
        // google_id is out of $fillable on purpose, so it is written the same
        // way the controller writes it.
        $user->forceFill(['google_id' => '110000000000000000001'])->save();

        $this->fakeGoogle($this->payload(['email' => 'aarav.new@gmail.com']));

        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])->assertOk();

        $this->assertSame($user->id, Auth::id());
        $this->assertSame(0, User::where('email', 'aarav.new@gmail.com')->count(), 'No second account.');
    }

    /*
    |--------------------------------------------------------------------------
    | What it refuses
    |--------------------------------------------------------------------------
    */

    /**
     * The whole reason this endpoint is customer-only: it is public and
     * passwordless, so it must never be a way into a staff panel.
     */
    public function test_a_staff_address_is_refused_and_no_session_is_opened(): void
    {
        $this->fakeGoogle($this->payload(['email' => 'owner@example.com']));

        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->assertFalse(Auth::check());
        $this->assertNull(User::where('email', 'owner@example.com')->first()->google_id);
    }

    public function test_a_credential_google_did_not_sign_is_refused(): void
    {
        $this->fakeGoogle(null);

        $this->postJson(route('auth.google'), ['credential' => 'forged'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertFalse(Auth::check());
        $this->assertSame(0, User::where('role', 'customer')->count());
    }

    /**
     * An address Google itself has not confirmed is no identity — somebody else
     * may hold the real mailbox, and it is what we key the account on.
     */
    public function test_an_unconfirmed_google_address_is_refused(): void
    {
        $this->fakeGoogle($this->payload(['email_verified' => false]));

        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])
            ->assertStatus(422);

        $this->assertFalse(Auth::check());
    }

    public function test_a_suspended_account_cannot_sign_in(): void
    {
        User::create([
            'name' => 'Aarav', 'email' => 'aarav@gmail.com', 'password' => bcrypt('x'),
            'role' => 'customer', 'status' => 'suspended',
        ]);

        $this->fakeGoogle($this->payload());

        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])
            ->assertStatus(403);

        $this->assertFalse(Auth::check());
    }

    /*
    |--------------------------------------------------------------------------
    | The FCM token
    |--------------------------------------------------------------------------
    */

    /**
     * A token addresses exactly one device. Whoever held it before was signed
     * in on this same browser and is now stale — leaving it on them means they
     * keep receiving somebody else's appointment notifications.
     */
    public function test_the_device_token_moves_off_whoever_held_it_before(): void
    {
        $previous = User::create([
            'name' => 'Previous', 'email' => 'previous@example.com', 'password' => bcrypt('x'),
            'role' => 'customer', 'status' => 'active', 'fcm_token' => 'device-token-abc',
        ]);

        $this->fakeGoogle($this->payload());

        $this->postJson(route('auth.google'), [
            'credential' => 'signed-by-google',
            'fcm_token'  => 'device-token-abc',
        ])->assertOk();

        $this->assertNull($previous->fresh()->fcm_token);
        $this->assertSame('device-token-abc', User::where('email', 'aarav@gmail.com')->first()->fcm_token);
    }

    /**
     * They booked as a guest minutes ago, before any of this. That booking was
     * written with no push address on it, so the shop completing or cancelling
     * it would reach nobody — signing in closes that window.
     */
    public function test_a_live_guest_booking_made_on_this_device_gets_the_token(): void
    {
        $employee = Employee::create([
            'vendor_id' => $this->vendor->id, 'name' => 'Dr Test',
            'working_start_time' => '09:00', 'working_end_time' => '21:00',
            'is_active' => true, 'avg_consultation_time' => 15,
        ]);

        $booking = Booking::create([
            'vendor_id' => $this->vendor->id,
            'employee_id' => $employee->id,
            'customer_name' => 'Aarav',
            'customer_phone' => '9000000042',
            'booking_date' => now()->toDateString(),
            'slot_start_time' => '10:00', 'slot_end_time' => '10:15',
            'status' => 'confirmed',
        ]);

        $this->fakeGoogle($this->payload());

        $res = $this->withSession(['customer_phone' => '9000000042'])
            ->postJson(route('auth.google'), [
                'credential' => 'signed-by-google',
                'fcm_token'  => 'device-token-abc',
            ]);

        $res->assertOk()->assertJson(['bookings_attached' => 1]);
        $this->assertSame('device-token-abc', $booking->fresh()->fcm_token);
    }

    /**
     * Notification permission may never have been granted, so there is often no
     * token to send. Sign-in must not depend on one.
     */
    public function test_sign_in_works_with_no_device_token_at_all(): void
    {
        $this->fakeGoogle($this->payload());

        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])
            ->assertOk()
            ->assertJson(['success' => true, 'bookings_attached' => 0]);

        $this->assertTrue(Auth::check());
    }

    /*
    |--------------------------------------------------------------------------
    | What it does to reviews
    |--------------------------------------------------------------------------
    */

    /**
     * The point of the change: once signed in, the credential has been spent on
     * the session, and the review still comes out verified and named.
     */
    public function test_a_signed_in_google_customer_posts_a_verified_review_without_resending_the_credential(): void
    {
        $this->fakeGoogle($this->payload());
        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])->assertOk();

        $this->postJson(route('vendor.reviews.store', $this->vendor->slug), [
            'rating'  => 5,
            'comment' => 'Excellent care.',
        ])->assertOk()->assertJson([
            'success' => true,
            'review'  => ['name' => 'Aarav Sharma', 'verified' => true],
        ]);

        $review = $this->vendor->reviews()->firstOrFail();
        $this->assertTrue((bool) $review->is_verified);
        $this->assertSame('aarav@gmail.com', $review->reviewer_email);
    }

    /**
     * Being signed in must not take away the option of reviewing a shop
     * unnamed — that was always available, and people rating a place badly
     * have obvious reasons to want it.
     */
    public function test_a_signed_in_customer_can_still_post_anonymously(): void
    {
        $this->fakeGoogle($this->payload());
        $this->postJson(route('auth.google'), ['credential' => 'signed-by-google'])->assertOk();

        $this->postJson(route('vendor.reviews.store', $this->vendor->slug), [
            'rating'    => 3,
            'comment'   => 'It was fine.',
            'anonymous' => 1,
        ])->assertOk()->assertJson([
            'review' => ['name' => 'Anonymous', 'verified' => false],
        ]);

        $review = $this->vendor->reviews()->firstOrFail();
        $this->assertNull($review->reviewer_email);
        $this->assertFalse((bool) $review->is_verified);
    }

    /**
     * A password registration proves nothing about the address on it, so it
     * lends a name but never the verified badge.
     */
    public function test_a_password_customer_is_named_but_not_badged_verified(): void
    {
        $user = User::create([
            'name' => 'Priya Nair', 'email' => 'priya@example.com', 'password' => bcrypt('secret'),
            'role' => 'customer', 'status' => 'active',
        ]);

        $this->actingAs($user)
            ->postJson(route('vendor.reviews.store', $this->vendor->slug), ['rating' => 4])
            ->assertOk()
            ->assertJson(['review' => ['name' => 'Priya Nair', 'verified' => false]]);
    }

    /*
    |--------------------------------------------------------------------------
    | The modal itself
    |--------------------------------------------------------------------------
    */

    /**
     * A guest still meets the Google button, and the modal knows nobody is
     * signed in yet.
     */
    public function test_the_shop_page_offers_the_google_button_to_a_guest(): void
    {
        $res = $this->get(route('vendor.show', $this->vendor->slug));

        $res->assertOk()
            ->assertSee('x-ref="googleBtn"', false)
            ->assertSee('signedIn: false', false)
            ->assertSee('account: null', false);
    }

    /**
     * A returning customer never sees the button again — the identity chip is
     * seeded straight from their account, so their details are already filled
     * in before the modal opens.
     */
    public function test_the_shop_page_seeds_the_modal_from_a_signed_in_customer(): void
    {
        $user = User::create([
            'name' => 'Aarav Sharma', 'email' => 'aarav@gmail.com', 'mobile' => '9000000042',
            'password' => bcrypt('x'), 'role' => 'customer', 'status' => 'active',
        ]);

        $res = $this->actingAs($user)->get(route('vendor.show', $this->vendor->slug));

        $res->assertOk()
            ->assertSee('signedIn: true', false)
            ->assertSee('Aarav Sharma', false)
            ->assertSee('9000000042', false);
    }

    /**
     * The guest path is untouched: no session, no credential, no identity.
     */
    public function test_a_guest_review_is_still_anonymous(): void
    {
        $this->postJson(route('vendor.reviews.store', $this->vendor->slug), [
            'rating'        => 5,
            'reviewer_name' => 'Someone',
        ])->assertOk()->assertJson([
            'review' => ['name' => 'Someone', 'verified' => false],
        ]);

        $this->assertNull($this->vendor->reviews()->firstOrFail()->reviewer_email);
    }
}
