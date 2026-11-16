<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Notifications\Concerns\RepliesToSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RepliesToSupport;

    public int $tries = 3;

    public function __construct(public Payment $payment)
    {
        // Never email a receipt for a payment that might still roll back.
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
        $payment = $this->payment->loadMissing(['plan', 'subscription']);
        $timezone = config('learnquest.daily_challenges.timezone') ?: config('app.timezone');

        $paidOn = ($payment->paid_at ?? $payment->created_at)->copy()->setTimezone($timezone)->format('F j, Y');
        $accessUntil = $payment->subscription?->ends_at?->copy()->setTimezone($timezone)->format('F j, Y');
        $planName = $payment->plan?->name ?? Str::headline($payment->plan_key);

        return $this->withSupportReply(
            (new MailMessage())
                ->subject("Your LearnQuest receipt: {$payment->formattedAmount()}")
                ->markdown('mail.payment-receipt', [
                    'name' => Str::before((string) $notifiable->name, ' '),
                    'planLabel' => "{$planName} · {$payment->periodLabel()}",
                    'amount' => $payment->formattedAmount(),
                    'paidOn' => $paidOn,
                    'method' => $this->methodLabel($payment->provider),
                    'reference' => $payment->reference,
                    'accessUntil' => $accessUntil,
                    'billingUrl' => route('parent.billing'),
                ])
        );
    }

    private function methodLabel(string $provider): string
    {
        return match ($provider) {
            'paypal' => 'PayPal',
            'paystack' => 'Card or M-Pesa',
            'intasend' => 'M-Pesa or card',
            default => ucfirst($provider),
        };
    }
}