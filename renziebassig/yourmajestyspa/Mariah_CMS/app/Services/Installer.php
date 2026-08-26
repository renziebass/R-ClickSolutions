<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Database;
use Mariah\Core\Env;

/**
 * Schema migration and content seeding.
 *
 * Shared by the CLI scripts (database/migrate.php, database/seed.php) and the
 * browser installer (setup.php), because shared hosting plans without SSH have
 * no other way to install the database.
 *
 * Every step is idempotent: migrations are recorded in a `migrations` table and
 * skipped once applied; every seed insert is keyed on a slug or email and
 * skipped if that record already exists. Existing content is never overwritten.
 */
final class Installer
{
    /** @var string[] */
    private array $log = [];

    /** @var callable|null */
    private $listener = null;

    /** Streams each line as it happens, for the CLI. */
    public function onLog(callable $listener): void
    {
        $this->listener = $listener;
    }

    public function log(string $message): void
    {
        $this->log[] = $message;
        if ($this->listener !== null) {
            ($this->listener)($message);
        }
    }

    /** @return string[] */
    public function getLog(): array
    {
        return $this->log;
    }

    // =================================================================
    // Connectivity
    // =================================================================

    /**
     * Makes sure the target database exists and is reachable.
     *
     * Order matters on shared hosting: the database is created for you in the
     * control panel and the MySQL user has rights ONLY on that database, so
     * `CREATE DATABASE` fails with "access denied". Connecting first means the
     * normal shared-hosting path never touches a privileged statement.
     */
    public function ensureDatabase(): void
    {
        $name = Env::require('DB_NAME');

        try {
            Database::pdo()->query('SELECT 1');
            $this->log("Connected to database \"{$name}\".");
            return;
        } catch (\PDOException $e) {
            // 1049 = unknown database, which creating one may fix. Every other
            // code is a credentials or grant problem that CREATE would not solve.
            if (($e->errorInfo[1] ?? null) !== 1049) {
                throw new \RuntimeException(self::explainConnectionError($e));
            }
        }

        $this->log("Database \"{$name}\" does not exist — attempting to create it.");

        try {
            Database::serverPdo()->exec(
                "CREATE DATABASE IF NOT EXISTS `{$name}`
                 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
            $this->log("Created database \"{$name}\".");
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                "The database \"{$name}\" does not exist, and this MySQL user is not\n"
                . "allowed to create it (normal on shared hosting).\n\n"
                . "Create the database in your hosting control panel first, then set\n"
                . "DB_NAME, DB_USER and DB_PASS in .env to match.\n\n"
                . "MySQL said: " . $e->getMessage()
            );
        }
    }

    /**
     * Turns a raw PDO connection failure into something actionable.
     *
     * MySQL already distinguishes these cases by error code; surfacing the
     * driver string alone makes every one of them look identical.
     */
    public static function explainConnectionError(\PDOException $e): string
    {
        $code = $e->errorInfo[1] ?? null;

        $explanation = match ($code) {
            1045 => "MySQL rejected the username and password.\n\n"
                . "This happens BEFORE the database is looked at, so the database "
                . "existing or not is not the cause. One of these is true:\n"
                . "  • DB_PASS does not match that user's actual password\n"
                . "  • DB_USER is misspelled, or is missing the account prefix\n"
                . "  • the MySQL user was never actually created\n\n"
                . "Fix: in hPanel → Databases → Management, find the user, use\n"
                . "\"Change password\", and paste the new password into DB_PASS.",

            1044 => "The username and password are correct, but that user has no\n"
                . "rights on this database.\n\n"
                . "Fix: in hPanel → Databases → Management, check the user is listed\n"
                . "against this database. If not, add it — or delete and recreate the\n"
                . "database, which creates and grants the user in one step.",

            1049 => "The credentials are valid, but no database with that name exists.\n\n"
                . "Fix: create it in hPanel → Databases → Management, and make sure\n"
                . "DB_NAME matches the full name including the account prefix.",

            2002, 2003 => "Could not reach the MySQL server at that host.\n\n"
                . "Fix: on shared hosting DB_HOST is almost always \"localhost\".\n"
                . "Only use something else if your control panel explicitly says so.",

            1203, 1226 => "The MySQL account has hit its connection limit.\n\n"
                . "Fix: wait a minute and retry. If it persists, contact your host.",

            default => 'MySQL refused the connection.',
        };

        $detail = 'MySQL said: ' . $e->getMessage();

        return $explanation . "\n\n" . $detail;
    }

    /**
     * Tries a set of credentials without touching .env, and reports which stage
     * failed. Lets the installer tell you whether the problem is the password,
     * the grant, or the database name — rather than one generic refusal.
     *
     * @return array{ok:bool, stage:string, message:string, detail:string}
     */
    public static function testConnection(
        string $host,
        int $port,
        string $database,
        string $user,
        string $password
    ): array {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 8,
        ];

        // Stage 1 — can these credentials authenticate at all?
        try {
            new \PDO("mysql:host={$host};port={$port}", $user, $password, $options);
        } catch (\PDOException $e) {
            return [
                'ok'      => false,
                'stage'   => 'Authentication',
                'message' => self::explainConnectionError($e),
                'detail'  => $e->getMessage(),
            ];
        }

        // Stage 2 — does this user reach that specific database?
        if ($database === '') {
            return [
                'ok'      => false,
                'stage'   => 'Database name',
                'message' => "The username and password work, but no database name was given.\n"
                    . 'Fill in DB_NAME, including the account prefix.',
                'detail'  => '',
            ];
        }

        try {
            $pdo = new \PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $user,
                $password,
                $options
            );

            $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();

            $tables = (int) $pdo->query(
                'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()'
            )->fetchColumn();

