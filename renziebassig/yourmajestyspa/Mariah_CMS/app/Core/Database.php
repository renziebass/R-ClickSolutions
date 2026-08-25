<?php
declare(strict_types=1);

namespace Mariah\Core;

use PDO;
use PDOStatement;

/**
 * PDO singleton. Every query in this application goes through here using
 * prepared statements — there is no path that interpolates user input into SQL.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host    = Env::string('DB_HOST', 'localhost');
        $port    = Env::int('DB_PORT', 3306);
        $name    = Env::require('DB_NAME');
        $charset = Env::string('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        self::$pdo = new PDO($dsn, Env::string('DB_USER'), Env::string('DB_PASS'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);

        return self::$pdo;
    }

    /** Connect without selecting a database — used by migrate.php to CREATE DATABASE. */
    public static function serverPdo(): PDO
    {
        $host = Env::string('DB_HOST', 'localhost');
        $port = Env::int('DB_PORT', 3306);

        return new PDO(
            "mysql:host={$host};port={$port}",
            Env::string('DB_USER'),
            Env::string('DB_PASS'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchValue(string $sql, array $params = []): mixed
    {
        $v = self::run($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public static function insertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }

    public static function transaction(callable $fn): mixed
    {
        $pdo = self::pdo();
        // Nested calls reuse the outer transaction rather than erroring.
        if ($pdo->inTransaction()) {
            return $fn($pdo);
        }

        $pdo->beginTransaction();
        try {
            $result = $fn($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
