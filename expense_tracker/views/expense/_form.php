<?php
/**
 * Form pengeluaran, dipakai create.php dan edit.php.
 *
 * Variabel wajib: $expense (array hasil Expense::toArray()), $errors (field => pesan),
 * $categories, $formTitle, $formAction, $submitLabel, $csrfToken.
 */

use App\Models\CashExpense;
use App\Models\CreditExpense;
use App\Models\Expense;
use App\Support\Format;

$errors = $errors ?? [];
$slugs  = array_column($categories, 'slug');

// Nilai awal: kategori pertama terpilih pada form baru agar bisa langsung simpan.
$selectedCategory = (string) ($expense['category'] ?? '');
if ($selectedCategory === '' && $slugs !== []) {
    $selectedCategory = (string) $slugs[0];
}

$selectedType = ($expense['type'] ?? CashExpense::TYPE) === CreditExpense::TYPE ? CreditExpense::TYPE : CashExpense::TYPE;

$val = static fn (string $key): string => Format::e((string) ($expense[$key] ?? ''));
$err = static fn (string $key): ?string => isset($errors[$key]) ? (string) $errors[$key] : null;
$aria = static fn (string $key): string => isset($errors[$key])
    ? ' aria-invalid="true" aria-describedby="err-' . $key . '"'
    : '';
$showError = static function (string $key) use ($err): void {
    if (($m = $err($key)) !== null) {
        echo '<p class="field-error" id="err-' . $key . '">' . Format::e($m) . '</p>';
    }
};

