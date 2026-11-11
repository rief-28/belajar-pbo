<?php
use App\Support\Format;

$pageTitle = $pageTitle ?? 'Expense Tracker';
$flash     = $flash ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#F6F7F9">
    <title><?= Format::e($pageTitle) ?> | Catatan Pengeluaran</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="index.php">
            <span class="brand-mark"><?= Format::icon('wallet') ?></span>
            <span class="brand-name">Expense Tracker</span>
        </a>
    </div>
</header>
<main class="container page">
<?php if (is_array($flash) && isset($flash['message'])): ?>
    <?php $flashType = in_array($flash['type'] ?? '', ['success', 'danger', 'warning', 'info'], true) ? $flash['type'] : 'info'; ?>
    <div class="flash flash-<?= $flashType ?>" role="<?= $flashType === 'danger' ? 'alert' : 'status' ?>" data-flash>
        <span><?= Format::e($flash['message']) ?></span>
        <button type="button" class="flash-close" aria-label="Tutup pesan"><?= Format::icon('x') ?></button>
    </div>
<?php endif; ?>
