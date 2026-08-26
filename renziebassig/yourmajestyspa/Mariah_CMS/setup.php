<?php
declare(strict_types=1);

/**
 * Browser installer.
 *
 * Shared hosting plans without SSH cannot run database/migrate.php or
 * database/seed.php, so this runs the same shared Installer through a web page.
 *
 * ACCESS CONTROL
 *   Requires ?token=<SETUP_TOKEN> matching the value in .env. Without a
 *   SETUP_TOKEN set, this page refuses to do anything at all — so an installer
 *   left on a live server is inert rather than a way in.
 *
 * DELETE THIS FILE once the CMS is installed, or clear SETUP_TOKEN from .env.
 */

// Bootstrap throws when .env is missing — which is exactly the state a first-time
// visitor arrives in. Catching it here turns a blank 500 into instructions.
try {
    require_once __DIR__ . '/config/bootstrap.php';
} catch (\Throwable $bootError) {
    $exampleExists = is_readable(__DIR__ . '/.env.example');

    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="robots" content="noindex, nofollow">
      <title>Mariah_CMS — Configuration needed</title>
      <link rel="stylesheet" href="admin/assets/css/admin.css">
    </head>
    <body>
      <div style="max-width:720px;margin:0 auto;padding:3rem 1.25rem">
        <div style="font-family:var(--f-display);font-size:.95rem;letter-spacing:.3em;
                    text-transform:uppercase;color:var(--gold-dp)">Majesty Day Spa</div>
        <h1 style="font-size:2.2rem;margin:.35rem 0 1.5rem">Configuration needed</h1>

        <div class="card">
          <div class="card__body">
            <p style="margin-top:0">
              Mariah_CMS cannot start because its <code>.env</code> file is missing or unreadable.
            </p>
            <p><b>To fix this:</b></p>
            <ol style="padding-left:1.15rem;color:var(--text-soft);line-height:2">
              <li>Open <code>Mariah_CMS/</code> in your hosting file manager.</li>
              <li>Turn on <b>show hidden files</b> if you cannot see <code>.env.example</code>.</li>
              <li><?= $exampleExists
                    ? 'Copy <code>.env.example</code> and rename the copy to <code>.env</code>.'
                    : 'Create a file named <code>.env</code> (the <code>.env.example</code> template is missing — re-upload it).' ?></li>
              <li>Edit <code>.env</code> and fill in <code>DB_NAME</code>, <code>DB_USER</code>,
                  <code>DB_PASS</code>, <code>APP_URL</code> and <code>SESSION_SECRET</code>.</li>
              <li>Reload this page.</li>
            </ol>
            <p class="muted" style="font-size:.86rem;margin-bottom:0">
              Step-by-step instructions for Hostinger are in
              <code>Mariah_CMS/DEPLOY-HOSTINGER.md</code>.
            </p>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card__body">
            <p style="margin:0;font-size:.86rem;color:var(--text-faint)">
              <b>Details:</b> <?= htmlspecialchars($bootError->getMessage(), ENT_QUOTES, 'UTF-8') ?>
            </p>
          </div>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

use Mariah\Core\Env;
use Mariah\Services\Installer;

// ---------------------------------------------------------------------
// Gate
// ---------------------------------------------------------------------
$configuredToken = Env::string('SETUP_TOKEN', '');
$providedToken   = (string) ($_GET['token'] ?? $_POST['token'] ?? '');

$authorised = $configuredToken !== ''
    && strlen($configuredToken) >= 16
    && hash_equals($configuredToken, $providedToken);

$installer  = new Installer();
$action     = (string) ($_POST['action'] ?? '');
$logLines   = [];
$errorText  = '';
$successMsg = '';
$testResult = null;

// The connection tester deliberately runs BEFORE ensureDatabase(), because its
// whole purpose is to diagnose a connection that is currently failing.
if ($authorised && $action === 'test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $testResult = Installer::testConnection(
        trim((string) ($_POST['t_host'] ?? 'localhost')) ?: 'localhost',
        (int) ($_POST['t_port'] ?? 3306) ?: 3306,
        trim((string) ($_POST['t_database'] ?? '')),
        trim((string) ($_POST['t_user'] ?? '')),
        (string) ($_POST['t_password'] ?? '')
    );
    $action = '';   // do not fall through to the install actions
}

