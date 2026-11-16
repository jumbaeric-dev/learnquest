<?php

namespace App\Services\Billing\Gateways;

use App\Contracts\Billing\PaymentGateway;
use App\Exceptions\Billing\PaymentGatewayUnavailableException;
use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use Illuminate\Http\Request;

/** Fail-closed adapter base used until provider-specific sandbox support is implemented. */
abstract class AbstractUnavailablePaymentGateway implements PaymentGateway
{
    public function initiateCheckout(Payment $payment): CheckoutResult
    {
        throw new PaymentGatewayUnavailableException('This payment gateway is not enabled for checkout yet.');
    }

    public function verifyPayment(Payment $payment): PaymentVerificationResult
    {
        throw new PaymentGatewayUnavailableException('Payment verification is not configured for this gateway yet.');
    }

    public function parseWebhook(Request $request): VerifiedPaymentWebhook
    {
        throw new PaymentGatewayUnavailableException('Authenticated webhooks are not configured for this gateway yet.');
    }
}
