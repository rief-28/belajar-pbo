<?php
if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');
// Contoh 3: NIM bersifat read-only bagi pemakai class.
class Mahasiswa {
    private string $nim;

    public function __construct(string $nim) {
        $nim = trim($nim);
        if ($nim === "") {
            throw new InvalidArgumentException("NIM wajib diisi");
        }
        $this->nim = $nim;
    }

    public function getNim(): string {
        return $this->nim;
    }
    // Tidak disediakan setNim()
}

$m = new Mahasiswa("23001");
echo $m->getNim() . PHP_EOL; // Hasil yang diharapkan: 23001

try {
    $m->setNim("99999");
} catch (Error $e) {
    echo "Tidak ada jalur untuk mengubah NIM: " . $e->getMessage() . PHP_EOL;
}
