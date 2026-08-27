<?php
declare(strict_types=1);

/**
 * End-to-end smoke test for Mariah_CMS.
 *
 *   php tests/smoke.php
 *
 * Drives the real HTTP API with a cookie jar, exactly as a browser would.
 * Requires APP_URL in .env to point at the deployed Mariah_CMS folder, and a
 * seeded database (php database/migrate.php && php database/seed.php --demo).
 *
 * The RBAC assertions need the demo accounts, so run the seed with --demo.
 * Anything this script creates is removed again at the end.
 *
 * Exit code 0 = all passed, 1 = at least one failure.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use Mariah\Core\Clock;
use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Repositories\SettingsRepository;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("smoke.php may only be run from the command line.\n");
}

if (!function_exists('curl_init')) {
    fwrite(STDERR, "ERROR: the cURL extension is required to run this test.\n");
    exit(1);
}

// ---------------------------------------------------------------------
// Harness
// ---------------------------------------------------------------------
$BASE = rtrim(Env::string('APP_URL', ''), '/');

if ($BASE === '') {
    fwrite(STDERR, "ERROR: APP_URL is not set in .env. It must point at the deployed\n"
        . "       Mariah_CMS folder, e.g. https://example.com/renziebassig/yourmajestyspa/Mariah_CMS\n");
    exit(1);
}

$API       = $BASE . '/api';
$COOKIE    = sys_get_temp_dir() . '/mariah-smoke-' . getmypid() . '.cookie';
$passed    = 0;
$failed    = 0;
$skipped   = 0;
$csrfToken = null;

function colour(string $text, string $code): string
{
    return DIRECTORY_SEPARATOR === '\\' ? $text : "\033[{$code}m{$text}\033[0m";
}

function section(string $title): void
{
    echo PHP_EOL . colour("── {$title} " . str_repeat('─', max(0, 56 - strlen($title))), '36') . PHP_EOL;
}

function pass(string $label): void
{
    global $passed;
    $passed++;
    echo '  ' . colour('PASS', '32') . "  {$label}" . PHP_EOL;
}

function fail(string $label, string $detail = ''): void
{
    global $failed;
    $failed++;
    echo '  ' . colour('FAIL', '31') . "  {$label}" . PHP_EOL;
    if ($detail !== '') {
        echo '        ' . str_replace("\n", "\n        ", $detail) . PHP_EOL;
    }
}

function skip(string $label, string $why): void
{
    global $skipped;
    $skipped++;
    echo '  ' . colour('SKIP', '33') . "  {$label} — {$why}" . PHP_EOL;
}

function check(string $label, bool $condition, string $detail = ''): bool
{
    if ($condition) {
        pass($label);
        return true;
    }
    fail($label, $detail);
    return false;
}

/**
 * @return array{status:int, body:array|null, raw:string}
 */
function request(string $method, string $path, array $body = null, bool $withCsrf = true): array
{
    global $API, $COOKIE, $csrfToken;

    $ch = curl_init($API . $path);

    $headers = ['Accept: application/json'];

    if ($withCsrf && $csrfToken !== null && !in_array($method, ['GET', 'HEAD'], true)) {
        $headers[] = 'X-CSRF-Token: ' . $csrfToken;
    }

    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_COOKIEJAR      => $COOKIE,
        CURLOPT_COOKIEFILE     => $COOKIE,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_FOLLOWLOCATION => false,
        // Self-signed certs are common on staging; this is a local test tool.
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['status' => 0, 'body' => null, 'raw' => 'cURL error: ' . $error];
    }

    $decoded = json_decode((string) $raw, true);

    return [
        'status' => $status,
        'body'   => is_array($decoded) ? $decoded : null,
        'raw'    => (string) $raw,
    ];
}

function login(string $email, string $password): bool
{
    global $csrfToken, $COOKIE;

    // Fresh cookie jar so each identity starts from a clean session.
    @unlink($COOKIE);
    $csrfToken = null;

    $csrf = request('GET', '/auth/csrf', null, false);
    $csrfToken = $csrf['body']['data']['csrf_token'] ?? null;

    $result = request('POST', '/auth/login', ['email' => $email, 'password' => $password]);

    if ($result['status'] === 200) {
        $csrfToken = $result['body']['data']['csrf_token'] ?? $csrfToken;
        return true;
    }

    return false;
}

function describe(array $result): string
{
    return 'HTTP ' . $result['status'] . ' — ' . substr(preg_replace('/\s+/', ' ', $result['raw']), 0, 240);
}

// ---------------------------------------------------------------------
echo colour("\nMariah_CMS smoke test", '1') . PHP_EOL;
echo "Target: {$API}" . PHP_EOL;

$adminEmail    = strtolower(trim(Env::string('ADMIN_EMAIL')));
$adminPassword = Env::string('ADMIN_PASSWORD');

if ($adminEmail === '' || $adminPassword === '') {
    fwrite(STDERR, "ERROR: ADMIN_EMAIL and ADMIN_PASSWORD must still be in .env to run this test.\n");
    exit(1);
}

$createdServiceIds = [];
$testSlug          = 'smoke-test-service-' . bin2hex(random_bytes(3));

// =====================================================================
section('Public API is reachable and unauthenticated');

$bootstrap = request('GET', '/public/bootstrap', null, false);
check('GET /public/bootstrap returns 200', $bootstrap['status'] === 200, describe($bootstrap));
check(
    'Bootstrap payload contains the expected collections',
    isset($bootstrap['body']['data']['services'], $bootstrap['body']['data']['specials'],
          $bootstrap['body']['data']['categories'], $bootstrap['body']['data']['blog_posts']),
    describe($bootstrap)
);

$blogPosts = request('GET', '/public/blog-posts', null, false);
check('GET /public/blog-posts returns 200', $blogPosts['status'] === 200, describe($blogPosts));

$firstPost = $blogPosts['body']['data'][0] ?? null;

if ($firstPost !== null) {
    $onePost = request('GET', '/public/blog-posts/' . rawurlencode((string) $firstPost['slug']), null, false);
    // A post written in the rich text editor is HTML in `content` and has no
    // paragraphs to split; one written before it still arrives pre-split.
    // Either is a readable body, and the page handles both.
    check(
        'GET /public/blog-posts/{slug} returns a readable body',
        $onePost['status'] === 200
            && (!empty($onePost['body']['data']['paragraphs'])
                || !empty($onePost['body']['data']['content'])),
        describe($onePost)
    );
} else {
    check('GET /public/blog-posts/{slug} — skipped, no published posts', true, '');
}

$missingPost = request('GET', '/public/blog-posts/definitely-not-a-real-post', null, false);
check(
    'GET /public/blog-posts/{unknown slug} returns 404',
    $missingPost['status'] === 404,
    describe($missingPost)
);

// =====================================================================
section('Unauthenticated access is refused');

$csrfToken = null;
@unlink($COOKIE);

$denied = request('GET', '/services', null, false);
check('GET /services without a session returns 401', $denied['status'] === 401, describe($denied));

$deniedWrite = request('POST', '/services', ['name' => 'Nope'], false);
check(
    'POST /services without a session is refused',
    in_array($deniedWrite['status'], [401, 419], true),
    describe($deniedWrite)
);

// =====================================================================
section('Sign-in');

$badLogin = null;
{
    $csrf = request('GET', '/auth/csrf', null, false);
    $csrfToken = $csrf['body']['data']['csrf_token'] ?? null;
    check('GET /auth/csrf issues a token', $csrfToken !== null, describe($csrf));

    $badLogin = request('POST', '/auth/login', ['email' => $adminEmail, 'password' => 'wrong-password-xyz']);
    check('Login with a wrong password returns 401', $badLogin['status'] === 401, describe($badLogin));
}

check('Login as Super Admin succeeds', login($adminEmail, $adminPassword), 'Check ADMIN_EMAIL / ADMIN_PASSWORD.');

