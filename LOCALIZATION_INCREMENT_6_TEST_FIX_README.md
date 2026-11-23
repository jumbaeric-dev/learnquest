# Localization Increment 6 — Test correction

This is a focused correction to `tests/Feature/CurriculumLocalizationRegressionTest.php`.

The first test supplied a French translation but called `localized($field)` without specifying a locale or setting the application locale to French. The application default is English, so the model correctly returned the canonical English value. The assertion now explicitly requests French with `localized($field, 'fr')`.

No application behavior, translations, database schema, or curriculum data are changed by this fix.

Apply by extracting this ZIP into the project root and overwriting the test file. Then run:

```bash
php artisan test --compact tests/Feature/CurriculumLocalizationRegressionTest.php
php artisan test --compact tests/Feature/CurriculumContentLocalizationTest.php
php artisan test --compact tests/Feature/LocalizationTest.php
php artisan test --compact tests/Feature/MissionDiscoveryServiceTest.php
```
