<?php
// Pengujian otomatis 12 skenario pada modul praktikum Pertemuan 06.
// Data diuji di memori, tidak menyentuh PHP Session aplikasi.
require_once __DIR__ . '/DataMahasiswa.php';
 
function aman(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
 
function pesanTolak(callable $f): string {
    try {
        $f();
    } catch (Throwable $e) {
        return $e->getMessage();
    }
    return '';
}
 
function rp(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
 
$hasil = [];
 
function catat(array &$hasil, string $no, string $skenario, string $harapan, callable $uji): void {
    try {
        [$keluaran, $lulus] = $uji();
    } catch (Throwable $e) {
        $keluaran = 'Error tak terduga: ' . $e->getMessage();
        $lulus = false;
    }
    $hasil[] = compact('no', 'skenario', 'harapan', 'keluaran', 'lulus');
}
 
// 01
catat($hasil, '01', 'Tambah Reguler UKT 5.000.000', 'Biaya akhir 5.000.000', function () {
    $repo = new DataMahasiswa();
    $m = new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000);
    $repo->tambah($m);
    return ['Biaya akhir ' . rp($m->hitungBiaya()), $m->hitungBiaya() === 5000000];
});
 
// 02
catat($hasil, '02', 'Tambah Beasiswa UKT 5.000.000, potongan 60%', 'Biaya akhir 2.000.000', function () {
    $repo = new DataMahasiswa();
    $m = new MahasiswaBeasiswa('23002', 'Siti', 'Informatika', 4, 5000000, 60);
    $repo->tambah($m);
    return ['Biaya akhir ' . rp($m->hitungBiaya()), $m->hitungBiaya() === 2000000];
});
 
// 03
catat($hasil, '03', 'NIM kosong', 'Ditolak parent constructor', function () {
    $pesan = pesanTolak(fn() => new MahasiswaReguler('', 'Andi', 'Informatika', 4, 5000000));
    return [$pesan !== '' ? 'Ditolak: ' . $pesan : 'Tidak ditolak', $pesan === 'Identitas wajib diisi'];
});
 
// 04
catat($hasil, '04', 'Semester 0 atau 15', 'Ditolak parent constructor', function () {
    $p0 = pesanTolak(fn() => new MahasiswaReguler('23003', 'Budi', 'Informatika', 0, 5000000));
    $p15 = pesanTolak(fn() => new MahasiswaReguler('23004', 'Cici', 'Informatika', 15, 5000000));
    $lulus = $p0 === 'Semester harus 1-14' && $p15 === 'Semester harus 1-14';
    return ['Semester 0: ' . ($p0 ?: 'tidak ditolak') . '. Semester 15: ' . ($p15 ?: 'tidak ditolak'), $lulus];
});
 
// 05
catat($hasil, '05', 'UKT -100', 'Ditolak child constructor', function () {
    $pesan = pesanTolak(fn() => new MahasiswaReguler('23005', 'Dina', 'Informatika', 4, -100));
    return [$pesan !== '' ? 'Ditolak: ' . $pesan : 'Tidak ditolak', $pesan === 'UKT tidak boleh negatif'];
});
 
// 06
catat($hasil, '06', 'Potongan 120%', 'Ditolak child constructor', function () {
    $pesan = pesanTolak(fn() => new MahasiswaBeasiswa('23006', 'Eko', 'Informatika', 4, 5000000, 120));
    return [$pesan !== '' ? 'Ditolak: ' . $pesan : 'Tidak ditolak', $pesan === 'UKT/potongan tidak valid'];
});
 
// 07
catat($hasil, '07', 'NIM duplikat', 'Ditolak repository', function () {
    $repo = new DataMahasiswa();
    $repo->tambah(new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000));
    $ok = $repo->tambah(new MahasiswaBeasiswa('23001', 'Siti', 'Informatika', 4, 5000000, 60));
    return [$ok ? 'Data duplikat diterima' : 'Ditolak, jumlah data ' . count($repo->semua()), !$ok && count($repo->semua()) === 1];
});
 
// 08
catat($hasil, '08', 'Edit Reguler menjadi Beasiswa pada NIM sama', 'Jenis berubah, data tetap satu record', function () {
    $repo = new DataMahasiswa();
    $repo->tambah(new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000));
    $ok = $repo->ubah('23001', new MahasiswaBeasiswa('23001', 'Andi', 'Informatika', 4, 5000000, 60));
    $m = $repo->cari('23001');
    $jenis = $m ? $m->getJenis() : '-';
    $jumlah = count($repo->semua());
    return ["Jenis {$jenis}, jumlah data {$jumlah}", $ok && $jenis === 'Beasiswa' && $jumlah === 1];
});
 
