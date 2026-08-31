<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Covers the public content pages, the contact-form pipeline (public form →
 * admin inbox → emailed reply) and the password reset flow.
 */
class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role'     => 'admin',
            'email'    => 'admin@example.test',
            'password' => Hash::make('secret-password'),
        ]);
    }

    // ---------------------------------------------------------------- pages

    public function test_public_content_pages_render(): void
    {
        $this->get('/about')->assertOk()->assertSee('Our Mission', false);
        $this->get('/terms')->assertOk()->assertSee('Governing law and disputes', false);
        $this->get('/privacy')->assertOk()->assertSee('Information we collect', false);
        $this->get('/contact')->assertOk()->assertSee('Send us a message', false);
    }

    public function test_pages_show_the_company_details_configured_by_the_admin(): void
    {
        SiteSetting::setMany([
            'company_legal_name' => 'Acme Queues Private Limited',
            'company_city'       => 'Pune',
        ]);

        $this->get('/terms')->assertSee('Acme Queues Private Limited', false);
        $this->get('/about')->assertSee('Pune', false);
    }

    // -------------------------------------------------------- contact form

    public function test_guest_can_submit_the_contact_form_and_admin_is_notified(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name'    => 'Riya Sharma',
            'email'   => 'riya@example.test',
            'phone'   => '+91 98765 43210',
            'subject' => 'Help with a booking',
            'message' => 'My token was skipped and I would like to know why.',
        ]);

        $response->assertRedirect(route('contact'))->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email'  => 'riya@example.test',
            'status' => 'new',
        ]);

        Mail::assertSent(ContactMessageReceived::class);
    }

    public function test_contact_form_rejects_incomplete_submissions(): void
    {
        $this->post('/contact', [
            'name'    => '',
            'email'   => 'not-an-email',
            'subject' => 'Help with a booking',
            'message' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_honeypot_submission_is_swallowed_without_storing_anything(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name'    => 'Bot',
            'email'   => 'bot@example.test',
            'subject' => 'Spam',
            'message' => 'Buy cheap things right now please',
            'website' => 'http://spam.example',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingSent();
    }

    // -------------------------------------------------------- admin inbox

    public function test_contact_inbox_is_closed_to_non_admins(): void
    {
        $this->get('/admin/contacts')->assertRedirect('/login');

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/contacts')->assertNotFound();
    }

    public function test_admin_can_read_and_reply_to_an_enquiry(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $enquiry = ContactMessage::create([
            'name'    => 'Riya Sharma',
            'email'   => 'riya@example.test',
            'subject' => 'Help with a booking',
            'message' => 'My token was skipped and I would like to know why.',
            'status'  => 'new',
        ]);

        // Opening it marks it read.
        $this->actingAs($admin)->get(route('admin.contacts.show', $enquiry))
            ->assertOk()
            ->assertSee('Riya Sharma', false);
        $this->assertSame('read', $enquiry->fresh()->status);

        $this->actingAs($admin)->post(route('admin.contacts.reply', $enquiry), [
            'subject' => 'Re: Help with a booking',
            'body'    => 'Sorry about that — the shop closed early. Your token was refunded.',
        ])->assertSessionHas('success');

        Mail::assertSent(ContactReplyMail::class, fn ($mail) => $mail->hasTo('riya@example.test'));

        $enquiry->refresh();
        $this->assertSame('replied', $enquiry->status);
        $this->assertSame($admin->id, $enquiry->replied_by);
        $this->assertNotNull($enquiry->replied_at);
    }

    public function test_admin_can_delete_an_enquiry(): void
    {
        $enquiry = ContactMessage::create([
            'name' => 'Spam', 'email' => 's@example.test',
            'subject' => 'x', 'message' => 'y', 'status' => 'new',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.contacts.destroy', $enquiry))
            ->assertRedirect(route('admin.contacts.index'));

        $this->assertDatabaseCount('contact_messages', 0);
    }

    // ------------------------------------------------------ admin settings

    public function test_admin_can_update_settings_and_the_public_page_changes(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.settings.update'), [
                'settings' => [
                    'about_hero_title' => 'A queue you can actually see.',
                    'company_name'     => 'Acme Queues',
                ],
            ])
            ->assertSessionHas('success');

        $this->get('/about')->assertSee('A queue you can actually see.', false);
    }

    public function test_admin_inbox_and_settings_screens_render(): void
    {
        $admin = $this->admin();

        ContactMessage::create([
            'name' => 'Riya Sharma', 'email' => 'riya@example.test',
            'subject' => 'Help with a booking', 'message' => 'Where is my token?',
            'status' => 'new',
        ]);

        $this->actingAs($admin)->get('/admin/contacts')
            ->assertOk()
            ->assertSee('Riya Sharma', false);

        $this->actingAs($admin)->get('/admin/contacts?filter=new&q=riya')
            ->assertOk()
            ->assertSee('riya@example.test', false);

        foreach (['company', 'social', 'about', 'contact', 'legal'] as $tab) {
            $this->actingAs($admin)->get("/admin/settings?tab=$tab")->assertOk();
        }
    }

    public function test_a_cleared_setting_stays_cleared_instead_of_reverting_to_its_default(): void
    {
        SiteSetting::setMany(['company_whatsapp' => '']);

        $this->assertSame('', SiteSetting::get('company_whatsapp'));
    }

    public function test_restoring_defaults_drops_the_saved_values(): void
    {
        SiteSetting::setMany(['about_hero_title' => 'Custom headline']);

        $this->actingAs($this->admin())
            ->post(route('admin.settings.reset'), ['group' => 'about'])
            ->assertSessionHas('success');

        $this->assertSame(
            SiteSetting::defaults()['about_hero_title'],
            SiteSetting::get('about_hero_title')
        );
    }

    public function test_settings_update_ignores_keys_that_are_not_declared(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.settings.update'), [
                'settings' => ['not_a_real_setting' => 'malicious'],
            ]);

        $this->assertDatabaseMissing('site_settings', ['key' => 'not_a_real_setting']);
    }

    public function test_settings_update_validates_email_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.settings.update'), [
                'settings' => ['contact_notify_email' => 'nope'],
            ])
            ->assertSessionHasErrors('settings.contact_notify_email');
    }

    public function test_settings_screen_is_closed_to_non_admins(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $this->actingAs($vendor)->get('/admin/settings')->assertNotFound();
    }

    // ------------------------------------------------------ password reset

    public function test_forgot_password_screen_renders(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot', false);
    }

    public function test_reset_link_is_emailed_to_a_registered_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'owner@example.test']);

        $this->post('/forgot-password', ['email' => 'owner@example.test'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_unknown_email_gets_the_same_response_and_no_mail(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nobody@example.test'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_their_password_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'owner@example.test']);
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->get(route('password.reset', ['token' => $notification->token]))->assertOk();

            $this->post('/reset-password', [
                'token'                 => $notification->token,
                'email'                 => $user->email,
                'password'              => 'new-secret-password',
                'password_confirmation' => 'new-secret-password',
            ])->assertRedirect(route('login'))->assertSessionHas('status');

            return true;
        });

        $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    }

    public function test_reset_fails_with_an_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.test']);

        $this->post('/reset-password', [
            'token'                 => 'this-token-is-wrong',
            'email'                 => $user->email,
            'password'              => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_reset_requires_a_matching_confirmation(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.test']);
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'new-secret-password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');
    }

    public function test_login_page_links_to_the_reset_flow(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('password.request'), false);
    }
}
