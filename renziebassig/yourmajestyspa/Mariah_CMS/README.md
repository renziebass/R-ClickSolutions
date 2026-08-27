# Mariah_CMS — Majesty Day Spa Content Manager

A custom CMS and admin dashboard for Majesty Day Spa. Staff manage services, categories,
promotions, specials, blog posts, retail products, gift cards, media and users through a secure
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

**Derived status for dated content.** Promotions, specials and blog posts store only
`draft` / `published` / `archived`. Whether something is *scheduled*, *active* or
*expired* is computed from the dates by `ScheduleResolver` — nobody types "expired".

**Soft deletes everywhere.** Content is never destroyed by the UI. `deleted_at` +
`deleted_by` hide it, the "Deleted items" filter shows it, and Restore brings it back.

**Rich text is an allowlist, not an editor setting.** Long-form descriptions are written
with a formatting toolbar and stored as HTML — but only the subset
`App\Services\HtmlSanitizer` rebuilds them into. It runs on **write**, in
`ResourceController`, driven by each controller's `richTextFields()`. That is the one
place on the public site where stored data is printed as markup instead of escaped, so
the allowlist is the boundary; see §14.

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
| `media` | Central image library | `file_path`, `file_url`, `mime_type`, `alt_text`, `folder` |
| `service_categories` | Website service tabs, two levels deep | `parent_id` (self FK, NULL = top level), `slug` UQ, `icon_key`, `display_order` |
| `services` | Treatment menu | `price` + `price_display`, `duration_minutes` + `duration_display`, `booking_url`, `icon_key`, `most_loved_rank`, `benefits`, `inclusions`, `contraindications` |
| `service_variants` | Price/duration tiers | `label`, `duration_minutes`, `price`, own `booking_url` |
| `service_addons` | Per-category enhancements | `category_id`, `price`, `duration_minutes` |
| `service_images` | Per-service gallery | `is_primary`, `display_order` |
| `promotions` | Discount rules | `discount_type`, `discount_value`, `start_date`, `end_date` |
| `promotion_services` | Promotion ↔ service join | composite PK |
| `specials` | Packaged offers at a price | `price`, `compare_at_price`, `badge_label`, dates |
| `blog_categories` | Journal topic filters | `slug` UQ, `display_order` |
| `blog_posts` | Journal articles | `excerpt`, `content`, `published_at`, `read_minutes`, `tags`, `meta_title` |
| `product_brands` | Skincare houses | `tagline` |
| `product_categories` | Shop filter chips | `display_order` |
| `products` | Retail products | `price`, `badge_label`, `icon_key` |
| `gift_cards` | Gift cards + memberships | `type`, `price_interval`, `purchase_url` |
| `audit_logs` | Administrative activity | `action`, `entity_type`, `entity_id`, `metadata` JSON |

**Promotions vs Specials** are separate because their business purpose differs: a
*promotion* is a discount rule applied to services (15% off midweek massages); a
*special* is a sellable bundle with its own price (`$215` struck through `$299`).

### The media library: folders filed by usage

Every module that carries an image owns one folder, so a photo's folder answers *what is
this picture for?* The set is declared in code (`app/Services/MediaFolders.php`), never
created by an operator — the slug doubles as a directory name under `storage/uploads`, so
a typo would strand photos somewhere nothing else looks. Files live at
`storage/uploads/{folder}/YYYY/MM/`; the date shard sits **inside** the folder so no
single directory grows without bound.

Filing is automatic and follows **first use wins**:

- An upload lands in `unsorted`. An upload started from a content form names its folder up
  front, so it is written straight there and never has to move.
- When a service, category, promotion, special, blog post, brand, gift card or product
  saves a `media_id`, `MediaFiler::file()` moves that photo into the module's folder — but
  only if it is still in `unsorted`. A photo shared by a service and a promotion therefore
  has one home and one stable URL, instead of ping-ponging between two.
- Detaching a photo leaves it where it is. "Move to folder" in the library is the manual
  override for the case where the automatic answer was the wrong one.

Filing is two separable things, and the distinction matters:

- **The folder recorded on the row** — what the library shows, badges and filters by. Every
  photo gets one.