if ($authorised && $action !== '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $installer->ensureDatabase();

        switch ($action) {
            case 'migrate':
                $installer->migrate();
                $successMsg = 'Schema is up to date.';
                break;

            case 'fresh':
                if (($_POST['confirm'] ?? '') !== 'DELETE EVERYTHING') {
                    throw new \RuntimeException(
                        'Type DELETE EVERYTHING exactly, to confirm you want to drop all tables.'
                    );
                }
                $installer->dropAllTables();
                $installer->migrate();
                $successMsg = 'Database rebuilt from scratch. Now run the seed.';
                break;

            case 'seed':
                // Clicking step 3 before step 2 is an easy mistake; say so plainly
                // rather than surfacing a raw "table doesn't exist" error.
                foreach ($installer->migrationStatus() as $migration) {
                    if (!$migration['applied']) {
                        throw new \RuntimeException(
                            'The database tables have not been created yet. '
                            . 'Run step 2 ("Create / update tables") first.'
                        );
                    }
                }

                $installer->syncRolesAndPermissions();

                $adminId = $installer->createSuperAdmin(
                    trim((string) ($_POST['admin_email'] ?? '')) ?: null,
                    (string) ($_POST['admin_password'] ?? '') ?: null,
                    trim((string) ($_POST['admin_first_name'] ?? '')) ?: null,
                    trim((string) ($_POST['admin_last_name'] ?? '')) ?: null
                );

                if (($_POST['demo'] ?? '') === '1') {
                    $installer->createDemoAccounts($adminId);
                }

                $installer->seedContent($adminId);
                $successMsg = 'Content seeded. You can sign in now.';
                break;

            case 'sync':
                $installer->syncRolesAndPermissions();
                $successMsg = 'Roles and permissions re-synced. No content was touched.';
                break;

            default:
                throw new \RuntimeException('Unknown action.');
        }
    } catch (\Throwable $e) {
        $errorText = $e->getMessage();
    }

    $logLines = $installer->getLog();
}

// ---------------------------------------------------------------------
// State for rendering
// ---------------------------------------------------------------------
$checks      = [];
$dbReachable = false;
$dbError     = '';
$installed   = false;
$migrations  = [];

if ($authorised) {
    $checks = Installer::environmentChecks();

    try {
        $installer->ensureDatabase();
        $dbReachable = true;
        $installed   = $installer->isInstalled();
        $migrations  = $installer->migrationStatus();
    } catch (\Throwable $e) {
        $dbError = $e->getMessage();
    }
}

