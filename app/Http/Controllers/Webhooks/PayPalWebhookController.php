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

/** Authenticated PayPal notifications are triggers; settlement is independently verified through Orders v2. */
final class PayPalWebhookController
{
    public function __invoke(Request $request, PaymentGatewayRegistry $gateways, PaymentReconciliationService $reconciler): JsonResponse
    {
        try {
            $webhook = $gateways->forProvider('paypal')->parseWebhook($request);
        } catch (WebhookAuthenticationException) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        } catch (PaymentGatewayUnavailableException | UnsupportedPaymentGatewayException $exception) {
            Log::error('PayPal webhook receiver is unavailable.', ['message' => mb_substr($exception->getMessage(), 0, 180)]);
            return response()->json(['message' => 'Webhook receiver unavailable.'], 503);
        } catch (PaymentGatewayException $exception) {
            return response()->json(['status' => 'ignored']);
        }

        $payment = Payment::query()->where('reference', $webhook->paymentReference)->where('provider', 'paypal')->first();
        if (! $payment) {
            return response()->json(['status' => 'ignored']);
        }

        $storedOrderId = data_get($payment->metadata, 'paypal_order_id');
        if (! is_string($storedOrderId) || ! hash_equals($storedOrderId, (string) $webhook->providerResourceId)) {
            Log::warning('PayPal webhook order does not match the stored LearnQuest payment.', ['reference' => $payment->reference]);
            return response()->json(['status' => 'ignored']);
        }

        try {
            $result = $reconciler->reconcile($payment);
            return response()->json(['status' => $result['status']]);
        } catch (PaymentGatewayUnavailableException $exception) {
            Log::warning('PayPal webhook processing is temporarily unavailable; requesting provider retry.', [
                'reference' => $payment->reference,
                'exception' => get_class($exception),
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);

            return response()->json(['message' => 'Webhook processing is temporarily unavailable.'], 503);
        } catch (PaymentGatewayException | ValidationException $exception) {
            Log::warning('PayPal webhook accepted; independent status check deferred.', [
                'reference' => $payment->reference, 'exception' => get_class($exception),
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);
            return response()->json(['status' => 'accepted']);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Webhook could not be processed.'], 503);
        }
    }
}
