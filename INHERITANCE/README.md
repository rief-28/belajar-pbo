# Data Mahasiswa

Aplikasi web sederhana untuk mengelola data mahasiswa. Dibuat untuk tugas PBO Pertemuan 06 dengan topik Inheritance dan CRUD berbasis PHP Session.

## Fitur

- Tambah data mahasiswa
- Ubah data mahasiswa
- Hapus data mahasiswa
- Tampil daftar mahasiswa beserta biaya akhir
- Export data ke file CSV
- Validasi input di sisi server

## Teknologi

- PHP 8.1 atau lebih baru
- HTML dan CSS murni
- PHP Session sebagai penyimpanan sementara

## Struktur File

```
.
├── index.php               Halaman utama, form, tabel, dan export CSV
├── DataMahasiswa.php       Class pengelola data (tambah, cari, ubah, hapus)
├── Mahasiswa.php           Abstract class induk
├── MahasiswaReguler.php    Class turunan untuk mahasiswa reguler
├── MahasiswaBeasiswa.php   Class turunan untuk mahasiswa beasiswa
├── README.md
└── test.php                Pengujian otomatis 12 skenario modul
```

## Pengujian

Buka `http://localhost/nama-folder/test.php` untuk menjalankan 12 skenario pengujian modul. Skenario juga dapat diuji manual lewat form di `index.php`. Validasi HTML bawaan browser dimatikan agar pesan validasi server tampil.

## Konsep PBO

- Abstraction: class Mahasiswa bersifat abstract dengan method hitungBiaya() dan getJenis().
- Inheritance: MahasiswaReguler dan MahasiswaBeasiswa mewarisi class Mahasiswa.
- Encapsulation: atribut bersifat private dan diakses lewat getter.
- Polymorphism: hitungBiaya() menghasilkan nilai berbeda di setiap class turunan.

## Aturan Biaya

- Reguler: biaya akhir sama dengan UKT dasar.
- Beasiswa: biaya akhir adalah UKT dasar dikurangi potongan persen.

Contoh: UKT 5.000.000 dengan potongan 50 persen menghasilkan biaya akhir Rp 2.500.000.

## Aturan Validasi

- NIM, nama, dan program studi wajib diisi.
- Semester harus bernilai 1 sampai 14.
- UKT tidak boleh negatif.
- Potongan beasiswa harus bernilai 0 sampai 100.
- NIM tidak boleh sama dengan data yang sudah ada.

## Cara Menjalankan

### Dengan XAMPP

1. Salin semua file ke folder `C:\xampp\htdocs\nama-folder`.
2. Jalankan Apache dari XAMPP Control Panel.
3. Buka `http://localhost/nama-folder/index.php` di browser.


## Catatan

- Data disimpan di PHP Session, bukan database. Data hilang saat session berakhir.
- Jika muncul error "Failed to decode session object" setelah mengubah struktur class, hapus cookie PHPSESSID di browser.
- Pastikan semua file memakai tanda kutip lurus saat disalin dari sumber lain.