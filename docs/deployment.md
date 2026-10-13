# Deployment

## Local development

Prerequisites: PHP 8.3+, Composer, Node.js 20+, and npm.

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
npm ci
php artisan migrate --no-interaction
npm run build
php artisan serve
```

Register an account at `/register`; sign in at `/login`. To use the Vite development server rather than the production asset build, run `npm run dev` in a second terminal. Local defaults are SQLite and file-backed session/cache storage.

## PostgreSQL / Supabase database

Provision a PostgreSQL database and set `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` using the provider's connection details. Use its required TLS settings and verify the Laravel `config/database.php` SSL configuration for the selected provider. Never commit those values. Run migrations with:

```bash
php artisan migrate --force
```

Only SQLite migrations have been executed in the current environment. Test against a staging PostgreSQL database before production. Supabase Storage is not configured because current features do not upload files.

## Google OAuth and Calendar

1. Create a Google Cloud project, configure the OAuth consent screen, and enable Google Calendar API.
2. Create a web OAuth client.
3. Set `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` in the deployment secret store.
4. Set `GOOGLE_REDIRECT_URI` to `https://your-domain/auth/google/callback` and `GOOGLE_CALENDAR_REDIRECT_URI` to `https://your-domain/integrations/google/callback`.
5. Register both exact redirect URIs in the Google OAuth client and set `APP_URL` to the public HTTPS origin.

Google sign-in asks for identity scopes. The Calendar scope is requested only when the user chooses to connect Calendar. The application currently syncs to the primary calendar.

## Production checklist

- Use a supported PHP 8.3+ runtime, PostgreSQL, HTTPS, durable shared session/cache storage, and a persistent application key.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and all secrets in the platform's secret manager.
- Install production Composer dependencies, build assets with `npm ci && npm run build`, migrate with `--force`, and run `php artisan optimize`.
- Configure scheduled backup/restore procedures and validate restoration. Keep database backups and storage snapshots encrypted and access-controlled.
- PDF generation uses Dompdf; run the PDF tests and inspect representative Unicode/multi-page exports in the target runtime.
- Calendar HTTP calls are synchronous and time-limited. Scale request workers and PHP timeouts accordingly; background queue retries are not implemented.
- Laravel Cloud, Forge, or a maintained VPS can host the Laravel runtime. Vercel alone is not a conventional Laravel runtime without additional server-side infrastructure.

Production deployment and a live PostgreSQL/Google integration have not been performed from this workspace.
