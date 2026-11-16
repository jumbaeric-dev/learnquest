<?php

namespace App\Services\Billing;

use App\Models\Payment;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Notifications\PaymentReceiptNotification;

/** Applies authenticated, normalized provider events exactly once. */
class PaymentWebhookProcessor
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    /**
     * The DTO is trusted input from a provider adapter after signature verification.
     * No HTTP controller should construct it directly from request fields.
     *
     * @return array{status:string,payment:?Payment,event:WebhookEvent}
     */
    public function process(VerifiedPaymentWebhook $webhook): array
    {
        $this->validateWebhook($webhook);

        try {
            return DB::transaction(function () use ($webhook) {
                $existingEvent = WebhookEvent::query()
                    ->where('provider', $webhook->provider)
                    ->where('event_id', $webhook->eventId)
                    ->first();

                if ($existingEvent) {
                    return ['status' => 'duplicate_event', 'payment' => $existingEvent->payment, 'event' => $existingEvent];
                }

                $event = WebhookEvent::create([
                    'provider' => $webhook->provider,
                    'event_id' => $webhook->eventId,
                    'event_type' => $webhook->eventType,
                    'payment_reference' => $webhook->paymentReference,
                    'status' => WebhookEvent::STATUS_RECEIVED,
                    'signature_verified_at' => $webhook->signatureVerifiedAt,
                    'payload' => $this->safeMetadata($webhook->metadata),
                ]);

                $payment = Payment::query()
                    ->where('reference', $webhook->paymentReference)
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    $event->forceFill([
                        'status' => WebhookEvent::STATUS_REJECTED,
                        'failure_reason' => 'Unknown internal payment reference.',
                        'processed_at' => now(),
                    ])->save();
                    return ['status' => 'rejected', 'payment' => null, 'event' => $event];
                }

                $event->payment()->associate($payment);
                $event->save();

                $mismatch = $this->paymentMismatch($payment, $webhook);
                if ($mismatch !== null) {
                    $event->forceFill([
                        'status' => WebhookEvent::STATUS_REJECTED,
                        'failure_reason' => $mismatch,
                        'processed_at' => now(),
                    ])->save();
                    return ['status' => 'rejected', 'payment' => $payment, 'event' => $event];
                }

                // A second, distinct event must never extend an already-paid subscription.
                if ($payment->status === Payment::STATUS_SUCCEEDED) {
                    $event->forceFill([
                        'status' => WebhookEvent::STATUS_DUPLICATE,
                        'processed_at' => now(),
                    ])->save();
                    return ['status' => 'duplicate_payment', 'payment' => $payment, 'event' => $event];
                }

                // Money received always wins: a confirmed success may settle an
                // attempt already marked failed or cancelled (e.g. a delayed M-Pesa confirmation).
                $settleable = [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING];
                if ($webhook->outcome === 'succeeded') {
                    $settleable[] = Payment::STATUS_FAILED;
                    $settleable[] = Payment::STATUS_CANCELLED;
                }

                if (!in_array($payment->status, $settleable, true)) {
                    $event->forceFill([
                        'status' => WebhookEvent::STATUS_REJECTED,
                        'failure_reason' => 'Payment is no longer eligible for settlement.',
                        'processed_at' => now(),
                    ])->save();
                    return ['status' => 'rejected', 'payment' => $payment, 'event' => $event];
                }

                if ($webhook->providerTransactionId !== null) {
                    $transactionUsed = Payment::query()
                        ->where('provider', $payment->provider)
                        ->where('provider_transaction_id', $webhook->providerTransactionId)
                        ->where('id', '!=', $payment->id)
                        ->exists();
                    if ($transactionUsed) {
                        $event->forceFill([
                            'status' => WebhookEvent::STATUS_REJECTED,
                            'failure_reason' => 'Provider transaction is already linked to another payment.',
                            'processed_at' => now(),
                        ])->save();
                        return ['status' => 'rejected', 'payment' => $payment, 'event' => $event];
                    }
                }

                $lateSettlement = $webhook->outcome === 'succeeded'
                    && (
                        !in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING], true)
                        || ($payment->expires_at && $payment->expires_at->isPast())
                    );

                if ($webhook->outcome === 'pending') {
                    $payment->forceFill([
                        'status' => Payment::STATUS_PROCESSING,
                        'provider_transaction_id' => $webhook->providerTransactionId ?? $payment->provider_transaction_id,
                    ])->save();
                    $event->forceFill(['status' => WebhookEvent::STATUS_PROCESSED, 'processed_at' => now()])->save();
                    return ['status' => 'pending', 'payment' => $payment->fresh(), 'event' => $event];
                }

                if ($webhook->outcome === 'failed') {
                    $payment->forceFill([
                        'status' => Payment::STATUS_FAILED,
                        'provider_transaction_id' => $webhook->providerTransactionId ?? $payment->provider_transaction_id,
                        'failed_at' => now(),
                        'failure_code' => 'provider_payment_failed',
                    ])->save();
                    $event->forceFill(['status' => WebhookEvent::STATUS_PROCESSED, 'processed_at' => now()])->save();
                    return ['status' => 'failed', 'payment' => $payment->fresh(), 'event' => $event];
                }

                $parent = User::query()->findOrFail($payment->user_id);
                $price = $payment->price()->first();
                if (!$price) {
                    $event->forceFill([
                        'status' => WebhookEvent::STATUS_REJECTED,
                        'failure_reason' => 'The purchased price record is unavailable.',
                        'processed_at' => now(),
                    ])->save();
                    return ['status' => 'rejected', 'payment' => $payment, 'event' => $event];
                }

                $payment->forceFill([
                    'status' => Payment::STATUS_PROCESSING,
                    'provider_transaction_id' => $webhook->providerTransactionId,
                ])->save();

                // Same database transaction: entitlement activation rolls back if ledger settlement fails.
                $subscription = $this->subscriptions->activate(
                    $parent,
                    $price,
                    $payment->provider,
                    $webhook->providerTransactionId ?? $payment->reference,
                    false,
                );

                $metadata = is_array($payment->metadata) ? $payment->metadata : [];
                if ($lateSettlement) {
                    // Flag for review; the money was received, so access is granted.
                    $metadata['late_settlement'] = true;
                }

                $payment->forceFill([
                    'subscription_id' => $subscription->id,
                    'status' => Payment::STATUS_SUCCEEDED,
                    'paid_at' => now(),
                    'failed_at' => null,
                    'failure_code' => null,
                    'metadata' => $metadata,
                ])->save();

                $event->forceFill(['status' => WebhookEvent::STATUS_PROCESSED, 'processed_at' => now()])->save();

                // One receipt per settlement, sent only after the transaction commits.
                // A mail problem must never undo or fail a settled payment.
                DB::afterCommit(function () use ($payment) {
                    try {
                        $payment->user?->notify(new PaymentReceiptNotification($payment->fresh()));
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                });

                return ['status' => 'succeeded', 'payment' => $payment->fresh(), 'event' => $event];
            }, 3);
        } catch (QueryException $exception) {
            // Unique constraints are the final concurrency guard for duplicate event/transaction IDs.
            if (str_contains(strtolower($exception->getMessage()), 'unique')) {
                $event = WebhookEvent::query()
                    ->where('provider', $webhook->provider)
                    ->where('event_id', $webhook->eventId)
                    ->first();
                if ($event) {
                    return ['status' => 'duplicate_event', 'payment' => $event->payment, 'event' => $event];
                }
            }
            throw $exception;
        }
    }

    private function validateWebhook(VerifiedPaymentWebhook $webhook): void
    {
        if (
            !in_array($webhook->provider, ['intasend', 'paystack', 'paypal'], true)
            || trim($webhook->eventId) === '' || strlen($webhook->eventId) > 180
            || trim($webhook->eventType) === '' || strlen($webhook->eventType) > 120
            || trim($webhook->paymentReference) === '' || strlen($webhook->paymentReference) > 64
            || $webhook->amountMinor < 1 || !preg_match('/^[A-Z]{3}$/', $webhook->currency)
            || !in_array($webhook->outcome, ['succeeded', 'failed', 'pending'], true)
            || $webhook->signatureVerifiedAt > now()->toDateTimeImmutable()
        ) {
            throw ValidationException::withMessages(['webhook' => 'The verified payment notification is invalid.']);
        }
    }

    /** @param array<string, scalar|null> $metadata
     *  @return array<string, scalar|null>
     */
    private function safeMetadata(array $metadata): array
    {
        $allowed = ['channel', 'payment_method', 'provider_status', 'merchant_reference'];
        $safe = [];

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $metadata)) {
                continue;
            }

            $value = $metadata[$key];
            if (is_string($value)) {
                $safe[$key] = mb_substr($value, 0, 120);
            } elseif (is_int($value) || is_bool($value) || $value === null) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }

    private function paymentMismatch(Payment $payment, VerifiedPaymentWebhook $webhook): ?string
    {
        if ($payment->provider !== $webhook->provider) return 'Provider does not match the payment attempt.';
        if ($payment->amount_minor !== $webhook->amountMinor) return 'Payment amount does not match the expected amount.';
        if ($payment->currency !== $webhook->currency) return 'Payment currency does not match the expected currency.';
        if ($webhook->providerTransactionId !== null && strlen($webhook->providerTransactionId) > 150) return 'Provider transaction identifier is invalid.';
        return null;
    }
}
