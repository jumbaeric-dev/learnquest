# LearnQuest Increment 1 — test failure corrections

Apply these files over the Increment 1 patch and the current LearnQuest project.

## Corrections

1. `StartTrials` was present in `app/Console/StartTrials.php`, but its namespace is `App\\Console\\Commands`. Laravel command auto-discovery expects the class at `app/Console/Commands/StartTrials.php`. Add the provided file there. Remove the misplaced `app/Console/StartTrials.php` after confirming the new command works.
2. Add `resources/views/errors/content-locked.blade.php`; the exception renderer already references this view.
3. The trial-to-paid test passed `autoRenews: true`, contrary to the agreed manual-renewal policy. It now purchases a fixed-term plan without auto-renewal.
4. The orphan-child fixture attempted `parent_id = null`, but the current schema makes `children.parent_id` NOT NULL. The test now checks Premium lesson access for a valid parent-owned child.
5. The detailed Parent Dashboard report test is testing report content, not the Premium gate. It disables billing enforcement for that test so it can assert the skill report content without changing production gating.
6. The billing page non-parent test now flushes the previous authenticated session before switching actors, preventing `auth.session` from redirecting due to a stale password hash in the test session before the parent-role middleware runs.

## Run

```bash
php artisan optimize:clear
php artisan test --filter=BillingGatingTest
php artisan test --filter=EntitlementServiceTest
php artisan test --filter=ParentDashboardTest
php artisan test
```

If the non-parent route test still returns 302 after the fresh-session change, capture the `Location` header. Do not weaken the route to force a 403; the production route must remain parent-only.
