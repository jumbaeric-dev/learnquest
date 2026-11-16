# LearnQuest Increment 3 — Payment Gateway Abstraction

Built against `LearnQuest_11_04.zip` (the user-provided current source archive).

## Included

- `App\Contracts\Billing\PaymentGateway`: provider-independent checkout, verification and authenticated-webhook parsing contract.
- Typed `CheckoutResult` and `PaymentVerificationResult` DTOs.
- `PaymentGatewayRegistry`: resolves configured drivers and checks enabled status and supported currency.
- IntaSend and PayPal adapter placeholders. They deliberately fail closed until their dedicated sandbox increments implement provider API calls and authentication.
- Gateway-specific configuration and disabled-by-default `.env.example` entries.
- Registry and DTO tests, including disabled provider, unsupported currency, invalid driver, HTTPS checkout URL and fail-closed adapter behaviour.

## Configuration

The following flags default to false and should remain false until the provider integrations are completed:

```env
LEARNQUEST_INTASEND_ENABLED=false
LEARNQUEST_PAYPAL_ENABLED=false
```

IntaSend is configured for KES; PayPal is configured for USD in this initial routing configuration. Reconfirm merchant-account capabilities before production.

After applying:

```bash
php artisan optimize:clear
php artisan test --filter=PaymentGatewayRegistryTest
php artisan test --filter=PaymentLedgerTest
php artisan test
```

## Safety boundaries

- No checkout controller or route is added.
- No gateway API request is made.
- The adapters do not trust browser redirects or construct verified webhook DTOs yet.
- Provider adapters must authenticate callbacks/responses before returning normalized data.
- Existing `PaymentWebhookProcessor` remains the only path that settles a ledger payment and activates subscription access.
- Do not enable either provider flag until the corresponding sandbox adapter, callback verification, and end-to-end tests are complete.
