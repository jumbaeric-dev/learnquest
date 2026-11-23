# LearnQuest Localization Increment 3 — Child Learning UI

Apply this patch to the current project. It adds English/French interface translations to:
- Child course, lesson, and activity screens
- Child dashboard learning/world/adventure cards
- World hero and progress components

It does not translate course titles, lesson titles, activity prompts, descriptions, or other curriculum content stored in the database. Those require a separate content-localization design so the platform does not silently translate or overwrite authored learning material.

## Apply
Merge the `lang`, `resources`, and `tests` folders into the project.

Run:
```bash
php artisan optimize:clear
php artisan test --compact tests/Feature/LocalizationTest.php
php artisan view:clear
php artisan view:cache
npm run build
```

Then test a child account in English and French across My Universe, Worlds, a Course, a Lesson, and an Activity.

All new `localization.*` keys are added to both `lang/en/localization.php` and `lang/fr/localization.php`. Dynamic labels use named placeholders.
