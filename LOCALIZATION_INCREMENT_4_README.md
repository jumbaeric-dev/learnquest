# LearnQuest Localization Increment 4

This patch is based on the uploaded `LearnQuest(2).zip` archive.

## Included

- English and French translations for the remaining child-facing interface labels and messages.
- Explorer profile and profile-edit screens.
- Child settings screen.
- Missions, mission categories, featured mission, daily challenges, and mission reward/status labels.
- Nova chat interface labels, prompts, and status messages.
- Child navigation, profile menu, logout labels, and parent-preview banner.
- Remaining dashboard, Explore, world, and adventure-path interface labels.
- Localized dashboard welcome copy, Nova widget copy, world difficulty labels, daily challenge definitions, and relevant success/preview messages.
- Expanded `LocalizationTest` coverage to scan child-facing Blade templates and relevant Livewire/service code for missing English/French keys, including dynamically composed daily-challenge keys and world difficulty values.

## Apply

Extract this ZIP into the LearnQuest project root and allow it to overwrite the listed files. The archive contains only changed files plus this README; it does not replace the whole project.

Then run:

```bash
php artisan optimize:clear
php artisan test --compact tests/Feature/LocalizationTest.php
php artisan view:clear
php artisan view:cache
npm run build
```

If the targeted localization test passes, run the full test suite:

```bash
php artisan test --compact
```

## Scope note

This increment localizes interface copy. Curriculum content stored in the database (world, course, lesson, activity, quiz, and authored mission titles/descriptions/instructions) remains as authored. That content needs a separate content-localization design so translations are stored and reviewed without overwriting the canonical curriculum or mixing UI strings with learning data.