// 09
catat($hasil, '09', 'Edit dengan NIM berbeda', 'Ditolak repository', function () {
    $repo = new DataMahasiswa();
    $repo->tambah(new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000));
    $ok = $repo->ubah('23001', new MahasiswaReguler('23999', 'Andi', 'Informatika', 4, 5000000));
    $lama = $repo->cari('23001') !== null;
    $baru = $repo->cari('23999') !== null;
    return [$ok ? 'Edit diterima' : 'Ditolak, NIM 23001 tetap ada', !$ok && $lama && !$baru];
});
 
// 10
catat($hasil, '10', 'Hapus satu record', 'Record lain tetap tersedia', function () {
    $repo = new DataMahasiswa();
    $repo->tambah(new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000));
    $repo->tambah(new MahasiswaBeasiswa('23002', 'Siti', 'Informatika', 4, 5000000, 60));
    $ok = $repo->hapus('23001');
    $sisa = array_map('strval', array_keys($repo->semua()));
    return ['Sisa NIM: ' . implode(', ', $sisa), $ok && $sisa === ['23002']];
});
 
// 11
catat($hasil, '11', 'Refresh browser selama Session aktif', 'Data tetap ada', function () {
    $repo = new DataMahasiswa();
    $repo->tambah(new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000));
    $repo->tambah(new MahasiswaBeasiswa('23002', 'Siti', 'Informatika', 4, 5000000, 60));
    // PHP Session menyimpan data dengan serialize, lalu membacanya kembali dengan unserialize
    $tersimpan = serialize($repo->semua());
    $dibaca = unserialize($tersimpan);
    $repoBaru = new DataMahasiswa(is_array($dibaca) ? $dibaca : []);
    $jumlah = count($repoBaru->semua());
    $biaya = $repoBaru->cari('23002')?->hitungBiaya();
    return ["Jumlah data setelah dibaca ulang {$jumlah}, biaya Siti " . rp((int)$biaya), $jumlah === 2 && $biaya === 2000000];
});
 
// 12
catat($hasil, '12', 'Akses $m->nim langsung', 'Ditolak karena private', function () {
    $m = new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000);
    set_error_handler(function (int $no, string $str) {
        throw new ErrorException($str, 0, $no);
    });
    try {
        $nilai = $m->nim;
        return ['Terbaca: ' . var_export($nilai, true), false];
    } catch (Throwable $e) {
        return ['Ditolak: ' . $e->getMessage(), true];
    } finally {
        restore_error_handler();
    }
});
 
$lulus = count(array_filter($hasil, fn($h) => $h['lulus']));
$total = count($hasil);
 
if (PHP_SAPI === 'cli') {
    foreach ($hasil as $h) {
        echo "{$h['no']} | {$h['skenario']} | {$h['keluaran']} | " . ($h['lulus'] ? 'Sesuai' : 'Tidak sesuai') . PHP_EOL;
    }
    echo "Hasil: {$lulus} dari {$total} sesuai" . PHP_EOL;
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pengujian</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px; background: #f4f7f9; color: #1f2937; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .container { max-width: 64rem; margin: 0 auto; }
        h1 { margin: 0 0 4px; font-size: 30px; color: #0f3057; }
        p { margin: 0 0 24px; font-size: 14px; color: #6b7280; }
        a { color: #1e5f74; }
        .kartu { background: #fff; border: 1px solid #f3f4f6; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,.05); padding: 24px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; text-align: left; }
        thead { background: #f3f4f6; font-size: 12px; text-transform: uppercase; color: #374151; }
        th, td { padding: 12px 16px; vertical-align: top; }
        tbody tr { border-bottom: 1px solid #e5e7eb; }
        .ok { color: #15803d; font-weight: 600; }
        .gagal { color: #b91c1c; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hasil Pengujian</h1>
        <p><?= $lulus ?> dari <?= $total ?> skenario sesuai | <a href="index.php">Kembali ke aplikasi</a></p>
        <div class="kartu">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Skenario</th>
                        <th>Hasil yang diharapkan</th>
                        <th>Keluaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hasil as $h): ?>
                    <tr>
                        <td><?= aman($h['no']) ?></td>
                        <td><?= aman($h['skenario']) ?></td>
                        <td><?= aman($h['harapan']) ?></td>
                        <td><?= aman($h['keluaran']) ?></td>
                        <td class="<?= $h['lulus'] ? 'ok' : 'gagal' ?>"><?= $h['lulus'] ? 'Sesuai' : 'Tidak sesuai' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
 