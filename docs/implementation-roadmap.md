# Implementation roadmap

## Delivery status

- [x] Foundation, auth scaffolding, local SQLite setup, and responsive guest/app layouts.
- [x] Dashboard, Diary, Todo, Journal, Gratitude, Mood, Habit, Schedule, Book, and Book Notes flows.
- [x] Owner-scoped data access, validation, profile time zone, UTC schedule storage, and monthly schedule calendar.
- [x] PDF downloads for Diary, Journal, Todos, Habits, Gratitude, Mood, Schedules, and Books/Book Notes.
- [x] Google sign-in and one-way manual primary-calendar sync implementation, encrypted tokens, refresh, update-by-event-ID, logging, and disconnect behavior.
- [x] PWA manifest, root service-worker endpoint, public-shell caching, and offline notice.
- [x] Automated feature coverage, frontend production build, local SQLite migration, and documentation.

## Remaining before a production-complete release

- Configure actual Google OAuth credentials and verify consent, Calendar API access, token refresh, and revocation in a controlled Google test account.
- Run migrations, tests, and backup/restore checks against the intended PostgreSQL/Supabase database.
- Perform a browser/device installability and offline check over HTTPS; verify responsive layout on the target device set.
- Configure production secrets, TLS, persistent sessions/cache, backups, and monitoring, then deploy to the selected hosting platform.
- Add a calendar selector, bidirectional sync, queued retry strategy, offline editing/sync, and book-cover uploads only if those expanded requirements are approved.

The implemented Google and PWA scope is deliberately one-way/online-only. These limitations are not presented as completed capabilities.
