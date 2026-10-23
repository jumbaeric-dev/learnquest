# LearnQuest Child Login Rate-Limit Patch

This patch protects the existing username + four-digit PIN login flow.

## Included project-relative files
- `app/Http/Controllers/Auth/ChildLoginController.php`
- `config/learnquest.php`
- `tests/Feature/ChildLoginTest.php`
- `.env.example`

## Behavior
- Allows five failed attempts by default per normalized username/IP pair within 60 seconds.
- The username/IP cache key is hashed so the cache key does not expose the entered username.
- Unknown usernames, wrong PINs, and inactive-child attempts all use the same generic login error and count toward the limit.
- Further attempts are temporarily blocked after the limit, including a correct PIN.
- Successful authentication clears the failed-attempt counter and preserves session regeneration and active-child selection.
- The defaults can be changed using `LEARNQUEST_CHILD_LOGIN_MAX_ATTEMPTS` and `LEARNQUEST_CHILD_LOGIN_DECAY_SECONDS`.

## Apply
Back up or commit current work first. Extract this ZIP directly into the project root; all code files are stored at their project-relative paths. Review the `config/learnquest.php` merge if you have changed that file locally after the source archive. The `.env.example` addition documents defaults only; add or change values in your actual `.env` only when you want to override them.

Then run:
```bash
php artisan config:clear
php artisan test --filter=ChildLoginTest
php artisan test
```

PHP syntax checks are included as preparation checks; PHPUnit must be run against your local project because this ZIP does not include `vendor/`.
