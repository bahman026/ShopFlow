<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The "reset your password" email. It renders our own Persian RTL view rather
 * than the framework's markdown template, whose surrounding chrome ("Regards",
 * the trouble-clicking footer) would come out in English.
 */
class ResetPasswordLink extends Notification
{
    public function __construct(private string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var CanResetPassword $notifiable */
        $email = $notifiable->getEmailForPasswordReset();

        $url = route('password.reset', ['token' => $this->token, 'email' => $email]);

        return (new MailMessage)
            ->subject(trans('messages.auth.reset.email_subject'))
            ->view('emails.password-reset', [
                'url' => $url,
                'minutes' => (int) config('auth.passwords.users.expire', 60),
            ]);
    }
}
