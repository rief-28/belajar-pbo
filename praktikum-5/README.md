# Praktikum PBO Pertemuan 5 — Enkapsulasi PHP

## Tujuan
Menerapkan **private property**, **getter**, **method perubahan tervalidasi**, dan **repository** pada CRUD Mahasiswa. Ini melanjutkan aplikasi Pertemuan 4 (tanpa database, data disimpan dalam PHP Session).

## Struktur file
- `Mahasiswa.php` — model: delapan property private, getter, setter per atribut (kecuali NIM), constructor, dan `ubahProfil()`.
- `DataMahasiswa.php` — repository: tambah, semua, cari, ubah, hapus; array diindeks oleh NIM.
- `index.php` — form Tambah/Edit, tabel, tombol Hapus, session, pesan kesalahan.
- `tests/test_model.php` — pengujian model dan repository dari command line.
- `tests/test_skenario.php` — 10 skenario uji wajib modul, hasil berupa tabel.
- `contoh/contoh1.php` sampai `contoh/contoh5.php` — contoh bertahap bagian E modul.
- `tugas/tugas1_produk.php` — Tugas 1: public, protected, private, dan class turunan.
- `tugas/tugas2_buku.php` — Tugas 2: class Buku dengan getter, setter, dan validasi.
- `laporan/jawaban_tugas4.txt` — jawaban refleksi Tugas 4.

## Menjalankan dengan XAMPP
1. Salin folder `praktikum-5` ke `C:\xampp\htdocs\`.
2. Jalankan **Apache** melalui XAMPP Control Panel.
3. Buka `http://localhost/praktikum-5/index.php`.
4. Tambahkan data, edit nama/semester, hapus, lalu refresh browser.
5. Contoh dan tugas: buka `http://localhost/praktikum-5/contoh/contoh1.php`, `.../tugas/tugas1_produk.php`, dan seterusnya.
6. Tes CLI (jika PHP ada di PATH): `php tests/test_model.php` dan `php tests/test_skenario.php`.

## Penjelasan bagian penting
1. **`private`** di `Mahasiswa.php`: kode luar tidak boleh membaca/mengubah `$m->nama` secara langsung.
2. **Getter** (`getNama()`, `getNim()`, dll.): akses baca resmi dari luar class.
3. **`ubahProfil()`**: menerima nilai baru, memvalidasi **seluruhnya terlebih dahulu**, baru memperbarui property. Dengan begitu, input tidak valid tidak menyebabkan perubahan sebagian.
4. **NIM tidak memiliki setter**: identitas mahasiswa dianggap tetap. Dalam mode Edit, NIM ditampilkan readonly dan dikirim juga lewat field tersembunyi `nim_lama`. Pada aplikasi nyata, hidden field bukan mekanisme otorisasi; data tetap harus diperiksa di server.
4a. **Setter per atribut**: `setNama()`, `setFakultas()`, `setProdi()`, `setAsalSekolah()`, `setSemester()`, `setHobi()`, `setAgama()`. Semua memakai method validasi private yang sama dengan constructor dan `ubahProfil()`, sehingga aturan tidak ditulis dua kali.
5. **`DataMahasiswa`**: `$data` private; method `tambah()` memanggil `$mahasiswa->getNim()`, bukan `$mahasiswa->nim`.
6. **`require_once` sebelum `session_start()`**: class tersedia ketika PHP memulihkan object dari session; mencegah `__PHP_Incomplete_Class`.
7. **Session**: penyimpanan hanya untuk latihan, bukan database permanen. Data hilang ketika session berakhir/dibersihkan.
8. **`htmlspecialchars()`** dalam `aman()` mencegah input pengguna ditafsirkan sebagai HTML ketika ditampilkan.
9. **Token CSRF dan POST-Redirect-GET**: perlindungan dasar dari permintaan lintas situs dan duplikasi form saat refresh.
10. **`filter_var(..., FILTER_VALIDATE_INT)`**: semester harus bilangan bulat, bukan sekadar hasil casting dari teks bebas.

## Alur CRUD
- **Create:** POST `aksi=tambah` → validasi → `new Mahasiswa(...)` → `DataMahasiswa::tambah()` → simpan ke session.
- **Read:** `DataMahasiswa::semua()` → loop tabel → getter.
- **Update:** klik Edit → `DataMahasiswa::cari(NIM)` → form terisi → POST `aksi=ubah` → `cari(NIM)` mengambil object lama → `ubahProfil()` memvalidasi dan mengubah → `DataMahasiswa::ubah()`.
- **Delete:** POST `aksi=hapus` → `DataMahasiswa::hapus(NIM)` → session diperbarui.