- **The file moved on disk** into `storage/uploads/{folder}/` — only for files this CMS
  actually stores. Rows whose `file_url` is not under `STORAGE_URL` are references to the
  website's own artwork in `assets/` rather than uploads (the demo seed creates these).
  There is nothing of ours to move, but they are still a service photo or a category photo,
  so they are still **filed by label** and keep their file where it is.

The move is real: `file_path` and `file_url` are rewritten, and **the database is only
written once the file is confirmed at its new path**. If a `rename()` fails, the row keeps
describing where the file actually still is, the live image keeps rendering, and the
failure is logged — an unfiled photo is a far smaller problem than a broken one. Filing
runs *after* the content record has committed, so it can never turn a successful save into
a 500.

**Reorganise files** in the admin (`POST /media/reorganize`) is the catch-up and the repair:
it re-derives a folder for everything still in `unsorted` from what currently uses it — the
same statements and the same order as migration 011's backfill — then walks the files to
match. Migration 011 only catches what was attached *at the moment it ran*, so an install
seeded afterwards would otherwise be stuck in Unsorted with no way back. Running it is
idempotent, and it never overrules a folder somebody picked by hand.

### The treatment menu: categories, tiers and add-ons

The real menu is two levels deep and priced by duration, which `services` alone
cannot express.

- **Sub-categories.** `service_categories.parent_id` points at another category, and
  nothing may nest deeper — `CategoryController` rejects a third level, a category as
  its own parent, and demoting a category that has children of its own. Top-level
  categories are the tabs on the website; sub-categories are headings inside a tab.
  Services always attach to the **leaf**, so a service on a childless top-level
  category keeps working exactly as before.

- **Price tiers.** A treatment offered at 50/80/110 minutes is one `services` row plus
  three `service_variants`. Each tier carries its own `booking_url`, because the Booker
  links are `.../detail-summary/{id}` deep links — 50 and 80 minutes of the same
  treatment are two different products to book. The **cheapest tier is mirrored back**
  onto `services.price` and `services.duration_minutes` by `syncVariants()`, exactly as
  `service_images` mirrors its primary onto `services.media_id`, so every existing sort,
  filter and public query keeps working with no join.

  This is what `price_display` and `duration_display` were for. Those columns exist
  because there was nowhere to put a range, so someone typed `"from $150"` and
  `"60 – 90 min"` by hand. They still win when set, but `ServiceRepository::applyLabels()`
  now derives the same strings — `"$150"` for one tier, `"from $150"` and `"50–110 min"`
  for several.

- **Add-ons** belong to a category and carry **their own price**, because the price is a
  property of the menu the add-on appears on: Aromatherapy is +$25 on the massage menu
  and +$20 on the facial menu. Two rows, one per category. A shared catalogue with a
  single price would have to be wrong for one of them.

**Gratuity is deliberately not a column.** It is exactly 15% of price throughout the
source menu, so it is computed if it is needed at all. The one row that disagreed had
stale gratuity figures left over from an earlier price rise — precisely the drift a
derived value cannot have.

### Indexes

Every slug is `UNIQUE`; every foreign key is indexed. Beyond that, composite indexes
match the exact shape of the queries that run most:

- `(status, deleted_at, display_order)` on every publicly-listed table — covers the
  public endpoints' `WHERE` and `ORDER BY` in one index.
- `(category_id, status, display_order)` on `services` — the category-filtered listing.
- `(status, start_date, end_date)` on `promotions` and `specials` — the date-window scan.
- `(status, deleted_at, published_at)` on `blog_posts` — the Journal listing scan.
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

service_categories ──< service_categories        (parent_id, two levels only)
service_categories ──< service_addons
service_categories ──< services ──< service_images >── media
                          └──────< service_variants
                                 └─< promotion_services >── promotions ── media
                                                            specials  ── media