$me = request('GET', '/auth/me');
check('GET /auth/me returns the signed-in profile', $me['status'] === 200, describe($me));
check(
    'The profile never contains a password hash',
    !str_contains(strtolower($me['raw']), 'password_hash'),
    'A password hash appeared in the API response.'
);
check(
    'Role is Super Admin',
    ($me['body']['data']['user']['role']['slug'] ?? null) === 'super-admin',
    describe($me)
);

// =====================================================================
section('CSRF protection');

$savedToken = $csrfToken;
$csrfToken  = 'not-a-valid-token';

$csrfFail = request('POST', '/services', ['name' => 'CSRF probe']);
check('A mutation with a bad CSRF token is rejected (419)', $csrfFail['status'] === 419, describe($csrfFail));

$csrfToken = $savedToken;

// =====================================================================
section('Dashboard');

$stats = request('GET', '/dashboard/stats');
check('GET /dashboard/stats returns 200', $stats['status'] === 200, describe($stats));
check(
    'Stats include service counters',
    isset($stats['body']['data']['services']['total']),
    describe($stats)
);

// =====================================================================
section('Validation');

$invalid = request('POST', '/services', ['name' => '']);
check('Creating a service with no name returns 422', $invalid['status'] === 422, describe($invalid));
check(
    'The 422 names the offending field',
    isset($invalid['body']['error']['fields']['name']),
    describe($invalid)
);

$badUrl = request('POST', '/services', [
    'name' => 'Bad URL probe', 'category_id' => 1, 'price' => 100,
    'booking_url' => 'javascript:alert(1)',
]);
check('A non-http booking link is rejected', $badUrl['status'] === 422, describe($badUrl));

// =====================================================================
section('Create → activate → public visibility → deactivate');

$categories = request('GET', '/categories/options');
$categoryId = $categories['body']['data'][0]['id'] ?? null;

if ($categoryId === null) {
    fail('A seeded category exists to attach the test service to', describe($categories));
} else {
    pass('A seeded category exists to attach the test service to');

    $created = request('POST', '/services', [
        'name'              => 'Smoke Test Service',
        'slug'              => $testSlug,
        'category_id'       => $categoryId,
        'short_description' => 'Created by tests/smoke.php.',
        'description'       => 'This record is created and removed by the smoke test.',
        'price'             => 123.45,
        'duration_minutes'  => 45,
        'status'            => 'inactive',
        'booking_url'       => 'https://go.booker.com/location/yourmajestyspa/service-menu',
    ]);

    check('POST /services returns 201', $created['status'] === 201, describe($created));

    $serviceId = $created['body']['data']['id'] ?? null;
    if ($serviceId !== null) {
        $createdServiceIds[] = (int) $serviceId;
    }

    if ($serviceId === null) {
        fail('The created service has an id', describe($created));
    } else {
        pass('The created service has an id');

        // --- duplicate slug -------------------------------------------
        $conflict = request('POST', '/services', [
            'name' => 'Smoke Test Service Again', 'slug' => $testSlug,
            'category_id' => $categoryId, 'price' => 10,
        ]);
        // The server auto-uniquifies slugs, so this succeeds with a new slug
        // rather than colliding — assert it did NOT reuse the slug.
        if ($conflict['status'] === 201) {
            $createdServiceIds[] = (int) $conflict['body']['data']['id'];
            check(
                'A duplicate slug is made unique rather than colliding',
                ($conflict['body']['data']['slug'] ?? '') !== $testSlug,
                describe($conflict)
            );
        } else {
            check('A duplicate slug returns 409', $conflict['status'] === 409, describe($conflict));
        }

        // --- admin list -----------------------------------------------
        $list = request('GET', '/services?search=Smoke+Test+Service&per_page=50');
        check(
            'The new service appears in the admin list',
            str_contains($list['raw'], 'Smoke Test Service'),
            describe($list)
        );

        // --- inactive → not public ------------------------------------
        $publicBefore = request('GET', '/public/services', null, false);
        check(
            'An INACTIVE service is absent from the public API',
            !str_contains($publicBefore['raw'], $testSlug),
            'The inactive service leaked to the public endpoint.'
        );

        // --- activate --------------------------------------------------
        $activate = request('PATCH', "/services/{$serviceId}/status", ['status' => 'active']);
        check('PATCH status → active returns 200', $activate['status'] === 200, describe($activate));

        $publicAfter = request('GET', '/public/services', null, false);
        check(
            'An ACTIVE service appears on the public API',
            str_contains($publicAfter['raw'], $testSlug),
            'The activated service did not reach the public endpoint.'
        );

        $publicBootstrap = request('GET', '/public/bootstrap', null, false);
        check(
            'It also appears in the aggregated bootstrap feed the website reads',
            str_contains($publicBootstrap['raw'], $testSlug),
            'The activated service is missing from /public/bootstrap.'
        );

        // --- update ----------------------------------------------------
        $updated = request('PUT', "/services/{$serviceId}", ['price' => 199.00]);
        check('PUT /services/:id returns 200', $updated['status'] === 200, describe($updated));
        check(
            'The price change was persisted',
            (float) ($updated['body']['data']['price'] ?? 0) === 199.00,
            describe($updated)
        );

        // --- deactivate -------------------------------------------------
        $deactivate = request('PATCH', "/services/{$serviceId}/status", ['status' => 'inactive']);
        check('PATCH status → inactive returns 200', $deactivate['status'] === 200, describe($deactivate));

        $publicGone = request('GET', '/public/services', null, false);
        check(
            'A DEACTIVATED service disappears from the public API',
            !str_contains($publicGone['raw'], $testSlug),
            'The deactivated service is still public.'
        );

        $adminStill = request('GET', '/services?search=Smoke+Test+Service&per_page=50');
        check(
            'It remains visible in the admin dashboard',
            str_contains($adminStill['raw'], 'Smoke Test Service'),
            describe($adminStill)
        );

        // --- soft delete & restore --------------------------------------
        $deleted = request('DELETE', "/services/{$serviceId}");
        check('DELETE /services/:id returns 200', $deleted['status'] === 200, describe($deleted));

        $afterDelete = request('GET', '/services?search=Smoke+Test+Service&per_page=50');
        check(
            'A deleted service is hidden from the default admin list',
            !str_contains($afterDelete['raw'], '"id":' . $serviceId . ','),
            describe($afterDelete)
        );

        $trash = request('GET', '/services?deleted=only&search=Smoke+Test+Service&per_page=50');
        check(
            'It is recoverable from the deleted-items filter',
            str_contains($trash['raw'], 'Smoke Test Service'),
            describe($trash)
        );

        $restored = request('POST', "/services/{$serviceId}/restore");
        check('POST /services/:id/restore returns 200', $restored['status'] === 200, describe($restored));
    }
}

// =====================================================================
section('Media upload validation');

{
    $phpFile = sys_get_temp_dir() . '/mariah-smoke-evil.php';
    file_put_contents($phpFile, "<?php echo 'should never execute'; ?>");

    $ch = curl_init($API . '/media');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'X-CSRF-Token: ' . $csrfToken],
        CURLOPT_COOKIEJAR      => $COOKIE,
        CURLOPT_COOKIEFILE     => $COOKIE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_POSTFIELDS     => ['file' => new CURLFile($phpFile, 'application/x-php', 'evil.php')],
    ]);

    $raw    = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    @unlink($phpFile);

    check(
        'Uploading a .php file is rejected',
        $status === 422 || $status === 400,
        'HTTP ' . $status . ' — ' . substr($raw, 0, 200)
    );
}

// =====================================================================
section('Service CSV import');

/** Posts a CSV to /services/import. The JSON request() helper cannot: it forces
 *  Content-Type: application/json, and an upload must be multipart. */
