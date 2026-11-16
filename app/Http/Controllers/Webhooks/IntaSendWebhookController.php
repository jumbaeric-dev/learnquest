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

/**
 * IntaSend deactivates a webhook after repeated non-2xx replies, so an error
 * status is only returned when the caller is not IntaSend (bad challenge) or
 * our own system is down and a retry is genuinely useful.
 *
 * The webhook is only a trigger: settlement always comes from the status API.
 */
final class IntaSendWebhookController
{
    public function __invoke(
        Request $request,
        PaymentGatewayRegistry $gateways,
        PaymentReconciliationService $reconciler,
    ): JsonResponse {
        try {
            $webhook = $gateways->forProvider('intasend')->parseWebhook($request);
        } catch (WebhookAuthenticationException) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        } catch (PaymentGatewayUnavailableException | UnsupportedPaymentGatewayException $exception) {
            Log::error('IntaSend webhook received but the gateway is not configured.', [
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);

            return response()->json(['message' => 'Webhook receiver unavailable.'], 503);
        } catch (PaymentGatewayException $exception) {
            // Authenticated but unusable (unknown topic or state). Retrying cannot help.
            Log::warning('IntaSend webhook ignored.', [
                'message' => mb_substr($exception->getMessage(), 0, 180),
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $payment = Payment::query()
            ->where('reference', $webhook->paymentReference)
            ->where('provider', 'intasend')
            ->first();

        // IntaSend reports every collection on the account, including ones
        // that did not come from LearnQuest. Acknowledge and move on.
        if (! $payment) {
            return response()->json(['status' => 'ignored']);
        }

        $storedInvoiceId = data_get($payment->metadata, 'intasend_invoice_id');

        if (
            ! is_string($storedInvoiceId)
            || $storedInvoiceId === ''
            || ! hash_equals($storedInvoiceId, (string) $webhook->providerResourceId)
        ) {
            Log::warning('IntaSend webhook invoice does not match the stored payment invoice.', [
                'reference' => $payment->reference,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        try {
            $result = $reconciler->reconcile($payment);
        } catch (PaymentGatewayException | ValidationException $exception) {
            // Status API unreachable or unusable. The scheduled reconciliation settles it.
            Log::warning('IntaSend webhook accepted; status check deferred.', [
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