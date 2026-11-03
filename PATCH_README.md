# LearnQuest Active Navigation Patch

## Problem
After mobile navigation was added, the shared app shell defaulted to `active="dashboard"`. Pages without an explicit active prop therefore highlighted My Universe instead of the current section. The issue affected desktop and mobile because both use the same sidebar component.

## Files
- `resources/views/components/child/layout/app-shell.blade.php`
- `resources/views/components/child/layout/sidebar.blade.php`
- `resources/views/livewire/child/explore/components/learning-worlds.blade.php`
- `tests/Feature/ChildActiveNavigationTest.php`

## Behavior
- `child` / `child.dashboard`: highlights My Universe.
- `child.explore`: highlights Explorer.
- `child.explore?section=worlds` and `child.world`: highlights Worlds.
- `child.missions`, `child.mission`, `learn.course`, `learn.lesson`, `learn.activity`: highlights Courses.
- Profile and settings don't highlight an unrelated sidebar item.
- Explicit `active="..."` props supplied by a page take precedence.
- Worlds sidebar link scrolls to the Learning Worlds section and highlights Worlds; this works on desktop and mobile because they share the same sidebar.

## Apply
Back up or commit current changes. Merge these files into the Laravel project, preserving any unrelated local edits. Then run:

```bash
php artisan view:clear
php artisan test --filter=ChildActiveNavigationTest
php artisan test
```
