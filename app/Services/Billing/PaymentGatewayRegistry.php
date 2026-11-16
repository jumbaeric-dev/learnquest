<?php

namespace App\Services\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Exceptions\Billing\UnsupportedPaymentGatewayException;
use App\Models\Payment;
use Illuminate\Support\Facades\App;

/** Resolves an enabled gateway only when it supports the payment's currency. */
final class PaymentGatewayRegistry
{
    public function forPayment(Payment $payment): PaymentGateway
    {
        return $this->forProvider($payment->provider, $payment->currency);
    }

    public function forProvider(string $provider, ?string $currency = null): PaymentGateway
    {
        $provider = strtolower(trim($provider));
        $settings = config("learnquest.gateways.providers.{$provider}");

        if (!is_array($settings) || !($settings['enabled'] ?? false)) {
            throw new UnsupportedPaymentGatewayException('The selected payment gateway is not currently available.');
        }

        $currencies = $settings['currencies'] ?? [];
        if ($currency !== null && !in_array(strtoupper($currency), $currencies, true)) {
            throw new UnsupportedPaymentGatewayException('The selected payment gateway does not support this currency.');
        }

        $gatewayClass = $settings['driver'] ?? null;
        if (
            !is_string($gatewayClass) || !class_exists($gatewayClass)
            || !is_subclass_of($gatewayClass, PaymentGateway::class)
        ) {
            throw new UnsupportedPaymentGatewayException('The selected payment gateway is not configured correctly.');
        }

        return App::make($gatewayClass);
    }

    /** The provider that handles checkout for a currency (falls back to the default). */
    public function providerForCurrency(string $currency): string
    {
        $mapped = config('learnquest.gateways.currency_providers.' . strtoupper($currency));

        return is_string($mapped) && $mapped !== ''
            ? strtolower($mapped)
            : (string) config('learnquest.gateways.default_provider', 'paystack');
    }
}
