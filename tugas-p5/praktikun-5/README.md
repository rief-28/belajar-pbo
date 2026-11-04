# Praktikum PBO Pertemuan 5 — Enkapsulasi PHP

## Tujuan
Menerapkan **private property**, **getter**, **method perubahan tervalidasi**, dan **repository** pada CRUD Mahasiswa. Ini melanjutkan aplikasi Pertemuan 4 (tanpa database, data disimpan dalam PHP Session).

## Struktur file
- `Mahasiswa.php` — model: delapan property private, getter, constructor, dan `ubahProfil()`.
- `DataMahasiswa.php` — repository: tambah, semua, cari, ubah, hapus; array diindeks oleh NIM.
- `index.php` — form Tambah/Edit, tabel, tombol Hapus, session, pesan kesalahan.
- `tests/test_model.php` — pengujian model dan repository dari command line.

## Menjalankan dengan XAMPP
1. Salin folder `PBO05_CRUD_Enkapsulasi` ke `C:\xampp\htdocs\`.
2. Jalankan **Apache** melalui XAMPP Control Panel.
3. Buka `http://localhost/PBO05_CRUD_Enkapsulasi/index.php`.
4. Tambahkan data, edit nama/semester, hapus, lalu refresh browser.
5. Untuk tes CLI (jika PHP ada di PATH): `php tests/test_model.php`.

## Penjelasan bagian penting
1. **`private`** di `Mahasiswa.php`: kode luar tidak boleh membaca/mengubah `$m->nama` secara langsung.
2. **Getter** (`getNama()`, `getNim()`, dll.): akses baca resmi dari luar class.
3. **`ubahProfil()`**: menerima nilai baru, memvalidasi **seluruhnya terlebih dahulu**, baru memperbarui property. Dengan begitu, input tidak valid tidak menyebabkan perubahan sebagian.
4. **NIM tidak memiliki setter**: identitas mahasiswa dianggap tetap. Dalam mode Edit, NIM ditampilkan readonly dan dikirim juga lewat field tersembunyi `nim_lama`. Pada aplikasi nyata, hidden field bukan mekanisme otorisasi; data tetap harus diperiksa di server.
5. **`DataMahasiswa`**: `$data` private; method `tambah()` memanggil `$mahasiswa->getNim()`, bukan `$mahasiswa->nim`.
6. **`require_once` sebelum `session_start()`**: class tersedia ketika PHP memulihkan object dari session; mencegah `__PHP_Incomplete_Class`.
7. **Session**: penyimpanan hanya untuk latihan, bukan database permanen. Data hilang ketika session berakhir/dibersihkan.
8. **`htmlspecialchars()`** dalam `aman()` mencegah input pengguna ditafsirkan sebagai HTML ketika ditampilkan.
9. **Token CSRF dan POST-Redirect-GET**: perlindungan dasar dari permintaan lintas situs dan duplikasi form saat refresh.
10. **`filter_var(..., FILTER_VALIDATE_INT)`**: semester harus bilangan bulat, bukan sekadar hasil casting dari teks bebas.

## Alur CRUD
- **Create:** POST `aksi=tambah` → validasi → `new Mahasiswa(...)` → `DataMahasiswa::tambah()` → simpan ke session.
- **Read:** `DataMahasiswa::semua()` → loop tabel → getter.
- **Update:** klik Edit → `DataMahasiswa::cari(NIM)` → form terisi → POST `aksi=ubah` → model baru dengan NIM tetap → `DataMahasiswa::ubah()`.
- **Delete:** POST `aksi=hapus` → `DataMahasiswa::hapus(NIM)` → session diperbarui.

## Latihan mahasiswa
1. Tambahkan validasi nama minimal tiga karakter, lalu uji input dua karakter.
2. Tambahkan method `naikSemester()` pada model. Tolak jika sudah semester 14. Bandingkan dengan setter bebas.
3. Tambahkan `getRingkasan()` yang mengembalikan `NIM - Nama (Semester X)` dan tampilkan di tabel.
4. Jelaskan mengapa **tidak ada** `setNim()` dan mengapa `DataMahasiswa::$data` juga `private`.
5. Uji NIM duplikat, semester -1/15, nama kosong, agama di luar pilihan, dan refresh setelah Tambah.

## Catatan untuk dosen
Contoh ini fokus pada **enkapsulasi**, bukan overloading. Magic method `__get()`/`__set()` tidak digunakan karena getter dan method domain eksplisit lebih mudah dipahami. Untuk produksi, diperlukan database, autentikasi, otorisasi, dan pengelolaan session yang lebih lengkap.
