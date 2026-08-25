<?php
declare(strict_types=1);

/**
 * Diagnostics page.
 *
 * Answers one question: when an admin list comes back empty, which layer is
 * responsible — the database, the repository query, or the HTTP/session hop?
 *
 * It reports three views of the same data side by side:
 *   1. Raw table counts, straight from SQL with no filtering.
 *   2. The result of the very same paginate() call the admin list endpoint
 *      makes, run here in-process with no HTTP involved.
 *   3. A live fetch of /api/... from your browser, carrying your real session
 *      cookie, so the response the SPA actually receives is visible verbatim.
 *
 * Wherever those three first disagree is the layer at fault.
 *
 * ACCESS CONTROL
 *   Requires ?token=<SETUP_TOKEN> matching .env, exactly like setup.php.
 *   Without SETUP_TOKEN set, this page does nothing at all.
 *
 * DELETE THIS FILE once the problem is found.
 */

try {
    require_once __DIR__ . '/config/bootstrap.php';
} catch (\Throwable $bootError) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Mariah_CMS could not start.\n\n";
    echo $bootError->getMessage() . "\n\n";
    echo "This almost always means Mariah_CMS/.env is missing or unreadable.\n";
    echo "See setup.php for the fix.\n";
    exit;
}

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Core\Request;

// ---------------------------------------------------------------------
// Gate — identical rules to setup.php
// ---------------------------------------------------------------------
$configuredToken = Env::string('SETUP_TOKEN', '');
$providedToken   = (string) ($_GET['token'] ?? '');

$authorised = $configuredToken !== ''
    && strlen($configuredToken) >= 16
    && hash_equals($configuredToken, $providedToken);

if (!$authorised) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Locked.\n\n";
    echo $configuredToken === ''
        ? "No SETUP_TOKEN is set in .env, so this page will not run.\n"
          . "Add one, then reload as debug.php?token=YOUR_TOKEN\n"
        : "Add ?token=YOUR_TOKEN to the URL.\n";
    exit;
}

