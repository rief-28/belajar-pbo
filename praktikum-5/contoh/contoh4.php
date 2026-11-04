<?php
if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');
// Contoh 4: dua aturan bisnis (nama dan semester).
// mb_strlen() membutuhkan ekstensi mbstring aktif di php.ini.
class Mahasiswa {
    private string $nama;
    private int $semester;

    public function __construct(string $nama, int $semester) {
        $this->setNama($nama);
        $this->setSemester($semester);
    }

    public function setNama(string $nama): void {
        $nama = trim($nama);
        if (mb_strlen($nama) < 3) {
            throw new InvalidArgumentException("Nama minimal 3 karakter");
        }
        $this->nama = $nama;
    }

    public function getNama(): string { return $this->nama; }

    public function setSemester(int $nilai): void {
        if ($nilai < 1 || $nilai > 14) {
            throw new InvalidArgumentException("Semester harus 1 sampai 14");
        }
        $this->semester = $nilai;
    }

    public function getSemester(): int { return $this->semester; }
}

$m = new Mahasiswa(" Siti ", 4);
echo $m->getNama() . " / " . $m->getSemester() . PHP_EOL; // Hasil yang diharapkan: Siti / 4

try {
    $m->setNama("Al");
} catch (InvalidArgumentException $e) {
    echo "Ditolak: " . $e->getMessage() . PHP_EOL;
}
