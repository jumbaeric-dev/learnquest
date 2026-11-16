<?php

namespace App\Notifications;

use App\Models\SubscriptionGrant;
use App\Notifications\Concerns\RepliesToSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** Tells a parent about free time an admin added. The internal reason is never included. */
class ComplimentaryTimeGrantedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RepliesToSupport;

    public int $tries = 3;

    public function __construct(public SubscriptionGrant $grant)
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
        $grant = $this->grant->loadMissing('plan');
        $timezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');

        $plan = $grant->plan?->name ?? 'plan';
        $days = $grant->days . ' ' . Str::plural('day', $grant->days);
        $endsOn = $grant->new_ends_at->copy()->setTimezone($timezone)->format('F j, Y');

        return $this->withSupportReply(
            (new MailMessage())
                ->subject("We’ve added {$days} to your LearnQuest {$plan}")
                ->greeting('Hi ' . Str::before((string) $notifiable->name, ' ') . ',')
                ->line("Good news: we’ve added {$days} of free {$plan} access to your account.")
                ->line("Your access now runs until {$endsOn}.")
                ->action('View your plan', route('parent.billing'))
        );
    }
}