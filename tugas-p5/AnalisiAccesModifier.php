<?php
class Produk {
    public string $nama;
    protected string $kategori;
    private int $harga;

    public function __construct(string $nama, string $kategori, int $harga) {
        $this->nama = $nama;
        $this->kategori = $kategori;
        $this->harga = $harga;
    }
}

class ProdukDigital extends Produk {
    public function getKategori(): string {
        // Berhasil: Properti protected dapat diakses dari dalam class turunan
        return $this->kategori; 
    }
    
    // public function getHarga() { 
    //     // Akan Error: Properti private tidak dapat diakses oleh class turunan
    //     return $this->harga; 
    // }
}

$produk = new Produk("Lisensi Software", "Digital", 150000);

// Pengujian akses dari luar class
echo $produk->nama;       // Berhasil: Bisa diakses dari luar karena public
// echo $produk->kategori; // Error: Akses ditolak karena protected
// echo $produk->harga;    // Error: Akses ditolak karena private