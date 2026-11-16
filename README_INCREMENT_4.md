# LearnQuest Increment 4 — IntaSend sandbox adapter

This patch targets `LearnQuest_11_04_V1.zip` (the source archive supplied for Increment 4).

## Scope

- Implements the existing `PaymentGateway` contract for IntaSend.
- Supports hosted checkout (the checkout page exposes M-Pesa and other payment methods enabled on the merchant account).
- Adds a direct M-Pesa STK Push initiation method for trusted parent-facing callers.
- Persists the IntaSend invoice ID in the payment metadata for later status checks.
- Adds the public server-to-server callback route `POST /api/webhooks/intasend`.
- Authenticates callbacks with the configured IntaSend webhook challenge using constant-time comparison.
- Independently calls IntaSend's invoice status endpoint before allowing any callback to reach the payment ledger.
- Checks the internal reference, invoice ID, amount, currency and current outcome before settlement.
- Keeps the PayPal adapter unchanged and keeps all checkout disabled by default.
- Does not add a checkout UI or expose payment initiation routes. The existing parent billing page remains unchanged.

## Apply

Extract the patch ZIP into the LearnQuest project root, preserving paths. It contains only new or modified files.

Then run:

```bash
php artisan optimize:clear
php artisan route:list --path=api/webhooks
php artisan test --filter=IntaSendGatewayTest
php artisan test --filter=PaymentGatewayRegistryTest
php artisan test --filter=PaymentLedgerTest
php artisan test
```

## Environment

Keep the integration disabled until sandbox credentials and the webhook are configured:

```dotenv
LEARNQUEST_INTASEND_ENABLED=false
INTASEND_PUBLIC_KEY=
INTASEND_SECRET_KEY=
INTASEND_WEBHOOK_CHALLENGE=
INTASEND_BASE_URL=https://api.intasend.com
```

When ready for sandbox testing, set `LEARNQUEST_INTASEND_ENABLED=true` and use IntaSend **test** keys (`ISPubKey_test_...` and `ISSecretKey_test_...`). IntaSend documents one API base URL for both environments; the key prefix selects sandbox versus live. Do not use live keys during this increment.

In the IntaSend sandbox dashboard, create a webhook for `collection_event`, set its Challenge to the exact same value as `INTASEND_WEBHOOK_CHALLENGE`, and point it to:

`https://YOUR_PUBLIC_TEST_HOST/api/webhooks/intasend`

A local Laragon URL is not reachable by IntaSend. Use a controlled HTTPS development tunnel for callback testing, and do not expose the app broadly.

`INTASEND_WEBHOOK_SECRET` remains as a legacy configuration fallback, but new deployments should use `INTASEND_WEBHOOK_CHALLENGE`.

## How to use the adapter

- Hosted checkout: resolve the IntaSend gateway from `PaymentGatewayRegistry` and call `initiateCheckout($payment)`. The returned `CheckoutResult` has `interaction=redirect` and an HTTPS checkout URL.
- Direct M-Pesa: call `initiateMpesaStkPush($payment, $validatedKenyanPhone)` from a trusted parent-only application flow. It returns `interaction=push` and no checkout URL. Phone validation/normalization is performed in the adapter. STK Push is restricted to whole-KES amounts to match the provider's rounding behaviour.
- Do not call either method from child routes or accept provider, amount, currency, plan, or payment reference from a child-controlled request.
- The callback controller verifies the challenge and queries `/payment/status/` using the secret key before it passes the normalized event to `PaymentWebhookProcessor`. A browser redirect is never treated as proof of payment.

## Important provider details

IntaSend's webhook authentication mechanism is a configured Challenge value in the JSON body, not an HMAC signature header. The adapter uses `hash_equals()` and then independently verifies the invoice through IntaSend's authenticated status API. This follows IntaSend's current webhook documentation. If IntaSend changes its webhook authentication contract, update this adapter before production use.

The hosted Checkout API response may initially expose a checkout/session identifier; the actual invoice identifier is captured from the authenticated `collection_event` and then verified with the status endpoint. The STK Push response returns the invoice identifier directly.

No automatic retries are applied to payment-initiation POST requests. This avoids accidentally creating duplicate payment prompts when a network timeout occurs after the provider has accepted a request.

## Not included

- Parent-facing checkout UI or route to initiate a payment.
- Live keys, live checkout, refunds, reconciliation dashboard, or automatic renewals.
- PayPal integration.
- Production readiness certification. Sandbox callback testing with your actual IntaSend test account is still required.

## Official references

- Getting started and API key modes: https://developers.intasend.com/getting-started/
- M-Pesa STK Push: https://developers.intasend.com/guides/collections/mpesa-stk-push/
- Hosted checkout: https://developers.intasend.com/guides/collections/checkout/
- Webhooks and Challenge verification: https://developers.intasend.com/guides/webhooks/
