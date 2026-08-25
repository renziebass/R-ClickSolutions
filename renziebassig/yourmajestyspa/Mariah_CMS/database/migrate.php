<?php
declare(strict_types=1);

/**
 * Applies every unapplied migration in database/migrations, in filename order.
 *
 *   php database/migrate.php            apply pending migrations
 *   php database/migrate.php --status   list applied / pending
 *   php database/migrate.php --fresh    drop every table and rebuild (destructive)
 *
 * Safe to re-run: applied files are recorded in the `migrations` table.
 * No SSH access? Use setup.php in a browser instead — see DEPLOY-HOSTINGER.md.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use Mariah\Services\Installer;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("migrate.php may only be run from the command line. Use setup.php in a browser instead.\n");
}

$args   = array_slice($argv, 1);
$fresh  = in_array('--fresh', $args, true);
$status = in_array('--status', $args, true);

$installer = new Installer();
$installer->onLog(static fn (string $line) => fwrite(STDOUT, $line . PHP_EOL));

try {
    $installer->ensureDatabase();

    if ($status) {
        fwrite(STDOUT, str_repeat('-', 60) . PHP_EOL);
        foreach ($installer->migrationStatus() as $row) {
            fwrite(STDOUT, sprintf(
                "  %s  %s%s\n",
                $row['applied'] ? '[applied]' : '[pending]',
                $row['filename'],
                $row['applied'] ? '  ' . $row['applied_at'] : ''
            ));
        }
        exit(0);
    }

    if ($fresh) {
        fwrite(STDOUT, "This DROPS every table in the database and all its data. Type YES to continue: ");
        if (trim((string) fgets(STDIN)) !== 'YES') {
            fwrite(STDOUT, 'Aborted. Nothing was changed.' . PHP_EOL);
            exit(0);
        }
        $installer->dropAllTables();
    }

    $installer->migrate();

    fwrite(STDOUT, PHP_EOL . 'Next step:  php database/seed.php' . PHP_EOL);
} catch (\Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
