<?php
declare(strict_types=1);

/**
 * Seeds Mariah_CMS with the real Majesty Day Spa content currently hard-coded
 * in mds_version_a.html, so the CMS starts as a replica of the live site.
 *
 *   php database/seed.php          roles, permissions, Super Admin, and content
 *   php database/seed.php --sync   re-sync roles/permissions only (content untouched)
 *   php database/seed.php --demo   also create demo Admin / Editor / Staff accounts
 *
 * Re-running is safe: every insert is keyed on a slug or email and skipped if
 * that record already exists. Existing content is never overwritten.
 *
 * No SSH access? Use setup.php in a browser instead — see DEPLOY-HOSTINGER.md.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Services\Installer;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("seed.php may only be run from the command line. Use setup.php in a browser instead.\n");
}

$args     = array_slice($argv, 1);
$syncOnly = in_array('--sync', $args, true);
$withDemo = in_array('--demo', $args, true);

$installer = new Installer();
$installer->onLog(static fn (string $line) => fwrite(STDOUT, $line . PHP_EOL));

try {
    $tableExists = Database::fetchValue(
        'SELECT COUNT(*) FROM information_schema.tables
          WHERE table_schema = DATABASE() AND table_name = ?',
        ['roles']
    );

    if ((int) $tableExists === 0) {
        throw new \RuntimeException('Tables are missing. Run "php database/migrate.php" first.');
    }

    fwrite(STDOUT, 'Syncing roles and permissions …' . PHP_EOL);
    $installer->syncRolesAndPermissions();

    if ($syncOnly) {
        fwrite(STDOUT, PHP_EOL . 'Roles and permissions synced. No content was touched.' . PHP_EOL);
        exit(0);
    }

    $adminId = $installer->createSuperAdmin();

    if ($withDemo) {
        $installer->createDemoAccounts($adminId);
    }

    fwrite(STDOUT, 'Seeding content …' . PHP_EOL);
    $installer->seedContent($adminId);

    $adminEmail = strtolower(trim(Env::string('ADMIN_EMAIL')));
    $adminUrl   = rtrim(Env::string('APP_URL', '<your-site>/…/Mariah_CMS'), '/') . '/admin/';

    fwrite(STDOUT, PHP_EOL . str_repeat('=', 64) . PHP_EOL);
    fwrite(STDOUT, ' Seed complete.' . PHP_EOL);
    fwrite(STDOUT, str_repeat('=', 64) . PHP_EOL);
    fwrite(STDOUT, " Sign in at:  {$adminUrl}" . PHP_EOL);
    fwrite(STDOUT, "   Email:     {$adminEmail}" . PHP_EOL);
    fwrite(STDOUT, '   Password:  the ADMIN_PASSWORD from your .env' . PHP_EOL);
    fwrite(STDOUT, PHP_EOL);
    fwrite(STDOUT, ' >> Change this password before the site goes live, and remove' . PHP_EOL);
    fwrite(STDOUT, '    ADMIN_PASSWORD from .env once you have signed in. <<' . PHP_EOL);
    fwrite(STDOUT, str_repeat('=', 64) . PHP_EOL);
} catch (\Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
