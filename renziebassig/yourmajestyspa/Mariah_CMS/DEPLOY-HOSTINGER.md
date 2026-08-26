# Deploying Mariah_CMS on Hostinger

Step-by-step for Hostinger shared hosting (hPanel). Allow about 30 minutes.

Assumes your repo already deploys to `public_html/`, so the spa site lives at
`public_html/renziebassig/yourmajestyspa/`. If yours is elsewhere, substitute your path
everywhere below.

---

## Before you start

**Which plan do you have?** It decides how you install the database.

| Plan | SSH? | Install method |
|---|---|---|
| Premium, Business, Cloud, VPS | Yes | Either — SSH is faster |
| Single / older shared plans | No | **Browser installer** (`setup.php`) |

Both paths do exactly the same thing. If you're unsure, use the browser installer — it
works on every plan.

**Check your PHP version first.** hPanel → **Advanced → PHP Configuration**. Set it to
**8.0 or newer** (8.1+ recommended) for the domain. On the **PHP extensions** tab,
confirm `pdo_mysql`, `fileinfo`, `mbstring` and `curl` are ticked. The installer
re-checks all of this and tells you if something is missing.

---

## Step 1 — Create the database

Hostinger does **not** let a MySQL user create databases, so this must happen in hPanel
first. The installer will fail with "access denied" if you skip it.

1. hPanel → **Databases → Management** (sometimes "MySQL Databases").
2. Under **Create a New MySQL Database**:
   - **Database name:** `majesty_cms` → Hostinger saves it as `u123456789_majesty_cms`
   - **Database username:** `majesty` → saved as `u123456789_majesty`
   - **Password:** use the **Generate** button, then copy it somewhere safe
3. Click **Create**.
4. **Write down the full prefixed names exactly as shown in the list afterwards.** The
   `u123456789_` prefix is part of the real name and you will need it verbatim.

> Do **not** reuse the database from `POS/config.php`. Give the CMS its own database and
> its own user, so a problem in one app cannot reach the other.

---

## Step 2 — Upload the files

