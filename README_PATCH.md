# LearnQuest — Published Quiz Authoring Validation Patch

Prepared against the `LearnQuest_22.zip` source archive. This change validates quiz content when an admin creates or edits an activity and attempts to publish it. Incomplete drafts and non-quiz activity types remain unaffected.

## Files

- `app/Services/Learning/PublishedQuizValidator.php` — shared publication validator.
- `app/Filament/Resources/LessonActivities/Schemas/LessonActivityForm.php` — conditional quiz requirements and a human-readable correct-answer selector.
- `app/Filament/Resources/LessonActivities/Pages/CreateLessonActivity.php` — validation before record creation.
- `app/Filament/Resources/LessonActivities/Pages/EditLessonActivity.php` — validation before saving/publishing edits.
- `tests/Unit/PublishedQuizValidatorTest.php` — regression tests covering valid quizzes, drafts, missing questions, invalid answer index, duplicate options, and non-quiz activities.

## Apply

Back up/commit the current project, then merge the ZIP's paths into the LearnQuest project root. No migration is required. Do not overwrite unrelated files.

Run:

```bash
php -l app/Services/Learning/PublishedQuizValidator.php
php -l app/Filament/Resources/LessonActivities/Schemas/LessonActivityForm.php
php -l app/Filament/Resources/LessonActivities/Pages/CreateLessonActivity.php
php -l app/Filament/Resources/LessonActivities/Pages/EditLessonActivity.php
php artisan test --filter=PublishedQuizValidatorTest
php artisan test
```

Syntax checks can be run against this patch, but PHPUnit must be run on the project's local installation. Check any Filament form/API validation errors from that run before accepting.
