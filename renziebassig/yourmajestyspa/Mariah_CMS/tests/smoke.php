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
          $bootstrap['body']['data']['categories']),
    describe($bootstrap)
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
