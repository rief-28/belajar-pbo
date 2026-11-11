<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Pembungkus sesi: token CSRF dan flash message.
 */
final class Session
{
    private const FLASH_TYPES = ['success', 'danger', 'warning', 'info'];

    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(mixed $submitted): bool
    {
        $expected = $_SESSION['csrf_token'] ?? null;
        return is_string($expected) && is_string($submitted) && hash_equals($expected, $submitted);
    }

    public static function flash(string $type, string $message): void
    {
        if (!in_array($type, self::FLASH_TYPES, true)) {
            $type = 'info';
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** @return array{type: string, message: string}|null */
    public static function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return is_array($flash) ? $flash : null;
    }
}
