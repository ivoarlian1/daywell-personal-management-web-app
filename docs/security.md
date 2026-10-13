# Security

## Controls in place

- Private application pages, module CRUD, profile, PDF exports, and Google Calendar actions require an authenticated and verified session. Blade forms use CSRF protection and escaped output.
- Personal reads/writes/exports are scoped to the current user; cross-user record IDs return 404 on module and calendar-sync routes. Relationship IDs are checked against the current account.
- Password authentication uses Laravel's hashed password cast and Breeze rate limiting. Google OAuth redirect/callback and Calendar sync routes have explicit rate limits.
- Google sign-in only accepts a provider response with a verified email. Calendar access is requested only during Calendar connection.
- OAuth access/refresh tokens are encrypted by Eloquent encrypted casts, hidden during serialization, and omitted from application logs. Calendar API errors are represented by status/error codes, not response bodies or tokens.
- Disconnect attempts Google's token revocation, removes local integration tokens even if Google is unavailable, and clearly warns the user to revoke access manually if remote revocation cannot be confirmed. Existing Google events are not deleted.
- The service worker never caches authenticated HTML or personal/API responses; it serves an offline notice instead. Only public shell assets are cacheable.
- `.env` is ignored by Git and `.env.example` contains no real secrets. Production requires a unique `APP_KEY`, TLS, `APP_DEBUG=false`, and externally managed secrets.

## Operational setup

- Configure `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and exact HTTPS redirect URIs outside source control. Calendar integration targets the primary calendar; no calendar-list permission or selector is implemented.
- Review web-server request/body limits and Laravel throttles before public deployment. No file uploads are currently enabled.
- Back up the database and protect the backup according to the provider's access controls. Token ciphertext depends on the application key; losing `APP_KEY` makes existing encrypted tokens unreadable.

## Known limitations

- Live Google authorization/revocation and real Calendar API behavior require user-supplied Google credentials and have not been externally exercised; automated tests use Socialite and HTTP fakes.
- Calendar sync is one-way, explicit, and synchronous. It does not import external edits, delete remote events, or queue retries.
- PostgreSQL/Supabase production security and migration behavior have not been verified against a provisioned production instance. Supabase Storage/RLS is not used.
- The PWA is not an offline data editor. Private pages require network connectivity and are intentionally not available from cache.
