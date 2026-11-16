<?php

namespace App\Http\Controllers\Webhooks;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Exceptions\Billing\PaymentGatewayUnavailableException;
use App\Exceptions\Billing\UnsupportedPaymentGatewayException;
use App\Exceptions\Billing\WebhookAuthenticationException;
use App\Models\Payment;
use App\Services\Billing\PaymentGatewayRegistry;
use App\Services\Billing\PaymentReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Paystack webhooks are authenticated triggers; settlement always uses API verification. */
final class PaystackWebhookController
{
    public function __invoke(
        Request $request,
        PaymentGatewayRegistry $gateways,
        PaymentReconciliationService $reconciler,
    ): JsonResponse {
        try {
            $webhook = $gateways->forProvider('paystack')->parseWebhook($request);
        } catch (WebhookAuthenticationException) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        } catch (PaymentGatewayUnavailableException | UnsupportedPaymentGatewayException $exception) {
            Log::error('Paystack webhook received but the gateway is not configured.', [
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);
            return response()->json(['message' => 'Webhook receiver unavailable.'], 503);
        } catch (PaymentGatewayException $exception) {
            // Authenticated but irrelevant/unusable events should not be retried.
            return response()->json(['status' => 'ignored']);
        }

        $payment = Payment::query()
            ->where('reference', $webhook->paymentReference)
            ->where('provider', 'paystack')
            ->first();

        // A Paystack account may receive transactions unrelated to LearnQuest.
        if (! $payment) {
            return response()->json(['status' => 'ignored']);
        }

        try {
            $result = $reconciler->reconcile($payment);
        } catch (PaymentGatewayException | ValidationException $exception) {
            // Provider status API outage is recoverable through scheduled reconciliation.
            Log::warning('Paystack webhook accepted; status check deferred.', [
                'reference' => $payment->reference,
                'exception' => get_class($exception),
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);
            return response()->json(['status' => 'accepted']);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Webhook could not be processed.'], 503);
        }

        return response()->json(['status' => $result['status']]);
    }
}
