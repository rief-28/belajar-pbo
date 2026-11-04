<?php
class Buku {
    private string $kodeBuku;
    private string $judul;
    private int $stok;

    public function __construct(string $kodeBuku, string $judul, int $stok) {
        $this->kodeBuku = trim($kodeBuku);
        $this->setJudul($judul);
        
        if ($stok < 0) {
            throw new InvalidArgumentException("Stok tidak boleh negatif");
        }
        $this->stok = $stok;
    }

    public function getKodeBuku(): string {
        return $this->kodeBuku;
    }

    public function getJudul(): string {
        return $this->judul;
    }

    public function setJudul(string $judul): void {
        $judul = trim($judul);
        if ($judul === "") {
            throw new InvalidArgumentException("Judul wajib diisi");
        }
        $this->judul = $judul;
    }

    public function getStok(): int {
        return $this->stok;
    }

    public function tambahStok(int $jumlah): void {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException("Jumlah harus positif");
        }
        $this->stok += $jumlah;
    }

    public function kurangiStok(int $jumlah): void {
        if ($jumlah <= 0 || $jumlah > $this->stok) {
            throw new InvalidArgumentException("Pengurangan tidak valid atau stok tidak cukup");
        }
        $this->stok -= $jumlah;
    }
}