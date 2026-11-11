<?php

declare(strict_types=1);

/**
 * Router untuk server bawaan PHP:  php -S localhost:8000 router.php
 *
 * Tanpa file ini, php -S menyajikan semua file di folder proyek, termasuk
 * database/*.sqlite dan source code. Di sini hanya file statis di /public
 * (bukan .php) yang disajikan langsung. Sisanya masuk ke index.php.
 */

require_once __DIR__ . '/bootstrap.php';

$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) ? $path : '/';

if (str_starts_with($path, '/public/')) {
    if (App\Support\StaticFiles::resolve($path, __DIR__) !== null) {
        return false; // biarkan server bawaan menyajikan file statis
    }
    http_response_code(404);
    echo 'Not found';
    return true;
}

require __DIR__ . '/index.php';