function importCsv(string $csv, bool $commit, ?string $digest = null): array
{
    global $API, $COOKIE, $csrfToken;

    $path = sys_get_temp_dir() . '/mariah-smoke-import.csv';
    file_put_contents($path, $csv);

    $fields = [
        'file'    => new CURLFile($path, 'text/csv', 'smoke-import.csv'),
        'dry_run' => $commit ? '0' : '1',
    ];

    if ($digest !== null) {
        $fields['confirm_digest'] = $digest;
    }

    $ch = curl_init($API . '/services/import');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'X-CSRF-Token: ' . $csrfToken],
        CURLOPT_COOKIEJAR      => $COOKIE,
        CURLOPT_COOKIEFILE     => $COOKIE,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_POSTFIELDS     => $fields,
    ]);

    $raw    = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    @unlink($path);

    return ['status' => $status, 'body' => json_decode($raw, true), 'raw' => $raw];
}

function serviceCount(): int
{
    return (int) Database::fetchValue('SELECT COUNT(*) FROM services WHERE deleted_at IS NULL');
}

$importCategory = Database::fetchOne(
    "SELECT name FROM service_categories WHERE status = 'active' AND deleted_at IS NULL LIMIT 1"
);

if ($importCategory === null) {
    skip('Service CSV import', 'no active service category to import into');
} else {
    $categoryName = (string) $importCategory['name'];
    $suffix       = bin2hex(random_bytes(3));

    $goodCsv = "name,category,price,duration_minutes,status\n"
        . "Smoke Test Service Alpha {$suffix},{$categoryName},\"\$1,250.00\",60,active\n"
        . "Smoke Test Service Beta {$suffix},{$categoryName},95,45,active\n";

    // --- dry run writes nothing ---------------------------------------
    $before  = serviceCount();
    $preview = importCsv($goodCsv, false);

    check(
        'Dry run previews two new services',
        $preview['status'] === 200
            && ($preview['body']['data']['summary']['create'] ?? null) === 2,
        'HTTP ' . $preview['status'] . ' — ' . substr($preview['raw'], 0, 300)
    );

    check(
        'Dry run wrote nothing',
        serviceCount() === $before,
        'Count moved from ' . $before . ' to ' . serviceCount()
    );

    check(
        'Money with a currency symbol and thousands separator parses',
        ($preview['body']['data']['rows'][0]['price'] ?? null) === 1250.0,
        'Parsed as: ' . var_export($preview['body']['data']['rows'][0]['price'] ?? null, true)
    );

    // --- commit --------------------------------------------------------
    $digest = $preview['body']['data']['file']['digest'] ?? null;
    $commit = importCsv($goodCsv, true, $digest);

    $committed = check(
        'Commit creates two services',
        $commit['status'] === 200
            && ($commit['body']['data']['committed'] ?? false) === true
            && ($commit['body']['data']['summary']['created'] ?? null) === 2,
        'HTTP ' . $commit['status'] . ' — ' . substr($commit['raw'], 0, 300)
    );

    check('Commit moved the service count by two', serviceCount() === $before + 2);

    if ($committed) {
        foreach ($commit['body']['data']['rows'] as $row) {
            if (!empty($row['service_id'])) {
                $createdServiceIds[] = (int) $row['service_id'];
            }
        }
    }

    // --- idempotency: the headline behaviour ---------------------------
    $again = importCsv($goodCsv, true, null);

    check(
        'Re-importing the same file changes nothing',
        ($again['body']['data']['summary']['unchanged'] ?? null) === 2
            && serviceCount() === $before + 2,
        'Summary: ' . json_encode($again['body']['data']['summary'] ?? null)
    );

    // --- bad rows are reported, not written ----------------------------
    $badCsv = "name,category,price,promo_price,status\n"
        . "Smoke Test Service Bad {$suffix},No Such Category {$suffix},100,,active\n"
        . "Smoke Test Service Bad2 {$suffix},{$categoryName},abc,,active\n"
        . "Smoke Test Service Bad3 {$suffix},{$categoryName},100,150,active\n"
        . "Smoke Test Service Bad4 {$suffix},{$categoryName},100,,Maybe\n";

    $bad = importCsv($badCsv, true);

    check(
        'A file with bad rows returns 200 and imports nothing',
        $bad['status'] === 200
            && ($bad['body']['data']['committed'] ?? true) === false
            && ($bad['body']['data']['summary']['error'] ?? 0) === 4,
        'HTTP ' . $bad['status'] . ' — ' . json_encode($bad['body']['data']['summary'] ?? null)
    );

    check(
        'Per-row errors ride in data.rows, not error.fields',
        !empty($bad['body']['data']['rows'][0]['errors'])
            && empty($bad['body']['error']),
        substr($bad['raw'], 0, 300)
    );

    check('The bad file left the service count alone', serviceCount() === $before + 2);

    // --- duplicate slug inside one file --------------------------------
    $dupeCsv = "name,category,price\n"
        . "Smoke Test Dupe {$suffix},{$categoryName},100\n"
        . "Smoke Test Dupe {$suffix},{$categoryName},120\n";

    $dupe = importCsv($dupeCsv, false);

    check(
        'Two rows sharing a slug are rejected, not silently suffixed',
        ($dupe['body']['data']['summary']['error'] ?? 0) === 1,
        json_encode($dupe['body']['data']['summary'] ?? null)
    );

    // --- file-level failures -------------------------------------------
    $wrongHeaders = importCsv("Treatment,Dept,Cost\nMassage,Massages,100\n", false);

    check(
        'A file with unrecognised required columns is a 422 on the file field',
        $wrongHeaders['status'] === 422
            && !empty($wrongHeaders['body']['error']['fields']['file']),
        'HTTP ' . $wrongHeaders['status'] . ' — ' . substr($wrongHeaders['raw'], 0, 300)
    );

    $headersOnly = importCsv("name,category,price\n", false);

    check(
        'A header-only file is not an error',
        $headersOnly['status'] === 200
            && ($headersOnly['body']['data']['summary']['rows'] ?? null) === 0,
        'HTTP ' . $headersOnly['status'] . ' — ' . substr($headersOnly['raw'], 0, 200)
    );

    // Excel writes a BOM on "CSV UTF-8"; it must not corrupt the first header.
    $bomCsv = "\xEF\xBB\xBFname,category,price\r\nSmoke Test BOM {$suffix},{$categoryName},77\r\n";
    $bom    = importCsv($bomCsv, false);

    check(
        'A UTF-8 BOM and CRLF endings parse correctly',
        $bom['status'] === 200 && ($bom['body']['data']['summary']['create'] ?? null) === 1,
        'HTTP ' . $bom['status'] . ' — ' . substr($bom['raw'], 0, 300)
    );

    $xlsx = importCsv("PK\x03\x04fake-zip-content", false);

    check(
        'An .xlsx renamed to .csv is named as such',
        $xlsx['status'] === 422,
        'HTTP ' . $xlsx['status'] . ' — ' . substr($xlsx['raw'], 0, 200)
    );
}

// =====================================================================
section('Template generator round-trip');

/**
 * Rebuilds in PHP exactly what admin/assets/js/pages/services-template.js
 * builds in the browser. Kept literal rather than clever — the point is to
 * encode the same decisions, so a divergence fails loudly.
 */
function templateCell(array $service, string $key): string
{
    if ($key === 'category') {
        return (string) ($service['category_name'] ?? '');
    }

    if ($key === 'featured') {
        return $service['featured'] ? 'yes' : 'no';
    }

    $value = $service[$key] ?? null;

    if ($value === null || $value === '') {
        return '';
    }

    if (in_array($key, ['price', 'promo_price', 'duration_minutes', 'most_loved_rank', 'display_order'], true)) {
        return is_numeric($value) ? (string) (0 + $value) : '';
    }

    return (string) $value;
}

function templateCsv(array $header, array $rows): string
{
    $escape = static function (string $value): string {
        return preg_match('/["\r\n,]/', $value) === 1
            ? '"' . str_replace('"', '""', $value) . '"'
            : $value;
    };

    $lines = [];

    foreach (array_merge([$header], $rows) as $row) {
        $lines[] = implode(',', array_map($escape, $row));
    }

    return "\xEF\xBB\xBF" . implode("\r\n", $lines) . "\r\n";
}

