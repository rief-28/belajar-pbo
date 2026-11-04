<?php
if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');
// Contoh 2: private, getter, dan setter.
class Mahasiswa {
    private int $semester;

    public function __construct(int $semester) {
        $this->setSemester($semester);
    }

    public function getSemester(): int {
        return $this->semester;
    }

    public function setSemester(int $semester): void {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException("Semester harus 1 sampai 14");
        }
        $this->semester = $semester;
    }
}

$m = new Mahasiswa(3);
$m->setSemester(5);
echo $m->getSemester() . PHP_EOL; // Hasil yang diharapkan: 5

// Percobaan 1: akses langsung ke property private
try {
    $m->semester = -7;
} catch (Error $e) {
    echo "Error akses private: " . $e->getMessage() . PHP_EOL;
}

// Percobaan 2: nilai tidak valid lewat setter
try {
    $m->setSemester(-7);
} catch (InvalidArgumentException $e) {
    echo "Ditolak setter: " . $e->getMessage() . PHP_EOL;
}
echo "Semester tetap: " . $m->getSemester() . PHP_EOL;
