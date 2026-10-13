# Testing

Run the full PHPUnit suite:

```bash
php artisan test --compact
```

Run focused PMS tests:

```bash
php artisan test --compact tests/Feature/PersonalModuleTest.php tests/Feature/ModuleExportTest.php tests/Feature/GoogleCalendarIntegrationTest.php
```

Build production frontend assets:

```bash
npm run build
```

## Coverage

- Breeze authentication, root redirects, profile timezone, and existing diary/todo flows.
- User-specific module creation, isolation, validation, relationship ownership, time-zone conversion, monthly schedules, and book progress.
- PDF download response, account-scoped data passed to the renderer, date filtering, Unicode/long text, empty data, and multi-page output.
- Google OAuth via Socialite fake; Calendar HTTP success, update/idempotency, token encryption and refresh, cross-user denial, rate-limit handling, logs, and disconnect/revocation behavior via HTTP fakes.
- PWA manifest and service-worker endpoint responses.

## Verification record

- `php artisan test --compact` — passed: 55 tests, 192 assertions.
- `php artisan test --compact tests/Feature/PersonalModuleTest.php tests/Feature/ModuleExportTest.php tests/Feature/GoogleCalendarIntegrationTest.php` — passed: 25 tests, 120 assertions.
- `npm run build` — passed with Vite production assets.
- `php artisan migrate --no-interaction` — all pending migrations applied to the local SQLite database.
- HTTP smoke check on the local Laravel server — `/` returned 302 to sign-in, `/register` returned 200, `/manifest.webmanifest` returned 200 with `application/manifest+json`, and `/service-worker.js` returned 200 with `application/javascript`.
- Changed PHP files were formatted with Pint; editor diagnostics reported no errors in the changed PHP/Blade files.

The application was tested locally over HTTP, but no full browser device matrix or PWA installation/offline simulation was run. Test doubles verify the application's request/response behavior; they do not prove a live Google account or API is configured. The test suite uses SQLite and does not replace a staging PostgreSQL migration.