            return [
                'ok'      => true,
                'stage'   => 'Connected',
                'message' => "Success. Connected to \"{$database}\" as \"{$user}\".",
                'detail'  => "MySQL {$version} · {$tables} table(s) currently in this database.",
            ];
        } catch (\PDOException $e) {
            return [
                'ok'      => false,
                'stage'   => 'Database access',
                'message' => self::explainConnectionError($e),
                'detail'  => $e->getMessage(),
            ];
        }
    }

    // =================================================================
    // Migrations
    // =================================================================

    private function migrationsDir(): string
    {
        return MARIAH_ROOT . '/database/migrations';
    }

    /** @return string[] absolute paths, in filename order */
    private function migrationFiles(): array
    {
        $files = glob($this->migrationsDir() . '/*.sql') ?: [];
        sort($files, SORT_STRING);

        if ($files === []) {
            throw new \RuntimeException('No migration files found in database/migrations.');
        }

        return $files;
    }

    private function ensureMigrationsTable(): void
    {
        Database::pdo()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                filename   VARCHAR(190) NOT NULL,
                applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_migrations_filename (filename)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /** @return array<int, array{filename:string, applied:bool, applied_at:?string}> */
    public function migrationStatus(): array
    {
        $this->ensureMigrationsTable();

        $applied = [];
        foreach (Database::fetchAll('SELECT filename, applied_at FROM migrations') as $row) {
            $applied[$row['filename']] = $row['applied_at'];
        }

        $status = [];
        foreach ($this->migrationFiles() as $file) {
            $name = basename($file);
            $status[] = [
                'filename'   => $name,
                'applied'    => isset($applied[$name]),
                'applied_at' => $applied[$name] ?? null,
            ];
        }

        return $status;
    }

    /**
     * Applies every unapplied migration.
     *
     * @return int number applied
     */
    public function migrate(): int
    {
        $this->ensureMigrationsTable();

        $applied = array_column(Database::fetchAll('SELECT filename FROM migrations'), 'filename');
        $count   = 0;

        foreach ($this->migrationFiles() as $file) {
            $name = basename($file);

            if (in_array($name, $applied, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                throw new \RuntimeException("Could not read migration {$name}.");
            }

            $this->log("Applying {$name} …");

            // MySQL commits DDL implicitly, so a transaction here would be
            // misleading. A failure stops the run without recording the file,
            // so a corrected re-run resumes from exactly this point.
            try {
                Database::pdo()->exec($sql);
            } catch (\PDOException $e) {
                throw new \RuntimeException("Migration {$name} failed: " . $e->getMessage());
            }

            Database::run('INSERT INTO migrations (filename) VALUES (?)', [$name]);
            $count++;
        }

        $this->log($count === 0
            ? 'Database schema is already up to date.'
            : "Applied {$count} migration(s).");

        return $count;
    }

    /**
     * Drops every table this CMS owns, so migrations can run from scratch.
     *
     * Used instead of DROP DATABASE because shared-hosting MySQL users cannot
     * drop a database. DESTRUCTIVE — callers must confirm first.
     */
    public function dropAllTables(): void
    {
        $pdo = Database::pdo();

        $tables = array_column(
            Database::fetchAll(
                'SELECT table_name AS t FROM information_schema.tables
                  WHERE table_schema = DATABASE() AND table_type = "BASE TABLE"'
            ),
            't'
        );

        if ($tables === []) {
            $this->log('No tables to drop.');
            return;
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            // Names come from information_schema for the current database only.
            $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->log('Dropped ' . count($tables) . ' table(s).');
    }

    public function isInstalled(): bool
    {
        try {
            $count = Database::fetchValue(
                'SELECT COUNT(*) FROM information_schema.tables
                  WHERE table_schema = DATABASE() AND table_name = ?',
                ['users']
            );

            if ((int) $count === 0) {
                return false;
            }

            return (int) Database::fetchValue('SELECT COUNT(*) FROM users') > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    // =================================================================
    // Seeding
    // =================================================================

    /**
     * Public URL prefix for the images already shipping with the site.
     *
     * These URLs are read by BOTH the public page (yourmajestyspa/) and the
     * admin SPA (yourmajestyspa/Mariah_CMS/admin/), which sit at different
     * depths — so a relative path cannot work for both.
     */
    private function assetUrlPrefix(): string
    {
        // APP_URL points at the Mariah_CMS folder; the assets sit beside it.
        $appUrl = rtrim(Env::string('APP_URL', ''), '/');
        if ($appUrl !== '') {
            return preg_replace('#/[^/]+$#', '', $appUrl) . '/assets/';
        }

        // Fallback: STORAGE_URL ends in /Mariah_CMS/storage/uploads.
        $storageUrl = rtrim(Env::string('STORAGE_URL', ''), '/');
        if ($storageUrl !== '' && str_contains($storageUrl, '/Mariah_CMS/')) {
            return substr($storageUrl, 0, strpos($storageUrl, '/Mariah_CMS/')) . '/assets/';
        }

        $this->log('WARNING: APP_URL is not set, so seeded image URLs use a relative path '
            . 'that only resolves from the public page. Set APP_URL and re-run the seed.');

        return 'assets/';
    }

    /** Roles and permissions only — safe to re-run after editing the catalogue. */
    public function syncRolesAndPermissions(): void
    {
        $config = require MARIAH_ROOT . '/config/permissions.php';

        $permissionCount = 0;
        foreach ($config['catalogue'] as $group => $permissions) {
            foreach ($permissions as $slug => $name) {
                Database::run(
                    'INSERT INTO permissions (slug, name, group_name) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), group_name = VALUES(group_name)',
                    [$slug, $name, $group]
                );
                $permissionCount++;
            }
        }
        $this->log("Synced {$permissionCount} permissions.");

        foreach ($config['roles'] as $slug => $role) {
            Database::run(
                'INSERT INTO roles (name, slug, description, is_system) VALUES (?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE description = VALUES(description), is_system = 1',
                [$role['name'], $slug, $role['description']]
            );

            $roleId = (int) Database::fetchValue('SELECT id FROM roles WHERE slug = ?', [$slug]);

            // config/permissions.php is the source of truth, so grants are
            // rewritten wholesale on every sync.
            Database::run('DELETE FROM role_permissions WHERE role_id = ?', [$roleId]);

            foreach ($role['permissions'] as $permissionSlug) {
                $permissionId = Database::fetchValue(
                    'SELECT id FROM permissions WHERE slug = ?',
                    [$permissionSlug]
                );

                if ($permissionId !== null) {
                    Database::run(
                        'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                        [$roleId, (int) $permissionId]
                    );
                }
            }

            $this->log(sprintf('  %-12s %d permissions', $slug, count($role['permissions'])));
        }
    }

    /**
     * Creates the first Super Admin from explicit values, or from
     * ADMIN_EMAIL / ADMIN_PASSWORD in .env.
     *
     * @return int the Super Admin's user id
     */
    public function createSuperAdmin(
        ?string $email = null,
        ?string $password = null,
        ?string $firstName = null,
        ?string $lastName = null
    ): int {
        $email    = strtolower(trim($email ?? Env::string('ADMIN_EMAIL')));
        $password = $password ?? Env::string('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            throw new \RuntimeException(
                'An administrator email and password are required to create the first account. '
                . 'Set ADMIN_EMAIL and ADMIN_PASSWORD in .env, or supply them in the installer.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('The administrator email address is not valid.');
        }

        if (strlen($password) < 10) {
            throw new \RuntimeException('The administrator password must be at least 10 characters.');
        }

        $roleId   = (int) Database::fetchValue("SELECT id FROM roles WHERE slug = 'super-admin'");
        $existing = Database::fetchValue('SELECT id FROM users WHERE email = ?', [$email]);

        if ($existing !== null) {
            $this->log("Super Admin already exists: {$email} (left unchanged).");
            return (int) $existing;
        }

        Database::run(
            'INSERT INTO users (first_name, last_name, email, password_hash, role_id, status)
             VALUES (?, ?, ?, ?, ?, "active")',
            [
                $firstName ?? Env::string('ADMIN_FIRST_NAME', 'Majesty'),
                $lastName  ?? Env::string('ADMIN_LAST_NAME', 'Administrator'),
                $email,
                password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                $roleId,
            ]
        );

        $id = Database::insertId();
        $this->log("Created Super Admin: {$email}");
        $this->log('>> Change this password before the site goes live. <<');

        return $id;
    }

    /** Demo Admin / Editor / Staff accounts, for testing role restrictions. */
    public function createDemoAccounts(int $createdBy): void
    {
        $hash = password_hash('DemoPassword123!', PASSWORD_BCRYPT, ['cost' => 12]);

        foreach ([
            ['Alicia', 'Reyes',    'admin@demo.local',  'admin'],
            ['Marcus', 'Devlin',   'editor@demo.local', 'editor'],
            ['Nia',    'Fontaine', 'staff@demo.local',  'staff'],
        ] as [$first, $last, $email, $roleSlug]) {
            if (Database::fetchValue('SELECT id FROM users WHERE email = ?', [$email]) !== null) {
                continue;
            }

            $roleId = (int) Database::fetchValue('SELECT id FROM roles WHERE slug = ?', [$roleSlug]);

            Database::run(
                'INSERT INTO users (first_name, last_name, email, password_hash, role_id, status, created_by)
                 VALUES (?, ?, ?, ?, ?, "active", ?)',
                [$first, $last, $email, $hash, $roleId, $createdBy]
            );
        }

        $this->log('Created demo accounts (password: DemoPassword123!) — delete these before production.');
    }

    /**
     * The real Majesty Day Spa content currently hard-coded in
     * mds_version_a.html, so the CMS starts as a replica of the live site.
     */
    public function seedContent(int $adminId): void
    {
        $assetBase = $this->assetUrlPrefix();
        $mediaIds  = $this->seedMedia($adminId, $assetBase);

        $media = static fn (?string $file): ?int => $file === null ? null : ($mediaIds[$file] ?? null);

        $categoryIds = $this->seedCategories($adminId, $media);
        $this->seedServices($adminId, $categoryIds, $media);
        $this->seedSpecials($adminId, $media);
        $this->seedPromotions($adminId, $media);
        $this->seedGiftCards($adminId, $media);
        $this->seedShop($adminId);
        $this->seedBlog($adminId, $media);

        Database::run(
            'INSERT INTO audit_logs (user_id, user_label, action, entity_type, description)
             VALUES (?, ?, ?, ?, ?)',
            [$adminId, 'System', 'seeded', 'system', 'Database seeded with Majesty Day Spa content']
        );
    }

    /** @return array<string,int> file name => media id */
    private function seedMedia(int $adminId, string $assetBase): array
    {
        $assetDir = dirname(MARIAH_ROOT) . '/assets';
        $mediaIds = [];

        $assets = [
            'sports_massage.png'          => 'Guest receiving a sports massage',
            'prenatal_massage.png'        => 'Guest receiving a prenatal massage',
            'hot_stone.png'               => 'Guest receiving a hot stone massage',
            'couples_massage.jpg'         => 'Couple receiving a side-by-side massage',
            'couples_river.png'           => 'Waterfront couples escape suite',
            'facial.jpg'                  => 'Guest receiving a facial treatment',
            'massage1.jpg'                => 'Relaxing massage treatment room',
            'massage2.png'                => 'Full body relaxation massage',
            'specials_img.png'            => 'Guest receiving a massage in a couples suite',
            'summer_reset.png'            => 'Majesty Summer Reset treatment',
            'member.png'                  => 'Crown Society membership',
            'Majesty-gift-card-white.png' => 'Majesty Day Spa gift card',
            'Majesty-gift-card.jpeg'      => 'Majesty Day Spa gift card',
            'majesty_day_spa_logo.png'    => 'Majesty Day Spa logo',
            'majesty_3chains.png'         => 'Majesty Day Spa emblem',
        ];

        foreach ($assets as $fileName => $altText) {
            $existing = Database::fetchValue('SELECT id FROM media WHERE file_name = ?', [$fileName]);

            if ($existing !== null) {
                $mediaIds[$fileName] = (int) $existing;
                continue;
            }

            $absolute = $assetDir . '/' . $fileName;
            $size     = is_file($absolute) ? (int) filesize($absolute) : 0;
            $width    = null;
            $height   = null;

            if ($size > 0 && ($dimensions = @getimagesize($absolute)) !== false) {
                [$width, $height] = $dimensions;
            }

            $mime = match (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
                'png'   => 'image/png',
                'webp'  => 'image/webp',
                default => 'image/jpeg',
            };

            Database::run(
                'INSERT INTO media
                    (file_name, original_name, file_path, file_url, mime_type,
                     file_size, width, height, alt_text, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$fileName, $fileName, $fileName, $assetBase . $fileName, $mime,
                 $size, $width, $height, $altText, $adminId]
            );

            $mediaIds[$fileName] = Database::insertId();
        }

        $this->log('Media library: ' . count($mediaIds) . ' records.');

        return $mediaIds;
    }

    /** @return array<string,int> slug => id */
    private function seedCategories(int $adminId, callable $media): array
    {
        $categories = [
            ['Massage',            'massage',            'Therapeutic and relaxation bodywork performed by licensed massage therapists.', 'i-hands', 'massage1.jpg',      1],
            ['Facials',            'facials',            'Clinical and corrective facial treatments tailored to your skin.',              'i-drop',  'facial.jpg',        2],
            ['Body Treatments',    'body-treatments',    'Exfoliation, wraps and full-body rituals that renew the skin.',                 'i-leaf',  'massage2.png',      3],
            ['Luxury Experiences', 'luxury-experiences', 'Signature multi-hour experiences and waterfront couples suites.',               'i-crown', 'couples_river.png', 4],
            ['Wellness',           'wellness',           'Salt lounge, recovery and restorative wellness sessions.',                      'i-spark', 'specials_img.png',  5],
            ['Packages',           'packages',           'Curated pairings that combine treatments at a better value.',                   'i-gift',  'summer_reset.png',  6],
        ];

        $ids = [];

        foreach ($categories as [$name, $slug, $description, $icon, $image, $order]) {
            $existing = Database::fetchValue('SELECT id FROM service_categories WHERE slug = ?', [$slug]);

            if ($existing !== null) {
                $ids[$slug] = (int) $existing;
                continue;
            }

            Database::run(
                'INSERT INTO service_categories
                    (name, slug, description, icon_key, media_id, status, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, "active", ?, ?, ?)',
                [$name, $slug, $description, $icon, $media($image), $order, $adminId, $adminId]
            );

            $ids[$slug] = Database::insertId();
        }

        $this->log('Service categories: ' . count($ids) . '.');

        return $ids;
    }

    private function seedServices(int $adminId, array $categoryIds, callable $media): void
    {
        $booker = 'https://go.booker.com/location/yourmajestyspa/';

        $services = [
            ['massage', 'Sports Massage',
                'Deep tissue, assisted stretching and targeted pressure for active guests.',
                'Perfect for athletes or active guests. Combines deep tissue, assisted stretching, and targeted pressure to improve flexibility, reduce injury, and speed recovery.',
                150.00, 'from $150', 50, '50 min', 'i-hands', 'sports_massage.png',
                $booker . 'detail-summary/4882465', 1, null, 1],

            ['massage', 'Prenatal Massage',
                'A soothing, safe escape designed for mothers-to-be.',
                'A soothing, safe escape designed for mothers-to-be. Pillows and gentle touch ease discomfort in the lower back, hips, and legs while promoting calm for you and baby.',
                150.00, 'from $150', 75, '60 – 90 min', 'i-hands', 'prenatal_massage.png',
                $booker . 'detail-summary/3904887', 0, null, 2],

            ['massage', 'Hot Stone Massage',
                'Heated basalt stones amplify relaxation and melt deep muscle tension.',
                'Smooth, heated basalt stones amplify relaxation while melting away deep muscle tension. A sensory journey ideal for grounding energy and recharging the soul.',
                210.00, '$210', 120, '2 hours', 'i-stone', 'hot_stone.png',
                $booker . 'detail-summary/4773861', 1, null, 3],

            ['massage', 'Couples Massage',
                'Unwind side by side with aromatherapy, sugar scrub and warm towels.',
                'Unwind side by side with our Wine & Couples Massage experience. Enjoy a personalized full-body relaxation session enhanced with aromatherapy, organic sugar scrub & soothing warm towels. Prepaid members only — call for questions. A $25 deposit is required to reserve your appointment and will be applied toward your final balance at checkout, which may include gratuity or valet parking.',
                300.00, '$300', 50, '50 min', 'i-hands', 'couples_massage.jpg',
                $booker . 'detail-summary/4895229', 1, null, 4],

            ['facials', 'HydraFacial Classic',
                'Vortex cleansing, exfoliation and hydration in one treatment.',
                'Our most requested facial. Vortex technology cleanses, exfoliates, extracts and infuses the skin with peptides and antioxidants for instant, visible radiance with no downtime.',
                199.00, '$199 – $225', 60, '60 min', 'i-drop', 'facial.jpg',
                $booker . 'service-menu', 1, null, 5],

            ['facials', 'Signature Facial',
                'A customised facial tailored to your skin on the day.',
                'A fully customised facial built around your skin. Deep cleansing, gentle exfoliation, extractions where needed, a treatment mask and a finishing serum selected by your esthetician.',
                100.00, 'from $100', 50, '50 min', 'i-drop', 'facial.jpg',
                $booker . 'service-menu', 0, null, 6],

            ['facials', 'Anti-Aging Facial',
                'Peptides and antioxidants to firm, smooth and brighten.',
                'A corrective treatment focused on fine lines and loss of firmness. Layered peptides, antioxidants and a lifting mask leave skin visibly plumper and more even.',
                140.00, 'from $140', 60, '60 min', 'i-spark', 'facial.jpg',
                $booker . 'service-menu', 0, null, 7],

            ['facials', 'Acne Clarifying Facial',
                'Steam, extractions and clarifying actives for congested skin.',
                'Designed for breakout-prone skin. Warm steam softens congestion before thorough extractions, followed by clarifying actives and a calming mask to reduce redness.',
                110.00, 'from $110', 50, '50 min', 'i-drop', 'facial.jpg',
                $booker . 'service-menu', 0, null, 8],

            ['body-treatments', 'Exfoliating Salt Scrub',
                'Mineral salt polish that leaves skin soft and luminous.',
                'A full-body mineral salt polish that sloughs away dull, dry skin. Finished with a hydrating application that leaves the skin soft, smooth and luminous.',
                85.00, 'from $85', 50, '50 min', 'i-leaf', 'massage2.png',
                $booker . 'service-menu', 0, null, 9],

            ['body-treatments', 'Detox Body Wrap',
                'A warming wrap that draws out impurities and eases bloating.',
                'A warming, mineral-rich wrap that draws out impurities while you rest beneath warm linens. Guests often leave feeling lighter, less bloated and deeply relaxed.',
                95.00, 'from $95', 50, '50 min', 'i-leaf', 'massage2.png',
                $booker . 'service-menu', 0, null, 10],

            ['body-treatments', 'Full Body Renewal Ritual',
                'A multi-step ritual: scrub, wrap and massage.',
                'Our most complete body treatment. A full-body scrub, a nourishing wrap and a finishing massage combine into a two-hour ritual that renews skin from head to toe.',
                220.00, 'from $220', 120, '2 hrs', 'i-leaf', 'massage1.jpg',
                $booker . 'service-menu', 1, null, 11],

            ['luxury-experiences', 'Waterfront Couples Escape',
                'A riverfront suite for two, with treatments side by side.',
                'Ninety minutes in our riverfront suite. Two therapists, two tables and uninterrupted views of the New River — our signature experience for anniversaries and celebrations.',
                395.00, 'from $395', 90, '90 min', 'i-boat', 'couples_river.png',
                $booker . 'service-menu', 1, null, 12],

            ['luxury-experiences', 'Sunset Renewal Ritual',
                'A golden-hour pairing of massage and facial.',
                'Booked for late afternoon, this pairing combines a relaxation massage with a hydrating facial so you leave as the light turns golden over Las Olas.',
                310.00, 'from $310', 100, '1 hr & 40 mins', 'i-crown', 'specials_img.png',
                $booker . 'service-menu', 0, null, 13],

            ['packages', "The King's Ultimate Relaxation",
                'A two-hour luxury experience built for hardworking men.',
                'Perfect for dads, husbands, grandfathers, father figures, and hardworking men who deserve a true luxury spa experience.',
                249.00, '$249', 120, '2 hours', 'i-crown', 'massage1.jpg',
                $booker . 'package-detail-summary/504132', 1, 1, 14],

            ['packages', 'Sports Massage and Facial',
                'Muscle recovery paired with a revitalising facial.',
                'Relieve muscle tension, improve flexibility, and support recovery, followed by a revitalizing facial that deeply cleanses, hydrates, and restores your natural glow.',
                275.00, '$275', 100, '1 hr & 40 mins', 'i-hands', 'sports_massage.png',
                $booker . 'package-detail-summary/494778', 1, 2, 15],

            ['packages', 'Recovery Hybrid',
                'Deep tissue, stretching and targeted pressure for recovery.',
                'Perfect for athletes or active guests. Combines deep tissue, assisted stretching, and targeted pressure to improve flexibility, reduce injury, and speed recovery.',
                210.00, '$210', 80, '1 hr & 20 mins', 'i-hands', 'sports_massage.png',
                $booker . 'package-detail-summary/494772', 0, 3, 16],
        ];

        $count = 0;

        foreach ($services as $s) {
            [$categorySlug, $name, $short, $description, $price, $priceDisplay,
             $durationMinutes, $durationDisplay, $icon, $image, $bookingUrl,
             $featured, $mostLoved, $order] = $s;

            $slug = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? ''), '-');

            if (Database::fetchValue('SELECT id FROM services WHERE slug = ?', [$slug]) !== null) {
                continue;
            }

            Database::run(
                'INSERT INTO services
                    (category_id, name, slug, short_description, description,
                     price, price_display, duration_minutes, duration_display,
                     icon_key, booking_url, media_id, status, featured,
                     most_loved_rank, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "active", ?, ?, ?, ?, ?)',
                [
                    $categoryIds[$categorySlug], $name, $slug, $short, $description,
                    $price, $priceDisplay, $durationMinutes, $durationDisplay,
                    $icon, $bookingUrl, $media($image),
                    $featured, $mostLoved, $order, $adminId, $adminId,
                ]
            );

            $serviceId = Database::insertId();
            $count++;

            if ($media($image) !== null) {
                Database::run(
                    'INSERT INTO service_images (service_id, media_id, display_order, is_primary, uploaded_by)
                     VALUES (?, ?, 0, 1, ?)',
                    [$serviceId, $media($image), $adminId]
                );
            }
        }

        $this->log("Services: {$count}.");
    }

    private function seedSpecials(int $adminId, callable $media): void
    {
        $booker = 'https://go.booker.com/location/yourmajestyspa/';

        $specials = [
            ['Majesty Summer Reset', 'majesty-summer-reset', 'Seasonal',
                '50 min Swedish Massage and HydraFacial Classic.',
                215.00, null, 299.00, 'summer_reset.png',
                $booker . 'package-detail-summary/495139', 1, 1],

            ['Couples Summer Escape', 'couples-summer-escape', 'For two',
                'A relaxing 1-hour-and-40-minute summer spa experience designed for two guests to unwind and reconnect. This package includes a 50-minute Couples Swedish Massage followed by a 50-minute Salt Lounge Experience, combining full-body relaxation with a peaceful wellness reset. Ideal for couples, anniversaries, staycations, birthdays, or a shared summer escape.',
                299.00, null, 350.00, 'couples_river.png',
                $booker . 'package-detail-summary/506705', 1, 2],

            ['Crown Society', 'crown-society', 'Members',
                'A monthly treatment, priority scheduling, and member-only pricing on every add-on and retail product.',
                109.00, 'From $109 / mo', null, 'member.png',
                $booker . 'service-menu', 1, 3],
        ];

        $count = 0;

        foreach ($specials as [$title, $slug, $badge, $description, $price,
                               $priceDisplay, $compareAt, $image, $url, $featured, $order]) {
            if (Database::fetchValue('SELECT id FROM specials WHERE slug = ?', [$slug]) !== null) {
                continue;
            }

            Database::run(
                'INSERT INTO specials
                    (title, slug, description, media_id, badge_label, price, price_display,
                     compare_at_price, booking_url, status, featured, display_order,
                     created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "published", ?, ?, ?, ?)',
                [$title, $slug, $description, $media($image), $badge, $price, $priceDisplay,
                 $compareAt, $url, $featured, $order, $adminId, $adminId]
            );

            $count++;
        }

        $this->log("Specials: {$count}.");
    }

    private function seedPromotions(int $adminId, callable $media): void
    {
        $booker = 'https://go.booker.com/location/yourmajestyspa/service-menu';

        // One of each derived state, so the schedule resolver is visibly working.
        $promotions = [
            ['Midweek Massage Offer', 'midweek-massage-offer',
                'Book any 50-minute massage Monday through Thursday and save 15%.',
                'percentage', 15.00, null, null, 'Midweek',
                date('Y-m-d', strtotime('-14 days')), date('Y-m-d', strtotime('+45 days')),
                1, 1, 'massage1.jpg'],

            ['Autumn Glow Facial Event', 'autumn-glow-facial-event',
                'Save $40 on any facial during our autumn skin event.',
                'fixed', 40.00, null, null, 'Coming soon',
                date('Y-m-d', strtotime('+21 days')), date('Y-m-d', strtotime('+51 days')),
                0, 2, 'facial.jpg'],

            ['Spring Renewal Special', 'spring-renewal-special',
                'Our spring body ritual pairing at a fixed seasonal price.',
                'special_price', 0.00, 260.00, 199.00, 'Ended',
                date('Y-m-d', strtotime('-120 days')), date('Y-m-d', strtotime('-30 days')),
                0, 3, 'massage2.png'],
        ];

        $count = 0;

        foreach ($promotions as [$title, $slug, $description, $type, $value, $originalPrice,
                                 $promoPrice, $badge, $start, $end, $featured, $order, $image]) {
            if (Database::fetchValue('SELECT id FROM promotions WHERE slug = ?', [$slug]) !== null) {
                continue;
            }

            Database::run(
                'INSERT INTO promotions
                    (title, slug, description, media_id, discount_type, discount_value,
                     original_price, promo_price, badge_label, booking_url,
                     start_date, end_date, status, featured, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "published", ?, ?, ?, ?)',
                [$title, $slug, $description, $media($image), $type, $value,
                 $originalPrice, $promoPrice, $badge, $booker,
                 $start, $end, $featured, $order, $adminId, $adminId]
            );

            $count++;
        }

        $this->log("Promotions: {$count} (active, scheduled and expired states).");
    }

    private function seedGiftCards(int $adminId, callable $media): void
    {
        $booker = 'https://go.booker.com/location/yourmajestyspa/';

        $giftCards = [
            ['gift_card', 'Majesty Day Spa Gift Card', 'majesty-day-spa-gift-card',
                'A thoughtful gift. A memorable experience. A moment of well-deserved self-care. Majesty Day Spa gift cards can be used toward any massage, facial, body treatment, or luxury spa experience, including our signature couples and oceanfront packages. Perfect for birthdays, anniversaries, holidays, thank-you gifts, or simply showing someone they deserve to be pampered.',
                null, null, 'one_time', $booker . 'buy/gift-certificate',
                'Any treatment', 'Majesty-gift-card-white.png', 1, 1],

            ['membership', 'Crown Society Membership', 'crown-society-membership',
                'A monthly treatment, priority scheduling, and member-only pricing on every add-on and retail product. Unused monthly treatments roll over, and members receive early access to seasonal specials.',
                109.00, 'From $109 / mo', 'monthly', $booker . 'service-menu',
                'Members', 'member.png', 1, 2],
        ];

        $count = 0;

        foreach ($giftCards as [$type, $title, $slug, $description, $price, $priceDisplay,
                                $interval, $url, $badge, $image, $featured, $order]) {
            if (Database::fetchValue('SELECT id FROM gift_cards WHERE slug = ?', [$slug]) !== null) {
                continue;
            }

            Database::run(
                'INSERT INTO gift_cards
                    (type, title, slug, description, media_id, price, price_display,
                     price_interval, purchase_url, badge_label, status, featured,
                     display_order, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "active", ?, ?, ?, ?)',
                [$type, $title, $slug, $description, $media($image), $price, $priceDisplay,
                 $interval, $url, $badge, $featured, $order, $adminId, $adminId]
            );

            $count++;
        }

        $this->log("Gift cards and memberships: {$count}.");
    }

    /**
     * Three starter articles for the website's Journal, so the section has
     * something real in it the day the CMS goes live. Post bodies are plain
     * text: a blank line starts a new paragraph.
     */
    private function seedBlog(int $adminId, callable $media): void
    {
        $topicIds = [];

        foreach ([
            ['Massage & Bodywork', 'massage-and-bodywork', 'Techniques, recovery and what to expect on the table.', 1],
            ['Skin & Facials',     'skin-and-facials',     'Treatment guides and everyday skincare that holds up.', 2],
            ['Spa Rituals',        'spa-rituals',          'Slow mornings, salt rooms and the art of an unhurried visit.', 3],
        ] as [$name, $slug, $description, $order]) {
            $existing = Database::fetchValue('SELECT id FROM blog_categories WHERE slug = ?', [$slug]);

            if ($existing !== null) {
                $topicIds[$slug] = (int) $existing;
                continue;
            }

            Database::run(
                'INSERT INTO blog_categories
                    (name, slug, description, status, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, "active", ?, ?, ?)',
                [$name, $slug, $description, $order, $adminId, $adminId]
            );

            $topicIds[$slug] = Database::insertId();
        }

        $posts = [
            [
                'How to Get the Most Out of Your First Massage',
                'how-to-get-the-most-out-of-your-first-massage',
                'massage-and-bodywork',
                'A first massage is mostly a conversation. Here is what to say, what to expect, and how to leave feeling the way you hoped to.',
                "A first massage is mostly a conversation. The table, the oils and the quiet room matter, but the difference between a pleasant hour and a treatment that actually changes how your week feels comes down to what you tell your therapist before they start.\n\nArrive ten minutes early. Not because we are strict about it, but because those ten minutes are when your shoulders come down from your ears. Fill in your intake form honestly, especially the parts about injuries, recent surgery, pregnancy and medication. None of it is judged and all of it changes the plan.\n\nWhen your therapist asks about pressure, answer in specifics. \"Firm through my shoulders, light everywhere else\" is more useful than \"medium\". You can change your mind halfway through, and you should: pressure that felt right at minute five often needs adjusting by minute thirty-five.\n\nTell us where you actually hurt, not where you think the problem is. Desk tension usually shows up in the neck, but it is often anchored in the chest and forearms. A good therapist will work the whole chain rather than only the spot that aches.\n\nBreathe normally. Holding your breath through a tender area tightens exactly the muscle we are trying to release. If a stroke is too much, say so before it becomes something you brace against.\n\nAfterward, drink water, skip the gym for the rest of the day, and give yourself an unstructured evening if you can. Deep work can leave you pleasantly sore for a day, much like a good workout. If anything feels wrong rather than tender, call us — we would always rather hear about it.",
                'massage1.jpg',
                'Majesty Day Spa',
                'first visit, massage, guest guide',
                'How to Get the Most Out of Your First Massage | Majesty Day Spa',
                'What to tell your massage therapist, how to talk about pressure, and how to feel your best afterward.',
                1, 1, '-21 days',
            ],
            [
                'HydraFacial vs. Classic Facial: Which One Is For You',
                'hydrafacial-vs-classic-facial',
                'skin-and-facials',
                'Both leave you glowing. They get there differently, and one of them suits your skin and your schedule better than the other.',
                "Guests ask us this almost every week, usually while booking something for an event on Saturday. Both treatments leave you glowing. They get there differently, and the right answer depends on your skin, your calendar and how much downtime you can afford.\n\nA classic facial is a hands-on treatment: cleanse, exfoliate, steam, extractions, massage, mask. The massage is a real part of it, not a flourish, and it is the reason people come out of a classic facial looking rested as well as clearer. It suits skin that is generally healthy and wants maintenance, and it suits anyone who wants the hour to feel like a spa treatment rather than a procedure.\n\nA HydraFacial uses a device to cleanse, exfoliate, extract and infuse serums in a single pass. It is methodical and consistent, it handles congestion and blackheads more thoroughly than hands alone, and it delivers hydrating and brightening serums straight after the exfoliation, when skin absorbs them best. There is essentially no downtime, which is why it is the one we recommend before a wedding or a photo shoot.\n\nIf your skin is congested, uneven in tone, or you are short on time before an event, book the HydraFacial. If your skin is behaving and you want the treatment to feel restorative as much as corrective, book the classic. If you are managing active breakouts, rosacea or recent sun damage, book either and tell your esthetician first — the products change even when the treatment name does not.\n\nWhichever you choose, come with clean skin if you can, skip retinol for two days beforehand, and plan to wear sunscreen religiously for the week after. Freshly exfoliated skin burns faster than you expect.",
                'facial.jpg',
                'Majesty Day Spa',
                'facials, hydrafacial, skincare',
                'HydraFacial vs. Classic Facial: Which One Is For You | Majesty Day Spa',
                'A plain comparison of two facial treatments, what each one fixes, and how to choose before an event.',
                1, 2, '-12 days',
            ],
            [
                'Making a Couples Visit Feel Unhurried',
                'making-a-couples-visit-feel-unhurried',
                'spa-rituals',
                'The couples suite is easy to rush through. A few small decisions turn a booking into an afternoon you both remember.',
                "The couples suite is the easiest room in the spa to rush through. Two people arrive from two different days, get an hour of quiet, and leave straight back into traffic. A few small decisions turn the same booking into an afternoon you both remember.\n\nBook later than you think you need to. An appointment at four leaves the evening open on the other side; an appointment at noon puts a deadline on the end of your massage. If you are marking an anniversary or a birthday, the hour after matters as much as the hour on the table.\n\nAdd the salt lounge before, not after. Twenty minutes in the salt room settles your breathing and warms your muscles, which means your therapist starts on a body that is already halfway relaxed rather than spending the first fifteen minutes getting there.\n\nAgree on pressure separately. Couples often book the same treatment and then quietly endure a pressure that suits the other person. You are in the same room, not on the same table — ask for what your own back needs.\n\nLeave the phones in the locker. Not on silent, in the locker. The single most common thing guests tell us afterward is that they did not check their phone once, and they say it as though it surprised them.\n\nFinally, plan nothing immediately after. A walk along the water, a slow lunch, or simply driving home without a schedule keeps the effect of the treatment intact. The massage is an hour. The unhurried afternoon around it is the part that actually resets you.",
                'couples_river.png',
                'Majesty Day Spa',
                'couples, rituals, planning',
                'Making a Couples Visit Feel Unhurried | Majesty Day Spa',
                'How to plan a couples spa visit so the treatment is the middle of the afternoon, not the whole of it.',
                0, 3, '-4 days',
            ],
        ];

        $count = 0;

        foreach ($posts as [$title, $slug, $topicSlug, $excerpt, $content, $image,
                            $author, $tags, $metaTitle, $metaDescription, $featured, $order, $ago]) {
            if (Database::fetchValue('SELECT id FROM blog_posts WHERE slug = ?', [$slug]) !== null) {
                continue;
            }

            $words = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            Database::run(
                'INSERT INTO blog_posts
                    (category_id, title, slug, excerpt, content, media_id, author_name,
                     read_minutes, tags, meta_title, meta_description, status,
                     published_at, featured, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "published", ?, ?, ?, ?, ?)',
                [
                    $topicIds[$topicSlug] ?? null, $title, $slug, $excerpt, $content,
                    $media($image), $author, max(1, (int) ceil(count($words) / 200)),
                    $tags, $metaTitle, $metaDescription,
                    date('Y-m-d', strtotime($ago)) . ' 09:00:00',
                    $featured, $order, $adminId, $adminId,
                ]
            );

            $count++;
        }

        $this->log('Blog topics: ' . count($topicIds) . ", posts: {$count}.");
    }

    private function seedShop(int $adminId): void
    {
        $brandIds = [];
        foreach ([
            ['Skin Script', 'skin-script', 'Botanical, results-driven', 1],
            ['PCA SKIN',    'pca-skin',    'Clinical corrective care',  2],
        ] as [$name, $slug, $tagline, $order]) {
            $existing = Database::fetchValue('SELECT id FROM product_brands WHERE slug = ?', [$slug]);

            if ($existing !== null) {
                $brandIds[$slug] = (int) $existing;
                continue;
            }

            Database::run(
                'INSERT INTO product_brands (name, slug, tagline, status, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, "active", ?, ?, ?)',
                [$name, $slug, $tagline, $order, $adminId, $adminId]
            );

            $brandIds[$slug] = Database::insertId();
        }

        $categoryIds = [];
        foreach ([
            ['Cleansers', 'cleansers', 1], ['Exfoliants', 'exfoliants', 2],
            ['Toners', 'toners', 3], ['Serums', 'serums', 4],
            ['Moisturizers', 'moisturizers', 5], ['SPF', 'spf', 6],
            ['Retail Masks', 'retail-masks', 7], ['Eye & Lip Care', 'eye-lip-care', 8],
            ['Travel Kits', 'travel-kits', 9],
        ] as [$name, $slug, $order]) {
            $existing = Database::fetchValue('SELECT id FROM product_categories WHERE slug = ?', [$slug]);

            if ($existing !== null) {
                $categoryIds[$slug] = (int) $existing;
                continue;
            }

            Database::run(
                'INSERT INTO product_categories (name, slug, status, display_order, created_by, updated_by)
                 VALUES (?, ?, "active", ?, ?, ?)',
                [$name, $slug, $order, $adminId, $adminId]
            );

            $categoryIds[$slug] = Database::insertId();
        }

        $products = [
            ['skin-script', 'exfoliants', 'Glycolic & Retinol Pads', 'glycolic-retinol-pads',
                'Pre-soaked pads that resurface with glycolic acid and refine texture with retinol. Use two to three evenings a week.',
                35.00, 'i-pad', 'Best seller', 1, 1],
            ['skin-script', 'toners', 'Cucumber Hydration Toner', 'cucumber-hydration-toner',
                'An alcohol-free toner that calms and hydrates after cleansing. Suitable for sensitive and post-treatment skin.',
                20.00, 'i-bottle', null, 0, 2],
            ['skin-script', 'cleansers', 'Green Tea Citrus Cleanser', 'green-tea-citrus-cleanser',
                'A gentle daily gel cleanser with green tea antioxidants and citrus brighteners. Removes impurities without stripping.',
                17.50, 'i-pump', null, 0, 3],
            ['pca-skin', 'toners', 'Clarifying Toner Pads', 'clarifying-toner-pads',
                'Salicylic acid pads that keep congestion and breakouts in check between facials.',
                27.00, 'i-jar', 'Low stock', 0, 4],
        ];

        $productCount = 0;

        foreach ($products as [$brandSlug, $categorySlug, $name, $slug, $description,
                               $price, $icon, $badge, $featured, $order]) {
            if (Database::fetchValue('SELECT id FROM products WHERE slug = ?', [$slug]) !== null) {
                continue;
            }

            Database::run(
                'INSERT INTO products
                    (brand_id, category_id, name, slug, description, price, icon_key,
                     badge_label, status, featured, display_order, created_by, updated_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active", ?, ?, ?, ?)',
                [$brandIds[$brandSlug] ?? null, $categoryIds[$categorySlug] ?? null,
                 $name, $slug, $description, $price, $icon, $badge,
                 $featured, $order, $adminId, $adminId]
            );

            $productCount++;
        }

        $this->log('Shop: ' . count($brandIds) . ' brands, ' . count($categoryIds)
            . " product types, {$productCount} products.");
    }

    // =================================================================
    // Environment checks — surfaced by setup.php before anything runs
    // =================================================================

    /** @return array<int, array{label:string, ok:bool, detail:string, fatal:bool}> */
    public static function environmentChecks(): array
    {
        $checks = [];

        $checks[] = [
            'label'  => 'PHP 8.0 or newer',
            'ok'     => PHP_VERSION_ID >= 80000,
            'detail' => 'Running PHP ' . PHP_VERSION,
            'fatal'  => true,
        ];

        foreach (['pdo_mysql' => true, 'fileinfo' => true, 'mbstring' => true, 'curl' => false] as $ext => $fatal) {
            $checks[] = [
                'label'  => "PHP extension: {$ext}",
                'ok'     => extension_loaded($ext),
                'detail' => extension_loaded($ext)
                    ? 'Loaded'
                    : ($fatal ? 'Required — enable it in your hosting control panel'
                              : 'Optional — only needed for tests/smoke.php'),
                'fatal'  => $fatal,
            ];
        }

        $storage = MARIAH_ROOT . '/storage/uploads';
        $checks[] = [
            'label'  => 'storage/uploads is writable',
            'ok'     => is_dir($storage) && is_writable($storage),
            'detail' => is_dir($storage)
                ? (is_writable($storage) ? 'Writable' : 'Not writable — set permissions to 755 or 775')
                : 'Missing — create Mariah_CMS/storage/uploads',
            'fatal'  => false,
        ];

        $logs = MARIAH_ROOT . '/storage/logs';
        $checks[] = [
            'label'  => 'storage/logs is writable',
            'ok'     => is_dir($logs) && is_writable($logs),
            'detail' => is_dir($logs)
                ? (is_writable($logs) ? 'Writable' : 'Not writable — set permissions to 755 or 775')
                : 'Missing — create Mariah_CMS/storage/logs',
            'fatal'  => false,
        ];

        $uploadMax = self::toBytes((string) ini_get('upload_max_filesize'));
        $configured = Env::int('UPLOAD_MAX_BYTES', 5_242_880);
        $checks[] = [
            'label'  => 'Upload limit is large enough',
            'ok'     => $uploadMax >= $configured,
            'detail' => 'PHP allows ' . ini_get('upload_max_filesize')
                . '; UPLOAD_MAX_BYTES asks for ' . round($configured / 1_048_576, 1) . 'M',
            'fatal'  => false,
        ];

        $appUrl = Env::string('APP_URL', '');
        $checks[] = [
            'label'  => 'APP_URL is set',
            'ok'     => $appUrl !== '',
            'detail' => $appUrl !== '' ? $appUrl : 'Required — seeded image URLs depend on it',
            'fatal'  => false,
        ];

        $secure = Env::bool('SESSION_COOKIE_SECURE', true);
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? '') === '443'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

        $checks[] = [
            'label'  => 'Session cookie setting matches the connection',
            'ok'     => !$secure || $https,
            'detail' => $secure && !$https
                ? 'SESSION_COOKIE_SECURE=true but this page is not HTTPS — sign-in will silently fail. '
                  . 'Enable SSL, or set it to false for local development.'
                : ($https ? 'HTTPS with a secure cookie' : 'Plain HTTP with SESSION_COOKIE_SECURE=false'),
            'fatal'  => false,
        ];

        return $checks;
    }

    private static function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit   = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g'     => $number * 1024 * 1024 * 1024,
            'm'     => $number * 1024 * 1024,
            'k'     => $number * 1024,
            default => $number,
        };
    }
}
