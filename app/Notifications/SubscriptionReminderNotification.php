<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent synchronously by the daily reminder command (no queue worker needed).
 */
class SubscriptionReminderNotification extends Notification
{
    public function __construct(
        public readonly Subscription $subscription,
        public readonly string $kind,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subscription = $this->subscription;
        $trial = $subscription->isTrial();
        $label = $trial ? 'free trial' : ($subscription->plan?->name ?? 'plan');
        $expired = $this->kind === 'expired';

        $timezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');
        $endsOn = $subscription->ends_at->copy()->setTimezone($timezone)->format('F j, Y');

        $daysLeft = max(1, (int) ceil(now()->diffInSeconds($subscription->ends_at, false) / 86400));
        $when = $daysLeft <= 1 ? 'within a day' : "in {$daysLeft} days";

        $graceEnds = ($expired && $subscription->inGrace())
            ? $subscription->graceEndsAt()->copy()->setTimezone($timezone)->format('F j, Y')
            : null;

        return (new MailMessage())
            ->subject($expired
                ? "Your LearnQuest {$label} has ended"
                : "Your LearnQuest {$label} ends {$when}")
            ->greeting('Hi ' . Str::before((string) $notifiable->name, ' ') . ',')
            ->line($expired
                ? "Your {$label} ended on {$endsOn}."
                : "Your {$label} ends on {$endsOn}.")
            ->line(match (true) {
                $graceEnds !== null => "We’re keeping your children’s access on until {$graceEnds} so nothing is interrupted. Add more time before then.",
                $expired => 'Your children’s progress is safe. Pick a plan to unlock everything again.',
                default => 'Add more time to keep everything unlocked. Your children’s progress is always kept.',
            })
            ->action($trial ? 'Choose a plan' : 'Add more time', route('parent.billing'))
            ->line('You’re receiving this because you have a LearnQuest parent account.');
    }
}