{
    $formOptions = request('GET', '/services/form-options');
    $columns     = $formOptions['body']['data']['columns'] ?? [];

    check(
        'GET /services/form-options carries the import columns',
        $formOptions['status'] === 200
            && array_column($columns, 'key') === \Mariah\Services\ServiceCsvSchema::columnKeys()
            && isset($formOptions['body']['data']['categories'], $formOptions['body']['data']['icons']),
        'HTTP ' . $formOptions['status'] . ' — ' . substr($formOptions['raw'], 0, 300)
    );

    $header = array_column($columns, 'key');

    $listed   = request('GET', '/services?per_page=100&sort=id&direction=asc');
    $services = $listed['body']['data'] ?? [];
    $total    = $listed['body']['meta']['total'] ?? count($services);

    // Each of these is asserted on its own below; letting one fail the
    // round-trip would hide which decision actually broke.
    $noCategory = array_filter($services, static fn (array $s): bool => ($s['category_name'] ?? null) === null);
    $oddIcons   = array_filter($services, static fn (array $s): bool =>
        ($s['icon_key'] ?? null) !== null
        && !in_array($s['icon_key'], \Mariah\Services\ServiceCsvSchema::iconKeys(), true));

    if ($services === []) {
        skip('Generated sheet re-imports with every row unchanged', 'no services to export');
    } elseif ($total > \Mariah\Services\ServiceCsvSchema::MAX_ROWS) {
        skip('Generated sheet re-imports with every row unchanged', "{$total} services exceeds the import cap");
    } elseif ($noCategory !== []) {
        skip('Generated sheet re-imports with every row unchanged',
             count($noCategory) . ' service(s) have a deleted category');
    } elseif ($oddIcons !== []) {
        // Pre-existing importer behaviour: an unknown icon_key normalises to
        // null, so such a row round-trips as an update that clears the icon.
        skip('Generated sheet re-imports with every row unchanged',
             count($oddIcons) . ' service(s) carry an icon_key outside the catalogue');
    } else {
        $rows = [];
        foreach ($services as $service) {
            $rows[] = array_map(static fn (string $key): string => templateCell($service, $key), $header);
        }

        $before     = serviceCount();
        $roundTrip  = importCsv(templateCsv($header, $rows), false);
        $summary    = $roundTrip['body']['data']['summary'] ?? [];

        // One assertion pinning column order, the category header spelling,
        // bool→yes/no, float→plain digits, null→blank, the CSV escaping, the
        // BOM and CRLF all at once.
        check(
            'Generated sheet re-imports with every row unchanged',
            $roundTrip['status'] === 200
                && ($summary['create'] ?? -1) === 0
                && ($summary['update'] ?? -1) === 0
                && ($summary['error'] ?? -1) === 0
                && ($summary['unchanged'] ?? -1) === count($rows),
            'summary: ' . json_encode($summary) . ' — '
                . json_encode(array_slice($roundTrip['body']['data']['rows'] ?? [], 0, 2))
        );

        check('The round-trip preview wrote nothing', serviceCount() === $before);

        // A direct regression guard against anyone reaching for money(),
        // which would emit "$1,250.00" and drift through Sheets' formatting.
        $priced = null;
        foreach ($services as $service) {
            if ((float) $service['price'] > 0) { $priced = $service; break; }
        }

        if ($priced === null) {
            skip('Prices export as plain digits', 'no priced service to check');
        } else {
            $csv = templateCsv($header, [array_map(
                static fn (string $key): string => templateCell($priced, $key),
                $header
            )]);

            check(
                'Prices export as plain digits, not currency text',
                str_contains($csv, ',' . (0 + $priced['price']) . ',') && !str_contains($csv, '$'),
                'Generated: ' . substr($csv, 0, 200)
            );
        }
    }

    if ($noCategory !== []) {
        $first = reset($noCategory);
        $row   = array_map(static fn (string $key): string => templateCell($first, $key), $header);
        $bad   = importCsv(templateCsv($header, [$row]), false);

        check(
            'A service whose category was deleted is rejected, naming the column',
            ($bad['body']['data']['summary']['error'] ?? 0) === 1
                && str_contains(
                    (string) ($bad['body']['data']['rows'][0]['errors']['category'] ?? ''),
                    'Category is required'
                ),
            substr($bad['raw'], 0, 300)
        );
    }

    // The blank template must import cleanly — today's hardcoded examples used
    // categories that may not exist, so it could fail its own first import.
    $categories = $formOptions['body']['data']['categories'] ?? [];
    $icons      = $formOptions['body']['data']['icons'] ?? [];

    if ($categories === [] || $icons === []) {
        skip('The blank template imports cleanly', 'no categories or icons defined');
    } else {
        $active = null;
        foreach ($categories as $category) {
            if (($category['status'] ?? '') === 'active') { $active = $category; break; }
        }
        $active ??= $categories[0];

        $iconKeys = array_column($icons, 'key');
        $seed     = [
            ['name' => 'EXAMPLE — Hot Stone Massage', 'price' => '165', 'duration_minutes' => '80',
             'icon_key' => in_array('i-stone', $iconKeys, true) ? 'i-stone' : $iconKeys[0],
             'short_description' => 'Warm basalt stones melt deep tension.'],
            ['name' => 'EXAMPLE — Signature Facial', 'price' => '140', 'duration_minutes' => '60',
             'icon_key' => in_array('i-drop', $iconKeys, true) ? 'i-drop' : $iconKeys[0],
             'short_description' => 'A tailored deep-cleansing facial.'],
        ];

        $exampleRows = [];
        foreach ($seed as $example) {
            $exampleRows[] = array_map(static function (string $key) use ($example, $active): string {
                if ($key === 'category') return (string) $active['name'];
                if ($key === 'status')   return 'inactive';
                if ($key === 'featured') return 'no';
                return (string) ($example[$key] ?? '');
            }, $header);
        }

        $template = importCsv(templateCsv($header, $exampleRows), false);

        check(
            'The blank template imports cleanly as two inactive services',
            ($template['body']['data']['summary']['error'] ?? 1) === 0
                && ($template['body']['data']['summary']['create'] ?? 0) === 2,
            'summary: ' . json_encode($template['body']['data']['summary'] ?? null)
                . ' — ' . substr($template['raw'], 0, 300)
        );
    }
}

// =====================================================================
section('Sub-categories, price tiers and add-ons');

