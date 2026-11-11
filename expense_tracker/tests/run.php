<?php

declare(strict_types=1);

/**
 * Tes backend tanpa dependensi. Jalankan:  php tests/run.php
 * Memakai database SQLite di memori, jadi aman dan cepat.
 * Exit code 0 bila semua lolos, 1 bila ada yang gagal.
 */

use App\Config\Database;
use App\Exceptions\HttpException;
use App\Models\CashExpense;
use App\Models\CreditExpense;
use App\Models\Expense;
use App\Models\ExpenseFactory;
use App\Models\ExpenseFilter;
use App\Repositories\CategoryRepository;
use App\Repositories\ExpenseRepository;
use App\Support\Requirements;
use App\Support\StaticFiles;
use App\Exceptions\SetupException;

require dirname(__DIR__) . '/bootstrap.php';

$passed = 0;
$failed = [];

function check(string $name, bool $ok): void
{
    global $passed, $failed;
    if ($ok) {
        $passed++;
    } else {
        $failed[] = $name;
        echo "GAGAL: $name\n";
    }
}

function throwsHttp(callable $fn, int $status): bool
{
    try {
        $fn();
    } catch (HttpException $e) {
        return $e->getStatus() === $status;
    }
    return false;
}

function throwsPdo(callable $fn): bool
{
    try {
        $fn();
    } catch (PDOException) {
        return true;
    }
    return false;
}

function cash(array $over = []): CashExpense
{
    $f = $over + ['name' => 'Makan siang', 'amount' => '25000', 'category' => 'makanan', 'date' => '2026-11-09'];
    return new CashExpense($f['name'], $f['amount'], $f['category'], $f['date'], $f['note'] ?? null, $f['cash'] ?? null);
}

function credit(array $over = []): CreditExpense
{
    $f = $over + [
        'name' => 'TV', 'amount' => '1000000', 'category' => 'belanja', 'date' => '2026-11-01',
        'card' => 'Mandiri', 'inst' => '12', 'rate' => '12', 'due' => null,
    ];
    return new CreditExpense($f['name'], $f['amount'], $f['category'], $f['date'], null, $f['card'], $f['inst'], $f['rate'], $f['due']);
}

// ---- Model: validasi (NFR-4) ------------------------------------------------

check('cash valid tanpa galat', cash()->validate() === []);
check('nama kosong ditolak', isset(cash(['name' => '   '])->validate()['name']));
check('nama 101 karakter ditolak', isset(cash(['name' => str_repeat('a', 101)])->validate()['name']));
check('nama 100 karakter diterima', !isset(cash(['name' => str_repeat('a', 100)])->validate()['name']));
check('nama multibyte dihitung per karakter', !isset(cash(['name' => str_repeat('é', 100)])->validate()['name']));
check('nama dengan karakter kontrol ditolak', isset(cash(['name' => "a\x00b"])->validate()['name']));

foreach (['0', '-5', '12.5', 'abc', '', '1e3', '1000000000000'] as $bad) {
    check("nominal '$bad' ditolak", isset(cash(['amount' => $bad])->validate()['amount']));
}
check('nominal int 1 diterima', cash(['amount' => 1])->validate() === []);

check('tanggal 2026-02-30 ditolak', isset(cash(['date' => '2026-02-30'])->validate()['date']));
check('tanggal format salah ditolak', isset(cash(['date' => '09-11-2026'])->validate()['date']));
check('tanggal kosong ditolak', isset(cash(['date' => ''])->validate()['date']));
check('catatan 256 karakter ditolak', isset(cash(['note' => str_repeat('x', 256)])->validate()['note']));
check('kategori lainnya tanpa catatan ditolak', isset(cash(['category' => 'lainnya'])->validate()['note']));
check('kategori lainnya dengan catatan diterima', cash(['category' => 'lainnya', 'note' => 'Parkir'])->validate() === []);
check('catatan kosong menjadi null', cash(['note' => '  '])->getNote() === null);
check('kategori dinormalkan ke huruf kecil', cash(['category' => ' Makanan '])->getCategory() === 'makanan');

// ---- Model: pewarisan dan perilaku -----------------------------------------

check('CashExpense extends Expense', cash() instanceof Expense);
check('CreditExpense extends Expense', credit() instanceof Expense);
check('Expense abstrak', (new ReflectionClass(Expense::class))->isAbstract());

$c = cash(['amount' => '25000', 'cash' => '30000']);
check('kembalian tunai 5000', $c->getChange() === 5000);
check('tunai kurang dari nominal ditolak', isset(cash(['amount' => '25000', 'cash' => '20000'])->validate()['cash_received']));
check('tunai kosong diperbolehkan', cash()->validate() === [] && cash()->getChange() === null);
check('tunai bukan angka ditolak', isset(cash(['cash' => 'abc'])->validate()['cash_received']));

