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

use Mariah\Core\Database;
use Mariah\Core\Env;

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
    check(
        'GET /public/blog-posts/{slug} returns the post with its paragraphs',
        $onePost['status'] === 200 && !empty($onePost['body']['data']['paragraphs']),
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

        $editorImport = request('POST', '/services/import', [
            'source_url' => 'https://example.com/x',
            'dry_run'    => '1',
        ]);
        check(
            'An Editor cannot bulk import services (403)',
            $editorImport['status'] === 403,
            describe($editorImport)
        );

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
