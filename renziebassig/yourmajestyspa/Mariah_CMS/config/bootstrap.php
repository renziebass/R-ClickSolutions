<?php
declare(strict_types=1);

/**
 * Shared bootstrap for every entrypoint (api/index.php, database/*.php, tests/).
 * Registers the autoloader, loads .env, and configures error handling.
 */

define('MARIAH_ROOT', dirname(__DIR__));

// --- PSR-4 style autoloader for the Mariah\ namespace ---------------------
spl_autoload_register(static function (string $class): void {
    $prefix = 'Mariah\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = MARIAH_ROOT . '/app/' . $relative . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once MARIAH_ROOT . '/app/Core/Env.php';

\Mariah\Core\Env::load(MARIAH_ROOT . '/.env');

// --- Error display --------------------------------------------------------
// Errors are always logged; they are only rendered when APP_DEBUG is on, and
// even then never inside an API JSON response body (see api/index.php).
$debug = \Mariah\Core\Env::bool('APP_DEBUG', false);

error_reporting(E_ALL);
ini_set('display_errors', $debug && PHP_SAPI === 'cli' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', MARIAH_ROOT . '/storage/logs/php-' . date('Y-m-d') . '.log');

date_default_timezone_set('America/New_York'); // Fort Lauderdale, FL

// Ensure writable runtime directories exist.
foreach ([MARIAH_ROOT . '/storage/logs', MARIAH_ROOT . '/storage/uploads'] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}
