# Mariah CMS

Custom CMS + admin dashboard for Majesty Day Spa. Staff manage content through
`/admin`; the public website reads it from a JSON API. See README.md for the full
schema, endpoint list and deployment notes.

## Stack

Plain PHP 8 (`declare(strict_types=1)`) + MySQL. **No framework, no Composer, no
build step, no package manager.** Autoloading is a PSR-4 shim in
`config/bootstrap.php` mapping `Mariah\` → `app/`. Do not introduce dependencies
or a build pipeline.

## Layout

- `api/index.php` — the entire front controller. Routes are registered here and
  **each route carries its own guard**; adding an endpoint means editing this file.
- `app/Controllers/` — one per resource; validate input, call a repository, return
  via `Core\Response`.
- `app/Repositories/` — all SQL. Extend `BaseRepository`. Prepared statements only.
- `app/Core/` — hand-written Router, Auth, Csrf, Validator, RateLimiter, Paginator,
  Logger, Env, Database, Slug, Clock. Look here before writing a helper.
- `app/Services/` — cross-cutting logic (media, CSV/Sheets import, sanitizing,
  installer).
- `admin/` — static HTML + **vanilla JS** SPA. `assets/js/router.js` dispatches to
  one module per screen in `assets/js/pages/`; shared widgets in `assets/js/ui/`;
  all HTTP through `assets/js/api.js`. No framework, no bundler.
- `database/migrations/` — numbered forward-only `.sql` files (`012_...` next).
- `config/`, `storage/`, `tests/`.

## Permissions

`config/permissions.php` is the **single source of truth** for the permission
catalogue and role map. Adding a module means adding its permissions there and
running `php database/seed.php --sync` — never hardcode authorization checks.

## Commands (CLI only — these refuse to run over HTTP)

```
php database/migrate.php [--status|--fresh]
php database/seed.php [--demo|--sync]
php tests/smoke.php
```

`tests/smoke.php` is the only test: an end-to-end HTTP run needing `APP_URL` in
`.env` pointing at a deployed copy and a database seeded with `--demo`. It is not
a unit suite; there is no test runner or linter.

## Operating constraint

There is no PHP or MySQL runtime on the development machine, so nothing here has
been executed. Changes cannot be verified locally — say so rather than claiming a
change is tested. `php -l <file>` is the most that can be checked, and only where
a PHP binary is available.

## Conventions

- Match surrounding style: `declare(strict_types=1)`, typed signatures, doc-block
  header on each file explaining *why*.
- The database is shared by the API, admin SPA and public site — before altering
  schema, grep every layer for references and consider production data.
- Migrations are forward-only; never edit an applied migration file.
