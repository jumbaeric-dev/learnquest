<?php

namespace App\Services\Billing;

use App\Models\Payment;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;

/**
 * Settles a payment from the provider's own status API, never from a
 * browser redirect or a webhook body. Webhooks are only a trigger.
 */
final class PaymentReconciliationService
{
    public function __construct(
        private readonly PaymentGatewayRegistry $gateways,
        private readonly PaymentWebhookProcessor $processor,
    ) {}

    /** @return array{status:string,payment:?Payment} */
    public function reconcile(Payment $payment): array
    {
        if (! in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING], true)) {
            return ['status' => 'unchanged', 'payment' => $payment];
        }

        // IntaSend requires its invoice ID. Paystack verifies against the
        // immutable internal reference used when the transaction was initialized.
        $providerResourceId = match ($payment->provider) {
            'intasend' => data_get($payment->metadata, 'intasend_invoice_id'),
            'paystack' => data_get($payment->metadata, 'paystack_transaction_reference', $payment->reference),
            'paypal' => data_get($payment->metadata, 'paypal_order_id'),
            default => null,
        };

        if (! is_string($providerResourceId) || $providerResourceId === '') {
            return ['status' => 'no_invoice', 'payment' => $payment];
        }

        $verified = $this->gateways->forPayment($payment)->verifyPayment($payment);
        $state = $verified->providerState ?? strtoupper($verified->outcome);

        // Stable event identity keeps a webhook-triggered check and scheduled
        // reconciliation from processing the same provider state twice.
        $eventIdentity = $payment->provider === 'intasend' ? $providerResourceId : $payment->reference;
        $result = $this->processor->process(new VerifiedPaymentWebhook(
            provider: $payment->provider,
            eventId: "{$eventIdentity}:{$state}",
            eventType: 'status_check',
            paymentReference: $payment->reference,
            providerTransactionId: $verified->providerTransactionId,
            amountMinor: $verified->amountMinor,
            currency: $verified->currency,
            outcome: $verified->outcome,
            signatureVerifiedAt: now()->toDateTimeImmutable(),
            metadata: ['channel' => 'status_check', 'provider_status' => $state],
            providerResourceId: $providerResourceId,
        ));

        return ['status' => $result['status'], 'payment' => $result['payment']];
    }
}
