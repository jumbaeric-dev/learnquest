# LearnQuest Increment 2 — Payment Ledger and Webhook Infrastructure

This patch is based on `LearnQuest_11_03_v1.zip` (the updated project supplied for Increment 2).

## Scope

Adds the persistence and domain-processing foundation for IntaSend and PayPal without exposing a checkout or webhook HTTP endpoint yet.

- `payments`: internal reference, parent owner, provider, plan/price snapshot, amount/currency, lifecycle timestamps and status.
- `webhook_events`: provider event deduplication, signature-verification timestamp, processing state, encrypted allow-listed metadata and audit outcome.
- `PaymentService`: creates pending attempts and enforces parent ownership, provider allow-list, active plan/price, supported currency and request idempotency.
- `PaymentWebhookProcessor`: accepts only a normalized `VerifiedPaymentWebhook`, validates the payment reference/provider/amount/currency, rejects expired or reused provider transactions, and settles a subscription in the same database transaction as the payment ledger update.
- Duplicate provider event IDs and distinct success events for an already-settled payment do not create or extend subscriptions again.

## Important security boundary

There is deliberately **no public webhook route** in this increment. The `VerifiedPaymentWebhook` DTO is an internal boundary: a future provider adapter must verify the provider's signature/token and normalize the event before constructing it. Never construct it directly from untrusted HTTP request fields. The processor stores only a small allow-list of metadata keys and encrypts that metadata at rest. Do not store card numbers, CVV, access tokens, authorization headers, raw provider payloads, or secrets.

This increment does not implement provider-specific signature verification, checkout initiation, refunds, chargebacks, or reconciliation jobs. Those belong to the gateway integration and production-hardening increments.

## Files added

- `database/migrations/2026_11_03_110000_create_payments_table.php`
- `database/migrations/2026_11_03_110100_create_webhook_events_table.php`
- `app/Models/Payment.php`
- `app/Models/WebhookEvent.php`
- `app/Services/Billing/DTO/VerifiedPaymentWebhook.php`
- `app/Services/Billing/PaymentService.php`
- `app/Services/Billing/PaymentWebhookProcessor.php`
- `tests/Feature/Billing/PaymentLedgerTest.php`

## Files modified

- `app/Models/User.php` — payments relationship.
- `app/Models/Subscription.php` — payments relationship.

## Apply

Extract the patch into the Laravel project root, preserving directories, then run:

```bash
php artisan optimize:clear
php artisan migrate
php artisan test --filter=PaymentLedgerTest
php artisan test --filter=EntitlementServiceTest
php artisan test --filter=BillingGatingTest
php artisan test
```

If you use a production database for local testing, take a backup first. The migrations add new tables and do not modify or delete existing payment/subscription data.

## Notes

- Amounts are stored as integer minor units (KES 499.00 = 49900; USD 4.99 = 499).
- The ledger snapshots `plan_key`, `duration_months`, `amount_minor`, and `currency`; later price changes do not rewrite an existing payment attempt.
- A payment's internal `reference` is distinct from the provider transaction ID.
- The unique indexes on `(provider, event_id)` and `(provider, provider_transaction_id)` are the final database-level duplicate guards.
- SQLite does not provide production-equivalent row-level locking. Concurrency behaviour must also be exercised against the production database engine before launch.
