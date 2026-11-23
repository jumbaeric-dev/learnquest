<?php

namespace App\Notifications;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Alerts payment managers when a provider confirms a payment after its checkout window. */
class LateSettlementAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public Payment $payment)
    {
        $this->afterCommit();
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment->loadMissing(['user', 'plan']);
        $timezone = config('app.timezone');
        $expiredAt = $payment->expires_at?->copy()->setTimezone($timezone)->format('F j, Y H:i T') ?? 'Not recorded';
        $settledAt = $payment->paid_at?->copy()->setTimezone($timezone)->format('F j, Y H:i T') ?? 'Not recorded';

        return (new MailMessage())
            ->subject("Action required: late payment settled ({$payment->reference})")
            ->greeting('Late payment settlement')
            ->line('A payment was confirmed by the provider after its checkout window had closed.')
            ->line("Payment reference: {$payment->reference}")
            ->line('Parent email: ' . ($payment->user?->email ?? 'Unavailable'))
            ->line("Amount: {$payment->formattedAmount()}")
            ->line('Provider: ' . ucfirst($payment->provider))
            ->line("Original checkout expiry: {$expiredAt}")
            ->line("Settlement time: {$settledAt}")
            ->line('The payment was honoured and the associated subscription was activated. Please review the payment ledger.')
            ->action('Review payment', PaymentResource::getUrl('view', ['record' => $payment]));
    }
}
