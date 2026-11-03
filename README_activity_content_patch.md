# LearnQuest — Published Activity Content Validation Patch

## Why

The Filament activity form advertises several activity types that do not yet have a complete learner-facing implementation. In addition, published stories/projects/challenges/AI-guided activities can be saved with missing content and show generic placeholder text to children.

## Files

- `app/Services/Learning/PublishedActivityContentValidator.php` — type-specific publication validator.
- `app/Filament/Resources/LessonActivities/Pages/CreateLessonActivity.php` — validates content before creating a published activity.
- `app/Filament/Resources/LessonActivities/Pages/EditLessonActivity.php` — validates content before saving a published activity.
- `app/Filament/Resources/LessonActivities/Schemas/LessonActivityForm.php` — field-level validation, minimum reward/order bounds, and clear draft-only labels for activity types whose learner UI is not implemented.
- `tests/Unit/PublishedActivityContentValidatorTest.php` — eight regression tests.

## Behavior

- Incomplete drafts remain saveable.
- The existing `PublishedQuizValidator` remains responsible for quiz question/options/answer integrity.
- Published story, project, coding challenge, and AI-guided activities require their relevant content.
- Video, audio, drag-and-drop, drawing, and flashcard activities remain available as drafts but cannot be published until their learner-facing screens exist.
- Negative XP rewards and non-positive positions are blocked by the admin form.

## Apply

Back up or commit the current working tree first. Extract these project-relative paths into the LearnQuest root, merging with your current files. Do not replace `.env` or any AI provider settings.

Run:

```bash
php -l app/Services/Learning/PublishedActivityContentValidator.php
php -l app/Filament/Resources/LessonActivities/Schemas/LessonActivityForm.php
php -l app/Filament/Resources/LessonActivities/Pages/CreateLessonActivity.php
php -l app/Filament/Resources/LessonActivities/Pages/EditLessonActivity.php
php artisan test --filter=PublishedQuizValidatorTest
php artisan test --filter=PublishedActivityContentValidatorTest
php artisan test
```

Expected additional coverage: eight new unit tests. Confirm with your local full test run before accepting the patch.
