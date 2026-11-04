<?php
// Contoh 1: masalah ketika property public.
// Constructor memeriksa nilai awal, tetapi property public tetap bisa diubah dari luar.
class Mahasiswa {
    public function __construct(public int $semester) {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException("Semester tidak valid");
        }
    }
}

$m = new Mahasiswa(3);
$m->semester = -7; // Bisa dilakukan karena public
echo $m->semester; // Hasil yang diharapkan: -7