{
    $parentId = null;
    $childId  = null;
    $addonId  = null;
    $tieredId   = null;
    $tieredSlug = '';

    $parent = request('POST', '/categories', [
        'name'   => 'Smoke Parent ' . bin2hex(random_bytes(3)),
        'status' => 'inactive',
    ]);
    check('A top-level category is created', $parent['status'] === 201, describe($parent));
    $parentId = $parent['body']['data']['id'] ?? null;

    if ($parentId === null) {
        skip('Sub-category checks', 'the parent category could not be created');
    } else {
        $child = request('POST', '/categories', [
            'name'      => 'Smoke Child ' . bin2hex(random_bytes(3)),
            'parent_id' => $parentId,
            'status'    => 'inactive',
        ]);
        check('A sub-category is created under it', $child['status'] === 201, describe($child));
        $childId = $child['body']['data']['id'] ?? null;

        // The whole point of the two-level cap: the public site draws parents
        // as tabs and children as headings, and has no third place to render.
        $grandchild = request('POST', '/categories', [
            'name'      => 'Smoke Grandchild ' . bin2hex(random_bytes(3)),
            'parent_id' => $childId,
            'status'    => 'inactive',
        ]);
        check(
            'A third level is refused (422)',
            $grandchild['status'] === 422
                && !empty($grandchild['body']['error']['fields']['parent_id']),
            describe($grandchild)
        );

        $selfParent = request('PUT', "/categories/{$childId}", ['parent_id' => $childId]);
        check(
            'A category cannot be its own parent (422)',
            $selfParent['status'] === 422,
            describe($selfParent)
        );

        $deleteParent = request('DELETE', "/categories/{$parentId}");
        check(
            'A category holding sub-categories cannot be deleted (409)',
            $deleteParent['status'] === 409,
            describe($deleteParent)
        );

        // --- add-ons, the two-prices-one-name case --------------------
        $addonA = request('POST', '/addons', [
            'name' => 'Smoke Aromatherapy', 'category_id' => $parentId, 'price' => 25,
        ]);
        $addonB = request('POST', '/addons', [
            'name' => 'Smoke Aromatherapy', 'category_id' => $childId, 'price' => 20,
        ]);
        check(
            'The same add-on name coexists under two categories at two prices',
            $addonA['status'] === 201 && $addonB['status'] === 201
                && ($addonA['body']['data']['price_label'] ?? null) === '+$25'
                && ($addonB['body']['data']['price_label'] ?? null) === '+$20',
            describe($addonA) . ' / ' . describe($addonB)
        );
        $addonId = $addonA['body']['data']['id'] ?? null;

        // --- price tiers ----------------------------------------------
        $tiered = request('POST', '/services', [
            'name'        => 'Smoke Tiered Treatment ' . bin2hex(random_bytes(3)),
            'category_id' => $childId,
            'price'       => 999,
            'status'      => 'inactive',
            'variants'    => [
                ['label' => '50 min',  'duration_minutes' => 50,  'price' => 150],
                ['label' => '80 min',  'duration_minutes' => 80,  'price' => 180],
                ['label' => '1h 50m',  'duration_minutes' => 110, 'price' => 210],
            ],
        ]);
        check('A service saves with three price tiers', $tiered['status'] === 201, describe($tiered));
        $tieredId = $tiered['body']['data']['id'] ?? null;
        $tieredSlug = $tiered['body']['data']['slug'] ?? '';

        if ($tieredId !== null) {
            $createdServiceIds[] = (int) $tieredId;

            $read = request('GET', "/services/{$tieredId}");
            $variants = $read['body']['data']['variants'] ?? [];

            check(
                'The tiers round-trip in order',
                count($variants) === 3
                    && $variants[0]['label'] === '50 min'
                    && $variants[2]['price'] === 210.0,
                'Got: ' . json_encode(array_column($variants, 'label'))
            );

            // The mirror is what keeps every existing sort, filter and public
            // query working without a join.
            check(
                'The cheapest tier is mirrored onto the parent row',
                ($read['body']['data']['price'] ?? null) === 150.0
                    && ($read['body']['data']['duration_minutes'] ?? null) === 50,
                'price=' . json_encode($read['body']['data']['price'] ?? null)
                    . ' duration=' . json_encode($read['body']['data']['duration_minutes'] ?? null)
            );

            check(
                'A multi-tier service reads as a "from" price',
                ($read['body']['data']['price_label'] ?? null) === 'from $150'
                    && ($read['body']['data']['duration_label'] ?? null) === '50–110 min',
                'price_label=' . json_encode($read['body']['data']['price_label'] ?? null)
                    . ' duration_label=' . json_encode($read['body']['data']['duration_label'] ?? null)
            );

            $badTier = request('PUT', "/services/{$tieredId}", [
                'variants' => [['label' => '', 'price' => 'not-a-number']],
            ]);
            check(
                'A malformed tier is rejected onto its own cell (422)',
                $badTier['status'] === 422
                    && !empty($badTier['body']['error']['fields']['variants.0.price']),
                describe($badTier)
            );

            $copy = request('POST', "/services/{$tieredId}/duplicate");
            $copyId = $copy['body']['data']['id'] ?? null;
            if ($copyId !== null) {
                $createdServiceIds[] = (int) $copyId;
                $copyRead = request('GET', "/services/{$copyId}");
                check(
                    'Duplicating a service copies its tiers',
                    count($copyRead['body']['data']['variants'] ?? []) === 3,
                    'Got ' . count($copyRead['body']['data']['variants'] ?? []) . ' tiers'
                );
            } else {
                fail('Duplicating a service copies its tiers', describe($copy));
            }

            // --- what a guest actually sees ------------------------------
            // The public endpoints show active records only, so everything
            // has to come out of hiding for the checks below.
            request('PATCH', "/categories/{$parentId}/status", ['status' => 'active']);
            request('PATCH', "/categories/{$childId}/status", ['status' => 'active']);
            request('PATCH', "/services/{$tieredId}/status", ['status' => 'active']);
            request('PUT', "/services/{$tieredId}", [
                'short_description' => 'Created by tests/smoke.php.',
                'contraindications' => 'Heat sensitivity, diabetic neuropathy.',
            ]);

            $boot = request('GET', '/public/bootstrap');
            $tab  = null;
            foreach ($boot['body']['data']['categories'] ?? [] as $category) {
                if ((int) $category['id'] === (int) $parentId) {
                    $tab = $category;
                }
            }

            check(
                'The public menu nests the sub-category under its parent',
                $tab !== null
                    && count($tab['groups'] ?? []) === 1
                    && (int) $tab['groups'][0]['id'] === (int) $childId,
                'Got: ' . json_encode($tab === null ? null : array_column($tab['groups'] ?? [], 'name'))
            );

            check(
                'A parent tab carries no services of its own, only the group',
                $tab !== null && ($tab['services'] ?? null) === [],
                'Got ' . count($tab['services'] ?? []) . ' loose services'
            );

            // The add-on menu is defined on Massage and has to reach a service
            // filed under Signature Massage, or the guest never sees it.
            $publicService = request('GET', '/public/services/' . $tieredSlug);
            $addonNames    = array_column($publicService['body']['data']['addons'] ?? [], 'name');

            check(
                'A sub-category service inherits its parent add-on menu',
                $publicService['status'] === 200 && in_array('Smoke Aromatherapy', $addonNames, true),
                'Got: ' . json_encode($addonNames)
            );

            // The sub-category prices the same add-on lower, and its own must
            // win — that is the whole reason add-ons are per-category rows.
            $inherited = null;
            foreach ($publicService['body']['data']['addons'] ?? [] as $addon) {
                if ($addon['name'] === 'Smoke Aromatherapy') {
                    $inherited = $addon['price_label'];
                }
            }
            check(
                'The sub-category price wins over the inherited one',
                $inherited === '+$20',
                'Got: ' . json_encode($inherited)
            );

            check(
                'The detail endpoint carries the tiers and the safety copy',
                count($publicService['body']['data']['variants'] ?? []) === 3
                    && str_contains(
                        (string) ($publicService['body']['data']['contraindications'] ?? ''),
                        'Heat sensitivity'
                    ),
                describe($publicService)
            );


            // Clearing the tiers must leave the service on a single price.
            request('PUT', "/services/{$tieredId}", ['variants' => []]);
            $cleared = request('GET', "/services/{$tieredId}");
            check(
                'Clearing the tiers returns the service to a single price',
                ($cleared['body']['data']['variants'] ?? null) === []
                    && ($cleared['body']['data']['price_label'] ?? null) === '$150',
                'price_label=' . json_encode($cleared['body']['data']['price_label'] ?? null)
            );

            // CASCADE: the tiers must not outlive the service.
            request('PUT', "/services/{$tieredId}", [
                'variants' => [['label' => '50 min', 'duration_minutes' => 50, 'price' => 150]],
            ]);
            Database::run('DELETE FROM services WHERE id = ?', [$tieredId]);
            $orphans = (int) Database::fetchValue(
                'SELECT COUNT(*) FROM service_variants WHERE service_id = ?',
                [$tieredId]
            );
            check('Deleting a service cascades its tiers away', $orphans === 0, "{$orphans} left behind");
            $createdServiceIds = array_values(array_diff($createdServiceIds, [(int) $tieredId]));
        }

        // --- teardown --------------------------------------------------
        if ($addonId !== null) {
            Database::run('DELETE FROM service_addons WHERE category_id IN (?, ?)', [$parentId, $childId]);
        }
        if (isset($copyId) && $copyId !== null) {
            Database::run('DELETE FROM services WHERE id = ?', [$copyId]);
            $createdServiceIds = array_values(array_diff($createdServiceIds, [(int) $copyId]));
        }
        if ($childId !== null) {
            Database::run('DELETE FROM service_categories WHERE id = ?', [$childId]);
        }
        Database::run('DELETE FROM service_categories WHERE id = ?', [$parentId]);
    }
}

