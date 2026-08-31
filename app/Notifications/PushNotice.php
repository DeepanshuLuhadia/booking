<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * The stored twin of a web push.
 *
 * FCM is fire-and-forget: if the device was off, or notification permission
 * was never granted, the alert simply never happened. Every push that goes to
 * a real account is therefore mirrored here (see NotificationService::
 * sendWebPush), so the notification tab on the vendor and employee panels can
 * replay what the phone may have missed.
 *
 * Flat scalars only — the row is read back long after the booking it talks
 * about may have changed, so it carries the exact words that were pushed.
 *
 * Not queued, matching ResetPasswordNotification — a worker may not be running.
 */
class PushNotice extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public array $data = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
        ] + $this->data;
    }
}
