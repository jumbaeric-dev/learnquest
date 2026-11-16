<?php

namespace App\Notifications;

use App\Notifications\Concerns\RepliesToSupport;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** Sent immediately (not queued): reset links are time-sensitive. */
class ParentResetPasswordNotification extends Notification
{
    use RepliesToSupport;

    public function __construct(public string $token)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('parent.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return $this->withSupportReply(
            (new MailMessage())
                ->subject('Reset your LearnQuest password')
                ->greeting('Hi ' . Str::before((string) $notifiable->name, ' ') . ',')
                ->line('We received a request to reset your LearnQuest password.')
                ->action('Reset password', $url)
                ->line("This link expires in {$minutes} minutes.")
                ->line('If you didn’t ask for this, you can safely ignore this email. Your password won’t change.')
        );
    }
}