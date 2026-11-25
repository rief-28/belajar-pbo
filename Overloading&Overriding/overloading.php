<?php
class Kalkulator {
    // Dipanggil untuk method instance yang tidak tersedia/tidak dapat diakses.
    public function __call(string $namaMethod, array $argumen): mixed {
        if ($namaMethod === "tambah") {
            if (count($argumen) === 2) {
                return $argumen[0] + $argumen[1];
            }
            if (count($argumen) === 3) {
                return $argumen[0] + $argumen[1] + $argumen[2];
            }
        }
        throw new BadMethodCallException("Method/argumen tidak valid");
    }
}

$hitung = new Kalkulator();
echo $hitung->tambah(10, 20) . PHP_EOL;
echo $hitung->tambah(10, 20, 30) . PHP_EOL;
