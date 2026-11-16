<?php

namespace Tests\Feature\Billing;

use App\Exceptions\Billing\PaymentGatewayUnavailableException;
use App\Exceptions\Billing\UnsupportedPaymentGatewayException;
use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\PaymentGatewayRegistry;
use App\Services\Billing\Gateways\IntaSendGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Billing\FakePaymentGateway;
use Tests\TestCase;

class PaymentGatewayRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateways_are_disabled_by_default(): void
    {
        config(['learnquest.gateways.providers.intasend.enabled' => false]);

        $this->expectException(UnsupportedPaymentGatewayException::class);
        app(PaymentGatewayRegistry::class)->forProvider('intasend', 'KES');
    }

    public function test_registry_rejects_currency_not_supported_by_provider(): void
    {
        config([
            'learnquest.gateways.providers.paypal.enabled' => true,
            'learnquest.gateways.providers.paypal.currencies' => ['USD'],
        ]);

        $this->expectException(UnsupportedPaymentGatewayException::class);
        app(PaymentGatewayRegistry::class)->forProvider('paypal', 'KES');
    }

    public function test_registry_resolves_a_configured_gateway_driver(): void
    {
        config([
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.currencies' => ['KES'],
            'learnquest.gateways.providers.intasend.driver' => FakePaymentGateway::class,
        ]);

        $gateway = app(PaymentGatewayRegistry::class)->forProvider('INTASEND', 'KES');

        $this->assertInstanceOf(FakePaymentGateway::class, $gateway);
    }

    public function test_registry_rejects_invalid_driver_configuration(): void
    {
        config([
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.currencies' => ['KES'],
            'learnquest.gateways.providers.intasend.driver' => \stdClass::class,
        ]);

        $this->expectException(UnsupportedPaymentGatewayException::class);
        app(PaymentGatewayRegistry::class)->forProvider('intasend', 'KES');
    }

    public function test_intasend_gateway_fails_closed_without_credentials(): void
    {
        config([
            'learnquest.gateways.providers.intasend.secret_key' => null,
            'learnquest.gateways.providers.intasend.public_key' => null,
        ]);

        $gateway = new IntaSendGateway();

        $payment = new Payment([
            'provider' => 'intasend',
            'amount_minor' => 49900,
            'currency' => 'KES',
        ]);

        $this->expectException(PaymentGatewayUnavailableException::class);

        $gateway->initiateCheckout($payment);
    }

    public function test_checkout_result_rejects_non_https_redirects(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CheckoutResult('http://untrusted.example.test/checkout');
    }

    public function test_verification_result_only_accepts_known_outcomes_and_valid_currency(): void
    {
        $valid = new PaymentVerificationResult('pending', null, 49900, 'KES');
        $this->assertSame('pending', $valid->outcome);

        $this->expectException(\InvalidArgumentException::class);
        new PaymentVerificationResult('complete', 'txn-1', 49900, 'KES');
    }
}
