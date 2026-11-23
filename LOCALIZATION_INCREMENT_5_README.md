# LearnQuest Localization Increment 5 — Curriculum Content

This increment adds database-backed translations for learner-facing curriculum content. It builds on the existing English/French interface localization and uses the latest supplied LearnQuest source archive plus Localization Increment 4.

## Scope

Translations are supported for:

- Learning worlds (`subjects`): name, description, tagline, story introduction, and hero character
- Courses: title and description
- Course modules: title and description
- Lessons: title and description
- Activities: title, instructions, story text, quiz question and answer-option labels, project instructions, coding challenge, learning goal, and AI system prompt

Child-facing world, course, lesson, activity, Explore, Missions, dashboard, and Nova context now request localized curriculum values. The admin Filament forms include optional French translation fields for each content type.

## Data model

The existing columns remain canonical English content. A nullable `translations` JSON column is added to each curriculum table:

- `subjects`
- `courses`
- `learning_modules`
- `lessons`
- `activities`

Example for a course:

```json
{
  "fr": {
    "title": "Mon premier budget",
    "description": "Apprends à planifier tes dépenses."
  }
}
```

Example for a quiz activity:

```json
{
  "fr": {
    "title": "Choisis la bonne décision",
    "content": {
      "question": "Quel choix est sûr ?",
      "options": [
        { "option": "Parler à un adulte de confiance" },
        { "option": "Partir avec un inconnu" }
      ]
    }
  }
}
```

## Fallback and quiz safety

- The original English field is retained and remains the source of truth.
- A missing or blank French field falls back to its English counterpart.
- Existing curriculum records are not automatically rewritten or machine-translated. They continue to display in English until an editor adds reviewed French copy.
- Quiz translations may change the question and visible option text only. The correct answer index is always taken from canonical activity content; translated data cannot change scoring.
- Quiz option translations must be entered in the same order as the canonical English options. Do not reorder options in the translation.
- Nova receives the selected locale as its response-language instruction and uses localized course/lesson context.

## Admin workflow

Open the relevant Filament resource and edit the world, course, module, lesson, or activity. Keep the original content in the existing fields. Add the reviewed French version in the **French Translation** section. Leave any untranslated field blank to retain English fallback for that field.

## Apply and verify

Run the migration and targeted tests:

```bash
php artisan migrate
php artisan optimize:clear
php artisan test --compact tests/Feature/CurriculumContentLocalizationTest.php
php artisan test --compact tests/Feature/LocalizationTest.php
php artisan test --compact tests/Feature/MissionDiscoveryServiceTest.php
php artisan view:clear
php artisan view:cache
npm run build
```

Then run the full suite:

```bash
php artisan test --compact
```

## Future locales

The storage structure supports additional locale keys (for example `es` or `sw`) without adding database columns. The current admin fields and supported application locale list remain English/French; adding another language should include the locale preference/configuration, admin translation fields, and reviewed curriculum translations.
