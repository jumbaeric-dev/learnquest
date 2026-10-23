# LearnQuest mobile navigation fix

This patch adds a mobile hamburger button to the shared child top bar and opens the existing sidebar as an off-canvas drawer on screens narrower than 1024px. The drawer closes by selecting a navigation item, tapping the backdrop, or pressing Escape. Desktop sidebar behavior is preserved.

## Files

- `resources/views/components/child/layout/app-shell.blade.php`
- `resources/views/components/child/layout/topbar.blade.php`
- `resources/views/components/child/layout/sidebar.blade.php`
- `resources/css/lqds/screens/dashboard.css`
- `tests/Feature/ChildMobileNavigationTest.php`

## Apply and verify

Copy these files into the same relative paths in your LearnQuest project, replacing the existing files. Then run:

```bash
npm run build
php artisan test --filter=ChildMobileNavigationTest
```

After the build, hard-refresh the page at mobile width. The hamburger should appear to the left of the LearnQuest logo. Opening it should reveal My Universe, Explorer, Worlds, and Courses.
