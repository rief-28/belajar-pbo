<?php
declare(strict_types=1);
require_once __DIR__ . '/../DataMahasiswa.php';

function cek(bool $kondisi, string $pesan): void {
    if (!$kondisi) throw new RuntimeException('GAGAL: ' . $pesan);
    echo 'OK: ' . $pesan . PHP_EOL;
}
function harusGagal(callable $fn, string $pesan): void {
    try { $fn(); } catch (InvalidArgumentException $e) { cek(true, $pesan); return; }
    throw new RuntimeException('Seharusnya ditolak: ' . $pesan);
}

// membuat objek $m dari kelas Mahasiswa
$m = new Mahasiswa('23001', ' Andi ', 'Sains dan Teknologi', 'Informatika', 'SMAN 1', 4, 'Musik', 'Islam');
cek($m->getNama() === 'Andi', 'Nama dibersihkan oleh model');

// membuat objek $repo dari kelas Mahasiswa
$repo = new DataMahasiswa();
cek($repo->tambah($m), 'Tambah berhasil');
cek(!$repo->tambah($m), 'NIM duplikat ditolak');
cek($repo->cari('23001')?->getSemester() === 4, 'Cari dan getter');
harusGagal(fn() => $m->ubahProfil('Andi', 'FST', 'Informatika', 'SMAN 1', -2, '', 'Islam'), 'Semester negatif ditolak');
cek($m->getSemester() === 4 && $m->getFakultas() === 'Sains dan Teknologi', 'Perubahan gagal tidak mengubah state');
harusGagal(fn() => new Mahasiswa('', 'Andi', 'FST', 'Informatika', 'SMAN 1', 4, '', 'Islam'), 'NIM kosong ditolak');
harusGagal(fn() => new Mahasiswa('23002', 'Budi', 'FST', 'Informatika', 'SMAN 1', 4, '', 'Lainnya'), 'Agama tidak terdaftar ditolak');


// membuat objek $m2 dari kelas Mahasiswa
$m2 = new Mahasiswa('23001', 'Budi', 'FST', 'Informatika', 'SMAN 1', 5, 'Bola', 'Islam');
cek($repo->ubah('23001', $m2), 'Edit berhasil');
cek($repo->cari('23001')?->getNama() === 'Budi', 'Hasil edit tersimpan');
cek($repo->hapus('23001'), 'Hapus berhasil');
cek($repo->cari('23001') === null, 'Data terhapus');
