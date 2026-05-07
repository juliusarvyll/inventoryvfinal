# External Integrations

**Analysis Date:** 2026-05-07

## APIs & External Services

No third-party external APIs or services detected in the codebase. All integrations use first-party Laravel packages.

## Data Storage

**Databases:**
- Laravel default database (configured via `config/database.php`, typically MySQL or SQLite per Laravel defaults)
  - Connection: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` env vars
  - Client: Eloquent ORM (Laravel's default)

**File Storage:**
- Local filesystem via Laravel Storage (`storage/` directory)
  - Public visibility requires explicit `->visibility('public')` on file uploads (per Filament best practices)
  - No cloud storage integrations detected

**Caching:**
- Default Laravel cache (file-based by default, configurable via `config/cache.php`)
  - No dedicated external caching service (e.g., Redis) detected

## Authentication & Identity

**Auth Provider:**
- Laravel Fortify v1 (first-party authentication backend)
  - Implementation: Handles login, registration, password reset, email verification, 2FA, profile updates via `app/Providers/FortifyServiceProvider.php`, `app/Actions/Fortify/`

## Monitoring & Observability

**Error Tracking:**
- None detected

**Logs:**
- Laravel Pail v1 for local log viewing
- Standard Laravel log channel (file-based by default, configurable via `config/logging.php`)

## CI/CD & Deployment

**Hosting:**
- Laravel Cloud (recommended per deployment rules in `CLAUDE.md`)

**CI Pipeline:**
- None detected

## Environment Configuration

**Required env vars:**
- Standard Laravel env vars: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`
- Database vars: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Fortify-related configuration (managed via `config/fortify.php`)

**Secrets location:**
- `.env` file (gitignored, not committed to repository)

## Webhooks & Callbacks

**Incoming:**
- None detected

**Outgoing:**
- None detected

## Event & Queue Systems

- Laravel's default event system (no external message queues like Redis, SQS detected)
- Queue configuration via `config/queue.php` (defaults to database queue if enabled)

---

*Integration audit: 2026-05-07*