product_brands     ──< products >── product_categories
products ── media                   gift_cards ── media
```

**Cascade policy**

| Behaviour | Where | Why |
|---|---|---|
| `RESTRICT` | category → services, category → sub-categories, brand/type → products, media → service_images, role → users | Deleting the parent would orphan or silently remove business-critical records. The API returns a clear 409 instead. |
| `SET NULL` | every optional `media_id`, `audit_logs.user_id`, `media.uploaded_by` | Losing an image or a staff account must not delete the content or rewrite history. |
| `CASCADE` | `role_permissions`, `service_images`, `service_variants`, `service_addons`, `promotion_services` | Rows with no meaning apart from their parent. A price tier without its treatment, or an add-on menu without its category, is nothing. |

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
| `APP_TIMEZONE` | | **Fallback only.** The live zone is the Timezone site setting — see §9.1. This is used before the database is readable, and if the setting is unreadable. Default `America/New_York`. |
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
- 3 blog topics and 3 published Journal articles
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
| Bulk import services | ✅ | ✅ | ✅ | ❌ |
| Edit site settings | ✅ | ✅ | ❌ | ❌ |
| Change the timezone | ✅ | ❌ | ❌ | ❌ |
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
services.{view,create,edit,delete,activate,import}
categories.{view,create,edit,delete}
promotions.{view,create,edit,delete,activate}
specials.{view,create,edit,delete,activate}
blog_posts.{view,create,edit,delete,activate}
blog_categories.{view,create,edit,delete}
products.{view,create,edit,delete,activate}
product_categories.{view,create,edit,delete}
brands.{view,create,edit,delete}
gift_cards.{view,create,edit,delete,activate}
media.{view,upload,edit,delete}
users.{view,create,edit,delete}
roles.{view,create,edit,delete}
audit_logs.view
settings.{view,edit}
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

A third rule sits above the permission system, at the level of a single setting
rather than an endpoint: a setting carrying `'super_admin' => true` in
`SettingsSchema` can be **read** by anyone with `settings.view` but **written**
only by a Super Admin. `settings.edit` still governs the screen as a whole. Only
the timezone uses this today; an Admin sees it and what it is set to, with the
input disabled.

### 9.1 Timezone

The CMS runs on one timezone, chosen in `setup.php` during installation and
changeable afterwards by a Super Admin under **Settings → Site settings**. It
drives audit log times, scheduled blog posts, and promotion and special start
and end dates.

It is not a display preference. Every timestamp column in this schema is
`DATETIME`, never `TIMESTAMP`, so a stored value is bare wall-clock text with no
zone attached — whichever clock wrote it. Two clocks write into those columns:

- **PHP**, for `date()` values and the repositories' explicit writes;
- **MySQL**, for `NOW()` and `DEFAULT CURRENT_TIMESTAMP` — which is where
  `audit_logs.created_at` comes from, since `AuditLogger` does not send it.

MySQL's session zone otherwise inherits the daemon's own, which is UTC on most
shared hosting while PHP is on the configured zone. Values written by one are
then compared against cutoffs computed by the other: the 15-minute login lockout
becomes a four-hour one, and audit entries land in the future.

`Clock::boot()` closes that gap, applying the zone to both on every request.
Two implementation notes:

- **MySQL is set by numeric offset, not by name** (`SET time_zone = '-04:00'`).
  Shared hosts rarely load the named-timezone tables, so `'America/New_York'`
  fails there with "Unknown or incorrect time zone". The offset is resolved per
  connection, so it is correct for the current DST state.
- **`APP_TIMEZONE` in `.env` is only the pre-database fallback.** The setting
  lives in the database, which cannot be read while `config/bootstrap.php` runs,
  so `Database::pdo()` pins the session to the env zone at connect time and
  `Clock::boot()` refines both clocks from the setting immediately after.
  Nothing is ever left on an unknown zone in between.

Changing the zone does not rewrite existing timestamps — only new ones follow it.

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
| `GET /public/blog-posts` | Live Journal posts, newest first (`?category=slug`, `?limit=`) |
| `GET /public/blog-posts/{slug}` | One post in full, with its paragraphs and related posts |
| `GET /public/blog-categories` | Topics that hold at least one live post |

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

Each of `services`, `categories`, `promotions`, `specials`, `blog-posts`,
`blog-categories`, `products`, `product-categories`, `brands`, `gift-cards` exposes:

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

### Bulk import

```
POST /services/import      multipart: file, dry_run=1|0, confirm_digest
POST /services/import      JSON:      source_url, dry_run=1|0, confirm_digest
```

Two sources, one endpoint: an uploaded CSV or a Google Sheets link are the same import
with a different way of getting the bytes, so they share the permission, the response
envelope and the preview/commit protocol. `data.file.source` is `upload` or
`google_sheet`, and `data.file.source_url` carries the export URL the server used.

**The Sheets link is never fetched as given.** `GoogleSheetUrl` extracts a document id
matching `[A-Za-z0-9-_]{20,120}` and a numeric gid, and the fetcher requests a hardcoded
`docs.google.com/spreadsheets/d/{id}/export?format=csv&gid={gid}`. The sheet must be
shared **Anyone with the link → Viewer**; anything less returns Google's sign-in page,
which the fetcher detects and explains rather than letting it reach the CSV parser.
Fetch failures use the same 422 / `error.fields.file` channel as file failures.

Both preview and commit fetch the sheet, mirroring the browser re-uploading a file. That
is what makes the digest check meaningful here — a shared sheet really can change between
the two, and a mismatch is a 409 that says so.

Requires the `services.import` permission, held by Super Admin, Admin and Editor. It is
a slug of its own rather than part of `services.create` so that a custom role can be
given single-record editing without the bulk tool — not because it is stricter. The
importer only creates and updates, never deletes and never creates categories, and every
column it writes is one the admin form already reaches, so for a role holding
`services.create` and `services.edit` it adds speed rather than reach.

### Import rules are configurable

Which columns must be filled, and what a blank cell falls back to, are set per site on the
**Import screen** (the only screen that knows the column list) and stored as one sparse
JSON site setting, `services_import_rules`. A column nobody has touched keeps whatever
`ServiceCsvSchema::columns()` says, so the built-in contract stays the source of truth.

Three things are worth knowing:

- **Defaults apply to NEW services only.** A blank cell on an existing service still means
  "leave the stored value alone" — the promise that lets someone import a file carrying
  only the columns they changed. Overriding stored data on every re-import would make the
  feature actively dangerous. The `display_order` fallback in `writeAll()` set this
  precedent. A literal `NULL` still clears a column, default or not.
- **A required column with a default needs no header.** The default supplies the value, so
  demanding an empty column would be busywork. That is why `requiredColumns()` (header
  presence) and `requiredKeys()` (cell must carry a value) are two different lists.
- **`name` and `slug` can never have a default.** `identitySlug()` reads them raw, before
  normalisation, and its answer decides whether a row creates or updates. A default there
  would silently change which record a row matches.

Defaults are validated **when they are saved**, through the same coercion the import will
apply — so "not a number" in the price default is a 422 on the settings write, not 500
identical row errors on the next import. `ServiceCsvSchema::rules()` (the admin form) is
deliberately *not* configurable; the importer calls `importRules()` instead, so a setting
named "import rules" cannot change what someone typing a service by hand may leave blank.

The blank template narrows to match: required columns first, then optional ones with no
default. Give a column a default and it leaves the sheet.

### The icon column reads words, not keys

`i-drop` is a machine key, and nobody maintaining a treatment menu in a spreadsheet is
going to type one. `ServiceCsvSchema::resolveIcon()` therefore accepts the key, the full
label, or any word from the label — `i-drop`, `Drop (facial)`, `Drop` and `facial` are the
same icon — and treats `No icon`, `none`, `n/a` and `nil` as *no icon*, with **no
warning**. Matching is on a lowercased, punctuation-stripped form, so `HOT  STONE` and
`hot-stone` both land on `i-stone`.

This is the same leniency `headerAliases()` gives column *names* and `status` gives
`live`/`published`: the importer meets the sheet where it is. A genuinely unrecognised
value still warns and imports as no icon, so a typo cannot pass silently.

The lookup is built from `iconChoices()`, and **a word claimed by two labels is dropped
rather than guessed at** — so adding an icon later can never silently start matching an
existing one to the wrong key. Adding one means two edits: the `iconChoices()` entry, and
an `<svg><symbol id="i-…">` in the public page's sprite. A key with no symbol renders an
empty box on the live site.

**Preview and commit are the same call.** `dry_run` defaults to `1`, so a request that
omits it can only preview; only an explicit `dry_run=0` writes. On confirm the browser
re-uploads the same file, which keeps the server stateless and means the preview and the
write cannot diverge. The preview returns a sha256 `digest` that the commit echoes back
as `confirm_digest`; a mismatch is a 409.

A row's identity is its **slug** — the `slug` column when given, otherwise slugified from
`name`. A match updates that service, a miss creates one, and an update never re-slugs
(so a record's identity cannot drift out from under the next import). Re-importing an
unchanged file therefore does nothing at all.

Both preview and commit return **200**, with per-row outcomes in `data.rows[]`
(`create` / `update` / `unchanged` / `error`) and per-row messages in `data.rows[].errors`
— never in `error.fields`, which the SPA maps onto form inputs and which stays reserved
for file-level failures keyed `file`. **The write is all-or-nothing:** every row is
validated before any row is written, and one failure mid-write rolls the whole batch back.
`Database::transaction()` flattens nesting rather than using savepoints, so a
"skip the bad rows" mode could not be implemented safely here.

Blank cells mean "leave this field alone"; the literal `NULL` clears it. Unknown category
names are a row error — the importer never creates categories. Caps: 2 MB, 500 rows,
60 columns.

Sort keys are resolved through a per-repository allowlist — an arbitrary `?sort=` value
can never reach SQL as a column name.

Also:

```
GET    /dashboard/stats
GET    /services/form-options      categories + icon choices
GET    /categories/options         id/name pairs for selects
GET    /media          POST /media (multipart)   PUT /media/{id}    DELETE /media/{id}
GET    /media/{id}/usage           what would break if this image were deleted
GET    /media/folders              folder slugs, labels and counts
POST   /media/reorganize           files every photo into the folder its row names
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
   block, the brand cards, the shop filter chips, the product grid and the Journal — reusing the exact
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

