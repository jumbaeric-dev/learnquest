LearnQuest Increment 1 — Billing foundation hardening

Apply these files to the matching paths in the current LearnQuest project.

Configuration:
  LEARNQUEST_FREE_PLAN_ENABLED=true|false

Policy:
- Existing user rows default to free_plan_grandfathered=true in the migration.
- New parent registrations copy LEARNQUEST_FREE_PLAN_ENABLED into that marker.
- When Free is disabled, non-grandfathered parents with no current subscription have no plan entitlements (zero limits and features denied).
- Existing grandfathered Free families retain their Free limits.
- Paid subscriptions and trial subscriptions remain independent of the Free-plan toggle.

Subscription lifecycle:
- Subscription changes lock the parent row within a DB transaction.
- Same-plan manual renewals extend from the later of now or the current expiry.
- Switching plans ends the prior current subscription before creating the replacement.
- Inactive prices/plans, unsupported currencies, invalid terms, non-parent accounts, and auto-renew requests are rejected.
- Automatic renewal remains disabled.

Entitlements:
- EntitlementService is scoped, not singleton; it no longer retains stale subscription query results.

Validation:
- PHP syntax checks passed for all changed PHP files.
- The archive does not include vendor/, so Laravel/Pest feature tests could not be executed here. Run the commands below locally after applying the files:
    php artisan migrate
    php artisan test --filter=EntitlementServiceTest
    php artisan test --filter=BillingGatingTest
    php artisan test

Important:
- This increment does not implement payment processing or activate access from browser redirects. Verified payment ledger/webhook work remains a later increment.
- PlanSeeder currently contains provisional premium prices (KES 499/month, KES 1,299/3 months, KES 4,499/year; USD 4.99/month, USD 12.99/3 months, USD 44.99/year). No prices were changed.
