<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Terms/Privacy tick on vendor registration is a gate, not decoration.
 *
 * The browser attribute alone is not the control — anyone can strip it — so the
 * server rule is the thing under test here, with the rendered checkbox checked
 * alongside it so the two cannot drift apart.
 */
class VendorRegConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_form_renders_a_required_consent_checkbox_linking_both_policies(): void
    {
        $response = $this->get('/register/vendor');

        $response->assertOk();
        $response->assertSee('name="terms"', false);
        $response->assertSee('type="checkbox"', false);
        $response->assertSee(route('terms'), false);
        $response->assertSee(route('privacy'), false);

        // The input itself must carry `required`, not just some other field.
        $this->assertMatchesRegularExpression(
            '/<input[^>]*name="terms"[^>]*>/s',
            $response->getContent(),
            'The consent checkbox is missing from the form.'
        );
        preg_match('/<input[^>]*name="terms"[^>]*>/s', $response->getContent(), $input);
        $this->assertStringContainsString('required', $input[0]);
    }

    public function test_registration_is_rejected_when_the_box_is_not_ticked(): void
    {
        $payload = [
            'vendor_type'           => 'salon',
            'business_name'         => 'Clip Joint',
            'owner_name'            => 'Alex Owner',
            'email'                 => 'consent@example.com',
            'mobile'                => '9876543210',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'subscription_plan_id'  => 1,
        ];

        $this->post('/register/vendor', $payload)->assertSessionHasErrors('terms');
        $this->assertDatabaseMissing('users', ['email' => 'consent@example.com']);

        // An unticked box posts nothing; a forged "0" must fail the same way.
        $this->post('/register/vendor', $payload + ['terms' => '0'])
            ->assertSessionHasErrors('terms');
        $this->assertDatabaseMissing('users', ['email' => 'consent@example.com']);
    }
}
