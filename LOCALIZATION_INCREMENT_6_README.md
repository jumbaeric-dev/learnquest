# LearnQuest Localization Increment 6 — Regression Coverage

This increment adds a focused regression suite for the database-backed curriculum localization introduced in Increment 5.

## Coverage

- French field resolution across worlds, courses, modules, and lessons.
- Explicit-locale resolution independent of the active application locale.
- English fallback for missing, null, blank, or unsupported-locale values.
- Preservation of canonical English database/model attributes.
- Quiz question and option translation without changing answer indexes, scoring settings, or option order.
- Partial option translations falling back to canonical option text; extra translated options are ignored.
- Activity translation allow-list behavior, including preservation of media URLs and activity settings.
- Presence of the JSON translation column on all five curriculum tables.

## Run the localization regression suite

```bash
php artisan test --compact tests/Feature/CurriculumLocalizationRegressionTest.php
php artisan test --compact tests/Feature/CurriculumContentLocalizationTest.php
php artisan test --compact tests/Feature/LocalizationTest.php
php artisan test --compact tests/Feature/MissionDiscoveryServiceTest.php
php artisan view:clear
php artisan view:cache
npm run build
```

Then run the full suite when ready:

```bash
php artisan test --compact
```

`EmailVerificationTest` is intentionally not changed in this increment. Its known failure is deferred as requested.

## Important scope note

This increment validates the localization infrastructure and regression behavior. It does not fabricate translations for the 12 worlds or overwrite canonical curriculum. Reviewed French curriculum copy should be authored and approved in the Filament translation fields, then entered incrementally. Financial, health, legal, and personal-safety material should receive additional subject-matter and jurisdiction review before publication.
