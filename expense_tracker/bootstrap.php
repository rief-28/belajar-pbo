<?php

declare(strict_types=1);

/**
 * Inisialisasi bersama (dipakai index.php dan tes): autoload, zona waktu,
 * dan konversi warning/notice PHP menjadi exception agar tidak ada galat
 * yang lolos diam-diam.
 */

const BASE_PATH = __DIR__;

spl_autoload_register(static function (string $class): void {
    // Urutan penting: App\Config\ harus dicek sebelum App\.
    $map = [
        'App\\Config\\' => BASE_PATH . '/config/',
        'App\\'         => BASE_PATH . '/src/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
});

$timezone = getenv('APP_TIMEZONE');
date_default_timezone_set(is_string($timezone) && $timezone !== '' ? $timezone : 'Asia/Jakarta');

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if ((error_reporting() & $severity) === 0) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
