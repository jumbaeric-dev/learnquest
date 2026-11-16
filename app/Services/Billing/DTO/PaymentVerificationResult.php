<?php

namespace App\Services\Billing\DTO;

final readonly class PaymentVerificationResult
{
    /** @param 'pending'|'succeeded'|'failed' $outcome */
    public function __construct(
        public string $outcome,
        public ?string $providerTransactionId,
        public int $amountMinor,
        public string $currency,
        public ?string $providerState = null,
    ) {
        if (!in_array($this->outcome, ['pending', 'succeeded', 'failed'], true)) {
            throw new \InvalidArgumentException('Unsupported payment verification outcome.');
        }
        if ($this->amountMinor < 0 || !preg_match('/^[A-Z]{3}$/', $this->currency)) {
            throw new \InvalidArgumentException('Invalid verified payment amount or currency.');
        }
    }
}
