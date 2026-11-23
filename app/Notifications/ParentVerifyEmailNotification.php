<?php

namespace App\Notifications;

use App\Notifications\Concerns\RepliesToSupport;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** Sent immediately (not queued): the parent is waiting on it. */
class ParentVerifyEmailNotification extends Notification
{
    use RepliesToSupport;

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = max(1, (int) config('learnquest.auth.verification_minutes', 1440));

        // The hash ties the link to the current address: change the email and old links stop working.
        $url = URL::temporarySignedRoute(
            'parent.verification.verify',
            now()->addMinutes($minutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $hours = max(1, intdiv($minutes, 60));

        return $this->withSupportReply(
            (new MailMessage())
                ->subject('Please verify your email address')
                ->greeting('Hi ' . Str::before((string) $notifiable->name, ' ') . ',')
                ->line('Please confirm this is your email address so we can send your receipts and renewal reminders.')
                ->action('Verify email address', $url)
                ->line("This link expires in {$hours} " . Str::plural('hour', $hours) . '.')
                ->line('If you didn’t create a LearnQuest account, you can ignore this email.')
        );
    }
}