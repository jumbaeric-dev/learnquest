<?php

namespace App\Contracts\Billing;

use App\Models\Payment;
use App\Services\Billing\DTO\CheckoutResult;
use App\Services\Billing\DTO\PaymentVerificationResult;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use Illuminate\Http\Request;

/** Provider boundary. Implementations must authenticate provider responses before returning normalized data. */
interface PaymentGateway
{
    public function initiateCheckout(Payment $payment): CheckoutResult;

    public function verifyPayment(Payment $payment): PaymentVerificationResult;

    /** Must verify the provider signature/authentication before constructing this DTO. */
    public function parseWebhook(Request $request): VerifiedPaymentWebhook;
}