### The treatment menu on the page

`/public/bootstrap` returns `categories` as a **two-level tree**, already grouped in
PHP so the page does no work to build it:

```
categories[]            top level — one tab each
  ├ services[]          treatments filed directly on the tab
  ├ groups[]            sub-categories, each a heading inside the tab
  │   └ services[]
  └ addons[]            the enhancement menu that applies
```

A tab is emitted only if it, or one of its groups, has a live service — an empty tab is
worse than no tab.

**A card opens a detail view.** `.svc` already carried `cursor: pointer` and the section
lede already said *"Select a service to see what it's best for"*, but nothing was ever
bound. Now every card and every Most Loved row carries `data-service`, and one delegated
handler opens `#svcReader` — the same full-screen surface the Journal reader uses, with
its own slug and its own `?treatment=` parameter so the two can never close each other.
The detail is fetched from `/public/services/{slug}`, an endpoint that already existed
and had no caller.

**Why a card with tiers has no Book button.** A treatment sold at 50, 80 and 110 minutes
has three prices *and three Booker links* — `detail-summary/{id}` deep links are per
product, not per treatment. Picking one on the card would be a guess, so the card shows
`3 lengths →` and the detail view lists every tier with its own button. Single-price
treatments keep the direct link exactly as before.

What the detail view adds beyond the card: the full description, the price tiers, the
category's add-on menu, and the guest-information copy — benefits, inclusions,
complimentary enhancement, and **contraindications**, which is the one a guest needs
*before* booking a hot stone treatment rather than after. None of that is in
`/public/bootstrap`: shipping four text columns for every treatment would multiply the
page's first payload for copy most visitors never open.

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
- **RBAC:** an Editor can create and can preview a CSV import, but gets **403** on
  `DELETE /services/:id`, on `/users`, on `/roles` and on `/audit-logs`; an Admin gets
  **403** editing a Super Admin, **403** editing a role and **403** changing the
  timezone while still getting **200** on every other setting; Staff can view but gets
  **403** creating and **403** importing
