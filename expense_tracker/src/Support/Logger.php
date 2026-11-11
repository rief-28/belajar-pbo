<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Log JSON satu baris per kejadian, ditulis lewat error_log
 * (stderr pada php -S, log Apache/PHP-FPM pada produksi).
 */
final class Logger
{
    private static string $requestId = '';

    public static function init(): string
    {
        self::$requestId = bin2hex(random_bytes(8));
        return self::$requestId;
    }

    public static function requestId(): string
    {
        return self::$requestId;
    }

    /** @param array<string, mixed> $context */
    public static function log(string $level, string $message, array $context = []): void
    {
        $record = [
            'ts'         => gmdate('c'),
            'level'      => $level,
            'request_id' => self::$requestId,
            'message'    => $message,
        ] + $context;

        error_log((string) json_encode(
            $record,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR
        ));
    }
}
