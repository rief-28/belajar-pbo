<?php
/**
 * Form tambah pengeluaran (FR-1).
 * Variabel dari ExpenseController: $expense, $errors, $categories, $types, $csrfToken.
 */

$pageTitle   = 'Tambah Pengeluaran';
$formTitle   = 'Tambah Pengeluaran';
$formAction  = 'index.php?action=store';
$submitLabel = 'Simpan Pengeluaran';
?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php include __DIR__ . '/_form.php'; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
