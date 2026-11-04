<?php
declare(strict_types=1);

/** Model: state private. Perubahan hanya lewat method yang memvalidasi input. */
final class Mahasiswa
{
    private string $nim; // Identitas tetap. Sengaja tidak ada setNim().
    private string $nama;
    private string $fakultas;
    private string $prodi;
    private string $asalSekolah;
    private int $semester;
    private string $hobi;
    private string $agama;

    public const AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    public function __construct(
        string $nim, string $nama, string $fakultas, string $prodi,
        string $asalSekolah, int $semester, string $hobi, string $agama
    ) {
        $this->nim = self::validasiNim($nim);
        $this->ubahProfil($nama, $fakultas, $prodi, $asalSekolah, $semester, $hobi, $agama);
    }

    // ===== Getter: jalur resmi membaca property private =====
    public function getNim(): string { return $this->nim; }
    public function getNama(): string { return $this->nama; }
    public function getFakultas(): string { return $this->fakultas; }
    public function getProdi(): string { return $this->prodi; }
    public function getAsalSekolah(): string { return $this->asalSekolah; }
    public function getSemester(): int { return $this->semester; }
    public function getHobi(): string { return $this->hobi; }
    public function getAgama(): string { return $this->agama; }

    // ===== Setter: hanya untuk atribut yang boleh diubah. Tidak ada setNim(). =====
    public function setNama(string $nama): void { $this->nama = self::validasiNama($nama); }
    public function setFakultas(string $fakultas): void { $this->fakultas = self::validasiFakultas($fakultas); }
    public function setProdi(string $prodi): void { $this->prodi = self::validasiProdi($prodi); }
    public function setAsalSekolah(string $asalSekolah): void { $this->asalSekolah = self::validasiAsalSekolah($asalSekolah); }
    public function setSemester(int $semester): void { $this->semester = self::validasiSemester($semester); }
    public function setHobi(string $hobi): void { $this->hobi = self::validasiHobi($hobi); }
    public function setAgama(string $agama): void { $this->agama = self::validasiAgama($agama); }

    /**
     * Ubah semua atribut sekaligus. Semua input diperiksa lebih dulu.
     * Jika satu input salah, tidak ada property yang berubah.
     */
    public function ubahProfil(
        string $nama, string $fakultas, string $prodi, string $asalSekolah,
        int $semester, string $hobi, string $agama
    ): void {
        $nama = self::validasiNama($nama);
        $fakultas = self::validasiFakultas($fakultas);
        $prodi = self::validasiProdi($prodi);
        $asalSekolah = self::validasiAsalSekolah($asalSekolah);
        $semester = self::validasiSemester($semester);
        $hobi = self::validasiHobi($hobi);
        $agama = self::validasiAgama($agama);

        $this->nama = $nama;
        $this->fakultas = $fakultas;
        $this->prodi = $prodi;
        $this->asalSekolah = $asalSekolah;
        $this->semester = $semester;
        $this->hobi = $hobi;
        $this->agama = $agama;
    }

    // ===== Aturan validasi: satu tempat, dipakai constructor, setter, dan ubahProfil() =====
    private static function validasiNim(string $nim): string
    {
        $nim = trim($nim);
        if ($nim === '' || strlen($nim) > 30 || !preg_match('/^[A-Za-z0-9-]+$/', $nim)) {
            throw new InvalidArgumentException('NIM wajib diisi (huruf/angka/tanda hubung, maks. 30 karakter).');
        }
        return $nim;
    }

    private static function validasiNama(string $nama): string { return self::wajib($nama, 'Nama', 100); }
    private static function validasiFakultas(string $nilai): string { return self::wajib($nilai, 'Fakultas', 100); }
    private static function validasiProdi(string $nilai): string { return self::wajib($nilai, 'Program studi', 100); }
    private static function validasiAsalSekolah(string $nilai): string { return self::wajib($nilai, 'Asal sekolah', 120); }

    private static function validasiSemester(int $semester): int
    {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('Semester harus antara 1 dan 14.');
        }
        return $semester;
    }

    private static function validasiHobi(string $hobi): string
    {
        $hobi = trim($hobi);
        if (strlen($hobi) > 120) {
            throw new InvalidArgumentException('Hobi maksimal 120 karakter.');
        }
        return $hobi;
    }

    private static function validasiAgama(string $agama): string
    {
        if (!in_array($agama, self::AGAMA, true)) {
            throw new InvalidArgumentException('Pilihan agama tidak valid.');
        }
        return $agama;
    }

    private static function wajib(string $nilai, string $label, int $maks): string
    {
        $nilai = trim($nilai);
        if ($nilai === '' || strlen($nilai) > $maks) {
            throw new InvalidArgumentException("{$label} wajib diisi dan maksimal {$maks} byte.");
        }
        return $nilai;
    }
}
