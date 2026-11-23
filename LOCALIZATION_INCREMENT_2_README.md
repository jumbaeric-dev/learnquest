# LearnQuest Localization — Increment 2

This increment translates the parent overview, parent account settings, parent sign-in, and child sign-in screens into English and French. It builds on Localization Increment 1 (locale persistence, browser/country selection, and language switcher).

## Apply
Copy the `resources` and `lang` directories into the project, merging folders and replacing the listed files. Do not delete existing translation files.

## Verify
```bash
php artisan optimize:clear
php artisan test --compact tests/Feature/LocalizationTest.php
npm run build
```

Then sign in, switch to French, and verify the parent overview/account screens and both login screens. Switch back to English and confirm the preference persists after refresh.

This increment translates interface copy only. User-authored course titles, lesson content, names, and database content are intentionally not machine-translated. Validation messages and transactional emails are scheduled for a later increment.
