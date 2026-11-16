# LearnQuest Paystack integration increment

This is an overlay patch for the `LearnQuest_11-05.zip` source archive. Copy the included paths into the project root, preserving directory structure. It does not include `.env`, `vendor/`, database files, logs, or generated caches.

## What changed

- Paystack is the configured default gateway (`LEARNQUEST_DEFAULT_PAYMENT_PROVIDER=paystack`).
- Paystack is fail-closed until explicitly enabled and configured.
- Parent checkout now resolves the configured provider rather than hardcoding IntaSend.
- Paystack hosted checkout initialization sends the immutable LearnQuest reference and amount in minor units.
- Paystack verification checks transaction reference, amount, currency and status.
- Signed Paystack webhook handling supports the timestamped `Paystack-Signature` format and legacy `x-paystack-signature` format.
- Webhooks trigger server-side transaction verification; they never directly grant access.
- Scheduled reconciliation includes enabled providers.
- IntaSend remains in the codebase but is no longer the default provider.
- Added Paystack gateway and checkout tests.

## Configure local sandbox

Set in `.env` (do not commit or share the key):

```dotenv
LEARNQUEST_DEFAULT_PAYMENT_PROVIDER=paystack
LEARNQUEST_PAYSTACK_ENABLED=true
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_WEBHOOK_SECRET=whsec_...
LEARNQUEST_INTASEND_ENABLED=false
```

`PAYSTACK_WEBHOOK_SECRET` is needed for the timestamped `Paystack-Signature` header. The adapter also supports legacy `x-paystack-signature` verification using the secret API key.

Then run:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:list --path=api/webhooks
php artisan test --filter=PaystackGatewayTest
php artisan test --filter=BillingCheckoutTest
php artisan test --filter=PaymentReconciliationTest
```

The source ZIP does not contain `vendor/`, so tests could not be executed in this build environment. PHP syntax checks passed for all modified PHP files. Run the tests in the local Laragon project before attempting a real sandbox checkout.

## Webhook URL

Configure Paystack's webhook as `https://YOUR_PUBLIC_HTTPS_HOST/api/webhooks/paystack`. Paystack must be able to reach this URL directly; local Laragon is not publicly reachable, so use a secure development tunnel for webhook testing.

## Safety

Do not manually mark a payment as paid. The integration only activates a subscription after the Paystack Verify Transaction API confirms `success` and the exact expected amount, currency and internal reference.
