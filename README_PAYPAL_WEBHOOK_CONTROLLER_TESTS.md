# LearnQuest — PayPal webhook controller test increment

This test-only increment adds `tests/Feature/Billing/PayPalWebhookControllerTest.php` against the PayPal webhook endpoint already present in the November 12 source archive and Increment 3 patch.

Coverage:
- Invalid PayPal transmission signature returns 401 and does not change payment state.
- Authenticated event resolving to an unknown LearnQuest reference is acknowledged as ignored.
- A valid completed capture causes independent Orders v2 reconciliation and subscription activation.
- Repeated delivery does not activate or extend the subscription twice.
- A PayPal order ID different from the order stored on the payment is ignored.
- A PayPal webhook cannot settle a payment belonging to another provider.

Run from the project root:

```bash
php artisan test --compact tests/Feature/Billing/PayPalWebhookControllerTest.php
```

Then run the combined billing regression tests:

```bash
php artisan test --compact tests/Feature/Billing
```

No production application code or environment settings are changed by this increment. Keep PayPal disabled and Paystack as the default until Sandbox end-to-end verification is completed.
