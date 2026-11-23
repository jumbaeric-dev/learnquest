# LearnQuest Localization — Increment 1

This patch establishes the localization foundation and the first English/French interface strings.

## Included
- Nullable `users.locale` preference.
- Supported locales: English (`en`) and French (`fr`).
- Locale middleware with this precedence: saved account preference, session preference, trusted country header (if explicitly enabled), browser `Accept-Language`, then English.
- Guest language selection is stored in the session; signed-in selection is also saved to the user account.
- Language dropdown in the parent portal and child topbar.
- Initial translations for shared navigation and common labels.
- Feature tests for browser detection, opt-in country detection, persistence, and invalid locales.

## Important country-detection security note
Country-header detection is OFF by default. Enable it only after the production site is behind a trusted proxy/CDN (for example Cloudflare) that overwrites `CF-IPCountry`. Do not enable it on a directly exposed origin because clients could spoof the header. Browser `Accept-Language` detection works without this setting.

Add to `.env` only after the trusted proxy is configured:

```env
LOCALIZATION_COUNTRY_DETECTION_ENABLED=true
LOCALIZATION_COUNTRY_HEADER=CF-IPCountry
```

## Apply
1. Copy the patch's `app`, `bootstrap`, `config`, `database`, `lang`, `resources`, `routes`, and `tests` directories into the project, preserving paths.
2. Run `php artisan migrate`.
3. Run `php artisan optimize:clear`.
4. Run:

```bash
php artisan test --compact tests/Feature/LocalizationTest.php
```

5. Build assets if required by your local setup: `npm run build`.

## Scope note
This is the foundation and first shared-interface translation increment, not a claim that every lesson, activity, email, admin screen, or user-generated content is translated. Those need dedicated translation passes. English remains the fallback for missing strings. Existing content and curriculum records are not translated by this patch.
