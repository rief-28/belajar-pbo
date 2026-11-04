<?php
declare(strict_types=1);

class Mahasiswa {
    private string $nim;
    private string $nama;
    private string $fakultas;
    private string $prodi;
    private string $asalSekolah;
    private int $semester;
    private string $hobi;
    private string $agama;

    public function __construct(
        string $nim, string $nama, string $fakultas, string $prodi, 
        string $asalSekolah, int $semester, string $hobi, string $agama
    ) {
        $nim = trim($nim);
        if ($nim === "") throw new InvalidArgumentException("NIM wajib diisi");
        $this->nim = $nim;
        
        $this->setNama($nama);
        $this->fakultas = $fakultas; 
        $this->prodi = $prodi;
        $this->asalSekolah = $asalSekolah;
        $this->setSemester($semester);
        $this->hobi = $hobi;
        $this->setAgama($agama);
    }

    public function getNim(): string { return $this->nim; }
    public function getNama(): string { return $this->nama; }
    public function getFakultas(): string { return $this->fakultas; }
    public function getProdi(): string { return $this->prodi; }
    public function getAsalSekolah(): string { return $this->asalSekolah; }
    public function getSemester(): int { return $this->semester; }
    public function getHobi(): string { return $this->hobi; }
    public function getAgama(): string { return $this->agama; }
    
    public function setNama(string $nama): void {
        $nama = trim($nama);
        if ($nama === "") throw new InvalidArgumentException("Nama wajib diisi");
        $this->nama = $nama;
    }

    public function setSemester(int $semester): void {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException("Semester harus antara 1-14");
        }
        $this->semester = $semester;
    }

    public function setAgama(string $agama): void {
        $agamaDiizinkan = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        if (!in_array($agama, $agamaDiizinkan)) {
            throw new InvalidArgumentException("Agama tidak terdaftar dalam pilihan yang diizinkan");
        }
        $this->agama = $agama;
    }
}
?>