<?php

namespace Tests\Support\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use Illuminate\Http\Request;

final class FakePaymentGateway implements PaymentGateway
{
    public function initiateCheckout(Payment $payment): CheckoutResult
    {
        return new CheckoutResult('https://checkout.example.test/session/123', 'fake-checkout-123');
    }

    public function verifyPayment(Payment $payment): PaymentVerificationResult
    {
        return new PaymentVerificationResult('pending', null, $payment->amount_minor, $payment->currency);
    }

    public function parseWebhook(Request $request): VerifiedPaymentWebhook
    {
        throw new \LogicException('Not needed in registry tests.');
    }
}
