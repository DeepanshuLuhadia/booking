<?php

namespace App\Notifications;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

/**
 * The password reset email, in the platform's own template rather than
 * Laravel's default markdown notification.
 *
 * Not queued: a reset link that waits for a worker feels broken to the person
 * staring at their inbox.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expiresInMinutes = Config::get('auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Reset your ' . (SiteSetting::get('company_name') ?? config('app.name')) . ' password')
            ->view('emails.reset_password', [
                'user'      => $notifiable,
                'resetUrl'  => $url,
                'expiresIn' => $expiresInMinutes,
            ]);
    }
}