$adminUrl = rtrim(Env::string('APP_URL', '.'), '/') . '/admin/';

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Mariah_CMS — Installer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Lato:wght@300;400;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="admin/assets/css/admin.css">
  <style>
    body { background: var(--bg); }
    .wrap { max-width: 880px; margin: 0 auto; padding: clamp(1.5rem, 4vw, 3.5rem) 1.25rem 5rem; }
    .mark { font-family: var(--f-display); font-size: .95rem; letter-spacing: .3em;
            text-transform: uppercase; color: var(--gold-dp); }
    h1 { font-size: clamp(1.9rem, 4vw, 2.6rem); margin: .35rem 0 .5rem; }
    .lede { color: var(--text-soft); max-width: 62ch; margin: 0 0 2rem; }
    .step { display: flex; gap: .6rem; align-items: baseline; }
    .step__n { font-family: var(--f-display); font-size: 1.5rem; color: var(--gold-dp); line-height: 1; }
    .checklist { list-style: none; margin: 0; padding: 0; }
    .checklist li { display: flex; gap: .75rem; align-items: flex-start; padding: .55rem 0;
                    border-bottom: 1px solid var(--line); font-size: .89rem; }
    .checklist li:last-child { border-bottom: 0; }
    .tick { width: 20px; flex: none; font-weight: 700; text-align: center; }
    .tick--ok { color: var(--ok); }
    .tick--warn { color: var(--warn); }
    .tick--bad { color: var(--danger); }
    .checklist small { display: block; color: var(--text-faint); }
    pre.log { background: var(--emerald-deep); color: #cfe6dd; padding: 1.1rem 1.25rem;
              border-radius: var(--r); font-size: .82rem; line-height: 1.65; overflow-x: auto;
              margin: 0; white-space: pre-wrap; }
    code.inline { background: var(--muted-bg); padding: .1rem .35rem; border-radius: 3px; font-size: .85em; }
    .danger-zone { border-color: rgba(163, 59, 44, .35); }
    .danger-zone .card__head { background: var(--danger-bg); border-bottom-color: rgba(163, 59, 44, .25); }
    .danger-zone .card__head h3 { color: var(--danger); }
  </style>
</head>

<body>
  <div class="wrap">

    <div class="mark">Majesty Day Spa</div>
    <h1>Mariah_CMS Installer</h1>

    <?php if (!$authorised): ?>
      <p class="lede">
        This page installs the CMS database. It is locked until you supply the setup token.
      </p>

      <div class="card">
        <div class="card__head"><h3>Locked</h3></div>
        <div class="card__body">
          <?php if ($configuredToken === ''): ?>
            <p style="margin-top:0">
              No <code class="inline">SETUP_TOKEN</code> is set in <code class="inline">.env</code>,
              so this installer will not run.
            </p>
            <p><b>To unlock it:</b></p>
            <ol style="padding-left:1.15rem;color:var(--text-soft);font-size:.9rem;line-height:1.9">
              <li>Open <code class="inline">Mariah_CMS/.env</code> in your file manager.</li>
              <li>Add a line with a long random value, for example:<br>
                <code class="inline">SETUP_TOKEN=<?= h(bin2hex(random_bytes(16))) ?></code>
                <small class="muted"> — that one was generated just now; you can copy it.</small>
              </li>
              <li>Reload this page as
                <code class="inline">setup.php?token=YOUR_TOKEN</code>.</li>
            </ol>
            <p class="muted" style="font-size:.85rem;margin-bottom:0">
              Delete <code class="inline">setup.php</code>, or clear <code class="inline">SETUP_TOKEN</code>,
              once the CMS is installed.
            </p>
          <?php elseif (strlen($configuredToken) < 16): ?>
            <p style="margin-top:0" class="form-error">
              Your <code class="inline">SETUP_TOKEN</code> is shorter than 16 characters.
              Use a longer random value — a short token is guessable.
            </p>
          <?php else: ?>
            <p style="margin-top:0">Add your setup token to continue.</p>
            <form method="get" class="grid" style="margin-top:1rem">
              <div class="field col-8">
                <label for="token">Setup token</label>
                <input type="text" id="token" name="token" autocomplete="off" autofocus
                       placeholder="the SETUP_TOKEN value from .env">
              </div>
              <div class="col-4" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn--block">Unlock</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>

    <?php else: ?>
      <p class="lede">
        Run these steps in order. Everything here is safe to repeat — migrations are
        recorded once applied, and seeding skips records that already exist.
      </p>

      <?php if ($errorText !== ''): ?>
        <div class="form-error" style="margin-bottom:1.5rem"><b>That step failed.</b><br><?= nl2br(h($errorText)) ?></div>
      <?php endif; ?>

      <?php if ($successMsg !== ''): ?>
        <div class="form-error" style="margin-bottom:1.5rem;background:var(--ok-bg);
             border-color:rgba(46,111,82,.3);color:var(--ok)"><b><?= h($successMsg) ?></b></div>
      <?php endif; ?>

      <?php if ($logLines !== []): ?>
        <div class="card mb-2">
          <div class="card__head"><h3>Output</h3></div>
          <div class="card__body"><pre class="log"><?= h(implode("\n", $logLines)) ?></pre></div>
        </div>
      <?php endif; ?>

      <!-- ============ 1. Environment ============ -->
      <div class="card mb-2">
        <div class="card__head">
          <div class="step"><span class="step__n">1</span><h3>Server environment</h3></div>
        </div>
        <div class="card__body">
          <ul class="checklist">
            <?php foreach ($checks as $check): ?>
              <li>
                <span class="tick <?= $check['ok'] ? 'tick--ok' : ($check['fatal'] ? 'tick--bad' : 'tick--warn') ?>">
                  <?= $check['ok'] ? '✓' : ($check['fatal'] ? '✕' : '!') ?>
                </span>
                <span>
                  <?= h($check['label']) ?>
                  <small><?= h($check['detail']) ?></small>
                </span>
              </li>
            <?php endforeach; ?>
            <li>
              <span class="tick <?= $dbReachable ? 'tick--ok' : 'tick--bad' ?>"><?= $dbReachable ? '✓' : '✕' ?></span>
              <span>
                Database connection
                <small><?= $dbReachable
                  ? 'Connected to ' . h(Env::string('DB_NAME'))
                  : nl2br(h($dbError ?: 'Not reachable')) ?></small>
              </span>
            </li>
          </ul>
        </div>
      </div>

      <?php if (!$dbReachable || $testResult !== null): ?>
        <!-- ============ Connection tester ============ -->
        <div class="card mb-2">
          <div class="card__head"><h3>Test database credentials</h3></div>
          <div class="card__body">
            <?php if ($testResult !== null): ?>
              <div class="form-error" style="margin-bottom:1.5rem;<?= $testResult['ok']
                    ? 'background:var(--ok-bg);border-color:rgba(46,111,82,.3);color:var(--ok)' : '' ?>">
                <b><?= h($testResult['stage']) ?></b><br>
                <?= nl2br(h($testResult['message'])) ?>
                <?php if ($testResult['detail'] !== ''): ?>
                  <br><span style="opacity:.75;font-size:.9em"><?= h($testResult['detail']) ?></span>
                <?php endif; ?>
              </div>

              <?php if ($testResult['ok']): ?>
                <p style="margin-top:0">
                  Copy these into <code class="inline">.env</code>, then reload this page:
                </p>
                <pre class="log">DB_HOST=<?= h((string) ($_POST['t_host'] ?? 'localhost')) ?>

