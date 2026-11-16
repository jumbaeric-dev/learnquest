<?php

namespace App\Services\Billing\DTO;

use DateTimeImmutable;

/**
 * Normalized webhook data. Provider adapters must construct this only after
 * authenticating the provider signature/token and validating the request shape.
 * `metadata` must contain allow-listed non-secret fields only, never raw payloads.
 */
final readonly class VerifiedPaymentWebhook
{
    /**
     * @param array<string, scalar|null> $metadata
     * @param 'succeeded'|'failed'|'pending' $outcome
     */
    public function __construct(
        public string $provider,
        public string $eventId,
        public string $eventType,
        public string $paymentReference,
        public ?string $providerTransactionId,
        public int $amountMinor,
        public string $currency,
        public string $outcome,
        public DateTimeImmutable $signatureVerifiedAt,
        public array $metadata = [],
        public ?string $providerResourceId = null,
    ) {
    }
}