$noteRequired = $selectedCategory === Expense::NOTE_REQUIRED_CATEGORY;
?>
<div class="card form-card">
    <div class="form-head">
        <h1 class="form-title"><?= Format::e($formTitle) ?></h1>
        <a href="index.php" class="icon-btn" aria-label="Tutup dan kembali ke daftar" title="Tutup"><?= Format::icon('x') ?></a>
    </div>

    <?php if ($errors !== []): ?>
        <div class="form-error-summary" role="alert">
            Data belum bisa disimpan. Periksa <?= count($errors) === 1 ? 'isian yang ditandai' : count($errors) . ' isian yang ditandai' ?> di bawah.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= Format::e($formAction) ?>" class="form-grid" id="expense-form"
          data-note-required-category="<?= Format::e(Expense::NOTE_REQUIRED_CATEGORY) ?>">
        <input type="hidden" name="csrf_token" value="<?= Format::e($csrfToken) ?>">

        <div class="field">
            <label class="field-label" for="amount">Nominal</label>
            <div class="money <?= $err('amount') !== null ? 'is-invalid' : '' ?>">
                <span class="money-prefix" aria-hidden="true">Rp</span>
                <input id="amount" name="amount" type="text" inputmode="numeric" autocomplete="off"
                       placeholder="0" required maxlength="16" pattern="[0-9.\s]*" data-money
                       value="<?= $val('amount') ?>"<?= $aria('amount') ?>>
            </div>
            <?php $showError('amount'); ?>
        </div>

        <div class="field">
            <label class="field-label" for="name">Nama pengeluaran</label>
            <input id="name" name="name" type="text" class="control" required maxlength="100"
                   placeholder="Contoh: Makan siang" autocomplete="off"
                   value="<?= $val('name') ?>"<?= $aria('name') ?>>
            <?php $showError('name'); ?>
        </div>

        <fieldset class="field">
            <legend class="field-label">Kategori</legend>
            <div class="cat-grid">
                <?php foreach ($categories as $i => $c): ?>
                    <label class="cat-option" style="<?= Format::categoryStyle($c['slug']) ?>">
                        <input type="radio" name="category" value="<?= Format::e($c['slug']) ?>"
                               <?= $selectedCategory === $c['slug'] ? 'checked' : '' ?> <?= $i === 0 ? 'required' : '' ?>>
                        <span class="cat-card">
                            <span class="cat-icon"><?= Format::categoryIcon($c['slug']) ?></span>
                            <span><?= Format::e($c['name']) ?></span>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?php $showError('category'); ?>
        </fieldset>

        <div class="field">
            <label class="field-label" for="date">Tanggal</label>
            <input id="date" name="date" type="date" class="control" required
                   value="<?= $val('date') ?>"<?= $aria('date') ?>>
            <?php $showError('date'); ?>
        </div>

        <fieldset class="field">
            <legend class="field-label">Cara bayar</legend>
            <div class="segmented">
                <label>
                    <input type="radio" name="type" value="<?= CashExpense::TYPE ?>" <?= $selectedType === CashExpense::TYPE ? 'checked' : '' ?>>
                    <span>Tunai</span>
                </label>
                <label>
                    <input type="radio" name="type" value="<?= CreditExpense::TYPE ?>" <?= $selectedType === CreditExpense::TYPE ? 'checked' : '' ?>>
                    <span>Kartu Kredit</span>
                </label>
            </div>
            <?php $showError('type'); ?>
        </fieldset>

        <fieldset class="type-fields" data-type-fields="<?= CashExpense::TYPE ?>"
                  <?= $selectedType === CashExpense::TYPE ? '' : 'hidden disabled' ?>>
            <div class="field">
                <label class="field-label" for="cash_received">Uang tunai dibayarkan <span class="opt">(opsional)</span></label>
                <input id="cash_received" name="cash_received" type="text" inputmode="numeric" class="control"
                       autocomplete="off" maxlength="16" pattern="[0-9.\s]*" data-money placeholder="Contoh: 50.000"
                       value="<?= $val('cash_received') ?>"<?= $aria('cash_received') ?>>
                <?php $showError('cash_received'); ?>
            </div>
        </fieldset>

        <fieldset class="type-fields" data-type-fields="<?= CreditExpense::TYPE ?>"
                  <?= $selectedType === CreditExpense::TYPE ? '' : 'hidden disabled' ?>>
            <div class="field">
                <label class="field-label" for="card_name">Nama kartu</label>
                <input id="card_name" name="card_name" type="text" class="control" required maxlength="50"
                       placeholder="Contoh: BCA Visa" autocomplete="off"
                       value="<?= $val('card_name') ?>"<?= $aria('card_name') ?>>
                <?php $showError('card_name'); ?>
            </div>
            <div class="row-2 row-2-always">
                <div class="field">
                    <label class="field-label" for="installments">Cicilan (bulan)</label>
                    <input id="installments" name="installments" type="number" class="control" min="1" max="60" step="1"
                           inputmode="numeric" value="<?= $val('installments') !== '' ? $val('installments') : '1' ?>"<?= $aria('installments') ?>>
                    <?php $showError('installments'); ?>
                </div>
                <div class="field">
                    <label class="field-label" for="interest_rate">Bunga total (%)</label>
                    <input id="interest_rate" name="interest_rate" type="text" class="control" inputmode="decimal"
                           autocomplete="off" maxlength="6" pattern="[0-9]{1,3}([.,][0-9]{1,2})?"
                           value="<?= $val('interest_rate') !== '' ? $val('interest_rate') : '0' ?>"<?= $aria('interest_rate') ?>>
                    <?php $showError('interest_rate'); ?>
                </div>
            </div>
            <div class="field">
                <label class="field-label" for="due_date">Jatuh tempo <span class="opt">(opsional)</span></label>
                <input id="due_date" name="due_date" type="date" class="control"
                       value="<?= $val('due_date') ?>"<?= $aria('due_date') ?>>
                <?php $showError('due_date'); ?>
            </div>
        </fieldset>

        <div class="field">
            <label class="field-label" for="note">
                Catatan
                <span class="opt" id="note-flag"><?= $noteRequired ? '(wajib untuk kategori Lainnya)' : '(opsional)' ?></span>
            </label>
            <textarea id="note" name="note" class="control" maxlength="255" rows="2"
                      placeholder="Contoh: Bersama teman"
                      <?= $noteRequired ? 'required' : '' ?><?= $aria('note') ?>><?= $val('note') ?></textarea>
            <?php $showError('note'); ?>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-ghost">Batal</a>
            <button type="submit" class="btn btn-primary"><?= Format::e($submitLabel) ?></button>
        </div>
    </form>
</div>