$k = credit();
check('credit valid', $k->validate() === []);
check('total dengan bunga 12%', $k->getTotalWithInterest() === 1_120_000);
check('cicilan dibulatkan ke atas', $k->getMonthlyInstallment() === 93_334);
check('nama kartu wajib', isset(credit(['card' => ''])->validate()['card_name']));
foreach (['0', '61', 'x', '1.5'] as $bad) {
    check("cicilan '$bad' ditolak", isset(credit(['inst' => $bad])->validate()['installments']));
}
foreach (['101', '-1', '1.234', 'abc'] as $bad) {
    check("bunga '$bad' ditolak", isset(credit(['rate' => $bad])->validate()['interest_rate']));
}
check('bunga koma desimal diterima', credit(['rate' => '1,5'])->validate() === [] && credit(['rate' => '1,5'])->getInterestRate() === 1.5);
check('cicilan dan bunga kosong jadi 1 dan 0', credit(['inst' => '', 'rate' => ''])->validate() === []);
check('jatuh tempo sebelum tanggal ditolak', isset(credit(['due' => '2026-10-31'])->validate()['due_date']));
check('jatuh tempo valid diterima', credit(['due' => '2026-12-01'])->validate() === []);

check('toRow cash memuat semua kolom wajib', array_keys(cash()->toRow()) === ['name', 'amount', 'category', 'type', 'date', 'note', 'cash_received']);
check('toRow tidak memuat id/timestamp', !array_key_exists('id', $k->toRow()) && !array_key_exists('created_at', $k->toRow()));

// ---- Factory ----------------------------------------------------------------

$fx = ExpenseFactory::fromInput([
    'type' => 'CreditExpense', 'name' => 'A', 'amount' => '100', 'category' => 'belanja', 'date' => '2026-11-01',
    'card_name' => 'BCA', 'id' => '999', 'created_at' => 'x',
]);
check('factory memilih CreditExpense', $fx instanceof CreditExpense);
check('factory mengabaikan id dari klien', $fx->getId() === null);
check('factory default tipe cash', ExpenseFactory::fromInput([]) instanceof CashExpense);
$threw = false;
try {
    ExpenseFactory::fromInput(['type' => 'Hacker']);
} catch (InvalidArgumentException) {
    $threw = true;
}
check('factory menolak tipe asing', $threw);
check('factory tahan input array', ExpenseFactory::fromInput(['name' => ['x'], 'amount' => ['1']])->validate() !== []);

// ---- Filter -----------------------------------------------------------------

$flt = ExpenseFilter::fromQuery(['search' => str_repeat('a', 500), 'category' => 'DROP TABLE', 'month' => '2026-13', 'sort' => 'x']);
check('filter: search dipotong 100', mb_strlen($flt->getSearch()) === 100);
check('filter: kategori ngawur dibuang', $flt->getCategory() === '');
check('filter: bulan 13 dibuang', $flt->getMonth() === '');
check('filter: sort bawaan desc', $flt->getSort() === 'desc');

// ---- Skema dan repository ---------------------------------------------------

$pdo = Database::open(':memory:');
$exp = new ExpenseRepository($pdo);
$cat = new CategoryRepository($pdo);

check('skema: versi tercatat', (int) $pdo->query('PRAGMA user_version')->fetchColumn() === Database::SCHEMA_VERSION);
check('skema: 4 kategori bawaan', $cat->slugs() === ['makanan', 'transportasi', 'belanja', 'lainnya']);
check('skema: foreign key aktif', (int) $pdo->query('PRAGMA foreign_keys')->fetchColumn() === 1);

$id1 = $exp->insert(cash(['name' => 'Makan siang', 'amount' => '25000', 'date' => '2026-11-09']));
$id2 = $exp->insert(cash(['name' => 'Bensin 100% full', 'amount' => '35000', 'category' => 'transportasi', 'date' => '2026-11-08']));
$id3 = $exp->insert(credit(['name' => 'Laptop', 'amount' => '5000000', 'date' => '2026-10-15']));
$id4 = $exp->insert(cash(['name' => 'Kopi_a', 'amount' => '10000', 'date' => '2026-11-09']));

$all = $exp->findAll(ExpenseFilter::fromQuery([]));
check('daftar: 4 baris', count($all) === 4);
check('daftar: terbaru dulu, id besar dulu pada tanggal sama', $all[0]->getId() === $id4 && $all[1]->getId() === $id1 && $all[3]->getId() === $id3);
$asc = $exp->findAll(ExpenseFilter::fromQuery(['sort' => 'asc']));
check('daftar: urut asc', $asc[0]->getId() === $id3);
check('daftar: subclass sesuai tipe', $all[3] instanceof CreditExpense && $all[0] instanceof CashExpense);
check('daftar: nama kategori ikut', $all[1]->getCategoryName() === 'Makanan');

