<?php
require_once 'RefactoringCRUDMahasiswa.php';
session_start();

if (!isset($_SESSION['data_mahasiswa'])) {
    $_SESSION['data_mahasiswa'] = [];
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="data_mahasiswa.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['NIM', 'Nama', 'Fakultas', 'Program Studi', 'Asal Sekolah', 'Semester', 'Hobi', 'Agama']);
    foreach ($_SESSION['data_mahasiswa'] as $m) {
        fputcsv($output, [
            $m->getNim(), 
            $m->getNama(), 
            $m->getFakultas(), 
            $m->getProdi(), 
            $m->getAsalSekolah(), 
            $m->getSemester(), 
            $m->getHobi(), 
            $m->getAgama()
        ]);
    }
    fclose($output);
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $mhs = new Mahasiswa(
            $_POST['nim'] ?? '',
            $_POST['nama'] ?? '',
            $_POST['fakultas'] ?? '',
            $_POST['prodi'] ?? '',
            $_POST['asal_sekolah'] ?? '',
            (int)($_POST['semester'] ?? 0),
            $_POST['hobi'] ?? '',
            $_POST['agama'] ?? ''
        );
        
        $is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] === '1';
        
        if (!$is_edit && isset($_SESSION['data_mahasiswa'][$mhs->getNim()])) {
            throw new InvalidArgumentException("NIM sudah terdaftar!");
        }

        $_SESSION['data_mahasiswa'][$mhs->getNim()] = $mhs;
        $success = $is_edit ? "Data berhasil diperbarui." : "Data berhasil ditambahkan.";
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Exception $e) {
        $error = "Kesalahan sistem: " . $e->getMessage();
    }
}

if (isset($_GET['hapus'])) {
    $nim_hapus = $_GET['hapus'];
    if (isset($_SESSION['data_mahasiswa'][$nim_hapus])) {
        unset($_SESSION['data_mahasiswa'][$nim_hapus]);
        $success = "Data berhasil dihapus.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $nim_edit = $_GET['edit'];
    if (isset($_SESSION['data_mahasiswa'][$nim_edit])) {
        $edit_data = $_SESSION['data_mahasiswa'][$nim_edit];
    }
}

function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

$total_data = count($_SESSION['data_mahasiswa']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas P5</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f7f9;
            color: #333;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .card-title {
            color: #0b345b;
            font-size: 1.5em;
            margin: 0;
            font-weight: 700;
            height: 45px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            color: #0b345b;
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .form-control {
            padding: 12px 15px;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1em;
            color: #333;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #1a629b;
        }
        .form-control[readonly] {
            background-color: #eef3f8;
            cursor: not-allowed;
        }
        .form-actions {
            margin-top: 25px;
        }
        .btn {
            background-color: #155e96;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #0f4470; }
        .btn-success { background-color: #198754; padding: 10px 16px; font-size: 0.9em; }
        .btn-success:hover { background-color: #157347; }
        .btn-edit { background-color: #155e96; padding: 8px 16px; font-size: 0.9em; margin-right: 5px; }
        .btn-edit:hover { background-color: #0f4470; }
        .btn-hapus { background-color: #c93a40; padding: 8px 16px; font-size: 0.9em; }
        .btn-hapus:hover { background-color: #b02a30; }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alert-error { background-color: #fde8e8; color: #9b1c1c; border: 1px solid #fbd5d5; }
        .alert-success { background-color: #def7ec; color: #03543f; border: 1px solid #bcdefa; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f4f8;
        }
        th {
            background-color: #eef3f8;
            color: #0b345b;
            font-weight: 700;
            font-size: 0.95em;
        }
        .empty-state {
            padding: 20px 15px;
            color: #6c757d;
            font-style: italic;
        }
        .action-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= aman($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= aman($success) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title"><?= $edit_data ? 'Edit Data Mahasiswa' : 'Tambah Data Mahasiswa' ?></h2>
            <form method="POST" action="index.php">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="is_edit" value="1">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIM</label>
                        <input type="text" name="nim" class="form-control" value="<?= $edit_data ? aman($edit_data->getNim()) : '' ?>" <?= $edit_data ? 'readonly' : 'required' ?>>
                    </div>
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" value="<?= $edit_data ? aman($edit_data->getNama()) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fakultas</label>
                        <input type="text" name="fakultas" class="form-control" value="<?= $edit_data ? aman($edit_data->getFakultas()) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Program Studi</label>
                        <input type="text" name="prodi" class="form-control" value="<?= $edit_data ? aman($edit_data->getProdi()) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Asal Sekolah</label>
                        <input type="text" name="asal_sekolah" class="form-control" value="<?= $edit_data ? aman($edit_data->getAsalSekolah()) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Semester</label>
                        <input type="number" name="semester" class="form-control" min="1" max="14" value="<?= $edit_data ? aman($edit_data->getSemester()) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Hobi</label>
                        <input type="text" name="hobi" class="form-control" value="<?= $edit_data ? aman($edit_data->getHobi()) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Agama</label>
                        <select name="agama" class="form-control" required>
                            <option value="">-- Pilih Agama --</option>
                            <?php 
                            $agama_list = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
                            $selected_agama = $edit_data ? $edit_data->getAgama() : '';
                            foreach ($agama_list as $a): 
                            ?>
                                <option value="<?= $a ?>" <?= $selected_agama === $a ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn"><?= $edit_data ? 'Update Mahasiswa' : 'Tambah Mahasiswa' ?></button>
                    <?php if ($edit_data): ?>
                        <a href="index.php" class="btn" style="background-color: #6c757d; margin-left: 10px;">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Daftar Mahasiswa (<?= $total_data ?>)</h2>
                <?php if ($total_data > 0): ?>
                    <a href="?export=csv" class="btn btn-success">↓ Export CSV</a>
                <?php endif; ?>
            </div>
            <div style="overflow-x: auto;">
                <table>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Fakultas</th>
                        <th>Program Studi</th>
                        <th>Asal Sekolah</th>
                        <th>Semester</th>
                        <th>Hobi</th>
                        <th>Agama</th>
                        <th>Aksi</th>
                    </tr>
                    
                    <?php if ($total_data === 0): ?>
                    <tr>
                        <td colspan="9" class="empty-state">Belum ada data.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($_SESSION['data_mahasiswa'] as $mhs): ?>
                        <tr>
                            <td><?= aman($mhs->getNim()) ?></td>
                            <td><?= aman($mhs->getNama()) ?></td>
                            <td><?= aman($mhs->getFakultas()) ?></td>
                            <td><?= aman($mhs->getProdi()) ?></td>
                            <td><?= aman($mhs->getAsalSekolah()) ?></td>
                            <td><?= aman($mhs->getSemester()) ?></td>
                            <td><?= aman($mhs->getHobi()) ?></td>
                            <td><?= aman($mhs->getAgama()) ?></td>
                            <td class="action-cell">
                                <a href="?edit=<?= urlencode($mhs->getNim()) ?>" class="btn btn-edit">Edit</a>
                                <a href="?hapus=<?= urlencode($mhs->getNim()) ?>" class="btn btn-hapus" onclick="return confirm('Hapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