**Option A — Git (if you deploy via Hostinger's Git integration)**

Commit and push as usual, then pull on the server. `.env` is gitignored by design, so
you will still create it by hand in Step 3.

**Option B — File Manager**

1. Zip the `Mariah_CMS` folder on your computer.
2. hPanel → **Files → File Manager** → navigate to
   `public_html/renziebassig/yourmajestyspa/`
3. **Upload** the zip, then right-click it → **Extract**.
4. Delete the zip.

**Option C — FTP**

Host `ftp.yourdomain.com`, credentials from hPanel → **Files → FTP Accounts**. Upload
the whole `Mariah_CMS` folder into
`public_html/renziebassig/yourmajestyspa/`.

Also upload the modified `mds_version_a.html` — it is what reads from the CMS.

### Set folder permissions

In File Manager, right-click → **Permissions**:

| Folder | Permissions |
|---|---|
| `Mariah_CMS/storage` | `755` (try `775` if uploads fail) |
| `Mariah_CMS/storage/uploads` | `755` / `775` |
| `Mariah_CMS/storage/logs` | `755` / `775` |

Tick **apply to subdirectories**.

---

## Step 3 — Create `.env`

In File Manager, open `public_html/renziebassig/yourmajestyspa/Mariah_CMS/`.

1. Right-click `.env.example` → **Copy**, then rename the copy to `.env`.
   (If hidden files aren't visible: File Manager **Settings** → **Show hidden files**.)
2. Right-click `.env` → **Edit** and fill it in:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS

DB_HOST=localhost
DB_PORT=3306
DB_NAME=u123456789_majesty_cms
DB_USER=u123456789_majesty
DB_PASS=the-password-you-generated-in-step-1
DB_CHARSET=utf8mb4

SESSION_SECRET=paste-a-long-random-string-here
SESSION_NAME=mariah_cms_session
SESSION_IDLE_TIMEOUT=28800
SESSION_COOKIE_SECURE=true

STORAGE_PATH=storage/uploads
STORAGE_URL=/renziebassig/yourmajestyspa/Mariah_CMS/storage/uploads
UPLOAD_MAX_BYTES=5242880

LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_SECONDS=900

ADMIN_EMAIL=you@yourdomain.com
ADMIN_PASSWORD=a-strong-temporary-password
ADMIN_FIRST_NAME=Majesty
ADMIN_LAST_NAME=Administrator

PUBLIC_CORS_ORIGINS=

SETUP_TOKEN=
```

### Getting these right

- **`DB_HOST=localhost`** — correct on Hostinger shared hosting. Only change it if
  hPanel explicitly shows a different host (some Cloud plans do).
- **`DB_NAME` / `DB_USER`** — must include the `u123456789_` prefix, exactly as hPanel
  lists them.
- **`APP_URL`** — full URL of the `Mariah_CMS` folder, **no trailing slash**. Seeded
  image URLs are built from this, so a wrong value means broken thumbnails.
- **`SESSION_COOKIE_SECURE=true`** requires HTTPS. Hostinger gives free SSL — hPanel →
  **Security → SSL**. If SSL isn't active yet, sign-in will appear to fail silently:
  the browser is discarding the cookie. Turn on SSL, don't set this to `false` on a
  live site.
- **`SESSION_SECRET`** — any long random string. Generate one at
  <https://www.random.org/strings/> (64 chars, alphanumeric), or use the value the
  installer offers you in Step 4.

---

## Step 4 — Install the database

### Path A — Browser installer (works on every plan)

1. Add a setup token to `.env`. Any long random string:
   ```ini
   SETUP_TOKEN=9f2c41ab88e05d3610b84fc9ae2d7b58
   ```
   *(Generate your own — don't use that one.)*

2. Visit, replacing the token with yours:
   ```
   https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS/setup.php?token=9f2c41ab77e05d3610b84fc9ae2d7b58
   ```
   Without the matching token the page refuses to do anything, so it isn't a way in.
   If `SETUP_TOKEN` is empty it generates a suggestion you can copy.

3. **Step 1 — Server environment.** Everything should be green. A red ✕ must be fixed
   before continuing (usually a PHP version or extension in hPanel). An amber `!` is a
   warning you can proceed past.

4. **Step 2 — Database schema.** Click **Create / update tables**. All seven migrations
   should report as applied.

5. **Step 3 — Roles, administrator and content.** Confirm the admin email, set a
   password, **choose the timezone**, and click **Seed roles, administrator and content**.
   Leave the demo accounts off unless you want to test role restrictions — they share a
   password that is published in this repository.

   The timezone matters more than it looks: Hostinger's MySQL runs on UTC, and without
   this setting the audit log timestamps and the login lockout window would both be
   several hours out. Pick the spa's own zone (`America/New_York`). If you get it wrong,
   the **Timezone** card further down `setup.php` fixes it, and afterwards a Super Admin
   can change it under **Settings → Site settings**.

6. **Step 4 — Finish up**, then follow Step 6 below.

### Path B — SSH (Premium / Business / Cloud / VPS)

hPanel → **Advanced → SSH Access**, enable it, and note the host, port and username.

```bash
ssh -p 65002 u123456789@your-server-ip

cd public_html/renziebassig/yourmajestyspa/Mariah_CMS

# Hostinger's default `php` can be an older version — check, and use an
# explicit binary if needed:
php -v
# e.g. /usr/bin/php8.1 database/migrate.php

php database/migrate.php
php database/seed.php
```

Add `--demo` to the seed if you want the test accounts.

> **Why not import a .sql file through phpMyAdmin?** The schema alone would import
> fine, but your administrator password has to be bcrypt-hashed by PHP — a plain SQL
> file cannot do that, and pasting a pre-computed hash would mean shipping a known
> password. Both paths above hash it properly on your own server.

---

## Step 5 — Check it works

1. **Public API** — open in a browser:
   ```
   https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS/api/public/bootstrap
   ```
   You should get JSON starting `{"success":true,"data":{"categories":[…`

   If you get a 404, see [Troubleshooting](#troubleshooting).

2. **Dashboard** — `…/Mariah_CMS/admin/` → sign in → the dashboard shows real counts.

3. **The website** — load `mds_version_a.html`. It should look exactly as before, but
   the content now comes from the database. Confirm it in the browser console:
   ```js
   document.documentElement.dataset.cms   // "live" = from the CMS, "fallback" = built-in copy
   ```

4. **The full loop** — in the CMS, deactivate a service. Reload the website: it's gone.
   Reactivate it: it's back. That is the whole system working end to end.

5. **Optional, if you have SSH** — run the full test suite:
   ```bash
   php database/seed.php --demo
   php tests/smoke.php
   ```

---

## Step 6 — Lock it down

Do all of these before telling anyone the site is live.

1. **Change the admin password** — sign in → **Settings → Change your password**.
2. **Edit `.env`** and blank out both:
   ```ini
   ADMIN_PASSWORD=
   SETUP_TOKEN=
   ```
3. **Delete `setup.php`** from the server. It is inert without a token, but there is no
   reason to leave an installer on a live site.
4. **Delete the demo accounts** if you created them — Users → delete the three
   `@demo.local` accounts. Their password is published in this repository.
5. **Verify nothing sensitive is reachable.** Each of these must return 403 or 404,
   never file contents:
   ```
   https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS/.env
   https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS/config/bootstrap.php
   https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS/app/Core/Database.php
   https://yourdomain.com/renziebassig/yourmajestyspa/Mariah_CMS/storage/logs/
   ```
   If any of them shows content, `.htaccess` isn't being applied — see below.
6. **Confirm SSL is on** and the site redirects HTTP → HTTPS (hPanel → **Security →
   SSL** → *Force HTTPS*).

---

## Troubleshooting

**`/api/public/bootstrap` returns 404**

`mod_rewrite` isn't applying. Test the direct form:
```
…/Mariah_CMS/api/index.php/public/bootstrap
```
If *that* works, the code is fine and only the rewrite is failing. Check for a
`.htaccess` in `public_html/` or `renziebassig/` with a catch-all rule that intercepts
the path first. As a stopgap you can point the website at the direct form: in
`mds_version_a.html`, change

```js
var API = 'Mariah_CMS/api/public/bootstrap';
```
to
```js
var API = 'Mariah_CMS/api/index.php/public/bootstrap';
```

**500 Internal Server Error on every page**

Almost always `.htaccess`. Rename `Mariah_CMS/.htaccess` to `.htaccess.bak` and reload.
If the 500 clears, your server rejects one of its directives — tell me which and I'll
adjust it. Also check `storage/logs/` and hPanel → **Advanced → Error log**.

**Database connection errors**

`setup.php` shows a **Test database credentials** panel whenever the connection is
failing. Use it — you can try values without editing `.env`, and it tells you which
stage failed rather than giving one generic refusal. The password you type there is
used for the test only and is not saved.

The MySQL error code narrows it down a lot:

| Error | What it means | Fix |
|---|---|---|
| **1045** — `Access denied for user ... (using password: YES)` | MySQL rejected the **username/password pair**. This happens *before* the database is looked at, so a missing database is not the cause. | Reset the password in hPanel → **Databases → Management** → the user's **⋮ → Change password**, and paste the new value into `DB_PASS`. Also confirm `DB_USER` is spelled exactly as hPanel lists it, prefix included. |
| **1044** — `Access denied for user ... to database ...` | Credentials are correct, but that user has **no grant** on that database. | In hPanel, check the user is listed against the database. If not, delete and recreate the database — Hostinger creates and grants the user in one step. |
| **1049** — `Unknown database` | Credentials are correct; the **database name** is wrong or it was never created. | Create it in hPanel and make `DB_NAME` match the full prefixed name. |
| **2002 / 2003** | Cannot reach the MySQL server at that host. | `DB_HOST` should be `localhost` on Hostinger shared hosting. |

Most 1045s on Hostinger come down to one of these:

1. **The password was never captured.** hPanel shows a generated password only at
   creation time. If you did not copy it, you cannot recover it — reset it.
2. **The user exists from an earlier attempt with a different password.** Creating a new
   database with the same username does *not* reset that user's password.
3. **The username is not what you think.** hPanel prefixes what you type, and can
   truncate it. Copy the username from the database list rather than retyping it.
4. **A stray character in `.env`.** No quotes are needed around the password. Leading and
   trailing spaces are stripped automatically, but a smart quote or a line break pasted
   into the middle of the value is not.

**"The database does not exist and this MySQL user is not allowed to create it"**

You skipped Step 1. Create the database in hPanel first — shared-hosting MySQL users
cannot create databases themselves.

**Sign-in does nothing — the page just reloads**

The session cookie is being discarded. Either turn on SSL (preferred), or temporarily
set `SESSION_COOKIE_SECURE=false` to confirm that's the cause. Don't leave it `false`
on a live site.

**Images upload but appear broken**

`STORAGE_URL` doesn't match where the files actually are. It must be the browser path
to `Mariah_CMS/storage/uploads`, starting with `/`.

**Seeded thumbnails are broken, but new uploads work**

`APP_URL` was empty or wrong when you seeded. Fix it in `.env`, then in the CMS go to
**Media** and check the image URLs, or re-run the seed after clearing the `media` table.

**Uploads fail on larger images**

hPanel → **Advanced → PHP Configuration → PHP options**: raise `upload_max_filesize`
and `post_max_size` to 8M or more. Keep `UPLOAD_MAX_BYTES` in `.env` at or below
whatever PHP allows.

---

## Ongoing maintenance

**Backups.** Two things must be backed up together:
- the **database** — hPanel → **Files → Backups**, or export via phpMyAdmin
- **`Mariah_CMS/storage/uploads/`** — the only copy of every uploaded image, and not in
  version control

**Updating the code.** Upload the changed files. If a release adds migrations, run
`php database/migrate.php`, or re-enable `SETUP_TOKEN` briefly and use setup.php Step 2.
Never overwrite `.env` or `storage/`.

**Rotating credentials.** Change the database password in hPanel and update `DB_PASS`.
Changing `SESSION_SECRET` signs everyone out, which is a fine thing to do if you
suspect a session was compromised.

---

## One thing worth fixing separately

`POS/config.php` and `POS/config_app.php` in this repository contain live database
credentials in plain text, and the repo has a GitHub remote. Anyone with access to that
repository has those credentials. Mariah_CMS deliberately keeps its own credentials in
a gitignored `.env`, but the POS ones are already exposed — worth rotating those
passwords in hPanel and moving them out of version control when you get a chance.
