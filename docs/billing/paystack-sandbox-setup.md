# LearnQuest Paystack sandbox setup

Paystack is the configured default checkout provider. IntaSend remains available as a separate adapter but is disabled by default while its sandbox issue is on hold.

## Environment

Add these values to the local `.env` file (never commit real keys):

```dotenv
LEARNQUEST_DEFAULT_PAYMENT_PROVIDER=paystack
LEARNQUEST_PAYSTACK_ENABLED=true
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_WEBHOOK_SECRET=
LEARNQUEST_INTASEND_ENABLED=false
```

Use the **test secret key** from the Paystack dashboard. LearnQuest uses hosted checkout, so the secret key is used server-side; a Paystack public key is not required for this redirect flow.

After changing `.env`, refresh Laravel's configuration:

```bash
php artisan optimize:clear
php artisan config:clear
```

## Webhook

Configure the Paystack webhook URL as:

```text
https://YOUR_PUBLIC_HTTPS_HOST/api/webhooks/paystack
```

The endpoint must be publicly reachable over HTTPS and must not redirect. A local Laragon URL is not reachable by Paystack; use a secure development tunnel for sandbox webhook testing.

The adapter supports both:

- `Paystack-Signature` timestamped signatures using the webhook signing secret (`PAYSTACK_WEBHOOK_SECRET`, `whsec_...`). Timestamp tolerance is five minutes.
- Legacy `x-paystack-signature` HMAC SHA512 signatures using `PAYSTACK_SECRET_KEY`.

Prefer configuring the webhook signing secret when Paystack has enabled the timestamped signature format for the account. Do not paste either secret into chat or commit it to source control.

## Settlement rules

- The checkout initialization amount is sent in the currency's minor unit (KES 499.00 becomes `49900`).
- The internal LearnQuest payment reference is sent as the Paystack transaction reference.
- A browser return is not proof of payment.
- A signed `charge.success` webhook only triggers a server-side `GET /transaction/verify/:reference` call.
- LearnQuest grants access only after the verification response matches the payment reference, amount and currency and reports `success`.
- Scheduled reconciliation uses the same verification path if a webhook is delayed or unavailable.
