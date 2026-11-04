<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=utf-8');

// Tugas 1: analisis access modifier.
// public nama, protected kategori, private harga.

class Produk
{
    public string $nama;
    protected string $kategori;
    private int $harga;

    public function __construct(string $nama, string $kategori, int $harga)
    {
        if ($harga < 0) {
            throw new InvalidArgumentException('Harga tidak boleh negatif');
        }
        $this->nama = $nama;
        $this->kategori = $kategori;
        $this->harga = $harga;
    }

    public function getHarga(): int
    {
        return $this->harga;
    }
}

class ProdukDigital extends Produk
{
    /** Property protected boleh dibaca dari class turunan. */
    public function bacaKategori(): string
    {
        return $this->kategori;
    }

    /** Property private milik Produk tidak terlihat dari class turunan. */
    public function bacaHargaLangsung(): int
    {
        return $this->harga;
    }
}

/** Jalankan satu percobaan akses dan cetak hasilnya. */
function uji(string $label, callable $aksi): void
{
    // Peringatan "Undefined property" diubah menjadi exception agar mudah dicatat.
    set_error_handler(function (int $no, string $pesan): bool {
        throw new ErrorException($pesan);
    });
    try {
        $nilai = $aksi();
        echo str_pad($label, 48) . 'BERHASIL, nilai: ' . $nilai . PHP_EOL;
    } catch (Throwable $e) {
        echo str_pad($label, 48) . 'DITOLAK, ' . $e->getMessage() . PHP_EOL;
    } finally {
        restore_error_handler();
    }
}

$p = new Produk('Buku PBO', 'Buku', 85000);
$d = new ProdukDigital('E-book PHP', 'Digital', 50000);

echo "A. Akses dari luar class" . PHP_EOL;
uji('1. $p->nama (public)', fn() => $p->nama);
uji('2. $p->kategori (protected)', fn() => $p->kategori);
uji('3. $p->harga (private)', fn() => $p->harga);
uji('4. $p->getHarga() (getter public)', fn() => $p->getHarga());

echo PHP_EOL . "B. Akses dari class turunan ProdukDigital" . PHP_EOL;
uji('5. $d->bacaKategori() (baca protected)', fn() => $d->bacaKategori());
uji('6. $d->bacaHargaLangsung() (baca private)', fn() => $d->bacaHargaLangsung());

echo PHP_EOL . "Penjelasan" . PHP_EOL;
echo "public: terbuka dari mana saja." . PHP_EOL;
echo "protected: hanya class itu sendiri dan class turunannya." . PHP_EOL;
echo "private: hanya class yang mendeklarasikannya. Class turunan harus memakai getHarga()." . PHP_EOL;
