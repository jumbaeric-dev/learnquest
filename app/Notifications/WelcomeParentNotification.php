<?php

namespace App\Notifications;

use App\Notifications\Concerns\RepliesToSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class WelcomeParentNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RepliesToSupport;

    public int $tries = 3;

    public function __construct(public ?Carbon $trialEndsAt = null)
    {
        $this->afterCommit();
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject('Welcome to LearnQuest')
            ->greeting('Welcome, ' . Str::before((string) $notifiable->name, ' ') . '!')
            ->line('Your family account is ready. Add your child, give them a username and PIN, and they can start exploring right away.');

        if ($this->trialEndsAt) {
            $timezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');

            $message->line(
                'You also have a free Premium trial, no card needed. It runs until '
                . $this->trialEndsAt->copy()->setTimezone($timezone)->format('F j, Y') . '.'
            );
        }

        return $this->withSupportReply(
            $message
                ->action('Add your first child', route('parent.children.index'))
                ->line('Questions? Just reply to this email and we’ll help.')
        );
    }
}