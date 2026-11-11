<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\SetupException;

/**
 * Memeriksa ekstensi PHP yang dibutuhkan aplikasi. Di Windows, ekstensi ini
 * sering masih nonaktif di php.ini, sehingga tanpa pemeriksaan ini pengguna
 * hanya melihat galat 500 umum ("could not find driver").
 */
final class Requirements
{
    public const REQUIRED_EXTENSIONS = ['pdo_sqlite', 'mbstring'];

    /**
     * @param list<string>|null $extensions
     * @return list<string> ekstensi yang belum aktif
     */
    public static function missing(?array $extensions = null): array
    {
        return array_values(array_filter(
            $extensions ?? self::REQUIRED_EXTENSIONS,
            static fn (string $name): bool => !extension_loaded($name)
        ));
    }

    /**
     * @param list<string>|null $extensions bawaan: REQUIRED_EXTENSIONS
     * @throws SetupException bila ada ekstensi yang belum aktif
     */
    public static function assertMet(?array $extensions = null): void
    {
        $missing = self::missing($extensions);
        if ($missing === []) {
            return;
        }

        $lines = implode(', ', array_map(static fn (string $n): string => 'extension=' . $n, $missing));
        throw new SetupException(
            'Ekstensi PHP belum aktif: ' . implode(', ', $missing) . '. '
            . 'Cara tercepat di Windows: jalankan server dengan serve.bat dari folder proyek. '
            . 'Atau jalankan "php --ini". Jika hasilnya "Loaded Configuration File: (none)", '
            . 'salin php.ini-development di folder PHP menjadi php.ini. '
            . 'Lalu buka php.ini, hapus tanda titik koma (;) di depan baris ' . $lines . ', '
            . 'simpan, dan jalankan ulang server.'
        );
    }
}
