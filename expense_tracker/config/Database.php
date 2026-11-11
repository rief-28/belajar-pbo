<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Membuka koneksi SQLite dan menerapkan skema (database/schema.sql)
 * satu kali, dilacak lewat PRAGMA user_version.
 *
 * Lokasi file database bisa diubah dengan variabel lingkungan
 * EXPENSE_DB_PATH (disarankan di luar web root pada produksi).
 */
final class Database
{
    public const SCHEMA_VERSION = 1;

    private const BUSY_TIMEOUT_MS = 5000;

    public static function defaultPath(): string
    {
        $env = getenv('EXPENSE_DB_PATH');
        if (is_string($env) && $env !== '') {
            return $env;
        }
        return dirname(__DIR__) . '/database/expense_tracker.sqlite';
    }

    /**
     * @param string $path path file SQLite, atau ':memory:' untuk tes
     * @throws RuntimeException jika folder tidak bisa dibuat
     */
    public static function open(string $path): PDO
    {
        if ($path !== ':memory:') {
            $dir = dirname($path);
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new RuntimeException('Folder database tidak bisa dibuat: ' . $dir);
            }
        }

        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        // Foreign key mati secara bawaan di SQLite dan harus dinyalakan per koneksi.
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = ' . self::BUSY_TIMEOUT_MS);
        if ($path !== ':memory:') {
            // WAL: pembaca tidak memblokir penulis. Durabilitas tetap penuh (synchronous FULL).
            $pdo->exec('PRAGMA journal_mode = WAL');
        }

        self::migrate($pdo);

        return $pdo;
    }

    private static function migrate(PDO $pdo): void
    {
        if (self::version($pdo) >= self::SCHEMA_VERSION) {
            return;
        }

        $schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
        if ($schema === false || trim($schema) === '') {
            throw new RuntimeException('database/schema.sql tidak terbaca atau kosong.');
        }

        // BEGIN IMMEDIATE menserialkan dua proses yang membuka database kosong bersamaan.
        $pdo->exec('BEGIN IMMEDIATE');
        try {
            if (self::version($pdo) < self::SCHEMA_VERSION) {
                $pdo->exec($schema);
                $pdo->exec('PRAGMA user_version = ' . self::SCHEMA_VERSION);
            }
            $pdo->exec('COMMIT');
        } catch (Throwable $e) {
            try {
                $pdo->exec('ROLLBACK');
            } catch (Throwable) {
                // Transaksi sudah berakhir. Galat asli lebih penting.
            }
            throw $e;
        }
    }

    private static function version(PDO $pdo): int
    {
        return (int) $pdo->query('PRAGMA user_version')->fetchColumn();
    }
}
