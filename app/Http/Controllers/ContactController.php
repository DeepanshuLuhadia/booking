<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Public "Contact Us" page and its form handler.
 *
 * Open to guests by design — most visitors on this platform never sign in, so
 * requiring an account to ask a question would silence the people most likely
 * to need help.
 */
class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact', ['settings' => SiteSetting::all_settings()]);
    }

    public function store(Request $request)
    {
        // `website` is a honeypot: hidden in the markup, so anything that fills
        // it in is a bot. Answered with the normal success message rather than
        // an error, so the bot has nothing to tune against.
        if (filled($request->input('website'))) {
            return redirect()->route('contact')->with('success', 'Thanks for reaching out. We will get back to you shortly.');
        }

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:120'],
            'email'   => ['required', 'email:rfc', 'max:190'],
            'phone'   => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'message.min' => 'Please give us a little more detail (at least 10 characters).',
            'phone.regex' => 'Enter a valid phone number.',
        ]);

        $message = ContactMessage::create([
            'user_id'    => auth()->id(),
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'subject'    => $data['subject'],
            'message'    => $data['message'],
            'status'     => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
        ]);

        // In-panel alert, alongside the mail below. The mailbox it is sent to
        // may not be one anybody watches; the panel always is.
        try {
            app(\App\Services\NotificationService::class)->notifyAdminsNewEnquiry($message);
        } catch (\Throwable $e) {
            Log::error('New-enquiry admin alert failed', ['error' => $e->getMessage()]);
        }

        // The enquiry is already safely stored, so a mail failure must not cost
        // the visitor their message or show them an error.
        try {
            $notifyTo = SiteSetting::get('contact_notify_email') ?: config('support.admin_email');

            if ($notifyTo) {
                Mail::to($notifyTo)->send(new ContactMessageReceived($message));
            }
        } catch (\Throwable $e) {
            Log::error('Contact form admin notification failed', [
                'contact_message_id' => $message->id,
                'error'              => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('contact')
            ->with('success', 'Thanks for reaching out, ' . $message->name . '. We have your message and will reply to ' . $message->email . ' shortly.');
    }
}