// =====================================================================
section('Rich text sanitising');

// This is the security boundary. Rich text is the only content on the public
// site that is printed as markup instead of escaped, so what survives this
// class is what runs in a guest's browser. Every case below is a payload that
// must not.
{
    $rteServiceId = null;

    $rteService = request('POST', '/services', [
        'name'        => 'Smoke RTE ' . bin2hex(random_bytes(3)),
        'category_id' => $categoryId,
        'price'       => 10,
        'status'      => 'inactive',
    ]);

    $rteServiceId = $rteService['body']['data']['id'] ?? null;

    if ($rteServiceId === null) {
        skip('Rich text sanitising', 'could not create the test service');
    } else {
        $createdServiceIds[] = (int) $rteServiceId;

        /** Saves $html into the description and returns what came back out. */
        $sanitised = static function (string $html) use ($rteServiceId): string {
            $result = request('PUT', "/services/{$rteServiceId}", ['description' => $html]);
            return (string) ($result['body']['data']['description'] ?? '');
        };

        $cases = [
            // label, input, must NOT contain, must contain ('' = skip)
            ['A <script> block is removed with its contents',
             '<p>before</p><script>alert(1)</script><p>after</p>', 'alert(1)', 'before'],

            ['An event handler cannot ride in on an allowed tag',
             '<p onclick="alert(1)">hi</p>', 'onclick', 'hi'],

            ['An <img onerror> payload is removed entirely',
             '<p>x</p><img src=x onerror=alert(1)>', 'onerror', 'x'],

            ['A javascript: link is stripped but its words are kept',
             '<a href="javascript:alert(1)">click me</a>', 'javascript', 'click me'],

            ['A data: URL link is stripped',
             '<a href="data:text/html,<script>alert(1)</script>">x</a>', 'data:', 'x'],

            ['Control characters cannot smuggle a scheme past the check',
             "<a href=\"java\tscript:alert(1)\">x</a>", 'script:', 'x'],

            ['A real link survives, and is forced safe',
             '<a href="https://go.booker.com/x">Book</a>', '', 'noopener noreferrer'],

            ['An <iframe> is removed',
             '<p>a</p><iframe src="https://evil.test"></iframe>', 'iframe', 'a'],

            ['A style attribute never survives',
             '<p style="position:fixed;inset:0">x</p>', 'style=', 'x'],

            ['An on-palette colour becomes a class',
             '<span style="color: rgb(168, 134, 42)">gold</span>', 'style=', 'rte-c-gold'],

            ['An off-palette colour is dropped, keeping the words',
             '<span style="color:#ff0000">red</span>', 'rte-c-', 'red'],

            ['A forged class is stripped',
             '<span class="evil-class">x</span>', 'evil-class', 'x'],

            ['Formatting and lists survive intact',
             '<p><strong>bold</strong> and <em>italic</em></p><ul><li>one</li><li>two</li></ul>',
             '', '<li>one</li>'],

            ['A valid dir is kept',
             '<p dir="rtl">x</p>', '', 'dir="rtl"'],

            ['An invalid dir is dropped',
             '<p dir="javascript:alert(1)">x</p>', 'dir=', 'x'],

            ['An unknown tag is unwrapped, keeping its text',
             '<blockquote>quoted</blockquote>', 'blockquote', 'quoted'],
        ];

        foreach ($cases as [$label, $input, $mustNot, $must]) {
            $out = $sanitised($input);

            $ok = ($mustNot === '' || !str_contains(strtolower($out), strtolower($mustNot)))
                && ($must === '' || str_contains($out, $must));

            check($label, $ok, 'Got: ' . substr($out, 0, 200));
        }

        // Clearing the editor must clear the column, not store an empty
        // paragraph that renders as a blank gap forever.
        $cleared = request('PUT', "/services/{$rteServiceId}", ['description' => '<p><br></p>']);
        check(
            'An empty editor stores nothing',
            ($cleared['body']['data']['description'] ?? 'x') === null,
            'Got: ' . json_encode($cleared['body']['data']['description'] ?? 'missing')
        );

        // Plain text written before the editor existed must round-trip
        // untouched, or every existing description would change on next save.
        $legacy = $sanitised("First paragraph.\n\nSecond paragraph.");
        check(
            'Legacy plain text is left alone',
            str_contains($legacy, 'First paragraph.') && !str_contains($legacy, '<'),
            'Got: ' . substr($legacy, 0, 200)
        );

        Database::run('DELETE FROM services WHERE id = ?', [$rteServiceId]);
        $createdServiceIds = array_values(array_diff($createdServiceIds, [(int) $rteServiceId]));
    }
}

// =====================================================================
section('Site settings');

$originalSheetUrl = null;

{
    $current = request('GET', '/settings');

    $ok = check(
        'GET /settings returns the setting definitions',
        $current['status'] === 200
            && isset($current['body']['data']['values']['services_import_sheet_url']),
        'HTTP ' . $current['status'] . ' — ' . substr($current['raw'], 0, 300)
    );

    if ($ok) {
        $originalSheetUrl = $current['body']['data']['values']['services_import_sheet_url'];
    }

    $bad = request('PUT', '/settings', ['services_import_sheet_url' => 'https://example.com/nope']);
    check(
        'A non-Google link is rejected onto its own field',
        $bad['status'] === 422 && !empty($bad['body']['error']['fields']['services_import_sheet_url']),
        'HTTP ' . $bad['status'] . ' — ' . substr($bad['raw'], 0, 300)
    );

    $published = request('PUT', '/settings', [
        'services_import_sheet_url' => 'https://docs.google.com/spreadsheets/d/e/2PACX-abc/pubhtml',
    ]);
    check(
        'A "Publish to web" link is rejected with its own message',
        $published['status'] === 422
            && str_contains(
                (string) ($published['body']['error']['fields']['services_import_sheet_url'] ?? ''),
                'Publish to web'
            ),
        'HTTP ' . $published['status'] . ' — ' . substr($published['raw'], 0, 300)
    );

    $unknown = request('PUT', '/settings', ['not_a_real_setting' => 'x']);
    check(
        'An unknown setting key is refused, not silently discarded',
        $unknown['status'] === 422,
        'HTTP ' . $unknown['status'] . ' — ' . substr($unknown['raw'], 0, 200)
    );

    $goodUrl = 'https://docs.google.com/spreadsheets/d/' . str_repeat('A', 44) . '/edit#gid=0';
    $saved   = request('PUT', '/settings', ['services_import_sheet_url' => $goodUrl]);

    check(
        'A valid Sheets link saves',
        $saved['status'] === 200
            && ($saved['body']['data']['values']['services_import_sheet_url'] ?? null) === $goodUrl,
        'HTTP ' . $saved['status'] . ' — ' . substr($saved['raw'], 0, 300)
    );

    $me = request('GET', '/auth/me');
    check(
        'Public settings reach the SPA through /auth/me',
        ($me['body']['data']['config']['services_import_sheet_url'] ?? null) === $goodUrl,
        substr($me['raw'], 0, 300)
    );
}

// =====================================================================
section('Timezone');

$originalZone = null;