check('cari: % tidak jadi wildcard', count($exp->findAll(ExpenseFilter::fromQuery(['search' => '%']))) === 1);
check('cari: _ tidak jadi wildcard', count($exp->findAll(ExpenseFilter::fromQuery(['search' => '_']))) === 1);
check('cari: huruf besar kecil', count($exp->findAll(ExpenseFilter::fromQuery(['search' => 'MAKAN']))) === 1);
check('cari: kutip tunggal aman', $exp->findAll(ExpenseFilter::fromQuery(['search' => "' OR 1=1 --"])) === []);
check('filter kategori', count($exp->findAll(ExpenseFilter::fromQuery(['category' => 'transportasi']))) === 1);
check('filter bulan', count($exp->findAll(ExpenseFilter::fromQuery(['month' => '2026-10']))) === 1);
check('filter gabungan (Makan siang dan Kopi_a)', count($exp->findAll(ExpenseFilter::fromQuery(['month' => '2026-11', 'category' => 'makanan']))) === 2);

check('total keseluruhan', $exp->totalOverall() === 25000 + 35000 + 5000000 + 10000);
check('total bulan', $exp->totalForMonth('2026-11') === 70000 && $exp->totalForMonth('2026-10') === 5000000);
check('total bulan kosong 0', $exp->totalForMonth('2020-01') === 0);
$per = $exp->totalsPerMonth();
check('total per bulan terbaru dulu', $per[0]['month'] === '2026-11' && $per[0]['count'] === 3 && $per[1]['month'] === '2026-10');

$orig = $exp->find($id1);
$edited = ExpenseFactory::fromInput(
    ['type' => 'CreditExpense', 'name' => 'Makan siang edit', 'amount' => '27000', 'category' => 'makanan',
     'date' => '2026-11-09', 'card_name' => 'BCA', 'installments' => '3', 'interest_rate' => '0'],
    $id1,
    $orig->getCreatedAt()
);
check('update: berhasil dan ganti tipe', $exp->update($edited) === true && $exp->find($id1) instanceof CreditExpense);
check('update: created_at tetap', $exp->find($id1)->getCreatedAt() === $orig->getCreatedAt());
check('update: total ikut berubah', $exp->totalOverall() === 27000 + 35000 + 5000000 + 10000);
$back = ExpenseFactory::fromInput(['name' => 'Kembali tunai', 'amount' => '27000', 'category' => 'makanan', 'date' => '2026-11-09'], $id1);
check('update: kembali ke tunai membersihkan kolom kredit', $exp->update($back) === true && $exp->find($id1) instanceof CashExpense);
check('update id tidak ada false', $exp->update(ExpenseFactory::fromInput(['name' => 'x', 'amount' => '1', 'category' => 'makanan', 'date' => '2026-11-01'], 9999)) === false);

check('hapus berhasil', $exp->delete($id2) === true && $exp->find($id2) === null);
check('hapus ulang false (aman diulang)', $exp->delete($id2) === false);
check('total setelah hapus', $exp->totalOverall() === 27000 + 5000000 + 10000);

// ---- Constraint di database (lapis terakhir) --------------------------------

$raw = static fn (string $sql) => $pdo->exec($sql);
$ins = "INSERT INTO expenses (name, amount, category, type, date, created_at, updated_at) VALUES ";
$ts  = "'2026-11-09 00:00:00', '2026-11-09 00:00:00'";
check('db: amount 0 ditolak', throwsPdo(fn () => $raw($ins . "('x', 0, 'makanan', 'CashExpense', '2026-11-09', $ts)")));
check('db: tanggal ngawur ditolak', throwsPdo(fn () => $raw($ins . "('x', 1, 'makanan', 'CashExpense', '2026-13-45', $ts)")));
check('db: 2026-02-30 ditolak', throwsPdo(fn () => $raw($ins . "('x', 1, 'makanan', 'CashExpense', '2026-02-30', $ts)")));
check('db: kategori tidak ada ditolak', throwsPdo(fn () => $raw($ins . "('x', 1, 'hantu', 'CashExpense', '2026-11-09', $ts)")));
check('db: tipe asing ditolak', throwsPdo(fn () => $raw($ins . "('x', 1, 'makanan', 'Lain', '2026-11-09', $ts)")));
check('db: nama kosong ditolak', throwsPdo(fn () => $raw($ins . "('  ', 1, 'makanan', 'CashExpense', '2026-11-09', $ts)")));
check('db: cash dengan card_name ditolak', throwsPdo(fn () => $raw(
    "INSERT INTO expenses (name, amount, category, type, date, card_name, created_at, updated_at) VALUES ('x', 1, 'makanan', 'CashExpense', '2026-11-09', 'BCA', $ts)"
)));
check('db: credit tanpa card_name ditolak', throwsPdo(fn () => $raw($ins . "('x', 1, 'makanan', 'CreditExpense', '2026-11-09', $ts)")));
check('db: hapus kategori bawaan lewat SQL mentah ditolak', throwsPdo(fn () => $raw("DELETE FROM categories WHERE slug = 'belanja'")));

