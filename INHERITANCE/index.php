<?php
require_once __DIR__ . '/DataMahasiswa.php';
session_start();

function aman(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$repo = new DataMahasiswa($_SESSION['mahasiswa'] ?? []);
$error = '';
$form = [
    'aksi'     => 'tambah',
    'nim_lama' => '',
    'nim'      => '',
    'nama'     => '',
    'prodi'    => '',
    'semester' => '',
    'jenis'    => 'Reguler',
    'ukt'      => '',
    'potongan' => '0',
];

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="data_mahasiswa.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['NIM', 'Nama', 'Prodi', 'Semester', 'Jenis', 'Biaya Akhir'], ',', '"', '');
    foreach ($repo->semua() as $m) {
        fputcsv($out, [
            $m->getNim(),
            $m->getNama(),
            $m->getProdi(),
            $m->getSemester(),
            $m->getJenis(),
            $m->hitungBiaya()
        ], ',', '"', '');
    }
    fclose($out);
    exit;
}

if (isset($_GET['edit'])) {
    $editData = $repo->cari((string)$_GET['edit']);
    if ($editData) {
        $form = [
            'aksi'     => 'ubah',
            'nim_lama' => $editData->getNim(),
            'nim'      => $editData->getNim(),
            'nama'     => $editData->getNama(),
            'prodi'    => $editData->getProdi(),
            'semester' => (string)$editData->getSemester(),
            'jenis'    => $editData->getJenis(),
            'ukt'      => method_exists($editData, 'getUkt') ? (string)$editData->getUkt() : '',
            'potongan' => method_exists($editData, 'getPotonganPersen') ? (string)$editData->getPotonganPersen() : '0',
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $aksi = (string)($_POST['aksi'] ?? 'tambah');

        if ($aksi === 'hapus') {
            $nim = (string)($_POST['nim'] ?? '');
            $repo->hapus($nim);
            $_SESSION['mahasiswa'] = $repo->semua();
            header('Location: index.php');
            exit;
        }

        $nim = trim((string)($_POST['nim'] ?? ''));
        $nama = trim((string)($_POST['nama'] ?? ''));
        $prodi = trim((string)($_POST['prodi'] ?? ''));
        $jenis = (string)($_POST['jenis'] ?? '');

        // Simpan isian agar form tidak kosong saat terjadi error
        $form = [
            'aksi'     => $aksi === 'ubah' ? 'ubah' : 'tambah',
            'nim_lama' => (string)($_POST['nim_lama'] ?? ''),
            'nim'      => $nim,
            'nama'     => $nama,
            'prodi'    => $prodi,
            'semester' => trim((string)($_POST['semester'] ?? '')),
            'jenis'    => $jenis,
            'ukt'      => trim((string)($_POST['ukt'] ?? '')),
            'potongan' => trim((string)($_POST['potongan'] ?? '0')),
        ];

        $semester = filter_var($form['semester'], FILTER_VALIDATE_INT);
        $ukt = filter_var($form['ukt'], FILTER_VALIDATE_INT);

        if ($semester === false || $ukt === false) {
            throw new InvalidArgumentException('Semester dan UKT harus berupa angka');
        }

        if ($jenis === 'Reguler') {
            $m = new MahasiswaReguler($nim, $nama, $prodi, $semester, $ukt);
        } elseif ($jenis === 'Beasiswa') {
            $potongan = filter_var($form['potongan'], FILTER_VALIDATE_INT);
            if ($potongan === false) throw new InvalidArgumentException('Potongan tidak valid');
            $m = new MahasiswaBeasiswa($nim, $nama, $prodi, $semester, $ukt, $potongan);
        } else {
            throw new InvalidArgumentException('Jenis mahasiswa tidak dikenal');
        }

        if ($aksi === 'ubah') {
            $ok = $repo->ubah($form['nim_lama'], $m);
            if (!$ok) throw new InvalidArgumentException('Edit ditolak: NIM tidak boleh diubah atau data tidak ditemukan');
        } else {
            $ok = $repo->tambah($m);
            if (!$ok) throw new InvalidArgumentException('NIM sudah terdaftar');
        }

        $_SESSION['mahasiswa'] = $repo->semua();
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

$mahasiswaList = $repo->semua();
$jumlahData = count($mahasiswaList);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
    <style>
        /* Dasar */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px;
            background-color: #f4f7f9;
            color: #1f2937;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .container { max-width: 64rem; margin: 0 auto; }

        /* Judul */
        .header { margin-bottom: 24px; }
        .judul { margin: 0 0 4px; font-size: 30px; font-weight: 700; color: #0f3057; }
        .subjudul { margin: 0; font-size: 14px; color: #6b7280; }

        /* Pesan error */
        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        /* Kartu */
        .kartu {
            background-color: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            padding: 32px;
            margin-bottom: 32px;
        }
        .kartu-judul { margin: 0; font-size: 20px; font-weight: 700; color: #0f3057; }
        .kartu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .form-judul { margin-bottom: 24px; }

        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }
        .label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; }
        .input {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }
        .input:focus { outline: none; border-color: #3b82f6; }
        .input[readonly] { background-color: #f3f4f6; }
        #potongan_container { display: none; }
        #potongan_container.tampil { display: block; }

        /* Tombol */
        .tombol-bar { display: flex; gap: 12px; }
        .btn {
            display: inline-block;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-utama { background-color: #1e5f74; }
        .btn-utama:hover { background-color: #164655; }
        .btn-hijau { background-color: #15803d; }
        .btn-hijau:hover { background-color: #166534; }
        .btn-merah { background-color: #dc2626; }
        .btn-merah:hover { background-color: #b91c1c; }
        .btn-kecil { padding: 6px 16px; font-size: 12px; }

        /* Tabel */
        .tabel-wrap { overflow-x: auto; }
        .tabel { width: 100%; border-collapse: collapse; font-size: 14px; text-align: left; }
        .tabel thead {
            background-color: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            text-transform: uppercase;
            color: #374151;
        }
        .tabel th { padding: 12px 16px; font-weight: 600; }
        .tabel td { padding: 16px; }
        .tabel tbody tr { border-bottom: 1px solid #e5e7eb; }
        .tabel tbody tr:hover { background-color: #f9fafb; }
        .aksi { display: flex; gap: 8px; }
        .aksi form { margin: 0; }

        .catatan { margin: 24px 0 0; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <h1 class="judul">Data Mahasiswa</h1>
            <p class="subjudul">PBO Pertemuan 06: Inheritance | <a href="test.php">Jalankan pengujian</a></p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= aman($error) ?></div>
        <?php endif; ?>

        <div class="kartu">
            <h2 class="kartu-judul form-judul">
                <?= $form['aksi'] === 'ubah' ? 'Edit Data Mahasiswa' : 'Tambah Data Mahasiswa' ?>
            </h2>
            <form method="POST" action="index.php" novalidate>
                <input type="hidden" name="aksi" value="<?= aman($form['aksi']) ?>">
                <?php if ($form['aksi'] === 'ubah'): ?>
                    <input type="hidden" name="nim_lama" value="<?= aman($form['nim_lama']) ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div>
                        <label class="label">NIM</label>
                        <input type="text" name="nim" value="<?= aman($form['nim']) ?>" class="input">
                    </div>
                    <div>
                        <label class="label">Nama</label>
                        <input type="text" name="nama" value="<?= aman($form['nama']) ?>" class="input">
                    </div>
                    <div>
                        <label class="label">Program Studi</label>
                        <input type="text" name="prodi" value="<?= aman($form['prodi']) ?>" class="input">
                    </div>
                    <div>
                        <label class="label">Semester</label>
                        <input type="number" name="semester" value="<?= aman($form['semester']) ?>" class="input">
                    </div>
                    <div>
                        <label class="label">Jenis Mahasiswa</label>
                        <select name="jenis" class="input" onchange="togglePotongan(this.value)">
                            <option value="Reguler" <?= $form['jenis'] === 'Reguler' ? 'selected' : '' ?>>Reguler</option>
                            <option value="Beasiswa" <?= $form['jenis'] === 'Beasiswa' ? 'selected' : '' ?>>Beasiswa</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">UKT Dasar</label>
                        <input type="number" name="ukt" value="<?= aman($form['ukt']) ?>" class="input">
                    </div>
                    <div id="potongan_container" class="<?= $form['jenis'] === 'Beasiswa' ? 'tampil' : '' ?>">
                        <label class="label">Potongan Persen</label>
                        <input type="number" name="potongan" value="<?= aman($form['potongan']) ?>" class="input">
                    </div>
                </div>

                <div class="tombol-bar">
                    <button type="submit" class="btn btn-utama">
                        <?= $form['aksi'] === 'ubah' ? 'Simpan Perubahan' : 'Tambah Data' ?>
                    </button>
                    <?php if ($form['aksi'] === 'ubah'): ?>
                        <a href="index.php" class="btn btn-utama">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="kartu">
            <div class="kartu-header">
                <h2 class="kartu-judul">Daftar Mahasiswa (<?= $jumlahData ?>)</h2>
                <a href="index.php?export=csv" class="btn btn-hijau">↓ Export CSV</a>
            </div>

            <div class="tabel-wrap">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Semester</th>
                            <th>Jenis</th>
                            <th>Biaya Akhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswaList as $m): ?>
                        <tr>
                            <td><?= aman($m->getNim()) ?></td>
                            <td><?= aman($m->getNama()) ?></td>
                            <td><?= aman($m->getProdi()) ?></td>
                            <td><?= aman($m->getSemester()) ?></td>
                            <td><?= aman($m->getJenis()) ?></td>
                            <td>Rp <?= aman(number_format($m->hitungBiaya(), 0, ',', '.')) ?></td>
                            <td class="aksi">
                                <a href="index.php?edit=<?= aman($m->getNim()) ?>" class="btn btn-utama btn-kecil">Edit</a>
                                <form method="POST" action="index.php" onsubmit="return confirm('Hapus data ini?');">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                                    <button type="submit" class="btn btn-merah btn-kecil">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="catatan">Data disimpan pada PHP Session (sementara), bukan MySQL.</p>
        </div>
    </div>

    <script>
        function togglePotongan(val) {
            const el = document.getElementById('potongan_container');
            el.classList.toggle('tampil', val === 'Beasiswa');
        }
    </script>
</body>
</html>