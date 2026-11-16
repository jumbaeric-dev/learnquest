<?php

namespace App\Services\Billing\DTO;

use DateTimeImmutable;

final readonly class CheckoutResult
{
    /** @param array<string, scalar|null> $metadata */
    public function __construct(
        public ?string $checkoutUrl,
        public ?string $providerReference = null,
        public ?DateTimeImmutable $expiresAt = null,
        public array $metadata = [],
        public string $interaction = 'redirect',
    ) {
        if ($this->checkoutUrl !== null
            && (!filter_var($this->checkoutUrl, FILTER_VALIDATE_URL)
                || parse_url($this->checkoutUrl, PHP_URL_SCHEME) !== 'https')) {
            throw new \InvalidArgumentException('Checkout URLs must be valid HTTPS URLs.');
        }

        if (!in_array($this->interaction, ['redirect', 'push'], true)
            || ($this->interaction === 'redirect' && $this->checkoutUrl === null)
            || ($this->interaction === 'push' && $this->checkoutUrl !== null)) {
            throw new \InvalidArgumentException('The checkout interaction details are invalid.');
        }
    }
}
