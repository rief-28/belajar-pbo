<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Menentukan file statis mana yang boleh disajikan langsung oleh router.php.
 * Hanya file di folder public/ dan bukan .php.
 */
final class StaticFiles
{
    /**
     * @return string|null path file sebenarnya bila boleh disajikan, null bila tidak
     */
    public static function resolve(string $urlPath, string $root): ?string
    {
        $urlPath = rawurldecode($urlPath);
        if (!str_starts_with($urlPath, '/public/') || str_ends_with(strtolower($urlPath), '.php')) {
            return null;
        }

        $real = realpath($root . $urlPath);
        if ($real === false || !is_file($real) || !self::isInside($real, $root . '/public')) {
            return null;
        }

        return $real;
    }

    /**
     * True bila $path berada di dalam $dir. Pemisah \ dan / dianggap sama
     * karena realpath() di Windows mengembalikan backslash.
     */
    public static function isInside(string $path, string $dir): bool
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $dir  = rtrim(str_replace('\\', '/', $dir), '/');

        // Windows tidak membedakan huruf besar kecil pada path.
        if (preg_match('#^[A-Za-z]:/#', $dir) === 1) {
            $path = strtolower($path);
            $dir  = strtolower($dir);
        }

        return str_starts_with($path, $dir . '/');
    }
}