// ---- Kategori (US-6) --------------------------------------------------------

check('kategori: tambah', $cat->add('Kos & Sewa') === 'kos-sewa' && in_array('kos-sewa', $cat->slugs(), true));
check('kategori: duplikat 409', throwsHttp(fn () => $cat->add('kos sewa'), 409));
check('kategori: terlalu pendek 422', throwsHttp(fn () => $cat->add('a'), 422));
check('kategori: terlalu panjang 422', throwsHttp(fn () => $cat->add(str_repeat('a', 31)), 422));
check('kategori: tanpa huruf/angka 422', throwsHttp(fn () => $cat->add('!!!'), 422));
check('kategori: bawaan tidak bisa dihapus 403', throwsHttp(fn () => $cat->delete('makanan'), 403));
check('kategori: tidak ada 404', throwsHttp(fn () => $cat->delete('hantu'), 404));

$exp->insert(cash(['category' => 'kos-sewa', 'name' => 'Kos', 'amount' => '900000']));
check('kategori: dipakai tidak bisa dihapus 409', throwsHttp(fn () => $cat->delete('kos-sewa'), 409));
$cat->add('Hobi');
$cat->delete('hobi');
check('kategori: tidak dipakai bisa dihapus', !in_array('hobi', $cat->slugs(), true));
$counts = array_column($cat->all(), 'expense_count', 'slug');
check('kategori: hitung pemakaian', $counts['kos-sewa'] === 1 && $counts['makanan'] === 2);

// ---- Pemeriksaan lingkungan dan file statis (masalah nyata di Windows) ------

check('requirements: ekstensi yang ada lolos', Requirements::missing(['mbstring']) === []);
check('requirements: ekstensi palsu terdeteksi', Requirements::missing(['mbstring', 'ekstensi_tidak_ada']) === ['ekstensi_tidak_ada']);
check('requirements: semua ekstensi wajib aktif di mesin tes', Requirements::missing() === []);
$setupMsg = '';
try {
    Requirements::assertMet(['ekstensi_tidak_ada']);
} catch (SetupException $e) {
    $setupMsg = $e->getMessage();
}
check('requirements: pesan berisi nama ekstensi dan langkah perbaikan',
    str_contains($setupMsg, 'extension=ekstensi_tidak_ada') && str_contains($setupMsg, 'php --ini'));

check('static: path Windows (backslash) dianggap di dalam public', StaticFiles::isInside('D:\\Downloads\\app\\public\\css\\style.css', 'D:\\Downloads\\app/public'));
check('static: huruf drive beda kapital tetap cocok', StaticFiles::isInside('d:\\app\\public\\a.css', 'D:\\app/public'));
check('static: folder saudara bernama mirip ditolak', !StaticFiles::isInside('D:\\app\\public_old\\a.css', 'D:\\app/public'));
check('static: di luar public ditolak', !StaticFiles::isInside('D:\\app\\database\\x.sqlite', 'D:\\app/public'));
check('static: css asli boleh disajikan', StaticFiles::resolve('/public/css/style.css', dirname(__DIR__)) !== null);
check('static: .php ditolak', StaticFiles::resolve('/public/../index.php', dirname(__DIR__)) === null);
check('static: traversal ke database ditolak', StaticFiles::resolve('/public/../database/schema.sql', dirname(__DIR__)) === null);
check('static: traversal ter-encode ditolak', StaticFiles::resolve('/public/%2e%2e/database/schema.sql', dirname(__DIR__)) === null);
check('static: file tidak ada ditolak', StaticFiles::resolve('/public/css/tidak-ada.css', dirname(__DIR__)) === null);

// ---- Database file: migrasi idempoten, data bertahan ------------------------

$tmp = sys_get_temp_dir() . '/expense_test_' . bin2hex(random_bytes(4)) . '/db.sqlite';
$a = Database::open($tmp);
(new ExpenseRepository($a))->insert(cash(['name' => 'Persisten']));
unset($a);
$b = Database::open($tmp);
check('persistensi: data bertahan setelah koneksi baru', (new ExpenseRepository($b))->totalOverall() === 25000);
check('persistensi: migrasi tidak menggandakan kategori', count((new CategoryRepository($b))->slugs()) === 4);
unset($b);
array_map('unlink', glob($tmp . '*') ?: []);
rmdir(dirname($tmp));

echo "\n" . $passed . ' lolos, ' . count($failed) . " gagal\n";
exit($failed === [] ? 0 : 1);