{
    // The headline assertion. Every timestamp column is DATETIME, so PHP and
    // MySQL write bare wall-clock text into the same columns and compare it
    // against each other. If the two clocks drift apart, audit log times, the
    // login lockout window and the promotion day boundary all go wrong at once.
    $mysqlNow = (string) Database::fetchValue('SELECT NOW()');
    $drift    = abs(strtotime($mysqlNow) - time());

    check(
        'The MySQL and PHP clocks agree',
        $drift < 60,
        'MySQL says ' . $mysqlNow . ', PHP says ' . date('Y-m-d H:i:s')
            . ' — ' . $drift . 's apart'
    );

    $current = request('GET', '/settings');
    $zoneSetting = null;

    foreach ($current['body']['data']['groups'] ?? [] as $group) {
        foreach ($group['settings'] as $setting) {
            if ($setting['key'] === 'site_timezone') {
                $zoneSetting = $setting;
            }
        }
    }

    check(
        'The timezone is offered as a populated dropdown',
        ($zoneSetting['type'] ?? null) === 'select' && count($zoneSetting['options'] ?? []) > 100,
        'Got: ' . json_encode($zoneSetting === null ? null : [
            'type'    => $zoneSetting['type'] ?? null,
            'options' => count($zoneSetting['options'] ?? []),
        ])
    );

    $originalZone = $current['body']['data']['values']['site_timezone'] ?? null;

    $badZone = request('PUT', '/settings', ['site_timezone' => 'Mars/Olympus_Mons']);
    check(
        'An unrecognised timezone is rejected onto its own field',
        $badZone['status'] === 422 && !empty($badZone['body']['error']['fields']['site_timezone']),
        'HTTP ' . $badZone['status'] . ' — ' . substr($badZone['raw'], 0, 300)
    );

    // Deliberately far from America/New_York, so a clock that ignored the
    // setting could not pass by coincidence.
    $savedZone = request('PUT', '/settings', ['site_timezone' => 'Asia/Manila']);
    check(
        'A valid timezone saves',
        $savedZone['status'] === 200
            && ($savedZone['body']['data']['values']['site_timezone'] ?? null) === 'Asia/Manila',
        'HTTP ' . $savedZone['status'] . ' — ' . substr($savedZone['raw'], 0, 300)
    );

    // This process has its own connection, pinned to the env zone at connect
    // time. Re-booting Clock is what the API front controller does on every
    // request, so this exercises the same path the application takes.
    SettingsRepository::forget();
    Clock::forget();
    Clock::boot();

    $manilaNow  = (string) Database::fetchValue('SELECT NOW()');
    $manilaReal = (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Manila')))
        ->format('Y-m-d H:i:s');

    check(
        'Booting the clock moves the MySQL session with it',
        abs(strtotime($manilaNow) - strtotime($manilaReal)) < 120,
        'MySQL says ' . $manilaNow . ', Manila is ' . $manilaReal
    );

    if ($originalZone !== null) {
        request('PUT', '/settings', ['site_timezone' => $originalZone]);
        Clock::forget();
        Clock::boot();
    }
}

// =====================================================================
section('Service import from a Google Sheets link');

{
    // These two are rejected by GoogleSheetUrl before any network call, so
    // they are deterministic on a host with no egress at all.
    $notASheet = request('POST', '/services/import', [
        'source_url' => 'https://example.com/not-a-sheet',
        'dry_run'    => '1',
    ]);

    check(
        'A non-Google source_url is a 422 on the file field',
        $notASheet['status'] === 422 && !empty($notASheet['body']['error']['fields']['file']),
        'HTTP ' . $notASheet['status'] . ' — ' . substr($notASheet['raw'], 0, 300)
    );

    $publishedLink = request('POST', '/services/import', [
        'source_url' => 'https://docs.google.com/spreadsheets/d/e/2PACX-abc/pubhtml',
        'dry_run'    => '1',
    ]);

    check(
        'A "Publish to web" source_url names that specific mistake',
        $publishedLink['status'] === 422
            && str_contains((string) ($publishedLink['body']['error']['fields']['file'] ?? ''), 'Publish to web'),
        'HTTP ' . $publishedLink['status'] . ' — ' . substr($publishedLink['raw'], 0, 300)
    );

    // Network-dependent: assert the SHAPE of the failure, never a success, so
    // the suite still passes on a host that blocks egress.
    if (!extension_loaded('curl')) {
        skip('A well-formed but non-existent sheet fails cleanly', 'cURL is not installed');
    } else {
        $missing = request('POST', '/services/import', [
            'source_url' => 'https://docs.google.com/spreadsheets/d/' . str_repeat('A', 44) . '/edit#gid=0',
            'dry_run'    => '1',
        ]);

        $message = (string) ($missing['body']['error']['fields']['file'] ?? '');

        if ($missing['status'] === 0) {
            skip('A well-formed but non-existent sheet fails cleanly', 'the server could not be reached');
        } else {
            check(
                'A well-formed but non-existent sheet fails with an explanation',
                $missing['status'] === 422 && $message !== '',
                'HTTP ' . $missing['status'] . ' — ' . substr($missing['raw'], 0, 300)
            );
        }
    }

    // Opt-in live test against a real shared sheet.
    $liveSheet = \Mariah\Core\Env::string('SMOKE_SHEET_URL');

    if ($liveSheet === '') {
        skip('Live import from a real Google Sheet', 'set SMOKE_SHEET_URL in .env to enable');
    } else {
        $live = request('POST', '/services/import', ['source_url' => $liveSheet, 'dry_run' => '1']);

        check(
            'A real shared sheet previews',
            $live['status'] === 200
                && ($live['body']['data']['file']['source'] ?? null) === 'google_sheet'
                && ($live['body']['data']['summary']['rows'] ?? 0) > 0,
            'HTTP ' . $live['status'] . ' — ' . substr($live['raw'], 0, 300)
        );
    }
}

// =====================================================================
section('Role-based access control');

$demoEditor = Database::fetchValue("SELECT id FROM users WHERE email = 'editor@demo.local' AND deleted_at IS NULL");
$demoAdmin  = Database::fetchValue("SELECT id FROM users WHERE email = 'admin@demo.local' AND deleted_at IS NULL");

if ($demoEditor === null || $demoAdmin === null) {
    skip('Editor cannot DELETE a service (403)', 'run "php database/seed.php --demo" first');
    skip('Admin cannot modify a Super Admin (403)', 'run "php database/seed.php --demo" first');
} else {
    $superAdminUserId = (int) Database::fetchValue(
        "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
          WHERE r.slug = 'super-admin' AND u.deleted_at IS NULL LIMIT 1"
    );

    // --- Editor ------------------------------------------------------
    if (!login('editor@demo.local', 'DemoPassword123!')) {
        fail('Sign in as the demo Editor', 'Check the demo seed ran and the password is unchanged.');
    } else {
        pass('Sign in as the demo Editor');

        $editorCreate = request('POST', '/services', [
            'name'        => 'Editor Smoke Service',
            'category_id' => $categoryId,
            'price'       => 50,
            'status'      => 'inactive',
        ]);
        check('An Editor CAN create a service (201)', $editorCreate['status'] === 201, describe($editorCreate));

        if (($editorCreate['body']['data']['id'] ?? null) !== null) {
            $createdServiceIds[] = (int) $editorCreate['body']['data']['id'];
        }

        // settings.view ends in ".view", so it would land in the Editor's
        // grants automatically unless permissions.php excludes it by name.
        $editorSettingsRead = request('GET', '/settings');
        check(
            'An Editor cannot read site settings (403)',
            $editorSettingsRead['status'] === 403,
            describe($editorSettingsRead)
        );

        $editorSettingsWrite = request('PUT', '/settings', ['services_import_url_enabled' => '1']);
        check(
            'An Editor cannot change site settings (403)',
            $editorSettingsWrite['status'] === 403,
            describe($editorSettingsWrite)
        );

        // Import is bulk create+edit, which an Editor already holds one record
        // at a time, so the guard lets them through. The request still fails —
        // on content, not on authorization: either the link path is switched
        // off, or GoogleSheetUrl refuses a non-Google host. Both are 422, and
        // the point of the check is that it is no longer 403.
        $editorImport = request('POST', '/services/import', [
            'source_url' => 'https://example.com/x',
            'dry_run'    => '1',
        ]);
        check(
            'An Editor reaches the import endpoint (422, not 403)',
            $editorImport['status'] === 422,
            describe($editorImport)
        );

        if ($importCategory === null) {
            skip('An Editor CAN preview a CSV import (200)', 'no active service category');
        } else {
            $editorCsv = "name,category,price,status\n"
                . 'Editor Import Smoke ' . bin2hex(random_bytes(3))
                . ',"' . str_replace('"', '""', $categoryName) . "\",75,inactive\n";

            // Dry run only: this block asserts access, and must not leave rows
            // behind for the cleanup at the end to chase.
            $editorPreview = importCsv($editorCsv, false);

            check(
                'An Editor CAN preview a CSV import (200)',
                $editorPreview['status'] === 200
                    && ($editorPreview['body']['data']['summary']['create'] ?? null) === 1,
                'HTTP ' . $editorPreview['status'] . ' — ' . substr($editorPreview['raw'], 0, 300)
            );
        }

        $target = $createdServiceIds[0] ?? 1;
        $editorDelete = request('DELETE', "/services/{$target}");
        check(
            'An Editor calling DELETE /services/:id is refused with 403',
            $editorDelete['status'] === 403,
            describe($editorDelete)
        );

        $editorUsers = request('GET', '/users');
        check('An Editor cannot list users (403)', $editorUsers['status'] === 403, describe($editorUsers));

        $editorRoles = request('GET', '/roles');
        check('An Editor cannot view roles (403)', $editorRoles['status'] === 403, describe($editorRoles));

        $editorAudit = request('GET', '/audit-logs');
        check('An Editor cannot read the audit log (403)', $editorAudit['status'] === 403, describe($editorAudit));
    }

    // --- Admin -------------------------------------------------------
    if (!login('admin@demo.local', 'DemoPassword123!')) {
        fail('Sign in as the demo Admin', 'Check the demo seed ran and the password is unchanged.');
    } else {
        pass('Sign in as the demo Admin');

        $adminEditsSuper = request('PUT', "/users/{$superAdminUserId}", ['first_name' => 'Hijacked']);
        check(
            'An Admin cannot modify a Super Admin account (403)',
            $adminEditsSuper['status'] === 403,
            describe($adminEditsSuper)
        );

        $adminEditsRole = request('PUT', '/roles/1', ['description' => 'Nope']);
        check(
            'An Admin cannot edit a role (403)',
            $adminEditsRole['status'] === 403,
            describe($adminEditsRole)
        );

        // The timezone is narrower than settings.edit. These two together are
        // the point: the restriction is per key, not a blanket lockout of the
        // settings screen, which the Admin still holds settings.edit for.
        $adminTimezone = request('PUT', '/settings', ['site_timezone' => 'Asia/Manila']);
        check(
            'An Admin cannot change the timezone (403)',
            $adminTimezone['status'] === 403,
            describe($adminTimezone)
        );

        $adminOtherSetting = request('PUT', '/settings', ['services_import_url_enabled' => 1]);
        check(
            'An Admin CAN still change other settings (200)',
            $adminOtherSetting['status'] === 200,
            describe($adminOtherSetting)
        );

        $adminSeesTimezone = request('GET', '/settings');
        $editableFlag = null;
        foreach ($adminSeesTimezone['body']['data']['groups'] ?? [] as $group) {
            foreach ($group['settings'] as $setting) {
                if ($setting['key'] === 'site_timezone') {
                    $editableFlag = $setting['editable'];
                }
            }
        }
        check(
            'An Admin sees the timezone but is told it is not editable',
            $editableFlag === false,
            'editable was: ' . var_export($editableFlag, true)
        );

        $adminServices = request('GET', '/services');
        check('An Admin CAN list services (200)', $adminServices['status'] === 200, describe($adminServices));
    }

    // --- Staff -------------------------------------------------------
    if (login('staff@demo.local', 'DemoPassword123!')) {
        pass('Sign in as the demo Staff member');

        $staffView = request('GET', '/services');
        check('Staff CAN view services (200)', $staffView['status'] === 200, describe($staffView));

        $staffCreate = request('POST', '/services', [
            'name' => 'Staff should not create', 'category_id' => $categoryId, 'price' => 10,
        ]);
        check('Staff cannot create a service (403)', $staffCreate['status'] === 403, describe($staffCreate));

        // Import reaches Editor but stops there: it is a write, and Staff is
        // read-only. Guards the one place widening the permission could have
        // leaked past $viewOnly.
        $staffImport = request('POST', '/services/import', [
            'source_url' => 'https://example.com/x',
            'dry_run'    => '1',
        ]);
        check('Staff cannot bulk import services (403)', $staffImport['status'] === 403, describe($staffImport));
    } else {
        skip('Staff read-only checks', 'demo staff account not available');
    }
}

// =====================================================================
section('Login rate limiting');

// Run last: a lockout is keyed on email OR IP, so this would block the
// remaining tests if it ran earlier. login_attempts is cleared afterwards.
{
    @unlink($COOKIE);
    $csrfToken = null;
    $csrf = request('GET', '/auth/csrf', null, false);
    $csrfToken = $csrf['body']['data']['csrf_token'] ?? null;

    $limit    = Env::int('LOGIN_MAX_ATTEMPTS', 5);
    $lastCode = 0;

    for ($i = 0; $i <= $limit; $i++) {
        $attempt = request('POST', '/auth/login', [
            'email'    => 'rate-limit-probe@example.com',
            'password' => 'definitely-wrong-' . $i,
        ]);
        $lastCode = $attempt['status'];
    }

    check(
        "Attempt " . ($limit + 1) . " is throttled with 429",
        $lastCode === 429,
        'Last response was HTTP ' . $lastCode . '. Check LOGIN_MAX_ATTEMPTS.'
    );

    Database::run('DELETE FROM login_attempts');
    echo '        (login_attempts cleared so real sign-ins are not locked out)' . PHP_EOL;
}

// =====================================================================
section('Cleanup');

try {
    foreach (array_unique($createdServiceIds) as $id) {
        Database::run('DELETE FROM service_images WHERE service_id = ?', [$id]);
        Database::run('DELETE FROM promotion_services WHERE service_id = ?', [$id]);
        Database::run('DELETE FROM services WHERE id = ?', [$id]);
    }
    Database::run("DELETE FROM audit_logs WHERE description LIKE '%Smoke Test Service%'");
    Database::run("DELETE FROM audit_logs WHERE description LIKE '%Editor Smoke Service%'");
    Database::run("DELETE FROM audit_logs WHERE description LIKE '%smoke-import.csv%'");
    Database::run("DELETE FROM audit_logs WHERE entity_type = 'setting'");

    // Put the template link back the way the operator had it.
    if ($originalSheetUrl !== null) {
        Database::run(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            ['services_import_sheet_url', (string) $originalSheetUrl]
        );
    }

    pass('Test records removed (' . count(array_unique($createdServiceIds)) . ' service(s))');
} catch (\Throwable $e) {
    fail('Test records removed', $e->getMessage());
}

@unlink($COOKIE);

// =====================================================================
echo PHP_EOL . str_repeat('═', 62) . PHP_EOL;
echo sprintf(
    " %s   %s   %s%s",
    colour("{$passed} passed", '32'),
    $failed > 0 ? colour("{$failed} failed", '31') : '0 failed',
    $skipped > 0 ? colour("{$skipped} skipped", '33') : '0 skipped',
    PHP_EOL
);
echo str_repeat('═', 62) . PHP_EOL;

exit($failed > 0 ? 1 : 0);
