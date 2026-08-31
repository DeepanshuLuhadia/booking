<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the shop when a customer has been handed to their UPI app to pay it
 * directly.
 *
 * Deliberately worded as "check your account", never as "verify on the site".
 * The platform sees none of this money — it goes straight from the customer's
 * UPI app into the shop's own account — and the booking does not wait on
 * anybody's approval, so this is a heads-up, not a task queue. The shop's own
 * UPI app is the source of truth and the counter is where it gets settled,
 * which is why the message names the exact expected amount.
 *
 * Mail only: the durable on-site copy used to be stored here on the `database`
 * channel, but every shop push is now mirrored into the notification tab by
 * NotificationService::sendWebPush (see PushNotice), so storing it here too
 * would show the shop the same payment twice.
 *
 * Not queued, matching ResetPasswordNotification — a worker may not be running.
 */
class DirectPaymentDue extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        // Mail only when there is somewhere to send it — a shop owner's
        // account is created from a phone number and may carry no address.
        return filled($notifiable->email) ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Online payment for Booking #{$this->booking->id} — check your account")
            ->view('emails.direct_payment_due', [
                'booking'     => $this->booking,
                'vendor'      => $this->booking->vendor,
                'paymentsUrl' => route('vendor.payments.index'),
            ]);
    }

    /**
     * The stored copy the dashboard reads. Flat scalars only: this row is read
     * back long after the booking may have changed, and the amount it shows has
     * to be the one that was actually asked for at the time.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'direct_payment_due',
            'booking_id'       => $this->booking->id,
            'customer_name'    => $this->booking->customer_name,
            'requested_amount' => (string) $this->booking->requested_amount,
            'message'          => "{$this->booking->customer_name} paid ₹"
                . number_format((float) $this->booking->requested_amount, 2)
                . " online for booking #{$this->booking->id}. Check your UPI app for the credit.",
            'url'              => route('vendor.payments.index'),
        ];
    }
}
