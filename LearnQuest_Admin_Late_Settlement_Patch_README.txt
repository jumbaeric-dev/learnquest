LearnQuest — Admin Late Settlement Alert patch

Based on LearnQuest_11_18.zip.

Files in this patch (copy over the matching paths in your project):
- app/Notifications/LateSettlementAdminNotification.php (new)
- app/Services/Billing/PaymentWebhookProcessor.php (updated)
- tests/Feature/Billing/PaymentAdminTest.php (updated)

Behavior:
- When a verified provider success settles a payment after its checkout expiry, the existing late_settlement metadata flag is retained.
- After the payment transaction commits, an email notification is queued for every user with the `manage payments` permission.
- Users who can only view payments are not notified.
- The alert contains payment reference, parent email, amount/currency, provider, checkout expiry, settlement time, and a link to the Filament payment detail page.
- The parent receipt and admin alert are dispatched independently. Notification/queue dispatch errors are reported and cannot roll back the settled payment.
- Laravel queued-notification retry/backoff is configured on the new notification.

After copying the files, run:
  php artisan optimize:clear
  php artisan test --compact tests/Feature/Billing/PaymentAdminTest.php
  php artisan test --compact tests/Feature/Billing/PaymentLedgerTest.php
  php artisan test --compact

The supplied archive does not include vendor/, so Laravel tests could not be executed in this patch-building environment. PHP syntax checks passed for all three PHP files.

Before production:
- Ensure the queue worker is running and monitored.
- Ensure the mail transport is configured and tested.
- Confirm admins who should receive alerts have the `manage payments` permission.
