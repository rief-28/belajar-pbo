<?php
/**
 * Halaman daftar pengeluaran (FR-2, FR-5, FR-6, FR-7, FR-8) dan pengelolaan kategori (US-6).
 *
 * Variabel dari ExpenseController::index():
 *   $expenses, $filter, $filteredTotal, $totalOverall, $totalThisMonth,
 *   $monthlyTotals, $categories, $csrfToken, $flash
 */

use App\Support\Format;

$pageTitle = 'Pengeluaran';

$expenses       = $expenses ?? [];
$filter         = $filter ?? ['search' => '', 'category' => '', 'month' => '', 'sort' => 'desc'];
$monthlyTotals  = $monthlyTotals ?? [];
$categories     = $categories ?? [];
$totalOverall   = (int) ($totalOverall ?? 0);
$totalThisMonth = (int) ($totalThisMonth ?? 0);
$filteredTotal  = (int) ($filteredTotal ?? 0);

$currentMonth      = date('Y-m');
$countThisMonth    = 0;
$countOverall      = 0;
foreach ($monthlyTotals as $m) {
    $countOverall += (int) $m['count'];
    if ($m['month'] === $currentMonth) {
        $countThisMonth = (int) $m['count'];
    }
}

$filterActive = $filter['search'] !== '' || $filter['category'] !== '' || $filter['month'] !== '';

/** URL daftar dengan filter saat ini, sebagian diganti. Nilai kosong dan bawaan dibuang. */
$listUrl = static function (array $override = []) use ($filter): string {
    $q = array_merge($filter, $override);
    if (($q['sort'] ?? 'desc') === 'desc') {
        unset($q['sort']);
    }
    $q = array_filter($q, static fn ($v): bool => $v !== '');
    return 'index.php' . ($q === [] ? '' : '?' . http_build_query($q));
};
?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="page-head">
    <div>
        <h1 class="page-title">Pengeluaran</h1>
        <p class="page-sub">Catat dan pantau pengeluaran harian Anda</p>
    </div>
    <a href="index.php?action=create" class="btn btn-primary btn-add">
        <?= Format::icon('plus') ?>
        <span>Tambah Pengeluaran</span>
    </a>
</div>

<section class="summary" aria-label="Ringkasan total">
    <div class="stat stat-hero">
        <p class="stat-label">Pengeluaran <?= Format::e(Format::month($currentMonth)) ?></p>
        <p class="stat-value"><?= Format::e(Format::rupiah($totalThisMonth)) ?></p>
        <p class="stat-note"><?= $countThisMonth ?> transaksi bulan ini</p>
    </div>
    <div class="stat">
        <p class="stat-label">Total keseluruhan</p>
        <p class="stat-value"><?= Format::e(Format::rupiah($totalOverall)) ?></p>
        <p class="stat-note"><?= $countOverall ?> transaksi tercatat</p>
    </div>
</section>

