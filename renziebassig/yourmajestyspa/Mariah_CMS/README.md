# Mariah_CMS — Majesty Day Spa Content Manager

A custom CMS and admin dashboard for Majesty Day Spa. Staff manage services, categories,
promotions, specials, retail products, gift cards, media and users through a secure
`/admin` dashboard; the public website reads that content from a JSON API.

> **Status: written but not yet executed.** There is no PHP or MySQL runtime on the
> machine this was built on, so the migrations, seed and smoke test have never been run.
> Follow [Installation](#4-installation) and then [Testing](#12-testing) to verify.

---

## Contents

1. [Architecture](#1-architecture)
2. [Database schema](#2-database-schema)
3. [Relationships](#3-relationships)
4. [Installation](#4-installation)
5. [Environment variables](#5-environment-variables)
6. [Migrations](#6-migrations)
7. [Seed data](#7-seed-data)
8. [Admin login](#8-admin-login)
9. [Roles and permissions](#9-roles-and-permissions)
10. [API endpoints](#10-api-endpoints)
11. [How the website connects](#11-how-the-website-connects)
12. [Testing](#12-testing)
13. [Deployment](#13-deployment)
14. [Security notes](#14-security-notes)
15. [Extending the CMS](#15-extending-the-cms)
16. [Not implemented](#16-not-implemented)

---

## 1. Architecture

```
Public website (mds_version_a.html)
        │  fetch()
        ▼
GET /api/public/*  ──────────────────►  PublicContentController
                                                │
Admin SPA (/admin)                              │
        │  fetch() + session cookie + CSRF      │
        ▼                                       │
/api/*  ──►  Guard (auth → status → permission) │
                    │                           │
                    ▼                           ▼
              Controllers ──► Repositories ──► MySQL (PDO, prepared statements)
                    │
                    └──► AuditLogger ──► audit_logs
```

**Stack: PHP 8 + MySQL, no build step.** Chosen because the deployment target is
Hostinger shared hosting, which runs PHP and MySQL but not Node. The admin dashboard is
a vanilla ES-module SPA for the same reason — it deploys by file upload, with no
`npm install` on the server.

Database credentials live in `.env`, are read only server-side, and are never sent to
the browser.

### Folder layout

```
Mariah_CMS/
├── .env.example              template — copy to .env
├── .htaccess                 API rewrite + blocks direct access to server code
├── index.php                 redirects to admin/
├── setup.php                 browser installer for hosts without SSH (token-gated)
├── api/index.php             single front controller: routes, guards, JSON envelope
├── admin/
│   ├── login.html            sign-in screen
│   ├── index.html            SPA shell + SVG icon sprite
│   └── assets/
│       ├── css/admin.css     design system (brand tokens from the public site)
│       └── js/
│           ├── app.js        boot, sidebar, route registration
│           ├── api.js        fetch wrapper, response envelope, CSRF
│           ├── router.js     hash router with per-route permissions
│           ├── session.js    signed-in identity and permission checks
│           ├── ui/           dom, feedback, table, form, media-picker
│           └── pages/        one module per screen
├── app/
│   ├── Core/                 Env, Database, Request, Response, Router, Validator,
│   │                         Auth, Csrf, RateLimiter, Logger, Slug, Paginator
│   ├── Middleware/Guard.php  route guards (auth / permission / super admin)
│   ├── Repositories/         query layer, one per entity + BaseRepository
│   ├── Services/             AuditLogger, MediaService, ScheduleResolver, Installer
│   └── Controllers/          one per resource + ResourceController base
├── config/
│   ├── bootstrap.php         autoloader, .env load, error handling
│   └── permissions.php       the permission catalogue and role → permission map
├── database/
│   ├── migrations/*.sql      schema, in filename order
│   ├── migrate.php           CLI wrapper around Installer::migrate()
│   └── seed.php              CLI wrapper around Installer::seed*()
├── storage/
│   ├── uploads/YYYY/MM/      uploaded images (script execution disabled)
│   └── logs/                 daily error logs
└── tests/smoke.php           end-to-end HTTP test incl. RBAC assertions
```

### Design decisions worth knowing

**Sessions, not JWT.** The brief listed `JWT_SECRET`. This uses hardened PHP sessions
plus a CSRF double-submit token instead: the admin SPA is same-origin with the API, so
nothing auth-related needs to be reachable from JavaScript, and revoking a user takes
effect on their very next request rather than at token expiry. `.env` therefore has
`SESSION_SECRET` and no `JWT_SECRET`.

**Derived status for dated content.** Promotions and specials store only
`draft` / `published` / `archived`. Whether something is *scheduled*, *active* or
*expired* is computed from the dates by `ScheduleResolver` — nobody types "expired".

**Soft deletes everywhere.** Content is never destroyed by the UI. `deleted_at` +
`deleted_by` hide it, the "Deleted items" filter shows it, and Restore brings it back.

**Display overrides alongside numbers.** The live site shows `from $150`,
`$199 – $225` and `1 hr & 40 mins`, not plain numbers. Services carry both a numeric
`price` / `duration_minutes` (for sorting and reporting) and an optional
`price_display` / `duration_display` string that wins on the website.

---

## 2. Database schema

All tables are InnoDB / `utf8mb4`. Content tables carry
`created_at, updated_at, created_by, updated_by, deleted_at, deleted_by`.

| Table | Purpose | Notable columns |
|---|---|---|
| `roles` | Named permission bundles | `slug`, `is_system` |
| `permissions` | The permission catalogue | `slug` (`services.create`), `group_name` |
| `role_permissions` | Role ↔ permission join | composite PK |
| `users` | Staff accounts | `email` UQ, `password_hash`, `role_id`, `status` |
| `login_attempts` | Login throttling | `email`, `ip_address`, `successful` |
| `media` | Central image library | `file_path`, `file_url`, `mime_type`, `alt_text` |
| `service_categories` | Website service tabs | `slug` UQ, `icon_key`, `display_order` |
| `services` | Treatment menu | `price` + `price_display`, `duration_minutes` + `duration_display`, `booking_url`, `icon_key`, `most_loved_rank` |
| `service_images` | Per-service gallery | `is_primary`, `display_order` |
| `promotions` | Discount rules | `discount_type`, `discount_value`, `start_date`, `end_date` |
| `promotion_services` | Promotion ↔ service join | composite PK |
| `specials` | Packaged offers at a price | `price`, `compare_at_price`, `badge_label`, dates |
| `product_brands` | Skincare houses | `tagline` |
| `product_categories` | Shop filter chips | `display_order` |
| `products` | Retail products | `price`, `badge_label`, `icon_key` |
| `gift_cards` | Gift cards + memberships | `type`, `price_interval`, `purchase_url` |
| `audit_logs` | Administrative activity | `action`, `entity_type`, `entity_id`, `metadata` JSON |

**Promotions vs Specials** are separate because their business purpose differs: a
*promotion* is a discount rule applied to services (15% off midweek massages); a
*special* is a sellable bundle with its own price (`$215` struck through `$299`).

### Indexes

Every slug is `UNIQUE`; every foreign key is indexed. Beyond that, composite indexes
match the exact shape of the queries that run most:

- `(status, deleted_at, display_order)` on every publicly-listed table — covers the
  public endpoints' `WHERE` and `ORDER BY` in one index.
- `(category_id, status, display_order)` on `services` — the category-filtered listing.
- `(status, start_date, end_date)` on `promotions` and `specials` — the date-window scan.
- `(entity_type, entity_id)`, `(user_id, created_at)`, `(action, created_at)` on
  `audit_logs` — the three ways the audit screen is filtered.
- `(email, attempted_at)` and `(ip_address, attempted_at)` on `login_attempts`.

---

## 3. Relationships

```
roles ──< role_permissions >── permissions
  │
  └──< users ──< audit_logs
              └─< media (uploaded_by)

service_categories ──< services ──< service_images >── media
                                 └─< promotion_services >── promotions ── media
                                                            specials  ── media

product_brands     ──< products >── product_categories
products ── media                   gift_cards ── media
```

**Cascade policy**

| Behaviour | Where | Why |
|---|---|---|
| `RESTRICT` | category → services, brand/type → products, media → service_images, role → users | Deleting the parent would orphan or silently remove business-critical records. The API returns a clear 409 instead. |
| `SET NULL` | every optional `media_id`, `audit_logs.user_id`, `media.uploaded_by` | Losing an image or a staff account must not delete the content or rewrite history. |
| `CASCADE` | `role_permissions`, `service_images`, `promotion_services` | Pure join tables with no independent meaning. |

---

## 4. Installation

**Requirements:** PHP 8.0+ with PDO MySQL, `fileinfo`, `mbstring`, and `curl` for the
smoke test. MySQL 5.7+ or MariaDB 10.3+. Apache with `mod_rewrite`.

> **Deploying to Hostinger?** Follow **[DEPLOY-HOSTINGER.md](DEPLOY-HOSTINGER.md)**
> instead — it covers creating the database in hPanel, the exact `.env` values, and the
> browser installer for plans without SSH.

**Create the database first.** Shared-hosting MySQL users are not allowed to run
`CREATE DATABASE`, so make it in your control panel before installing. (Locally, the
installer creates it for you if the user has the privilege.)

### With command-line access

```bash
cd renziebassig/yourmajestyspa/Mariah_CMS

cp .env.example .env
# then edit .env — see the next section

php database/migrate.php
php database/seed.php

chmod -R 775 storage
```

### Without command-line access

Shared hosting without SSH cannot run the scripts above, so the same logic is exposed
through a browser installer.

1. Set a long random `SETUP_TOKEN` in `.env`.
2. Open `.../Mariah_CMS/setup.php?token=YOUR_TOKEN`.
3. Work through the three steps: environment check → create tables → seed roles,
   administrator and content.
4. Clear `SETUP_TOKEN` and **delete `setup.php`**.

Without a matching token, `setup.php` does nothing at all — so an installer left behind
by mistake is inert rather than an entry point. Both paths run the same
`app/Services/Installer.php`, so they produce identical results.

Then open `https://your-site/renziebassig/yourmajestyspa/Mariah_CMS/admin/`.

**Local development (XAMPP / Laragon):** put the repo under the web root, set
`DB_USER=root`, `DB_PASS=`, and — because a local site is plain HTTP —
`SESSION_COOKIE_SECURE=false`. Leaving it `true` on `http://` makes the browser discard
the session cookie and login appears to silently fail.

---

## 5. Environment variables

Copy `.env.example` to `.env`. **Never commit `.env`** — it is in `.gitignore`.

| Variable | Required | Notes |
|---|---|---|
| `APP_ENV` | | `production` or `local` |
| `APP_DEBUG` | | `false` in production. Errors are always logged, never displayed. |
| `APP_URL` | yes | Full URL of this folder, no trailing slash. Used to build seeded image URLs and by the smoke test. |
| `DB_HOST` `DB_PORT` `DB_NAME` `DB_USER` `DB_PASS` | yes | MySQL connection |
| `SESSION_SECRET` | yes | `php -r "echo bin2hex(random_bytes(32));"` |
| `SESSION_NAME` | | Cookie name |
| `SESSION_IDLE_TIMEOUT` | | Seconds before an idle session is invalidated (default 28800) |
| `SESSION_COOKIE_SECURE` | | `true` in production; `false` only for local HTTP |
| `STORAGE_PATH` | | Filesystem path for uploads, relative to this folder |
| `STORAGE_URL` | yes | Public URL prefix that maps to `STORAGE_PATH` |
| `UPLOAD_MAX_BYTES` | | Default 5 MB. Must be ≤ PHP's `upload_max_filesize`. |
| `LOGIN_MAX_ATTEMPTS` `LOGIN_LOCKOUT_SECONDS` | | Login throttle (default 5 per 15 min) |
| `ADMIN_EMAIL` `ADMIN_PASSWORD` | seed only | Creates the first Super Admin. **Change the password and remove these after first sign-in.** |
| `PUBLIC_CORS_ORIGINS` | | Comma-separated origins allowed to read `/api/public/*`. Empty = same-origin only. |

---

## 6. Migrations

```bash
php database/migrate.php            # apply anything not yet applied
php database/migrate.php --status   # list applied / pending
php database/migrate.php --fresh    # drop every table and rebuild (asks for confirmation)
```

Or use `setup.php` **Step 2** in a browser, which does the same thing.

Applied files are recorded in a `migrations` table, so re-running is safe. Migrations
run in filename order; to add one, drop a new `NNN_name.sql` into
`database/migrations/`.

`--fresh` drops the individual tables rather than the database itself, because
shared-hosting MySQL users cannot drop a database.

---

## 7. Seed data

```bash
php database/seed.php           # roles, permissions, Super Admin, and all content
php database/seed.php --demo    # also create demo Admin / Editor / Staff accounts
php database/seed.php --sync    # re-sync roles and permissions only; content untouched
```

The seed is idempotent — every insert is keyed on a slug or email and skipped if the
record already exists, so existing content is never overwritten.

It loads the **real content currently hard-coded in `mds_version_a.html`**, so the CMS
starts as an exact replica of the live site:

- 6 service categories, 16 services with their real prices, durations, descriptions and
  Booker.com links, and the three "Most Loved" ranks assigned
- 3 specials (Majesty Summer Reset, Couples Summer Escape, Crown Society)
- 3 promotions deliberately covering the active, scheduled and expired states
- 2 brands, 9 product types, 4 products
- 1 gift card and 1 membership
- `media` rows pointing at the existing files in `yourmajestyspa/assets/`

Run `--sync` after editing `config/permissions.php` to push permission changes into
the database.

---

## 8. Admin login

Sign in at `.../Mariah_CMS/admin/` with the `ADMIN_EMAIL` and `ADMIN_PASSWORD` you put
in `.env`.

> **Before going live:** sign in, change the password under **Settings → Change your
> password**, then delete `ADMIN_PASSWORD` from `.env`. If you seeded with `--demo`,
> delete the three `@demo.local` accounts too — they share a published password.

Passwords are hashed with bcrypt (cost 12) and are re-hashed automatically if the cost
is ever raised. A password hash is never included in any API response.

---

## 9. Roles and permissions

Four roles ship as system roles. `config/permissions.php` is the source of truth.

| | Super Admin | Admin | Editor | Staff |
|---|---|---|---|---|
| View content | ✅ | ✅ | ✅ | ✅ |
| Create / edit content | ✅ | ✅ | ✅ | ❌ |
| Activate / deactivate | ✅ | ✅ | ✅ | ❌ |
| Delete content | ✅ | ✅ | ❌ | ❌ |
| Upload media | ✅ | ✅ | ✅ | ❌ |
| View users | ✅ | ✅ | ❌ | ❌ |
| Create / edit users | ✅ | ✅ | ❌ | ❌ |
| Delete users | ✅ | ❌ | ❌ | ❌ |
| Manage roles & permissions | ✅ | ❌ | ❌ | ❌ |
| View audit logs | ✅ | ✅ | ❌ | ❌ |

Permission slugs follow `resource.action`:

```
dashboard.view
services.{view,create,edit,delete,activate}
categories.{view,create,edit,delete}
promotions.{view,create,edit,delete,activate}
specials.{view,create,edit,delete,activate}
products.{view,create,edit,delete,activate}
product_categories.{view,create,edit,delete}
brands.{view,create,edit,delete}
gift_cards.{view,create,edit,delete,activate}
media.{view,upload,edit,delete}
users.{view,create,edit,delete}
roles.{view,create,edit,delete}
audit_logs.view
```

### Enforcement

Authorization is enforced **on the server, per endpoint**. Hiding a sidebar item or a
button is convenience only. Every guarded request re-checks, in order:

1. a valid session exists,
2. the user is still `active` and not soft-deleted (re-read from the database on every
   request, so deactivating someone locks them out immediately),
3. their role is loaded,
4. the role holds the required permission.

Two rules sit **above** the permission system, because `users.edit` alone would
otherwise allow privilege escalation:

- only a Super Admin may create, edit or delete a Super Admin account;
- only a Super Admin may assign the Super Admin role.

There are also guards against locking everyone out: you cannot deactivate or delete
your own account, and you cannot remove the last active Super Admin.

**An Editor who calls `DELETE /api/services/123` by hand receives `403 Forbidden`.**
`tests/smoke.php` asserts exactly this.

---

## 10. API endpoints

Base URL: `.../Mariah_CMS/api`

Every response uses one envelope:

```jsonc
// success
{ "success": true, "data": …, "meta": { "page": 1, "per_page": 20, "total": 57, "last_page": 3, "from": 1, "to": 20 } }

// failure
{ "success": false, "error": { "code": "VALIDATION_FAILED", "message": "Please correct the highlighted fields.", "fields": { "name": "Service name is required." } } }
```

| Code | Meaning |
|---|---|
| 200 / 201 | OK / Created |
| 400 | Malformed request |
| 401 | Not signed in |
| 403 | Signed in, but the role lacks the permission |
| 404 | No such record or endpoint |
| 405 | Wrong HTTP verb for that path |
| 409 | Conflict — duplicate email, or a record still referenced by others |
| 419 | CSRF token missing or stale — refresh the page |
| 422 | Validation failed; `error.fields` maps column → message |
| 429 | Login throttled |
| 500 | Server fault. The message carries a reference id; the detail is in `storage/logs/`. |

### Public (no authentication)

| Endpoint | Returns |
|---|---|
| `GET /public/bootstrap` | Everything the website needs, in one request |
| `GET /public/services` | Active services (`?category=slug`) |
| `GET /public/services/{slug}` | One service with its gallery |
| `GET /public/categories` | Active categories |
| `GET /public/specials` | Live specials |
| `GET /public/promotions` | Live promotions |
| `GET /public/products` | Active products (`?category=slug`) |
| `GET /public/product-categories`, `GET /public/brands` | Shop taxonomy |
| `GET /public/gift-cards` | Gift cards and memberships (`?type=`) |

These return **only** records that are active/published, inside their date window, and
not soft-deleted.

### Authentication

```
GET  /auth/csrf          issue a CSRF token
POST /auth/login         { email, password } — rate limited
POST /auth/logout
GET  /auth/me            current profile, role and permission slugs
POST /auth/password      { current_password, new_password }
```

### Admin resources

Each of `services`, `categories`, `promotions`, `specials`, `products`,
`product-categories`, `brands`, `gift-cards` exposes:

```
GET    /{resource}                 list — search, filter, sort, paginate
GET    /{resource}/{id}            one record
POST   /{resource}                 create
PUT    /{resource}/{id}            update
DELETE /{resource}/{id}            soft delete
POST   /{resource}/{id}/restore    undo a soft delete
PATCH  /{resource}/{id}/status     { status: "active" | "inactive" }
POST   /{resource}/{id}/duplicate  copy as an inactive draft
```

Common list parameters: `page`, `per_page` (max 100), `search`, `sort`, `direction`,
`deleted` (`only` | `with`), plus resource-specific filters (`category_id`, `status`,
`featured`, `state`, `date_from`, `date_to`, `brand_id`, `type`).

Sort keys are resolved through a per-repository allowlist — an arbitrary `?sort=` value
can never reach SQL as a column name.

Also:

```
GET    /dashboard/stats
GET    /services/form-options      categories + icon choices
GET    /categories/options         id/name pairs for selects
GET    /media          POST /media (multipart)   PUT /media/{id}    DELETE /media/{id}
GET    /media/{id}/usage           what would break if this image were deleted
GET    /users   POST /users   PUT /users/{id}   DELETE /users/{id}   POST /users/{id}/restore
GET    /users/assignable-roles
GET    /roles   POST /roles   PUT /roles/{id}   DELETE /roles/{id}   GET /permissions
GET    /audit-logs                 GET /audit-logs/filters
```

---

## 11. How the website connects

`mds_version_a.html` keeps all of its markup, CSS and behaviour. Only the *source* of
its content changed.

1. A `window.MajestyCMS` module fetches `Mariah_CMS/api/public/bootstrap` and re-renders
   the specials grid, the service tabs and panels, the Most Loved ranking, the gift card
   block, the brand cards, the shop filter chips and the product grid — reusing the exact
   original class names, so the stylesheet and animations are untouched.
2. **Then** the original behaviour script runs. This ordering matters: the reveal
   `IntersectionObserver`, the service-tab handlers and the scrollspy all bind once at
   startup, so injecting content afterwards would leave it inert. The old IIFE is now
   `window.__majestyInit`, invoked by `MajestyCMS.hydrate().then(…)`.
3. The "Add to cart" handler is **delegated** on `document` rather than bound per button,
   because the product buttons no longer exist at script-parse time.
4. If the API is unreachable, `hydrate()` logs a warning and resolves anyway. The
   hard-coded markup is left on screen and the page works exactly as before — a
   marketing site must never render an empty services section. Check
   `document.documentElement.dataset.cms`: `live` means CMS content is showing,
   `fallback` means the built-in copy is.

Two fields exist in the schema specifically because the real page needs them:
`booking_url` (each service card links to its own Booker.com page) and `icon_key`
(services map to `<symbol>` ids in the page's existing SVG sprite).

**Verify the whole loop:** deactivate a service in the CMS, reload the website, and it
is gone. Reactivate it and it returns.

Public responses are sent `Cache-Control: no-store` so a deactivation takes effect on
the very next page load. If you later add a CDN, cache these endpoints for no more than
a minute or two.

---

## 12. Testing

### Automated

```bash
php tests/smoke.php
```

Requires `APP_URL` set, a seeded database, and — for the RBAC assertions — the demo
accounts from `php database/seed.php --demo`. It drives the real HTTP API with a cookie
jar, exactly as a browser does, and cleans up everything it creates.

It asserts the full brief flow, plus the failure cases:

- the public API is reachable without a session, and returns the expected collections
- an unauthenticated request to an admin endpoint returns **401**
- a wrong password returns **401**; the profile response contains no password hash
- a mutation with a bad CSRF token returns **419**
- creating a service with no name returns **422**, naming the field
- a `javascript:` booking link is rejected
- **create → the service appears in the admin list → activate → it appears on the public
  API and in `/public/bootstrap` → update → deactivate → it disappears from the public
  API but is still in the admin dashboard → soft delete → it is recoverable from the
  deleted-items filter → restore**
- uploading a `.php` file is rejected
- **RBAC:** an Editor can create but gets **403** on `DELETE /services/:id`, on
  `/users`, on `/roles` and on `/audit-logs`; an Admin gets **403** editing a Super
  Admin and **403** editing a role; Staff can view but gets **403** creating
- the 6th failed sign-in returns **429**, then `login_attempts` is cleared so real
  sign-ins are not locked out

Exit code is non-zero if anything fails.

### Manual browser checklist

1. Sign in at `/Mariah_CMS/admin/`. The dashboard shows real counts, not zeros.
2. **Services** → Add Service. Submit it empty: errors appear on the fields, not as a
   raw dump. Fill it in and save.
3. Upload a JPG through the media picker and attach it to the service.
4. Reload `mds_version_a.html` — the new service is in its category tab with its image,
   price and Book Now link.
5. Deactivate it in the CMS, reload the website — it is gone. Reactivate — it returns.
6. Delete it, switch the list filter to **Deleted items**, and Restore it.
7. Repeat create/edit/activate/delete on Categories, Promotions, Specials, Products,
   Product types, Brands and Gift cards.
8. Set a promotion's start date in the future — it shows **Scheduled**, and is absent
   from the website. Set an end date in the past — it shows **Expired**.
9. Try to delete a category that still holds services — a clear 409 message appears.
10. Try to delete an image that is in use — refused, with a list of what uses it.
11. **Audit logs** — every action above is recorded with the actor and a before/after
    diff.
12. Sign in as `editor@demo.local` — Users, Roles and Audit Logs are absent from the
    sidebar, and delete buttons are gone from the tables.
13. Resize to tablet and phone width — the sidebar collapses behind the menu button and
    the tables become stacked cards.

---

## 13. Deployment

**For Hostinger specifically, follow [DEPLOY-HOSTINGER.md](DEPLOY-HOSTINGER.md)** — it
has the hPanel steps, the exact `.env` values, and a troubleshooting section.

The general shape, on any host:

1. Create the database and its user in your control panel.
2. Upload `Mariah_CMS/` alongside `mds_version_a.html`, and the modified
   `mds_version_a.html` itself.
3. `cp .env.example .env` on the server and fill in the production database.
4. Set `APP_DEBUG=false`, `SESSION_COOKIE_SECURE=true`, a fresh `SESSION_SECRET`, and
   the correct `APP_URL`.
5. Make `storage/` writable (`775`, or `755` if the host runs PHP as your user).
6. Install the database — `php database/migrate.php && php database/seed.php`, or
   `setup.php?token=…` in a browser if you have no shell.
7. Sign in, change the admin password, then clear `ADMIN_PASSWORD` and `SETUP_TOKEN`
   from `.env` and **delete `setup.php`**.
8. Confirm nothing sensitive is served: each of `/.env`, `/app/Core/Database.php`,
   `/config/bootstrap.php` and `/storage/logs/` must return 403 or 404, never content.

**If `/api/...` returns 404**, `mod_rewrite` isn't applying. Check that `AllowOverride`
permits `.htaccess` and that no parent directory's `.htaccess` intercepts the path
first. The API also works when called directly as `/api/index.php/public/bootstrap`,
which is a usable fallback — set that as `API` in the page's `MajestyCMS` module.

**Backups.** `storage/uploads/` holds the only copy of every uploaded image and is not
in version control. Back it up together with the database.

---

## 14. Security notes

- **Passwords** — bcrypt cost 12, automatic re-hash on cost increase. Never returned by
  any endpoint; repositories select explicit column lists rather than `SELECT *` for
  users.
- **SQL injection** — every query is a PDO prepared statement with bound parameters.
  Table names, sort columns and filter columns come from hardcoded allowlists, never
  from input. `LIKE` wildcards in search terms are escaped so `%` is matched literally.
- **XSS** — the admin SPA escapes every database value through one `esc()` helper before
  it reaches `innerHTML`; the public renderer uses the same approach.
- **CSRF** — a double-submit token is required on every POST/PUT/PATCH/DELETE, on top of
  a `SameSite=Lax` session cookie.
- **Session** — HttpOnly, Secure, SameSite=Lax, regenerated on sign-in (defeating
  session fixation), with an idle timeout.
- **File uploads** — accepted only if the extension is allowlisted **and** the MIME type
  sniffed by `finfo` matches **and** `getimagesize()` succeeds. Stored under a
  randomised name so a crafted filename cannot control the path, in a directory whose
  `.htaccess` disables PHP and CGI execution.
- **Login throttling** — 5 failures per email/IP in 15 minutes, then 429.
- **Timing** — a failed sign-in runs a hash comparison whether or not the account exists,
  so responses do not reveal which emails are registered.
- **Errors** — the client gets a generic message and a reference id; the stack trace goes
  to `storage/logs/`. Raw database errors are never returned.
- **Secrets** — only in `.env`, which is gitignored and blocked at the web-server level.

---

## 15. Extending the CMS

The structure is built so new modules drop in without touching authorization:

1. Add a migration in `database/migrations/`.
2. Add the module's permissions to `config/permissions.php`, then
   `php database/seed.php --sync`.
3. Add a repository extending `BaseRepository` (declare `fillable`, `sortable`,
   `searchable`) — that alone gives you search, filtering, safe sorting, pagination and
   soft deletes.
4. Add a controller extending `ResourceController` (declare `rules()`) — that gives you
   the full CRUD surface with validation, slugs and audit logging.
5. Register it in `api/index.php` with `$registerResource(...)`.
6. Add a page module under `admin/assets/js/pages/` and an entry in `NAV` in `app.js`.

Appointments, customers, memberships, testimonials, blog, FAQs, locations and staff all
fit this shape.

---

## 16. Not implemented

Stated plainly rather than left to be discovered:

- **Testimonials and site settings** are still hard-coded in `mds_version_a.html`. They
  were explicitly out of scope. The guest quotes in the Reviews section and the contact
  details, opening hours and Booker.com links in the header/footer are still edited in
  the HTML.
- **`majesty-day-spa.html`, `sample.html` and `concierge.html`** are untouched. Only
  `mds_version_a.html` reads from the CMS.
- **Image resizing / thumbnails.** Uploads are stored at their original dimensions;
  there is no derivative generation. Large photos should be sized before upload.
- **Multi-image galleries** are supported by the schema and API (`service_images`,
  `image_ids` on the service payload), but the admin form currently manages a single
  primary image per service.
- **Password reset by email.** Users change their own password under Settings; a Super
  Admin sets a new one for anyone else. There is no self-service email reset flow.
- **The contact form** on the website still only validates client-side and does not
  submit anywhere. That was pre-existing and out of scope.
- **Nothing in this repository has been executed.** No PHP or MySQL runtime was
  available while building it, so the migrations, seed and smoke test are written but
  unrun. Treat `php tests/smoke.php` as the acceptance gate.