- **Clocks:** `SELECT NOW()` and PHP's `time()` agree, and saving a new timezone moves
  the MySQL session with it
- **Treatment menu:** a third category level is refused, a parent holding sub-categories
  cannot be deleted, the same add-on name coexists under two categories at two prices,
  tiers round-trip and cascade away with their service, and `/public/bootstrap` nests
  sub-categories under their parent while a sub-category service inherits the parent
  add-on menu
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
  it reaches `innerHTML`; the public renderer uses the same approach. **The one
  exception is rich text**, and it is worth understanding precisely:
  - Long-form descriptions (service `description`/`benefits`/`inclusions`/
    `contraindications`, blog `content`, and the `description` on products, promotions,
    specials and gift cards) are printed as markup by the `rich()` helper on the public
    page, not escaped.
  - That is safe only because `App\Services\HtmlSanitizer` rewrites those columns from
    an allowlist on every write, server-side, in `ResourceController::store()`/`update()`.
    Nothing outside the allowlist can be in the database to print.
  - The allowlist is `p h2 h3 ul ol li br strong em u s a span`. Every attribute is
    stripped and only `a[href]`, `span[class]` and `dir="ltr|rtl"` are put back.
    `href` must be `http`/`https`/`mailto`/`tel` after control characters are removed,
    and every anchor is forced to `rel="noopener noreferrer"`.
  - Colour is a **class from a fixed palette**, never a `style` attribute. The editor
    emits `style="color: rgb(…)"`, the sanitiser maps palette values onto classes and
    drops the attribute — so `style` never reaches storage at all.
  - It rebuilds rather than filters. `strip_tags()` is not a sanitiser: it keeps
    `onerror=` on every tag it does not remove.
  - It **fails closed**. Without `ext-dom` it returns text with all markup removed,
    never the input unchanged — which is why `dom` is a fatal check in the installer.
  - Every one of these rules has a payload asserting it in the `Rich text sanitising`
    block of `tests/smoke.php`. Treat that block as the specification.
  - **There is no CSP backstop.** The project sets no Content-Security-Policy, so the
    sanitiser is the only control. Adding one is the obvious next hardening step.
