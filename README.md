# Daywell Personal Management

Daywell is a Laravel personal management application for diary and journal entries, gratitude, mood tracking, tasks, habits, schedules, books, and book notes. It uses authenticated, user-owned records, responsive Blade pages, downloadable PDFs, optional Google sign-in and one-way Google Calendar sync, and a privacy-first installable PWA shell.

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- SQLite for local development (included with the PHP installation used by Laravel)

## Run locally

```bash
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
php artisan migrate --no-interaction
npm run build
php artisan serve
```

Open the URL printed by `php artisan serve` and register an account at `/register`, or sign in at `/login`. The example environment uses SQLite, file-backed sessions/cache, and the synchronous queue driver.

For live asset development, use a second terminal and run `npm run dev`.

## Optional Google setup

Create OAuth credentials in Google Cloud Console. Add the client ID and secret to `.env`, and register both callback URLs as authorized redirect URIs:

- `${APP_URL}/auth/google/callback` for Google sign-in.
- `${APP_URL}/integrations/google/callback` for Calendar connection.

Set `GOOGLE_REDIRECT_URI` and `GOOGLE_CALENDAR_REDIRECT_URI` to those exact URLs. Calendar permission is requested only when connecting Calendar. Without Google credentials, password registration and sign-in remain available.

## Verify

```bash
php artisan test --compact
npm run build
```

See [docs/architecture.md](./docs/architecture.md), [docs/database-design.md](./docs/database-design.md), [docs/security.md](./docs/security.md), [docs/testing.md](./docs/testing.md), and [docs/deployment.md](./docs/deployment.md) for the system design, deployment configuration, and verified limitations.