<div class="layout">
    <div class="layout-main">

        <form id="filter-form" class="card filters" method="get" action="index.php" role="search" aria-label="Cari dan saring pengeluaran">
            <div class="filter-top">
                <div class="field-search">
                    <?= Format::icon('search') ?>
                    <label for="f-search" class="sr-only">Cari nama pengeluaran</label>
                    <input id="f-search" class="control" type="search" name="search" maxlength="100"
                           placeholder="Cari nama pengeluaran" autocomplete="off"
                           value="<?= Format::e($filter['search']) ?>">
                </div>
                <button type="submit" class="btn btn-ghost filter-submit">Terapkan</button>
                <a href="index.php" class="btn btn-ghost" data-reset>Reset</a>
            </div>
            <div class="filter-row">
                <div class="span-2">
                    <label for="f-category" class="sr-only">Kategori</label>
                    <select id="f-category" class="control" name="category">
                        <option value="">Semua kategori</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= Format::e($c['slug']) ?>" <?= $filter['category'] === $c['slug'] ? 'selected' : '' ?>>
                                <?= Format::e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="f-month" class="sr-only">Bulan</label>
                    <select id="f-month" class="control" name="month">
                        <option value="">Semua bulan</option>
                        <?php foreach ($monthlyTotals as $m): ?>
                            <option value="<?= Format::e($m['month']) ?>" <?= $filter['month'] === $m['month'] ? 'selected' : '' ?>>
                                <?= Format::e(Format::month($m['month'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="f-sort" class="sr-only">Urutan tanggal</label>
                    <select id="f-sort" class="control" name="sort">
                        <option value="desc" <?= $filter['sort'] === 'desc' ? 'selected' : '' ?>>Terbaru dulu</option>
                        <option value="asc" <?= $filter['sort'] === 'asc' ? 'selected' : '' ?>>Terlama dulu</option>
                    </select>
                </div>
            </div>
        </form>

        <section id="results" class="card" data-live aria-live="polite" aria-label="Daftar pengeluaran">
            <div class="list-head">
                <h2 class="card-title">Daftar pengeluaran</h2>
                <span class="card-meta"><?= count($expenses) ?> transaksi<?= $filterActive ? ' (hasil saring)' : '' ?></span>
            </div>

            <?php if ($expenses === []): ?>
                <div class="empty">
                    <?= Format::icon('inbox') ?>
                    <?php if ($filterActive): ?>
                        <strong>Tidak ada pengeluaran yang cocok</strong>
                        <span>Ubah kata kunci atau filter Anda.</span>
                        <a href="index.php" class="btn btn-ghost btn-sm">Hapus filter</a>
                    <?php else: ?>
                        <strong>Belum ada pengeluaran</strong>
                        <span>Catat pengeluaran pertama Anda, cukup beberapa detik.</span>
                        <a href="index.php?action=create" class="btn btn-primary btn-sm">Tambah Pengeluaran</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="tx-table">
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col" class="num">Nominal</th>
                                <th scope="col"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($expenses as $item): ?>
                            <?php
                            $style = Format::categoryStyle((string) $item['category']);
                            $sub   = [$item['type_label']];
                            if ($item['type'] === 'CreditExpense') {
                                if (($item['card_name'] ?? '') !== '') {
                                    $sub[] = $item['card_name'];
                                }
                                if ((int) ($item['installments'] ?? 1) > 1) {
                                    $sub[] = (int) $item['installments'] . 'x cicilan';
                                }
                            }
                            if (($item['note'] ?? '') !== '') {
                                $sub[] = $item['note'];
                            }
                            ?>
                            <tr>
                                <td class="td-main">
                                    <div class="tx-main">
                                        <span class="cat-icon" style="<?= $style ?>"><?= Format::categoryIcon((string) $item['category']) ?></span>
                                        <div>
                                            <div class="tx-name"><?= Format::e($item['name']) ?></div>
                                            <div class="tx-sub"><?= Format::e(implode(' · ', $sub)) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-cat"><span class="pill" style="<?= $style ?>"><?= Format::e($item['category_name']) ?></span></td>
                                <td class="td-date"><?= Format::e(Format::date((string) $item['date'])) ?></td>
                                <td class="td-amount"><span class="tx-amount"><?= Format::e(Format::rupiah($item['amount'])) ?></span></td>
                                <td class="td-actions">
                                    <a class="icon-btn" href="index.php?action=edit&amp;id=<?= (int) $item['id'] ?>"
                                       aria-label="Edit <?= Format::e($item['name']) ?>" title="Edit">
                                        <?= Format::icon('pencil') ?>
                                    </a>
                                    <form method="post" action="index.php?action=delete&amp;id=<?= (int) $item['id'] ?>"
                                          data-confirm="Hapus pengeluaran &quot;<?= Format::e($item['name']) ?>&quot; sebesar <?= Format::e(Format::rupiah($item['amount'])) ?>? Tindakan ini tidak bisa dibatalkan.">
                                        <input type="hidden" name="csrf_token" value="<?= Format::e($csrfToken) ?>">
                                        <button type="submit" class="icon-btn is-danger"
                                                aria-label="Hapus <?= Format::e($item['name']) ?>" title="Hapus">
                                            <?= Format::icon('trash') ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="list-foot">
                    <span><?= count($expenses) ?> transaksi<?= $filterActive ? ' sesuai filter' : '' ?></span>
                    <span>Total <strong class="num"><?= Format::e(Format::rupiah($filteredTotal)) ?></strong></span>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="layout-side">

        <section id="months" class="card card-pad" data-live aria-labelledby="months-title">
            <div class="card-head">
                <h2 id="months-title" class="card-title">Total per bulan</h2>
            </div>
            <?php if ($monthlyTotals === []): ?>
                <p class="card-meta">Belum ada data.</p>
            <?php else: ?>
                <ul class="month-list">
                    <li>
                        <a class="month-link <?= $filter['month'] === '' ? 'is-active' : '' ?>" href="<?= Format::e($listUrl(['month' => ''])) ?>"
                           <?= $filter['month'] === '' ? 'aria-current="true"' : '' ?>>
                            <span class="month-name">Semua bulan</span>
                            <span class="month-total"><?= Format::e(Format::rupiah($totalOverall)) ?></span>
                        </a>
                    </li>
                    <?php foreach ($monthlyTotals as $m): ?>
                        <li>
                            <a class="month-link <?= $filter['month'] === $m['month'] ? 'is-active' : '' ?>"
                               href="<?= Format::e($listUrl(['month' => $m['month']])) ?>"
                               <?= $filter['month'] === $m['month'] ? 'aria-current="true"' : '' ?>>
                                <span class="month-name"><?= Format::e(Format::month($m['month'])) ?></span>
                                <span class="month-total">
                                    <?= Format::e(Format::rupiah($m['total'])) ?>
                                    <span class="month-count"><?= (int) $m['count'] ?> transaksi</span>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="card card-pad" aria-labelledby="cats-title">
            <div class="card-head">
                <h2 id="cats-title" class="card-title">Kategori</h2>
                <span class="card-meta"><?= count($categories) ?> kategori</span>
            </div>
            <ul class="cat-list">
                <?php foreach ($categories as $c): ?>
                    <li class="cat-row">
                        <span class="cat-icon" style="<?= Format::categoryStyle($c['slug']) ?>"><?= Format::categoryIcon($c['slug']) ?></span>
                        <div class="cat-info">
                            <div class="cat-name"><?= Format::e($c['name']) ?></div>
                            <div class="cat-count">
                                <?= (int) $c['expense_count'] ?> pengeluaran<?= $c['is_default'] ? ' · bawaan' : '' ?>
                            </div>
                        </div>
                        <?php if (!$c['is_default'] && (int) $c['expense_count'] === 0): ?>
                            <form method="post" action="index.php?action=category_delete"
                                  data-confirm="Hapus kategori &quot;<?= Format::e($c['name']) ?>&quot;?">
                                <input type="hidden" name="csrf_token" value="<?= Format::e($csrfToken) ?>">
                                <input type="hidden" name="slug" value="<?= Format::e($c['slug']) ?>">
                                <button type="submit" class="icon-btn is-danger"
                                        aria-label="Hapus kategori <?= Format::e($c['name']) ?>" title="Hapus kategori">
                                    <?= Format::icon('trash') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form class="cat-add" method="post" action="index.php?action=category_store">
                <input type="hidden" name="csrf_token" value="<?= Format::e($csrfToken) ?>">
                <label for="new-category" class="sr-only">Nama kategori baru</label>
                <input id="new-category" class="control" type="text" name="name" required
                       minlength="2" maxlength="30" placeholder="Kategori baru" autocomplete="off">
                <button type="submit" class="btn btn-primary btn-sm">Tambah</button>
            </form>
        </section>

    </aside>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