- **CSRF** — a double-submit token is required on every POST/PUT/PATCH/DELETE, on top of
  a `SameSite=Lax` session cookie.
- **Session** — HttpOnly, Secure, SameSite=Lax, regenerated on sign-in (defeating
  session fixation), with an idle timeout.
- **Outbound requests** — the Google Sheets import is the application's *only* egress.
  The operator's URL is never fetched as given: `GoogleSheetUrl` extracts a document id
  matching `[A-Za-z0-9-_]{20,120}` and a numeric gid, and the fetcher requests a hardcoded
  `docs.google.com` template built from those two fragments. Every hop is confined to
  HTTPS (`CURLOPT_PROTOCOLS` and `CURLOPT_REDIR_PROTOCOLS`), redirects are capped at 5,
  connect and total timeouts are 5 s and 20 s, certificate verification is explicitly on,
  no cookies are sent, and a write callback aborts the transfer the moment the response
  exceeds the size cap. The residual exposure is that redirects may follow to
  Google-controlled hosts, which cannot be pinned without breaking the feature; the caller
  already holds `services.import` and can therefore already rewrite the whole public menu.
  That caller may be an Editor, whose `services.create` / `services.edit` grants carry the
  same reach — and the link path additionally stays dark until an Admin turns on
  `services_import_url_enabled`, which needs `settings.edit`.
  Note also that **a sheet imported by link is world-readable to anyone holding the link**
  — that is what "Anyone with the link → Viewer" means.
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

Appointments, customers, memberships, testimonials, FAQs, locations and staff all
fit this shape.

---

## 16. Not implemented

Stated plainly rather than left to be discovered:

- **Website content that is still hard-coded in `mds_version_a.html`.** A `settings` store
  now exists (Settings → Site settings) but holds only the timezone and the service-import
  keys. The guest
  quotes in the Reviews section, and the contact details, opening hours and Booker.com
  links in the header and footer, are still edited in the HTML.
- **Private Google Sheets.** Importing from a link requires the sheet to be shared
  "Anyone with the link → Viewer". An OAuth or service-account path would need a Google
  API client, which would need Composer, and this project has no dependencies.
- **`session.config.uploadMaxBytes`** is still a client-side constant rather than being
  served from `UPLOAD_MAX_BYTES`, even though `/auth/me` now carries a `config` block.
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
