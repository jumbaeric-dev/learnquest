# LearnQuest PayPal webhook increment 3

This patch is based on `LearnQuest_11_12.zip`.

## Files
- `app/Services/Billing/Gateways/PayPalGateway.php`: PayPal REST signature verification using `PAYPAL_WEBHOOK_ID`; accepts capture completed/pending/denied events only; resolves the LearnQuest reference from the authenticated PayPal order; checks capture ID, amount, currency and order capture state.
- `app/Http/Controllers/Webhooks/PayPalWebhookController.php`: authenticated webhook receiver; checks stored order ID and triggers independent Orders v2 reconciliation.
- `routes/api.php`: registers `POST /api/webhooks/paypal`.
- `tests/Feature/Billing/PayPalGatewayTest.php`: adds signature success/failure coverage.

## Configuration
Keep `LEARNQUEST_DEFAULT_PAYMENT_PROVIDER=paystack`.
Keep `LEARNQUEST_PAYPAL_ENABLED=false` until the focused and full test suites pass and a real Sandbox end-to-end payment has been verified.
Set `PAYPAL_WEBHOOK_ID` to the webhook ID shown in the PayPal Developer Dashboard for the REST app. It is not the Client ID.

Webhook URL: `https://YOUR_PUBLIC_DOMAIN/api/webhooks/paypal`

Subscribe to at least:
- `PAYMENT.CAPTURE.COMPLETED`
- `PAYMENT.CAPTURE.PENDING`
- `PAYMENT.CAPTURE.DENIED`

The webhook is only a trigger. The controller calls the existing reconciliation service, which retrieves the PayPal order and independently verifies capture details before the payment ledger activates a subscription.

## Apply and test
Extract the ZIP into the project root, preserving paths, then run:

```bash
php artisan optimize:clear
php artisan test --compact tests/Feature/Billing/PayPalGatewayTest.php
php artisan test --compact tests/Feature/Billing/PaymentReconciliationTest.php
```

If focused tests pass, run the full suite:

```bash
php artisan test --compact
```

This archive does not include `vendor`, so PHPUnit was not executable in the patch-building environment. PHP syntax was checked for every changed PHP file.