DB_PORT=<?= h((string) ($_POST['t_port'] ?? '3306')) ?>

DB_NAME=<?= h((string) ($_POST['t_database'] ?? '')) ?>

DB_USER=<?= h((string) ($_POST['t_user'] ?? '')) ?>

DB_PASS=<?= h((string) ($_POST['t_password'] ?? '')) ?></pre>
              <?php endif; ?>
            <?php else: ?>
              <p style="margin-top:0;color:var(--text-soft);font-size:.9rem">
                Try a set of credentials without editing <code class="inline">.env</code>.
                This reports which stage fails — authentication, database access, or the
                database name — so you know exactly what to change in your control panel.
              </p>
            <?php endif; ?>

            <form method="post" class="grid" autocomplete="off">
              <input type="hidden" name="token" value="<?= h($providedToken) ?>">
              <input type="hidden" name="action" value="test">

              <div class="field col-8">
                <label for="t_host">Host</label>
                <input type="text" id="t_host" name="t_host"
                       value="<?= h((string) ($_POST['t_host'] ?? Env::string('DB_HOST', 'localhost'))) ?>">
                <small class="field__hint">Almost always <code>localhost</code> on shared hosting.</small>
              </div>

              <div class="field col-4">
                <label for="t_port">Port</label>
                <input type="text" id="t_port" name="t_port"
                       value="<?= h((string) ($_POST['t_port'] ?? Env::string('DB_PORT', '3306'))) ?>">
              </div>

              <div class="field col-12">
                <label for="t_database">Database name</label>
                <input type="text" id="t_database" name="t_database"
                       value="<?= h((string) ($_POST['t_database'] ?? Env::string('DB_NAME'))) ?>"
                       placeholder="u123456789_majesty_cms">
                <small class="field__hint">Include the account prefix, exactly as your control panel lists it.</small>
              </div>

              <div class="field col-6">
                <label for="t_user">Username</label>
                <input type="text" id="t_user" name="t_user"
                       value="<?= h((string) ($_POST['t_user'] ?? Env::string('DB_USER'))) ?>"
                       placeholder="u123456789_majesty">
              </div>

              <div class="field col-6">
                <label for="t_password">Password</label>
                <input type="password" id="t_password" name="t_password" autocomplete="new-password">
                <small class="field__hint">Not saved anywhere — used only for this test.</small>
              </div>

              <div class="col-12">
                <button type="submit" class="btn">Test connection</button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($dbReachable): ?>

        <!-- ============ 2. Schema ============ -->
        <div class="card mb-2">
          <div class="card__head">
            <div class="step"><span class="step__n">2</span><h3>Database schema</h3></div>
          </div>
          <div class="card__body">
            <ul class="checklist" style="margin-bottom:1.25rem">
              <?php foreach ($migrations as $migration): ?>
                <li>
                  <span class="tick <?= $migration['applied'] ? 'tick--ok' : 'tick--warn' ?>">
                    <?= $migration['applied'] ? '✓' : '·' ?>
                  </span>
                  <span>
                    <?= h($migration['filename']) ?>
                    <small><?= $migration['applied']
                      ? 'Applied ' . h($migration['applied_at'])
                      : 'Not yet applied' ?></small>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>

            <form method="post">
              <input type="hidden" name="token" value="<?= h($providedToken) ?>">
              <input type="hidden" name="action" value="migrate">
              <button type="submit" class="btn">Create / update tables</button>
            </form>
          </div>
        </div>

        <!-- ============ 3. Seed ============ -->
        <div class="card mb-2">
          <div class="card__head">
            <div class="step"><span class="step__n">3</span><h3>Roles, administrator and content</h3></div>
          </div>
          <div class="card__body">
            <?php if ($installed): ?>
              <p style="margin-top:0;color:var(--ok)">
                <b>Already installed.</b> Re-running the seed is harmless — it skips
                everything that already exists.
              </p>
            <?php endif; ?>

            <p style="margin-top:0;color:var(--text-soft);font-size:.9rem">
              This creates the four roles with their permissions, your Super Admin account,
              and the full Majesty Day Spa content — 6 categories, 16 services with real
              prices and booking links, 3 specials, 3 promotions, 3 Journal articles, the shop
              and the gift cards.
            </p>

            <form method="post" class="grid" style="margin-top:1.25rem">
              <input type="hidden" name="token" value="<?= h($providedToken) ?>">
              <input type="hidden" name="action" value="seed">

              <div class="field col-6">
                <label for="admin_email">Administrator email</label>
                <input type="email" id="admin_email" name="admin_email"
                       value="<?= h(Env::string('ADMIN_EMAIL')) ?>"
                       placeholder="you@majestydayspa.com">
                <small class="field__hint">Leave blank to use ADMIN_EMAIL from .env.</small>
              </div>

              <div class="field col-6">
                <label for="admin_password">Administrator password</label>
                <input type="password" id="admin_password" name="admin_password"
                       autocomplete="new-password" placeholder="at least 10 characters">
                <small class="field__hint">Leave blank to use ADMIN_PASSWORD from .env.</small>
              </div>

              <div class="field col-6">
                <label for="admin_first_name">First name</label>
                <input type="text" id="admin_first_name" name="admin_first_name"
                       value="<?= h(Env::string('ADMIN_FIRST_NAME', 'Majesty')) ?>">
              </div>

              <div class="field col-6">
                <label for="admin_last_name">Last name</label>
                <input type="text" id="admin_last_name" name="admin_last_name"
                       value="<?= h(Env::string('ADMIN_LAST_NAME', 'Administrator')) ?>">
              </div>

              <div class="field col-12">
                <label class="switch">
                  <input type="checkbox" name="demo" value="1">
                  <span class="switch__track"></span>
                  <span class="switch__text">Also create demo Admin / Editor / Staff accounts
                    <small>For testing role restrictions. They share a published password —
                      delete them before going live.</small></span>
                </label>
              </div>

              <div class="col-12">
                <button type="submit" class="btn">Seed roles, administrator and content</button>
              </div>
            </form>
          </div>
        </div>

        <!-- ============ 4. Finish ============ -->
        <?php if ($installed): ?>
          <div class="card mb-2">
            <div class="card__head">
              <div class="step"><span class="step__n">4</span><h3>Finish up</h3></div>
            </div>
            <div class="card__body">
              <ol style="padding-left:1.15rem;color:var(--text-soft);font-size:.9rem;line-height:2;margin-top:0">
                <li><a href="<?= h($adminUrl) ?>" target="_blank" rel="noopener">Open the dashboard</a>
                    and sign in.</li>
                <li>Change your password under <b>Settings → Change your password</b>.</li>
                <li>Remove <code class="inline">ADMIN_PASSWORD</code> and
                    <code class="inline">SETUP_TOKEN</code> from <code class="inline">.env</code>.</li>
                <li><b>Delete <code class="inline">setup.php</code> from the server.</b></li>
                <li>Load the public site and confirm the services now come from the CMS.</li>
              </ol>
            </div>
          </div>
        <?php endif; ?>

        <!-- ============ Maintenance ============ -->
        <div class="card mb-2">
          <div class="card__head"><h3>Maintenance</h3></div>
          <div class="card__body">
            <p style="margin-top:0;color:var(--text-soft);font-size:.9rem">
              Run this after editing <code class="inline">config/permissions.php</code> to push
              permission changes into the database. Content is not touched.
            </p>
            <form method="post">
              <input type="hidden" name="token" value="<?= h($providedToken) ?>">
              <input type="hidden" name="action" value="sync">
              <button type="submit" class="btn btn--ghost">Re-sync roles &amp; permissions</button>
            </form>
          </div>
        </div>

        <!-- ============ Danger zone ============ -->
        <div class="card danger-zone">
          <div class="card__head"><h3>Danger zone</h3></div>
          <div class="card__body">
            <p style="margin-top:0;font-size:.9rem">
              Drops <b>every table</b> in <code class="inline"><?= h(Env::string('DB_NAME')) ?></code>
              and rebuilds the schema from scratch. All services, promotions, media records,
              users and audit history are destroyed. There is no undo.
            </p>
            <form method="post" class="grid" style="margin-top:1rem">
              <input type="hidden" name="token" value="<?= h($providedToken) ?>">
              <input type="hidden" name="action" value="fresh">
              <div class="field col-8">
                <label for="confirm">Type <b>DELETE EVERYTHING</b> to confirm</label>
                <input type="text" id="confirm" name="confirm" autocomplete="off">
              </div>
              <div class="col-4" style="display:flex;align-items:flex-end">
                <button type="submit" class="btn btn--danger btn--block">Drop &amp; rebuild</button>
              </div>
            </form>
          </div>
        </div>

      <?php endif; ?>
    <?php endif; ?>

    <p class="muted" style="font-size:.8rem;margin-top:2.5rem;text-align:center">
      Mariah_CMS · Delete this file once installation is complete.
    </p>
  </div>
</body>

</html>
