<?php
declare(strict_types=1);
require_once __DIR__ . '/DataMahasiswa.php';
session_start();

$repo = new DataMahasiswa($_SESSION['mahasiswa'] ?? []);
$data = $repo->semua();

if (empty($data)) {
    header('Location: index.php');
    exit;
}

$keys = [
    'nim' => 'NIM','nama' => 'Nama','fakultas' => 'Fakultas',
    'prodi' => 'Program Studi','asalSekolah' => 'Asal Sekolah',
    'semester' => 'Semester','hobi' => 'Hobi','agama' => 'Agama',
];

$filename = 'data_mahasiswa_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

fwrite($out, "\xEF\xBB\xBF");
fwrite($out, "sep=;\n");

fputcsv($out, array_values($keys), ';');

// Baris data
foreach ($data as $m) {
    $row = [];
    foreach (array_keys($keys) as $key) {
        $row[] = $m->$key ?? '';
    }
    fputcsv($out, $row, ';');
}

fclose($out);
exit;