<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Masalah pemasangan server (misalnya ekstensi PHP belum aktif).
 * Pesan berisi langkah perbaikan dan aman ditampilkan ke pengguna.
 */
final class SetupException extends RuntimeException
{
}