function h(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Runs a probe and turns any failure into a printable row rather than a 500. */
function attempt(callable $fn): array
{
    try {
        return ['ok' => true, 'value' => $fn(), 'error' => ''];
    } catch (\Throwable $e) {
        return ['ok' => false, 'value' => null, 'error' => get_class($e) . ': ' . $e->getMessage()];
    }
}

// The session is read so this page can report the identity the admin API would
// see. Same cookie name, same origin — so opening this in the tab where you are
// already signed in shows your real session, not a fresh one.
Auth::startSession();

// ---------------------------------------------------------------------
// 1. Connection
// ---------------------------------------------------------------------
$connection = attempt(static function (): array {
    Database::pdo();

    return [
        'database' => (string) Database::fetchValue('SELECT DATABASE()'),
        'version'  => (string) Database::fetchValue('SELECT VERSION()'),
        'user'     => (string) Database::fetchValue('SELECT CURRENT_USER()'),
        'sql_mode' => (string) Database::fetchValue('SELECT @@SESSION.sql_mode'),
    ];
});

// ---------------------------------------------------------------------
// 2. Raw table counts — no filters, no joins, no repository code
// ---------------------------------------------------------------------
$tables = [
    'service_categories', 'services', 'service_images', 'media',
    'promotions', 'specials', 'product_brands', 'product_categories',
    'products', 'gift_cards', 'users', 'roles', 'permissions',
    'role_permissions', 'audit_logs',
];

$rawCounts = [];
foreach ($tables as $table) {
    $rawCounts[$table] = attempt(static function () use ($table): array {
        $columns = array_column(Database::fetchAll("SHOW COLUMNS FROM `{$table}`"), 'Field');

        $row = ['total' => (int) Database::fetchValue("SELECT COUNT(*) FROM `{$table}`")];

        if (in_array('deleted_at', $columns, true)) {
            $row['live']    = (int) Database::fetchValue(
                "SELECT COUNT(*) FROM `{$table}` WHERE deleted_at IS NULL"
            );
            $row['deleted'] = $row['total'] - $row['live'];
        }

        if (in_array('status', $columns, true)) {
            $row['by_status'] = [];
            foreach (Database::fetchAll(
                "SELECT status, COUNT(*) AS n FROM `{$table}` GROUP BY status"
            ) as $s) {
                $row['by_status'][(string) $s['status']] = (int) $s['n'];
            }
        }

        return $row;
    });
}

// ---------------------------------------------------------------------
// 3. The admin list query, run in-process
//
// This is the exact code GET /api/<resource> runs. $_GET is emptied first so
// the Request carries no filters — the same state as a freshly opened list.
// ---------------------------------------------------------------------
$repositories = [
    'services'           => \Mariah\Repositories\ServiceRepository::class,
    'categories'         => \Mariah\Repositories\CategoryRepository::class,
    'promotions'         => \Mariah\Repositories\PromotionRepository::class,
    'specials'           => \Mariah\Repositories\SpecialRepository::class,
    'products'           => \Mariah\Repositories\ProductRepository::class,
    'product-categories' => \Mariah\Repositories\ProductCategoryRepository::class,
    'brands'             => \Mariah\Repositories\BrandRepository::class,
    'gift-cards'         => \Mariah\Repositories\GiftCardRepository::class,
    'media'              => \Mariah\Repositories\MediaRepository::class,
    'users'              => \Mariah\Repositories\UserRepository::class,
];

$savedGet    = $_GET;
$_GET        = [];
$listRequest = new Request();
$_GET        = $savedGet;

$listResults = [];
foreach ($repositories as $label => $class) {
    $listResults[$label] = attempt(static function () use ($class, $listRequest): array {
        /** @var \Mariah\Repositories\BaseRepository $repository */
        $repository = new $class();
        $result     = $repository->paginate($listRequest);

        return [
            'returned' => count($result['rows']),
            'total'    => $result['meta']['total'] ?? null,
            'first'    => $result['rows'][0] ?? null,
        ];
    });
}

// ---------------------------------------------------------------------
// 4. The public query, for comparison
// ---------------------------------------------------------------------
$publicServices = attempt(static fn (): int => (int) Database::fetchValue(
    "SELECT COUNT(*)
       FROM services s
       JOIN service_categories c ON c.id = s.category_id
            AND c.status = 'active' AND c.deleted_at IS NULL
      WHERE s.status = 'active' AND s.deleted_at IS NULL"
));

// ---------------------------------------------------------------------
// 5. Session and identity
// ---------------------------------------------------------------------
$identity = attempt(static function (): array {
    $user = Auth::user();

    return [
        'session_name'       => session_name(),
        'session_id'         => session_id() === '' ? '(none)' : substr(session_id(), 0, 8) . '…',
        'user_id_in_session' => $_SESSION['user_id'] ?? null,
        'resolved_user'      => $user === null ? null : [
            'id'          => $user['id'],
            'email'       => $user['email'],
            'status'      => $user['status'],
            'role'        => $user['role_name'] . ' (' . $user['role_slug'] . ')',
            'permissions' => $user['permissions'],
        ],
        'cookie_secure' => Env::bool('SESSION_COOKIE_SECURE', true),
        'request_https' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ];
});

$appUrl = Env::string('APP_URL', '(not set)');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Mariah_CMS — Diagnostics</title>
  <link rel="stylesheet" href="admin/assets/css/admin.css">
  <style>
    .wrap { max-width: 980px; margin: 0 auto; padding: clamp(1.5rem, 4vw, 3rem) 1.25rem 5rem; }
    h1 { font-size: clamp(1.8rem, 4vw, 2.4rem); margin: .35rem 0 .4rem; }
    .lede { color: var(--text-soft); max-width: 68ch; margin: 0 0 2rem; }
    table.d { width: 100%; border-collapse: collapse; font-size: .86rem; }
    table.d th, table.d td { text-align: left; padding: .5rem .6rem; border-bottom: 1px solid var(--line); }
    table.d thead th { font-weight: 700; color: var(--text-faint); text-transform: uppercase;
                       font-size: .72rem; letter-spacing: .08em; }
    td.n, th.n { text-align: right; font-variant-numeric: tabular-nums; }
    pre.log { background: var(--emerald-deep); color: #cfe6dd; padding: 1rem 1.15rem;
              border-radius: var(--r); font-size: .8rem; line-height: 1.6;
              overflow-x: auto; margin: 0; white-space: pre-wrap; word-break: break-word; }
    .bad { color: var(--danger); font-weight: 700; }
    .good { color: var(--ok); font-weight: 700; }
    .warn { color: var(--warn); font-weight: 700; }
  </style>
</head>

<body>
  <div class="wrap">
    <div style="font-family:var(--f-display);font-size:.9rem;letter-spacing:.3em;
                text-transform:uppercase;color:var(--gold-dp)">Majesty Day Spa</div>
    <h1>Diagnostics</h1>
    <p class="lede">
      Three views of the same data. <b>Raw counts</b> say what is in the tables.
      <b>Admin query</b> runs the identical code the list endpoint runs, with no HTTP in the way.
      <b>Live API call</b> replays the real request from your browser with your session cookie.
      Whichever of the three first shows zero is the layer at fault.
    </p>

    <!-- ============ 1. Connection ============ -->
    <div class="card mb-2">
      <div class="card__head"><h3>1 · Database connection</h3></div>
      <div class="card__body">
        <?php if (!$connection['ok']): ?>
          <p class="bad" style="margin:0">Not connected.</p>
          <pre class="log" style="margin-top:.75rem"><?= h($connection['error']) ?></pre>
        <?php else: ?>
          <table class="d">
            <tr><th>Connected database</th><td><b><?= h($connection['value']['database']) ?></b></td></tr>
            <tr><th>DB_NAME in .env</th><td><?= h(Env::string('DB_NAME')) ?></td></tr>
            <tr><th>MySQL user</th><td><?= h($connection['value']['user']) ?></td></tr>
            <tr><th>Server version</th><td><?= h($connection['value']['version']) ?></td></tr>
            <tr><th>APP_URL</th><td><?= h($appUrl) ?></td></tr>
            <tr><th>sql_mode</th><td style="font-size:.8rem"><?= h($connection['value']['sql_mode']) ?></td></tr>
          </table>
          <?php if ($connection['value']['database'] !== Env::string('DB_NAME')): ?>
            <p class="bad" style="margin:.9rem 0 0">
              The connected database does not match DB_NAME. Two installations are in play.
            </p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ============ 2. Raw counts ============ -->
    <div class="card mb-2">
      <div class="card__head"><h3>2 · Raw table counts</h3></div>
      <div class="card__body">
        <p class="muted" style="margin-top:0;font-size:.85rem">
          Straight <code>COUNT(*)</code>, no joins and no filters. If <b>Total</b> is 0 here,
          the seed never reached this table.
        </p>
        <table class="d">
          <thead>
            <tr><th>Table</th><th class="n">Total</th><th class="n">Live</th>
                <th class="n">Deleted</th><th>By status</th></tr>
          </thead>
          <tbody>
            <?php foreach ($rawCounts as $table => $probe): ?>
              <tr>
                <td><code><?= h($table) ?></code></td>
                <?php if (!$probe['ok']): ?>
                  <td colspan="4" class="bad" style="font-size:.8rem"><?= h($probe['error']) ?></td>
                <?php else: ?>
                  <td class="n <?= $probe['value']['total'] === 0 ? 'bad' : '' ?>">
                    <?= (int) $probe['value']['total'] ?></td>
                  <td class="n"><?= array_key_exists('live', $probe['value'])
                        ? (int) $probe['value']['live'] : '—' ?></td>
                  <td class="n"><?= array_key_exists('deleted', $probe['value'])
                        ? (int) $probe['value']['deleted'] : '—' ?></td>
                  <td>
                    <?php if (!empty($probe['value']['by_status'])):
                        $parts = [];
                        foreach ($probe['value']['by_status'] as $status => $n) {
                            $parts[] = h((string) $status) . ' ' . (int) $n;
                        }
                        echo implode(' · ', $parts);
                    else:
                        echo '—';
                    endif; ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ============ 3. Admin query ============ -->
    <div class="card mb-2">
      <div class="card__head"><h3>3 · Admin list query, run in-process</h3></div>
      <div class="card__body">
        <p class="muted" style="margin-top:0;font-size:.85rem">
          The same <code>paginate()</code> call <code>GET /api/&lt;resource&gt;</code> makes, with no
          filters applied. Non-zero here but empty in the browser means the fault is in the
          HTTP or session hop, not the query.
        </p>
        <table class="d">
          <thead><tr><th>Resource</th><th class="n">Rows returned</th>
                     <th class="n">Meta total</th><th>First row</th></tr></thead>
          <tbody>
            <?php foreach ($listResults as $label => $probe): ?>
              <tr>
                <td><code>/api/<?= h($label) ?></code></td>
                <?php if (!$probe['ok']): ?>
                  <td class="n bad">error</td><td class="n">—</td>
                  <td class="bad" style="font-size:.8rem"><?= h($probe['error']) ?></td>
                <?php else: ?>
                  <td class="n <?= $probe['value']['returned'] === 0 ? 'bad' : 'good' ?>">
                    <?= (int) $probe['value']['returned'] ?></td>
                  <td class="n"><?= $probe['value']['total'] === null
                        ? '—' : (int) $probe['value']['total'] ?></td>
                  <td style="font-size:.8rem;color:var(--text-faint)">
                    <?= $probe['value']['first'] === null
                        ? 'no rows'
                        : h((string) ($probe['value']['first']['name']
                             ?? $probe['value']['first']['title']
                             ?? $probe['value']['first']['file_name']
                             ?? ('#' . ($probe['value']['first']['id'] ?? '?')))) ?>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <p style="margin:1.1rem 0 0;font-size:.86rem">
          Public services query (active only — the one the website uses):
          <b class="<?= ($publicServices['ok'] && $publicServices['value'] > 0) ? 'good' : 'bad' ?>">
            <?= $publicServices['ok'] ? (int) $publicServices['value'] : h($publicServices['error']) ?>
          </b>
        </p>
      </div>
    </div>

    <!-- ============ 4. Session ============ -->
    <div class="card mb-2">
      <div class="card__head"><h3>4 · Your session, as the API sees it</h3></div>
      <div class="card__body">
        <?php if (!$identity['ok']): ?>
          <pre class="log"><?= h($identity['error']) ?></pre>
        <?php else: $id = $identity['value']; ?>
          <table class="d">
            <tr><th>Cookie name</th><td><code><?= h($id['session_name']) ?></code></td></tr>
            <tr><th>Session id</th><td><?= h($id['session_id']) ?></td></tr>
            <tr><th>user_id in session</th>
                <td><?= $id['user_id_in_session'] === null
                      ? '<span class="warn">none — not signed in in this browser</span>'
                      : (int) $id['user_id_in_session'] ?></td></tr>
            <tr><th>SESSION_COOKIE_SECURE</th>
                <td><?= $id['cookie_secure'] ? 'true' : 'false' ?></td></tr>
            <tr><th>This request is HTTPS</th>
                <td><?= $id['request_https'] ? 'yes' : 'no' ?></td></tr>
          </table>

          <?php if ($id['cookie_secure'] && !$id['request_https']): ?>
            <p class="bad" style="margin:.9rem 0 0">
              SESSION_COOKIE_SECURE is on but this page was loaded over plain HTTP, so the
              browser will refuse to store the session cookie. Use HTTPS, or set
              SESSION_COOKIE_SECURE=false while testing.
            </p>
          <?php endif; ?>

          <?php if ($id['resolved_user'] !== null): ?>
            <p style="margin:1.1rem 0 .4rem;font-size:.86rem"><b>Signed in as</b>
              <?= h($id['resolved_user']['email']) ?> ·
              <?= h($id['resolved_user']['role']) ?> ·
              status <?= h($id['resolved_user']['status']) ?></p>
            <p style="margin:0 0 .4rem;font-size:.86rem"><b>Permissions
              (<?= count($id['resolved_user']['permissions']) ?>)</b></p>
            <pre class="log"><?= h(implode(', ', $id['resolved_user']['permissions']) ?: '(none)') ?></pre>
            <?php if ($id['resolved_user']['permissions'] === []): ?>
              <p class="bad" style="margin:.9rem 0 0">
                This role has no permissions attached. Run "Re-sync roles &amp; permissions"
                in setup.php.
              </p>
            <?php endif; ?>
          <?php elseif ($id['user_id_in_session'] !== null): ?>
            <p class="bad" style="margin:.9rem 0 0">
              The session holds user_id <?= (int) $id['user_id_in_session'] ?>, but that user could
              not be resolved — deleted, inactive, or its role row is missing. Every admin
              endpoint will answer 401 for this session.
            </p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ============ 5. Live API ============ -->
    <div class="card">
      <div class="card__head"><h3>5 · Live API call from your browser</h3></div>
      <div class="card__body">
        <p class="muted" style="margin-top:0;font-size:.85rem">
          Same origin, same cookie, same query string the admin table sends. This is the
          response the SPA actually receives.
        </p>
        <div id="probe"><p class="muted">Running…</p></div>
      </div>
    </div>

    <p class="muted" style="font-size:.8rem;margin-top:2.5rem;text-align:center">
      Mariah_CMS · Delete <code>debug.php</code> once the problem is found.
    </p>
  </div>

  <script>
    // The default query an admin table sends when a list is first opened.
    var ENDPOINTS = [
      ['/api/auth/me', ''],
      ['/api/services', '?page=1&per_page=20&sort=display_order&direction=ASC'],
      ['/api/categories', '?page=1&per_page=20'],
      ['/api/products', '?page=1&per_page=20'],
      ['/api/public/services', '']
    ];

    // debug.php sits at the Mariah_CMS root, so the API is a sibling directory.
    var ROOT = window.location.pathname.replace(/\/[^/]*$/, '');

    (async function () {
      var out = document.getElementById('probe');
      out.replaceChildren();

      for (var i = 0; i < ENDPOINTS.length; i++) {
        var path = ENDPOINTS[i][0];
        var query = ENDPOINTS[i][1];
        var url = ROOT + path + query;
        var line = '';

        try {
          var response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
          });

          var text = await response.text();
          var summary;

          try {
            var json = JSON.parse(text);
            var count = Array.isArray(json.data) ? json.data.length : null;

            summary = 'success=' + json.success
              + (count !== null ? '  ·  data[] length = ' + count : '')
              + (json.meta ? '  ·  meta.total = ' + json.meta.total : '')
              + (json.error ? '\n  error: ' + json.error.code + ' — ' + json.error.message : '');
          } catch (parseError) {
            // Non-JSON means PHP emitted an error page instead of the envelope.
            summary = 'NOT JSON — first 400 characters:\n' + text.slice(0, 400);
          }

          line = 'HTTP ' + response.status + ' ' + response.statusText + '\n' + summary;
        } catch (error) {
          line = 'Request failed before a response arrived: ' + error.message;
        }

        var block = document.createElement('div');
        block.style.marginBottom = '1.25rem';

        var heading = document.createElement('p');
        heading.style.cssText = 'margin:0 0 .35rem;font-size:.84rem;font-weight:700';
        heading.textContent = 'GET ' + path + query;

        var pre = document.createElement('pre');
        pre.className = 'log';
        pre.textContent = line;

        block.appendChild(heading);
        block.appendChild(pre);
        out.appendChild(block);
      }
    })();
  </script>
</body>

</html>
