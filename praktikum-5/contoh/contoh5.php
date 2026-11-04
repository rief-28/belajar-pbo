<?php
if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');
// Contoh 5: enkapsulasi perilaku pada class Rekening.
class Rekening {
    private int $saldo = 0;

    public function setor(int $jumlah): void {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException("Setoran harus positif");
        }
        $this->saldo += $jumlah;
    }

    public function tarik(int $jumlah): void {
        if ($jumlah <= 0 || $jumlah > $this->saldo) {
            throw new InvalidArgumentException("Penarikan tidak valid");
        }
        $this->saldo -= $jumlah;
    }

    public function getSaldo(): int { return $this->saldo; }
}

$r = new Rekening();
$r->setor(100000);
$r->tarik(25000);
echo $r->getSaldo() . PHP_EOL; // Hasil yang diharapkan: 75000

try {
    $r->tarik(500000); // melebihi saldo
} catch (InvalidArgumentException $e) {
    echo "Ditolak: " . $e->getMessage() . PHP_EOL;
}
echo "Saldo tetap: " . $r->getSaldo() . PHP_EOL;
