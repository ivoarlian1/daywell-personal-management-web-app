# Architecture

Daywell is a Laravel 13 application (PHP 8.3) built with Blade, Eloquent, Laravel session authentication, Vite, Tailwind CSS 4, and Alpine.js. Requests are served through Laravel web routes and CSRF-protected forms.

## Application layers

- `routes/web.php` exposes guest sign-in, authenticated/verified pages, module CRUD, exports, Google integration, and PWA endpoints.
- `DiaryEntryController` and `TodoController` implement their dedicated CRUD flows and policies.
- `PersonalModuleController` provides allow-listed CRUD for journal, gratitude, mood, habits, schedules, books, and book notes. `config/pms.php` declares field definitions; `PersonalModuleRequest` applies validation and same-owner foreign-key checks.
- `DashboardController` builds current-user summaries.
- `ModuleExportController` scopes data to the current account and renders PDF documents with Dompdf.
- `GoogleOAuthController` handles Google sign-in and the separately authorized Calendar connection. `GoogleCalendarController` refreshes encrypted tokens and performs explicit one-way event create/update requests.
- `resources/views/layouts` contains the responsive authenticated and guest shells. Module forms and index pages share the definitions while preserving module-specific behavior.

## Runtime and integrations

- Local development defaults to SQLite, file sessions/cache, and synchronous queues. Production can use PostgreSQL; the migrations have been exercised against SQLite only in this environment.
- Google Calendar sync is manual and synchronous, targets the user's primary calendar, and sends schedule changes from Daywell to Google. External changes are not imported. Disconnect revokes the token when Google is reachable, removes local token data, and never deletes external events.
- The PWA caches only its public offline shell, manifest, icon, and built assets. Authenticated pages and user records are always fetched from the server. Offline editing and background synchronization are not implemented.
- File uploads and Supabase Storage are not currently used; book cover upload is not implemented.

## Data ownership

Authenticated query paths filter by the current user. Related records are validated against that same user, schedule route binding returns 404 for another user's record, and export queries apply the same ownership boundary. Google token fields use Laravel encrypted casts and are hidden from model serialization.
