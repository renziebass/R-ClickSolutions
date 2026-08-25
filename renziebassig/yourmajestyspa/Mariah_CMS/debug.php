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

// Seeded image URLs are built from APP_URL, so a malformed APP_URL bakes a
// broken prefix into every media row. Sampling one shows whether that happened.
$sampleMediaUrl = attempt(static fn (): ?string => Database::fetchValue(
    'SELECT file_url FROM media WHERE deleted_at IS NULL ORDER BY id LIMIT 1'
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

// ---------------------------------------------------------------------
// 6. Front-end file integrity
//
// Every admin asset, hashed at the commit this diagnostic was written from.
// Line endings are normalised to LF before hashing, so an FTP client that
// rewrote CRLF in transit does not show up as a false mismatch — only a
// genuine content difference does.
// ---------------------------------------------------------------------
$expectedAssets = [
    'index.html'                    => ['bc5a9c24917ff73c336b34a373721330', 7474],
    'login.html'                    => ['173dfb0cb3a2d232bfa53b1f9ca1c9b8', 4411],
    'assets/css/admin.css'          => ['b46452a1bf42d37549e77182de419fb3', 31796],
    'assets/js/api.js'              => ['62ffd140bc0c4f9a54654e90af977643', 3926],
    'assets/js/app.js'              => ['a91ea9eed14ff0cdf55c4b0e88035e8d', 11815],
    'assets/js/router.js'           => ['204343d11fa10a1d037a47a415e03cd9', 2793],
    'assets/js/session.js'          => ['0b2a57ab5ae0f70a14eaf59857b60f15', 1164],
    'assets/js/pages/audit-logs.js' => ['e8bf8642d6eed2e1e95e0ff98a6b5b9f', 5116],
    'assets/js/pages/categories.js' => ['4ebc5ee5c4928def8a00da779a9a7263', 5993],
    'assets/js/pages/dashboard.js'  => ['a5bd97b773f7edc383904bf8273d5b5e', 8074],
    'assets/js/pages/gift-cards.js' => ['3fa585bf7981e40cd100e56f7332070d', 7259],
    'assets/js/pages/helpers.js'    => ['93d3a1a6db389865df00e86bbb73b594', 8552],
    'assets/js/pages/media.js'      => ['d5e772892306240879dcf34d6c2f2079', 7823],
    'assets/js/pages/promotions.js' => ['8e13c15815264f617216b71c197847ac', 10730],
    'assets/js/pages/roles.js'      => ['1c775e065da9165a5b3ec99358a0a365', 6122],
    'assets/js/pages/services.js'   => ['6d913079b3a1f340cb4473098cc9cd38', 11578],
    'assets/js/pages/settings.js'   => ['509fdcc574029907be129e6a74da3f8e', 4701],
    'assets/js/pages/shop.js'       => ['a7f5662e4a63f2cf6cdbb7131ebda764', 14961],
    'assets/js/pages/specials.js'   => ['7a5e8c0e76412fdd9b3b6ee7e7e63183', 7482],
    'assets/js/pages/users.js'      => ['f086906b4745f012e52e5fc9eae86e76', 8838],
    'assets/js/ui/dom.js'           => ['a6fcf543fb3eaba9a8bca11f261e03dd', 3749],
    'assets/js/ui/feedback.js'      => ['c5f65dd497c46fdf461bae9c272b2a28', 5898],
    'assets/js/ui/form.js'          => ['2e782f4edd61cd70661118a447255f9d', 6274],
    'assets/js/ui/media-picker.js'  => ['d3571cc9d8f25f7c76da1ee2377c8f16', 9879],
    'assets/js/ui/table.js'         => ['1d6465a48854403c45a11d5c1da83631', 10268],
];

$assetChecks  = [];
$assetProblem = 0;

foreach ($expectedAssets as $relative => [$expectedHash, $expectedSize]) {
    $path = __DIR__ . '/admin/' . $relative;

    if (!is_readable($path)) {
        $assetChecks[$relative] = ['state' => 'missing', 'detail' => 'not on the server'];
        $assetProblem++;
        continue;
    }

    $contents = (string) file_get_contents($path);
    $contents = str_replace("\r\n", "\n", $contents);

    $actualHash = md5($contents);
    $actualSize = strlen($contents);

    if ($actualHash === $expectedHash) {
        $assetChecks[$relative] = ['state' => 'ok', 'detail' => number_format($actualSize) . ' bytes'];
        continue;
    }

    $assetChecks[$relative] = [
        'state'  => 'differs',
        'detail' => number_format($actualSize) . ' bytes on server vs '
                  . number_format($expectedSize) . ' expected'
                  . ($actualSize < $expectedSize ? ' — truncated or older' : ' — edited or older'),
    ];
    $assetProblem++;
}
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

          <?php if (!preg_match('#^https?://[^/]+#', $appUrl)): ?>
            <p class="bad" style="margin:.9rem 0 0">
              APP_URL is malformed — it must be a single scheme followed by the host, e.g.
              <code>https://rclicksolutions.com/renziebassig/yourmajestyspa/Mariah_CMS</code>.
              Seeded image URLs are built from it, so every media row currently holds a
              broken address.
            </p>
          <?php endif; ?>

          <?php if ($sampleMediaUrl['ok'] && $sampleMediaUrl['value'] !== null): ?>
            <p style="margin:.9rem 0 0;font-size:.82rem">
              <b>Sample media file_url:</b><br>
              <code style="word-break:break-all"><?= h($sampleMediaUrl['value']) ?></code>
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

    <!-- ============ 6. Asset integrity ============ -->
    <div class="card mb-2 mt-2">
      <div class="card__head"><h3>6 · Front-end files on the server</h3></div>
      <div class="card__body">
        <p class="muted" style="margin-top:0;font-size:.85rem">
          Each admin asset hashed on disk and compared against the version in the project.
          A file that <b>differs</b> or is <b>missing</b> means the upload to this server was
          partial or stale — the browser is then running different code than the repository.
        </p>

        <?php if ($assetProblem === 0): ?>
          <p class="good" style="margin:0 0 1rem">
            All <?= count($expectedAssets) ?> files match the project exactly.
          </p>
        <?php else: ?>
          <p class="bad" style="margin:0 0 1rem">
            <?= (int) $assetProblem ?> of <?= count($expectedAssets) ?> files do not match.
            Re-upload the ones marked below, in binary mode.
          </p>
        <?php endif; ?>

        <table class="d">
          <thead><tr><th>File</th><th>State</th><th>Detail</th></tr></thead>
          <tbody>
            <?php foreach ($assetChecks as $relative => $check): ?>
              <?php if ($check['state'] === 'ok' && $assetProblem > 0) { continue; } ?>
              <tr>
                <td><code>admin/<?= h($relative) ?></code></td>
                <td class="<?= $check['state'] === 'ok' ? 'good' : 'bad' ?>">
                  <?= $check['state'] === 'ok' ? 'match' : h($check['state']) ?></td>
                <td style="font-size:.8rem;color:var(--text-faint)"><?= h($check['detail']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($assetProblem > 0): ?>
          <p class="muted" style="margin:.9rem 0 0;font-size:.8rem">
            Matching files are hidden above so the mismatches stand out.
          </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- ============ 7. What the SPA renders ============ -->
    <div class="card">
      <div class="card__head"><h3>7 · What the admin actually renders</h3></div>
      <div class="card__body">
        <p class="muted" style="margin-top:0;font-size:.85rem">
          The real Services screen is loaded in a hidden frame and its output read back —
          along with any JavaScript error it throws. This is the screen you are looking at,
          reported as text.
        </p>
        <div id="spa"><p class="muted">Loading the admin…</p></div>
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

  <script>
    // Loads the real Services screen in a hidden frame and reads back what it
    // rendered. Same origin, so the frame's DOM and its errors are both
    // readable — which turns "the list looks empty" into an exact cause.
    (function () {
      var out = document.getElementById('spa');
      var errors = [];

      var frame = document.createElement('iframe');
      // Positioned off-screen rather than display:none, so the page still
      // lays out and innerText reports what a viewer would actually see.
      frame.style.cssText = 'position:absolute;left:-10000px;top:0;width:1280px;height:900px;border:0';
      frame.src = ROOT + '/admin/index.html#/services';
      document.body.appendChild(frame);

      // The app's scripts are modules, so they run after the frame's document
      // is parsed. Polling for contentWindow attaches the listeners before then.
      var hook = setInterval(function () {
        try {
          var w = frame.contentWindow;
          if (w && !w.__probeHooked) {
            w.__probeHooked = true;

            w.addEventListener('error', function (event) {
              errors.push((event.message || 'Error')
                + (event.filename ? '\n    at ' + event.filename + ':' + event.lineno : ''));
            });

            w.addEventListener('unhandledrejection', function (event) {
              var reason = event.reason;
              errors.push('Unhandled promise rejection: '
                + (reason && reason.message ? reason.message : String(reason)));
            });
          }
        } catch (err) {
          // Frame not navigable yet — try again on the next tick.
        }
      }, 20);

      setTimeout(function () {
        clearInterval(hook);

        var lines = [];

        try {
          var doc = frame.contentDocument;
          var win = frame.contentWindow;

          lines.push('Final URL: ' + win.location.pathname + win.location.hash);

          var bounced = win.location.pathname.indexOf('login.html') !== -1;
          if (bounced) {
            lines.push('>> The admin bounced to the login page: the session was rejected.');
          }

          var boot = doc.getElementById('boot');
          lines.push('Splash still showing: '
            + (boot ? 'YES — app.js never finished starting' : 'no'));

          lines.push('Sidebar links built: ' + doc.querySelectorAll('.sidebar__link').length);
          lines.push('Table rows rendered: '
            + doc.querySelectorAll('#view table.data tbody tr').length);

          var view = doc.getElementById('view');
          lines.push('');
          lines.push('--- text content of #view ---');
          lines.push(view ? (view.innerText || '').trim().slice(0, 900) : '(no #view element)');
        } catch (err) {
          lines.push('Could not read the frame: ' + err.message);
        }

        lines.push('');
        lines.push('--- JavaScript errors captured (' + errors.length + ') ---');
        lines.push(errors.length ? errors.join('\n\n') : '(none)');

        var pre = document.createElement('pre');
        pre.className = 'log';
        pre.textContent = lines.join('\n');

        out.replaceChildren(pre);
      }, 6000);
    })();
  </script>
</body>

</html>
