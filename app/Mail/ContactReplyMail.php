<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The admin's reply to a contact enquiry, sent from the panel.
 *
 * Not queued — the admin is watching the screen and needs the send result
 * reflected in the flash message, not deferred to a worker.
 */
class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $replyBody,
        public string $replySubject,
    ) {
    }

    public function envelope(): Envelope
    {
        $supportEmail = \App\Models\SiteSetting::get('company_support_email');

        return new Envelope(
            subject: $this->replySubject,
            replyTo: $supportEmail
                ? [new Address($supportEmail, \App\Models\SiteSetting::get('company_name') ?? config('app.name'))]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact_reply');
    }
}
