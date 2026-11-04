<?php
declare(strict_types=1);
// Jalankan: php tests/test_skenario.php
// Menguji 10 skenario wajib dari modul Pertemuan 05 dan mencetak tabel hasil uji.
require_once __DIR__ . '/../DataMahasiswa.php';

$hasil = [];

function catat(string $no, string $kasus, string $harapan, bool $lulus, string $catatan = ''): void
{
    global $hasil;
    $hasil[] = [$no, $kasus, $harapan, $lulus ? 'LULUS' : 'GAGAL', $catatan];
}

/** Jalankan fungsi, kembalikan exception/error yang muncul (atau null jika tidak ada). */
function tangkap(callable $fn): ?Throwable
{
    try {
        $fn();
    } catch (Throwable $e) {
        return $e;
    }
    return null;
}

function baru(string $nim = '23001', int $semester = 4): Mahasiswa
{
    return new Mahasiswa($nim, 'Andi', 'Sains dan Teknologi', 'Informatika', 'SMAN 1', $semester, 'Musik', 'Islam');
}

// 01 Mahasiswa valid, semester 4
$repo = new DataMahasiswa();
$m = baru();
$ok = $repo->tambah($m) && $repo->cari('23001')?->getSemester() === 4;
catat('01', 'Mahasiswa valid, semester 4', 'Object dibuat dan tersimpan', $ok);

// 02 NIM kosong
$e = tangkap(fn() => baru('   '));
catat('02', 'NIM kosong', 'Ditolak dengan pesan validasi', $e instanceof InvalidArgumentException, $e?->getMessage() ?? '');

// 03 Semester -1
$e = tangkap(fn() => $m->setSemester(-1));
catat('03', 'Semester -1', 'Ditolak oleh setter',
    $e instanceof InvalidArgumentException && $m->getSemester() === 4, $e?->getMessage() ?? '');

// 04 Semester 15
$e = tangkap(fn() => $m->setSemester(15));
catat('04', 'Semester 15', 'Ditolak oleh setter',
    $e instanceof InvalidArgumentException && $m->getSemester() === 4, $e?->getMessage() ?? '');

// 05 Nama kosong atau spasi
$e1 = tangkap(fn() => $m->setNama(''));
$e2 = tangkap(fn() => $m->setNama('   '));
catat('05', 'Nama kosong/spasi', 'Ditolak oleh setter',
    $e1 instanceof InvalidArgumentException && $e2 instanceof InvalidArgumentException && $m->getNama() === 'Andi',
    $e2?->getMessage() ?? '');

// 06 Akses langsung property private
$e = tangkap(function () use ($m) { return $m->semester; });
catat('06', 'Akses langsung $m->semester', 'Error akses private',
    $e instanceof Error && str_contains($e->getMessage(), 'private'), $e?->getMessage() ?? '');

// 07 Edit nama valid lewat jalur resmi
$m->setNama('  Budi Santoso ');
$m->ubahProfil('Budi Santoso', 'Sains dan Teknologi', 'Informatika', 'SMAN 1', 5, 'Bola', 'Islam');
$ok = $m->getNama() === 'Budi Santoso' && $m->getSemester() === 5 && $repo->ubah('23001', $m);
catat('07', 'Edit nama valid', 'Data berubah melalui jalur yang diizinkan', $ok && $repo->cari('23001')?->getNama() === 'Budi Santoso');

// 08 NIM duplikat pada tambah
$jumlahSebelum = count($repo->semua());
$ditolak = $repo->tambah(baru('23001')) === false;
catat('08', 'NIM duplikat pada tambah', 'Ditolak oleh repository', $ditolak && count($repo->semua()) === $jumlahSebelum);

// 09 Hapus mahasiswa: hanya record terkait yang hilang
$repo->tambah(baru('23002'));
$repo->tambah(baru('23003'));
$hapus = $repo->hapus('23002');
$ok = $hapus && $repo->cari('23002') === null && $repo->cari('23001') !== null && $repo->cari('23003') !== null;
catat('09', 'Hapus mahasiswa', 'Hanya record terkait dihapus', $ok);

// 10 Data tetap ada setelah refresh. serialize/unserialize meniru cara PHP Session menyimpan object.
$tersimpan = serialize($repo->semua());
$dipulihkan = unserialize($tersimpan, ['allowed_classes' => [Mahasiswa::class]]);
$repoBaru = new DataMahasiswa(is_array($dipulihkan) ? $dipulihkan : []);
$ok = count($repoBaru->semua()) === count($repo->semua()) && $repoBaru->cari('23003')?->getNim() === '23003';
catat('10', 'Refresh browser setelah tambah', 'Data tetap tersedia selama Session berlaku', $ok);

// Uji tambahan: setter lain dan perubahan atomik
$e = tangkap(fn() => $m->setAgama('Lainnya'));
catat('T1', 'setAgama di luar daftar', 'Ditolak oleh setter', $e instanceof InvalidArgumentException && $m->getAgama() === 'Islam');

$e = tangkap(fn() => $m->ubahProfil('Nama Baru', 'FST', 'Informatika', 'SMAN 2', 99, '', 'Islam'));
catat('T2', 'ubahProfil dengan semester 99', 'Ditolak dan tidak ada property berubah',
    $e instanceof InvalidArgumentException && $m->getNama() === 'Budi Santoso' && $m->getAsalSekolah() === 'SMAN 1');

catat('T3', 'Method setNim()', 'Tidak tersedia', !method_exists($m, 'setNim'));

// Cetak tabel
$lebar = [4, 34, 44, 7];
$garis = str_repeat('-', array_sum($lebar) + 3 * count($lebar) + 1);
echo PHP_EOL . 'TABEL HASIL UJI' . PHP_EOL . $garis . PHP_EOL;
printf("%-{$lebar[0]}s | %-{$lebar[1]}s | %-{$lebar[2]}s | %-{$lebar[3]}s | %s\n", 'No', 'Kasus uji', 'Hasil yang diharapkan', 'Status', 'Pesan');
echo $garis . PHP_EOL;
$gagal = 0;
foreach ($hasil as [$no, $kasus, $harapan, $status, $catatan]) {
    if ($status === 'GAGAL') $gagal++;
    printf("%-{$lebar[0]}s | %-{$lebar[1]}s | %-{$lebar[2]}s | %-{$lebar[3]}s | %s\n", $no, $kasus, $harapan, $status, $catatan);
}
echo $garis . PHP_EOL;
echo 'Total: ' . count($hasil) . ' uji, gagal: ' . $gagal . PHP_EOL;
exit($gagal === 0 ? 0 : 1);
