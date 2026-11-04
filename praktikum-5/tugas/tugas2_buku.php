<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');

// Tugas 2: getter, setter, dan validasi pada class Buku.
// kodeBuku hanya bisa dibaca. judul wajib diisi. stok tidak boleh negatif.

class Buku
{
    private string $kodeBuku;
    private string $judul;
    private int $stok;

    public function __construct(string $kodeBuku, string $judul, int $stok = 0)
    {
        $kodeBuku = trim($kodeBuku);
        if ($kodeBuku === '') {
            throw new InvalidArgumentException('Kode buku wajib diisi');
        }
        if ($stok < 0) {
            throw new InvalidArgumentException('Stok awal tidak boleh negatif');
        }
        $this->kodeBuku = $kodeBuku;
        $this->setJudul($judul);
        $this->stok = $stok;
    }

    public function getKodeBuku(): string { return $this->kodeBuku; }
    public function getJudul(): string { return $this->judul; }
    public function getStok(): int { return $this->stok; }
    // Tidak ada setKodeBuku() dan tidak ada setStok().

    public function setJudul(string $judul): void
    {
        $judul = trim($judul);
        if ($judul === '') {
            throw new InvalidArgumentException('Judul wajib diisi');
        }
        $this->judul = $judul;
    }

    public function tambahStok(int $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Jumlah tambahan harus positif');
        }
        $this->stok += $jumlah;
    }

    public function kurangiStok(int $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Jumlah pengurangan harus positif');
        }
        if ($jumlah > $this->stok) {
            throw new InvalidArgumentException('Stok tidak mencukupi');
        }
        $this->stok -= $jumlah;
    }
}

function uji(string $kasus, bool $harusValid, callable $aksi): void
{
    try {
        $aksi();
        $hasil = $harusValid ? 'LULUS (diterima)' : 'GAGAL (seharusnya ditolak)';
        $pesan = '';
    } catch (InvalidArgumentException $e) {
        $hasil = $harusValid ? 'GAGAL (seharusnya diterima)' : 'LULUS (ditolak)';
        $pesan = $e->getMessage();
    }
    echo str_pad($kasus, 42) . str_pad($hasil, 30) . $pesan . PHP_EOL;
}

$b = new Buku('BK-001', 'Pemrograman Berorientasi Objek', 10);

echo str_pad('Kasus uji', 42) . str_pad('Hasil', 30) . 'Pesan' . PHP_EOL;
echo str_repeat('-', 90) . PHP_EOL;

// Input valid (minimal dua)
uji('V1. tambahStok(5)', true, fn() => $b->tambahStok(5));
uji('V2. kurangiStok(3)', true, fn() => $b->kurangiStok(3));
uji('V3. setJudul(" PBO dengan PHP ")', true, fn() => $b->setJudul(' PBO dengan PHP '));

// Input tidak valid (minimal tiga)
uji('X1. setJudul("   ")', false, fn() => $b->setJudul('   '));
uji('X2. tambahStok(-2)', false, fn() => $b->tambahStok(-2));
uji('X3. kurangiStok(100)', false, fn() => $b->kurangiStok(100));
uji('X4. new Buku("BK-002", "Algoritma", -1)', false, fn() => new Buku('BK-002', 'Algoritma', -1));

echo str_repeat('-', 90) . PHP_EOL;
echo 'Kode buku : ' . $b->getKodeBuku() . PHP_EOL;
echo 'Judul     : ' . $b->getJudul() . PHP_EOL;
echo 'Stok akhir: ' . $b->getStok() . ' (10 + 5 - 3 = 12)' . PHP_EOL;
echo 'setKodeBuku tersedia? ' . (method_exists($b, 'setKodeBuku') ? 'ya' : 'tidak') . PHP_EOL;
echo 'setStok tersedia? ' . (method_exists($b, 'setStok') ? 'ya' : 'tidak') . PHP_EOL;
