<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\DirectPaymentDue;
use App\Services\UpiPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Direct-to-vendor UPI payments.
 *
 * The platform is not in the money path AND does not verify it. That second
 * half is the whole design: a booking is confirmed the moment it is made, the
 * customer is handed straight to their own UPI app from the confirmation
 * screen, and the shop checks the credit in its own app and settles it with the
 * customer at the counter. Nothing on this platform gates an appointment on a
 * payment it cannot see.
 *
 * So the guarantees worth asserting here are: the amount is decided
 * server-side and cannot be influenced by the customer; the booking is real and
 * announced immediately; the shop is told there is money to look for; and
 * nobody but the booking's owner and the receiving shop can touch either end.
 */
class DirectUpiPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $plan = SubscriptionPlan::create([
            'name' => 'Test', 'price' => 0, 'max_employees' => 5,
            // The profile page @foreach-es this unguarded, so a plan without it
            // cannot render the settings screen at all.
            'features' => ['Test plan'],
            'duration_days' => 30, 'is_active' => true,
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
            'is_open' => true,
            'status' => 'active',
            'vendor_type' => 'doctor',
            'appointment_mode' => 'token',
            'subscription_plan_id' => $plan->id,
            'subscription_expires_at' => now()->addDays(30),
            'global_opening_time' => '00:00', 'global_closing_time' => '23:59',
            'service_fee' => 500,
            // Not optional in token mode: BookingController copies this straight
            // onto the booking, and bookings.token_amount is NOT NULL.
            'token_amount' => 0,
            'require_customer_details' => true,
            // The feature under test.
            'is_direct_payment_enabled' => true,
            'upi_id' => 'clinic@okaxis',
            'upi_name' => 'City Dental Clinic',
            'advance_amount' => 250.00,
        ]);

        $this->employee = Employee::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Dr Test',
            'working_start_time' => '09:00', 'working_end_time' => '21:00',
            'is_active' => true,
            'avg_consultation_time' => 15,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | The booking itself
    |--------------------------------------------------------------------------
    */

    /**
     * The change this whole flow turns on: paying the shop directly no longer
     * makes an appointment provisional.
     *
     * A booking used to be written `pending` and left there until the customer
     * uploaded a receipt and the shop approved it — two manual steps, either of
     * which losing a customer their slot. It is `confirmed` on arrival now, and
     * `payment_status = 'paid'` records only that the customer was handed to
     * their UPI app for that amount.
     */
    public function test_a_booking_at_a_direct_payment_shop_is_confirmed_at_once_and_priced_server_side(): void
    {
        $response = $this->postJson('/bookings', [
            'vendor_id' => $this->vendor->id,
            'employee_id' => $this->employee->id,
            'booking_type' => 'normal',
            'customer_name' => 'Test Customer',
            'customer_phone' => '9123456789',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $response->assertJsonPath('booking.payment_required', true);
        // The confirmation is unconditional — no "slot held" wording survives.
        $response->assertJsonPath('message', 'Booking confirmed successfully!');

        $booking = Booking::latest('id')->first();

        $this->assertSame('confirmed', $booking->status, 'The appointment must not wait on a payment.');
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('direct_upi', $booking->payment_method);
        $this->assertSame('250.00', (string) $booking->requested_amount);
        $this->assertNotNull($booking->payment_submitted_at);
    }

    /**
     * The confirmation response carries everything needed to raise the device's
     * own payment chooser, so there is no second page between "Pay & Book" and
     * "Confirmed".
     */
    public function test_the_booking_response_carries_the_upi_handoff_not_a_redirect(): void
    {
        $response = $this->postJson('/bookings', [
            'vendor_id' => $this->vendor->id,
            'employee_id' => $this->employee->id,
            'booking_type' => 'normal',
            'customer_name' => 'Test Customer',
            'customer_phone' => '9123456789',
        ])->assertOk();

        $payment = $response->json('booking.payment');

        $this->assertIsArray($payment);
        $this->assertSame('250.00', $payment['amount']);
        $this->assertSame('City Dental Clinic', $payment['payee']);
        $this->assertSame('clinic@okaxis', $payment['vpa']);
        // Who the customer shows their receipt to when their number comes up.
        $this->assertSame('Dr Test', $payment['employee_name']);
        $this->assertTrue($payment['is_advance']);
        $this->assertStringStartsWith('upi://pay?', $payment['upi_link']);
        $this->assertStringContainsString('mam=250.00', $payment['upi_link']);
        // The desktop fallback, inline so there is no file and no cached URL.
        $this->assertStringContainsString('<svg', $payment['qr_svg']);

        // The old second page is not advertised any more.
        $this->assertNull($response->json('booking.payment_url'));
    }

    /**
     * The amount lock. A customer posting their own figure must not be able to
     * change what they are asked for — this is the whole safety property, since
     * the `mam` parameter is only honoured by the customer's own UPI app.
     */
    public function test_a_customer_cannot_influence_the_requested_amount(): void
    {
        $this->postJson('/bookings', [
            'vendor_id' => $this->vendor->id,
            'employee_id' => $this->employee->id,
            'booking_type' => 'normal',
            'customer_name' => 'Test Customer',
            'customer_phone' => '9123456789',
            // All of these are ignored: there is no such input in the flow.
            'requested_amount' => '1.00',
            'advance_amount' => '1.00',
            'payment_status' => 'verified',
        ])->assertOk();

        $booking = Booking::latest('id')->first();

        $this->assertSame('250.00', (string) $booking->requested_amount);
        $this->assertSame('paid', $booking->payment_status);
    }

    /** The deep link carries both am and mam, set to the same figure. */
    public function test_the_deep_link_locks_the_amount_with_both_am_and_mam(): void
    {
        $booking = $this->makeDirectPaymentBooking();

        $link = app(UpiPaymentService::class)->deepLinkFor($booking);

        parse_str(parse_url($link, PHP_URL_QUERY), $params);

        $this->assertSame('upi', parse_url($link, PHP_URL_SCHEME));
        $this->assertSame('clinic@okaxis', $params['pa']);
        $this->assertSame('City Dental Clinic', $params['pn']);
        $this->assertSame('250.00', $params['am']);
        $this->assertSame('250.00', $params['mam'], 'mam must equal am or the amount stays editable.');
        $this->assertSame('INR', $params['cu']);
        $this->assertSame('Booking-' . $booking->id, $params['tn']);
    }

    /*
    |--------------------------------------------------------------------------
    | Who finds out, and when
    |--------------------------------------------------------------------------
    */

    /**
     * Both halves fire at booking time: the appointment is announced like any
     * other, AND the shop is told there is a credit to look for.
     *
     * The second used to arrive only if the customer came back and filled in a
     * proof form, which meant a shop could be paid and never know a booking
     * existed.
     */
    public function test_the_shop_is_told_about_the_booking_and_the_money_at_once(): void
    {
        Notification::fake();
        $spy = $this->spy(\App\Services\NotificationService::class);

        $this->makeDirectPaymentBooking();

        // The appointment, as for any other booking.
        $spy->shouldHaveReceived('notifyVendorNewBooking');

        // ...and the money, as a separate ask with a different action.
        $spy->shouldHaveReceived('notifyShop')->withArgs(
            fn ($vendor, $booking, $title, $message, $data = []) =>
                $title === 'Online Payment — Please Check'
                && str_contains($message, '250.00')
                && str_contains($message, 'Check your UPI app')
        );

        // The email/database copy that survives a phone being switched off.
        Notification::assertSentTo($this->vendor->user, DirectPaymentDue::class);
    }

    /**
     * The booking is on the shop's sheet immediately.
     *
     * Bookings owing a direct payment used to be hidden from the shop until a
     * proof arrived, so a customer could pay, turn up, and not be on the list.
     */
    public function test_a_direct_payment_booking_is_visible_to_the_shop_immediately(): void
    {
        $this->makeDirectPaymentBooking();

        $this->actingAs($this->vendor->user)
            ->get(route('vendor.bookings.index'))
            ->assertOk()
            ->assertSee('Test Customer', false);

        $this->actingAs($this->vendor->user)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Test Customer', false);
    }

    /** A shop that takes no payment is announced exactly the same way. */
    public function test_a_shop_without_direct_payments_announces_bookings_identically(): void
    {
        $this->vendor->update(['is_direct_payment_enabled' => false]);

        $spy = $this->spy(\App\Services\NotificationService::class);

        $this->makeDirectPaymentBooking();

        $spy->shouldHaveReceived('notifyVendorNewBooking');
        // Nothing to look for in the bank, so no second ask.
        $spy->shouldNotHaveReceived('notifyShop');
    }

    /*
    |--------------------------------------------------------------------------
    | The customer's screens
    |--------------------------------------------------------------------------
    */

    /**
     * Paying is a tap on the confirmation screen, not a redirect and not an
     * ambush.
     *
     * The `upi://` anchor is what makes the phone raise its own chooser with
     * the apps the customer actually has. Two things are asserted about it:
     * that it lives on the BOOKING page's confirmation modal rather than on a
     * page of its own, and that nothing fires it for the customer — a payment
     * sheet thrown over a confirmation nobody has read yet gets dismissed out
     * of surprise, which is indistinguishable from refusing to pay.
     */
    public function test_the_confirmation_screen_offers_the_upi_chooser_on_a_tap(): void
    {
        $response = $this->get(route('vendor.show', $this->vendor->slug));

        $response->assertOk();

        // The confirmation modal carries the payment block...
        $response->assertSee('Pay The Business', false);
        $response->assertSee('When Your Turn Comes', false);
        $response->assertSee('Show your UPI payment screenshot to', false);

        // ...with the pay action as a real anchor on the scheme, fired by the
        // customer's own tap.
        $response->assertSee(':href="payment.upi_link" @click="payNow($event)"', false);
        $response->assertSee("'Pay ₹' + payment.amount + ' Now'", false);
        $response->assertSee('payNow(event) {', false);

        // Nothing launches it for them, and there is no second page to go to.
        $response->assertDontSee('this.launchUpi()', false);
        $response->assertDontSee('payment_url', false);
    }

    /** The same tap-to-pay handoff on the single-specialist page. */
    public function test_the_specialist_page_offers_the_upi_chooser_too(): void
    {
        $response = $this->get(route('employee.public.show', $this->employee));

        $response->assertOk();
        $response->assertSee(':href="payment.upi_link" @click="payNow($event)"', false);
        $response->assertSee('Show your UPI payment screenshot to', false);
        $response->assertDontSee('this.launchUpi()', false);
        $response->assertDontSee('payment_url', false);
    }

    /**
     * Nothing is collected from the customer after they pay.
     *
     * The proof endpoint is not merely hidden — it does not exist. A UTR box, a
     * screenshot upload and a "verifying your payment" wait were three ways for
     * an already-paid customer to end up without an appointment.
     */
    public function test_there_is_no_proof_submission_endpoint(): void
    {
        $this->assertFalse(
            Route::has('payment.proof.store'),
            'The proof endpoint must be gone: the platform collects no evidence of payment.'
        );
        $this->assertFalse(
            Route::has('vendor.payments.reject'),
            'The platform performs no verification, so there is nothing for a shop to reject.'
        );
    }

    /**
     * The surviving payment page is a way BACK to the UPI app, not a step.
     *
     * It exists for the customer who dismissed the chooser without paying, or
     * who returns from My Bookings a day later — so it must say plainly that
     * the appointment is not at risk, and must ask for nothing.
     */
    public function test_the_pay_again_screen_offers_the_link_and_asks_for_nothing(): void
    {
        $booking = $this->makeDirectPaymentBooking();

        $response = $this->get(route('payment.show', $booking));

        $response->assertOk();
        $response->assertSee('Your appointment is already confirmed', false);
        $response->assertSee('Pay ₹250.00 Now', false);
        $response->assertSee('Show your UPI payment screenshot to', false);
        $response->assertSee('Dr Test', false);
        // The QR encodes the same string, rendered inline as SVG.
        $response->assertSee('<svg', false);

        // A real link on the generic scheme — the only form that raises the
        // system chooser, and the least likely to be blocked.
        $this->assertMatchesRegularExpression(
            '/<a[^>]+href="upi:\/\/pay\?[^"]*mam=250\.00[^"]*"/',
            $response->getContent(),
            'The payment screen must link straight to the upi:// scheme.'
        );

        // No hardcoded app schemes, and nothing to fill in.
        foreach (['tez:', 'phonepe:', 'paytmmp:', 'bhim:'] as $scheme) {
            $response->assertDontSee($scheme, false);
        }
        $response->assertDontSee('utr_number', false);
        $response->assertDontSee('payment_screenshot', false);
    }

    /**
     * My Bookings keeps a way back to the payment, folded behind a question.
     *
     * Two things have to hold. The page must not ASSERT that a payment
     * happened — `payment_status` becomes 'paid' when the customer is handed to
     * their UPI app, not when money moves — and it must still reach the link,
     * because `payment_due` is false from the moment a booking is made and the
     * customer who dismissed the chooser has no other route to it.
     *
     * A closed disclosure does both: nothing is claimed, and the only people
     * who see a Pay button are the ones who answered "no".
     */
    public function test_my_bookings_hides_the_pay_link_behind_a_question(): void
    {
        $response = $this->get(route('bookings.mine'));

        $response->assertOk();

        // The question, and the link it opens onto.
        $response->assertSee("Haven't made the payment yet?", false);
        $response->assertSee(':href="booking.payment_url"', false);
        $response->assertSee("'Pay ₹' + booking.requested_amount + ' Now'", false);

        // Asked only where there is something to pay — `payment_url` is null
        // for shops that do not collect directly.
        $response->assertSee("booking.payment_url && booking.payment_status !== 'verified'", false);

        // The unverifiable claim is gone, and so is the pay button that used to
        // stand next to it.
        $response->assertDontSee('Paid · Show Receipt', false);
    }

    /** Another device may not see or pay for somebody else's booking. */
    public function test_a_stranger_cannot_reach_the_payment_screen(): void
    {
        $booking = $this->makeDirectPaymentBooking();

        // flushSession() drops the identity the booking request established.
        $this->flushSession();

        $this->get(route('payment.show', $booking))->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | The shop's ledger
    |--------------------------------------------------------------------------
    */

    /** Every direct payment lands on the shop's list to be checked off. */
    public function test_the_payments_ledger_lists_the_money_to_look_for(): void
    {
        $this->makeDirectPaymentBooking();

        $response = $this->actingAs($this->vendor->user)->get(route('vendor.payments.index'));

        $response->assertOk();
        $response->assertSee('Paid Online &middot; Not Ticked Off', false);
        $response->assertSee('₹250.00', false);
        $response->assertSee('Test Customer', false);
        $response->assertSee('Mark As Received', false);
        // The shop must not be led to believe the platform checked anything.
        $response->assertSee('never received them and cannot confirm them for you', false);
        $response->assertSee('already confirmed', false);
    }

    /**
     * Marking a payment received is bookkeeping and nothing more — it must not
     * touch the appointment, which was already confirmed.
     */
    public function test_marking_a_payment_received_does_not_touch_the_booking(): void
    {
        $booking = $this->makeDirectPaymentBooking();

        $this->assertSame('confirmed', $booking->status);

        $this->actingAs($this->vendor->user)
            ->post(route('vendor.payments.approve', $booking))
            ->assertRedirect();

        $booking->refresh();

        $this->assertSame('verified', $booking->payment_status);
        $this->assertSame('confirmed', $booking->status);
        $this->assertNotNull($booking->payment_verified_at);
    }

    /** A second tick on the same payment is a no-op, not a double action. */
    public function test_marking_received_twice_does_not_re_notify(): void
    {
        $booking = $this->makeDirectPaymentBooking();

        $this->actingAs($this->vendor->user)->post(route('vendor.payments.approve', $booking));
        $this->actingAs($this->vendor->user)
            ->post(route('vendor.payments.approve', $booking))
            ->assertSessionHas('info');

        $this->assertSame('verified', $booking->fresh()->payment_status);
    }

    /** A shop may only tick off payments made into its own account. */
    public function test_another_vendor_cannot_touch_this_shops_payment(): void
    {
        $booking = $this->makeDirectPaymentBooking();

        $otherOwner = User::create([
            'name' => 'Other', 'email' => 'other@example.com', 'mobile' => '9000000009',
            'password' => bcrypt('secret'), 'role' => 'vendor',
        ]);
        Vendor::create([
            'user_id' => $otherOwner->id,
            'business_name' => 'Other Clinic', 'owner_name' => 'Other',
            'contact_number' => '9000000009', 'address' => '2 Test Road',
            'latitude' => 26.9, 'longitude' => 75.8, 'status' => 'active', 'is_open' => true,
            'vendor_type' => 'doctor', 'appointment_mode' => 'token',
            'subscription_plan_id' => $this->vendor->subscription_plan_id,
            'subscription_expires_at' => now()->addDays(30),
            'global_opening_time' => '00:00', 'global_closing_time' => '23:59',
        ]);

        $this->actingAs($otherOwner)
            ->post(route('vendor.payments.approve', $booking))
            ->assertNotFound();

        $this->assertSame('paid', $booking->fresh()->payment_status);
    }

    /*
    |--------------------------------------------------------------------------
    | Shops that take no payment
    |--------------------------------------------------------------------------
    */

    /** With the feature off, the old behaviour is untouched. */
    public function test_a_shop_without_direct_payments_still_confirms_immediately(): void
    {
        $this->vendor->update(['is_direct_payment_enabled' => false]);

        $response = $this->postJson('/bookings', [
            'vendor_id' => $this->vendor->id,
            'employee_id' => $this->employee->id,
            'booking_type' => 'normal',
            'customer_name' => 'Test Customer',
            'customer_phone' => '9123456789',
        ])->assertOk();

        $response->assertJsonPath('booking.payment_required', false);
        $response->assertJsonPath('booking.payment', null);

        $booking = Booking::latest('id')->first();
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('pending', $booking->payment_status);
        $this->assertSame('0.00', (string) $booking->requested_amount);
        $this->assertFalse($booking->collectsAdvance());
    }

    /**
     * The UPI ID is the one thing the toggle cannot do without — enabling
     * without it strands the customer on a payment screen with nobody to pay.
     */
    public function test_the_toggle_cannot_be_enabled_without_a_valid_vpa(): void
    {
        $this->actingAs($this->vendor->user)
            ->post(route('vendor.profile.update'), $this->profilePayload([
                'is_direct_payment_enabled' => '1',
                'upi_id' => 'not-a-vpa',
                'advance_amount' => '250',
            ]))
            ->assertSessionHasErrors('upi_id');

        $this->actingAs($this->vendor->user)
            ->post(route('vendor.profile.update'), $this->profilePayload([
                'is_direct_payment_enabled' => '1',
                'upi_id' => '',
                'advance_amount' => '250',
            ]))
            ->assertSessionHasErrors('upi_id');
    }

    /**
     * The advance is optional. An empty box is a real setting — "charge the
     * full booking price" — so it must save cleanly and leave the feature live.
     */
    public function test_the_advance_amount_is_optional_and_the_feature_stays_enabled_without_it(): void
    {
        $this->actingAs($this->vendor->user)
            ->post(route('vendor.profile.update'), $this->profilePayload([
                'is_direct_payment_enabled' => '1',
                'upi_id' => 'clinic@okaxis',
                'advance_amount' => '',
            ]))
            ->assertSessionHasNoErrors();

        $vendor = $this->vendor->fresh();

        $this->assertSame('0.00', (string) $vendor->advance_amount);
        $this->assertTrue($vendor->is_direct_payment_enabled);
        // Still live: no advance means full amount, not "switched off".
        $this->assertTrue($vendor->acceptsDirectAdvance());
    }

    public function test_a_negative_advance_is_refused(): void
    {
        $this->actingAs($this->vendor->user)
            ->post(route('vendor.profile.update'), $this->profilePayload([
                'is_direct_payment_enabled' => '1',
                'upi_id' => 'clinic@okaxis',
                'advance_amount' => '-50',
            ]))
            ->assertSessionHasErrors('advance_amount');
    }

    /**
     * With no advance set, the customer is asked for the whole booking price:
     * base service fee plus the premium supplement on a premium slot. This is
     * the same "Due Now" figure the booking screen quoted them.
     */
    public function test_with_no_advance_the_customer_is_charged_the_full_booking_amount(): void
    {
        // service_fee 500, no advance.
        $this->vendor->update(['advance_amount' => 0]);

        $booking = $this->makeDirectPaymentBooking();

        $this->assertSame('500.00', (string) $booking->requested_amount);
        $this->assertSame('confirmed', $booking->status);
        $this->assertTrue($booking->collectsAdvance());
    }

    /** The employee's own fee overrides the shop's when one is set. */
    public function test_the_full_amount_honours_an_employee_fee_override(): void
    {
        $this->vendor->update(['advance_amount' => 0]);
        $this->employee->update(['service_fee_override' => 750]);

        $this->assertSame('750.00', (string) $this->makeDirectPaymentBooking()->requested_amount);
    }

    /** A named advance wins over the full amount — it is a deposit. */
    public function test_a_named_advance_is_charged_instead_of_the_full_amount(): void
    {
        // advance 250 against a 500 service fee.
        $booking = $this->makeDirectPaymentBooking();

        $this->assertSame('250.00', (string) $booking->requested_amount);
    }

    /** No advance and nothing to charge: the booking confirms as normal. */
    public function test_a_free_booking_with_no_advance_collects_nothing_and_confirms(): void
    {
        $this->vendor->update(['advance_amount' => 0, 'service_fee' => 0]);

        $booking = $this->makeDirectPaymentBooking();

        $this->assertSame('0.00', (string) $booking->requested_amount);
        $this->assertSame('confirmed', $booking->status);
        $this->assertFalse($booking->collectsAdvance());
    }

    /**
     * A shop whose `upi_id` holds legacy free text rather than a real VPA is
     * not payable, even with the toggle on — it would produce a deep link
     * pointing nowhere.
     */
    public function test_a_legacy_non_vpa_upi_id_does_not_make_the_shop_payable(): void
    {
        $this->vendor->update(['upi_id' => 'ask at the counter']);

        $this->assertFalse($this->vendor->fresh()->acceptsDirectAdvance());

        $booking = $this->makeDirectPaymentBooking();

        $this->assertSame('0.00', (string) $booking->requested_amount);
        $this->assertSame('confirmed', $booking->status);
    }

    /**
     * The booking screen asks for payment in one action.
     *
     * One tap books AND raises the payment chooser — the button must name that
     * and quote the figure the customer's UPI app will show, and the vendor
     * page must carry the state the label is built from.
     */
    public function test_the_booking_screen_offers_a_single_pay_and_book_action(): void
    {
        $response = $this->get(route('vendor.show', $this->vendor->slug));

        $response->assertOk();

        // The label is built from these, and the amount line beside it.
        $response->assertSee('directPayment: true', false);
        $response->assertSee('fixedAdvance: 250', false);
        $response->assertSee("'PAY ₹' + payableNow + ' & BOOK'", false);
        $response->assertSee('Pay Now', false);
        $response->assertSee('Paid directly to City Dental Clinic via UPI', false);

        // The un-paid wording is not merely hidden — it is not sent at all.
        $response->assertDontSee('AUTHENTICATE &amp; BOOK', false);
    }

    /** A shop not taking payment keeps the original wording untouched. */
    public function test_the_booking_screen_keeps_its_original_button_without_direct_payment(): void
    {
        $this->vendor->update(['is_direct_payment_enabled' => false]);

        $response = $this->get(route('vendor.show', $this->vendor->slug));

        $response->assertOk();
        $response->assertSee('directPayment: false', false);
        $response->assertSee('AUTHENTICATE &amp; BOOK', false);

        // None of the payment wording reaches a shop that does not charge.
        $response->assertDontSee('Paid directly to City Dental Clinic via UPI', false);
        $response->assertDontSee("'PAY ₹' + payableNow + ' & BOOK'", false);
        $response->assertDontSee('Balance At Venue', false);
    }

    /**
     * The browser-side half of the same rules, which must mirror the server's
     * split exactly: the UPI ID required-when-enabled, the amount never
     * required.
     *
     * The UPI requirement must be BOUND to the toggle, never hardcoded — these
     * inputs sit in the one big profile form, so a static `required` would stop
     * every shop that does not use the feature from saving at all.
     */
    public function test_the_form_requires_the_vpa_with_the_toggle_but_never_the_amount(): void
    {
        $response = $this->actingAs($this->vendor->user)->get(route('vendor.profile.edit'));

        $response->assertOk();

        preg_match('/<input[^>]*name="upi_id"[^>]*>/s', $response->getContent(), $vpaInput);
        $this->assertNotEmpty($vpaInput, 'The upi_id input is missing from the settings form.');
        $this->assertStringContainsString(':required="enabled"', $vpaInput[0]);
        $this->assertDoesNotMatchRegularExpression('/\srequired[\s=>]/', $vpaInput[0]);

        // The amount carries no requirement of either kind.
        preg_match('/<input[^>]*name="advance_amount"[^>]*>/s', $response->getContent(), $amountInput);
        $this->assertNotEmpty($amountInput, 'The advance_amount input is missing from the settings form.');
        $this->assertStringNotContainsString(':required', $amountInput[0]);
        $this->assertDoesNotMatchRegularExpression('/\srequired[\s=>]/', $amountInput[0]);
    }

    /**
     * The counterpart risk: a shop that never touches this feature must still
     * be able to save its profile with no UPI details at all.
     */
    public function test_a_shop_not_using_advances_can_still_save_its_profile(): void
    {
        $this->vendor->update(['is_direct_payment_enabled' => false]);

        // No toggle, no VPA, no amount.
        $this->actingAs($this->vendor->user)
            ->post(route('vendor.profile.update'), $this->profilePayload())
            ->assertSessionHasNoErrors();

        $vendor = $this->vendor->fresh();
        $this->assertFalse($vendor->is_direct_payment_enabled);
        $this->assertSame('0.00', (string) $vendor->advance_amount);
        $this->assertFalse($vendor->acceptsDirectAdvance());
    }

    public function test_the_qr_preview_endpoint_refuses_an_invalid_vpa_and_renders_a_valid_one(): void
    {
        $this->actingAs($this->vendor->user)
            ->getJson(route('vendor.profile.upi-qr', ['upi_id' => 'nope', 'advance_amount' => 100]))
            ->assertOk()
            ->assertJsonPath('ok', false);

        $response = $this->actingAs($this->vendor->user)
            ->getJson(route('vendor.profile.upi-qr', [
                'upi_id' => 'clinic@okaxis', 'upi_name' => 'City Dental', 'advance_amount' => 250,
            ]))->assertOk()->assertJsonPath('ok', true);

        $this->assertStringContainsString('mam=250.00', $response->json('link'));
        $this->assertStringContainsString('<svg', $response->json('svg'));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * A valid profile-update body. The unrelated required fields (name, phone,
     * coordinates) are boilerplate every one of these posts needs, and spelling
     * them out per test buries the field actually under examination.
     */
    public function vendorId(): int { return $this->vendor->id; }
    public function employeeId(): int { return $this->employee->id; }

    private function profilePayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'City Dental Clinic',
            'owner_name' => 'Shop Owner Name',
            'contact_number' => '9000000001',
            'address' => '1 Test Road',
            'latitude' => 26.9124,
            'longitude' => 75.7873,
        ], $overrides);
    }

    /**
     * A booking made through the real endpoint, so the session/cookie identity
     * the ownership checks rely on is established exactly as it is in life.
     */
    private function makeDirectPaymentBooking(
        string $phone = '9123456789',
        ?int $employeeId = null,
        ?int $vendorId = null
    ): Booking {
        $this->postJson('/bookings', [
            'vendor_id' => $vendorId ?? $this->vendor->id,
            'employee_id' => $employeeId ?? $this->employee->id,
            'booking_type' => 'normal',
            'customer_name' => 'Test Customer',
            'customer_phone' => $phone,
        ])->assertOk();

        return Booking::latest('id')->first();
    }
}
