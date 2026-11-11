<?php
// Halaman galat. Variabel: $status (int), $message (string), $requestId (string).
use App\Support\Format;

$status    = (int) ($status ?? 500);
$message   = $message ?? 'Terjadi kesalahan.';
$requestId = $requestId ?? '';
$pageTitle = 'Galat ' . $status;
?>
<?php include __DIR__ . '/layouts/header.php'; ?>

<div class="card card-pad empty" style="max-width:520px;margin:48px auto 0">
    <p class="page-title"><?= $status ?></p>
    <p><?= Format::e($message) ?></p>
    <?php if ($requestId !== ''): ?>
        <p class="card-meta">Kode: <?= Format::e($requestId) ?></p>
    <?php endif; ?>
    <a href="index.php" class="btn btn-primary">Kembali ke daftar</a>
</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>
