<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;
use Throwable;

/**
 * Merender file di views/. Output ditampung dulu, sehingga galat di tengah
 * view tidak mengirim halaman setengah jadi ke pengguna.
 *
 * Setiap view otomatis menerima $csrfToken dan $flash.
 */
final class View
{
    public static function render(string $name, array $vars = [], int $status = 200): void
    {
        // Nama view berasal dari kode, bukan input pengguna. Pemeriksaan ini hanya pengaman.
        if (preg_match('#^[a-z_]+(/[a-z_]+)?$#', $name) !== 1) {
            throw new RuntimeException('Nama view tidak valid: ' . $name);
        }
        $file = dirname(__DIR__, 2) . '/views/' . $name . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('View tidak ditemukan: ' . $name);
        }

        $active = session_status() === PHP_SESSION_ACTIVE;
        $vars += [
            'csrfToken' => $active ? Session::csrfToken() : '',
            'flash'     => $active ? Session::pullFlash() : null,
        ];

        ob_start();
        try {
            (static function (string $__file, array $__vars): void {
                extract($__vars, EXTR_SKIP);
                include $__file;
            })($file, $vars);
            $html = (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        if ($active) {
            // Untuk fetch()/XHR di app.js: kirim ulang sebagai header X-CSRF-Token.
            header('X-CSRF-Token: ' . $vars['csrfToken']);
        }
        echo $html;
    }
}
