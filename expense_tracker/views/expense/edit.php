<?php
/**
 * Form edit pengeluaran (FR-3). Menampilkan data lama.
 * Variabel dari ExpenseController: $expense (berisi id), $errors, $categories, $types, $csrfToken.
 */

$pageTitle   = 'Edit Pengeluaran';
$formTitle   = 'Edit Pengeluaran';
$formAction  = 'index.php?action=update&id=' . (int) ($expense['id'] ?? 0);
$submitLabel = 'Simpan Perubahan';
?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/_form.php'; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
