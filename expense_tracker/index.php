<?php

declare(strict_types=1);

/**
 * Front controller. Semua request masuk lewat sini.
 *
 * GET  index (bawaan) | create | edit&id= | health
 * POST store | update&id= | delete&id= | category_store | category_delete
 *
 * Semua POST wajib membawa csrf_token (field form atau header X-CSRF-Token).
 */

use App\Config\Database;
use App\Controllers\ExpenseController;
use App\Exceptions\HttpException;
use App\Exceptions\SetupException;
use App\Repositories\CategoryRepository;
use App\Repositories\ExpenseRepository;
use App\Support\Logger;
use App\Support\Requirements;
use App\Support\Session;
use App\Support\View;

require_once __DIR__ . '/bootstrap.php';

const ROUTES = [
    'index'           => 'GET',
    'create'          => 'GET',
    'edit'            => 'GET',
    'health'          => 'GET',
    'store'           => 'POST',
    'update'          => 'POST',
    'delete'          => 'POST',
    'category_store'  => 'POST',
    'category_delete' => 'POST',
];

$startedAt = hrtime(true);
$requestId = Logger::init();
$method    = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$action    = is_string($_GET['action'] ?? null) ? $_GET['action'] : 'index';

header('X-Request-Id: ' . $requestId);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

// Satu baris log per request, termasuk yang berakhir dengan galat.
register_shutdown_function(static function () use ($startedAt, $method, $action): void {
    $status = http_response_code();
    $status = is_int($status) ? $status : 200;
    Logger::log(
        $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info'),
        'http.request',
        [
            'method'      => $method,
            'action'      => $action,
            'status'      => $status,
            'duration_ms' => (int) round((hrtime(true) - $startedAt) / 1_000_000),
        ]
    );
});

try {
    dispatch($method, $action);
} catch (HttpException $e) {
    respondError($e->getStatus(), $e->getMessage());
} catch (SetupException $e) {
    // Masalah pemasangan: pesannya berisi langkah perbaikan, aman ditampilkan.
    Logger::log('error', 'setup.incomplete', ['error' => $e->getMessage()]);
    respondError(500, $e->getMessage());
} catch (PDOException $e) {
    Logger::log('error', 'db.error', ['error' => $e->getMessage(), 'code' => (string) $e->getCode()]);
    if (stripos($e->getMessage(), 'locked') !== false || stripos($e->getMessage(), 'busy') !== false) {
        header('Retry-After: 2');
        respondError(503, 'Database sedang sibuk. Coba lagi sebentar lagi.');
    } else {
        respondError(500, 'Terjadi kesalahan pada server.');
    }
} catch (Throwable $e) {
    Logger::log('error', 'unhandled', [
        'class' => $e::class,
        'error' => $e->getMessage(),
        'at'    => basename($e->getFile()) . ':' . $e->getLine(),
    ]);
    respondError(500, 'Terjadi kesalahan pada server.');
}

// ---------------------------------------------------------------------------

function dispatch(string $method, string $action): void
{
    $expected = ROUTES[$action] ?? null;
    if ($expected === null) {
        throw new HttpException(404, 'Halaman tidak ditemukan.');
    }
    if ($method !== $expected && !($method === 'HEAD' && $expected === 'GET')) {
        header('Allow: ' . $expected);
        throw new HttpException(405, 'Metode HTTP tidak diizinkan untuk alamat ini.');
    }

    if ($action === 'health') {
        health();
        return;
    }

    Requirements::assertMet();
    Session::start();

    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!Session::verifyCsrf($token)) {
            throw new HttpException(403, 'Sesi formulir tidak valid atau kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        }
    }

    $pdo        = Database::open(Database::defaultPath());
    $controller = new ExpenseController(new ExpenseRepository($pdo), new CategoryRepository($pdo));

    match ($action) {
        'index'           => $controller->index($_GET),
        'create'          => $controller->create(),
        'edit'            => $controller->edit(requestId()),
        'store'           => $controller->store($_POST),
        'update'          => $controller->update(requestId(), $_POST),
        'delete'          => $controller->delete(requestId()),
        'category_store'  => $controller->storeCategory($_POST),
        'category_delete' => $controller->deleteCategory($_POST),
    };
}

/** id dari ?id= atau field POST id. Format salah diperlakukan sebagai data tidak ada. */
function requestId(): int
{
    $raw = $_GET['id'] ?? $_POST['id'] ?? null;
    if (!is_string($raw) || preg_match('/^[1-9]\d{0,17}$/', $raw) !== 1) {
        throw new HttpException(404, 'Pengeluaran tidak ditemukan.');
    }
    return (int) $raw;
}

/** Healthcheck untuk monitoring: database bisa dibuka, ada skema, dan file bisa ditulis. */
function health(): void
{
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');

    try {
        $path = Database::defaultPath();
        $pdo  = Database::open($path);
        $pdo->query('SELECT 1')->fetchColumn();
        $schema = (int) $pdo->query('PRAGMA user_version')->fetchColumn();
        if (!is_writable($path)) {
            throw new RuntimeException('File database tidak bisa ditulis.');
        }
        echo json_encode(['status' => 'ok', 'schema_version' => $schema]);
    } catch (Throwable $e) {
        Logger::log('error', 'health.failed', ['error' => $e->getMessage()]);
        http_response_code(503);
        echo json_encode(['status' => 'unavailable']);
    }
}

function respondError(int $status, string $message): void
{
    // Pesan aman untuk pengguna. Detail internal hanya ada di log.
    try {
        View::render('error', [
            'status'    => $status,
            'message'   => $message,
            'requestId' => Logger::requestId(),
        ], $status);
    } catch (Throwable $e) {
        Logger::log('error', 'error_view.failed', ['error' => $e->getMessage()]);
        http_response_code($status);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $status . ' - ' . $message . ' (kode: ' . Logger::requestId() . ')';
    }
}
