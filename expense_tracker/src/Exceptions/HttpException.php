<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Kegagalan yang boleh ditampilkan ke pengguna.
 * Pesan wajib aman dibaca pengguna (tanpa detail internal).
 */
final class HttpException extends RuntimeException
{
    public function __construct(private int $status, string $message)
    {
        parent::__construct($message);
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
