# Aplikasi CRUD Data Mahasiswa (PHP OOP)

Aplikasi web sederhana berbasis PHP native untuk mengelola data mahasiswa (Tambah, Lihat, Ubah, Hapus) dengan menerapkan konsep **Pemrograman Berorientasi Objek (OOP)**. Aplikasi ini menggunakan **Array** dan **PHP Session** sebagai media penyimpanan sementara tanpa memerlukan database eksternal.

## Fitur Utama

* **Manajemen Data (CRUD):** Menambah, menampilkan, memperbarui, dan menghapus data mahasiswa.
* **Validasi Input:** Memastikan NIM, Nama, dan Prodi tidak kosong, serta mencegah duplikasi NIM.
* **Perhitungan UKT Otomatis:** Menghitung nominal UKT secara dinamis berdasarkan status mahasiswa (Reguler atau Beasiswa dengan potongan 40%).
* **Keamanan Dasar:** Dilengkapi token anti-CSRF pada setiap form dan sanitasi output (`htmlspecialchars`) untuk mencegah XSS.

## Konsep OOP yang Diterapkan

1. **Encapsulation:** Penggunaan *access modifier* (`private` dan `protected`) pada properti kelas `Mahasiswa` dan `DataMahasiswa` serta penggunaan *getter/setter*.
2. **Inheritance (Pewarisan):** Kelas `MahasiswaReguler` dan `MahasiswaBeasiswa` mewarisi sifat dan perilaku dari kelas induk `Mahasiswa`.
3. **Polymorphism:** Metode `hitungUKT()` dan `jenis()` di-*override* pada kelas `MahasiswaBeasiswa` untuk memberikan perilaku perhitungan biaya yang berbeda.
4. **Magic Methods:** Implementasi `__construct()`, `__destruct()`, dan `__call()` (untuk *method overloading* pada fungsi `tampil()`).

## Struktur File

* `Mahasiswa.php` — Definisi kelas induk `Mahasiswa`, kelas turunan (`MahasiswaReguler`, `MahasiswaBeasiswa`), serta fungsi helper.
* `DataMahasiswa.php` — Kelas *repository* untuk mengelola logika operasi CRUD dan validasi keunikan NIM.
* `index.php` — Antarmuka pengguna (UI) sekaligus pengendali alur request POST/GET dan manajemen session.
* `uji_oop.php` — Skrip pengujian mandiri untuk memverifikasi perilaku objek, pewarisan, dan siklus hidup (*destructor*).

## Cara Menjalankan

1. Pastikan **PHP** (minimal versi 8.0 karena menggunakan ekspresi `match` dan *union/mixed types*) sudah terinstal di komputer Anda.
2. Letakkan seluruh file dalam satu folder proyek (misalnya di dalam `htdocs` jika menggunakan XAMPP, atau folder kerja bebas).
3. Jalankan server lokal PHP melalui terminal di dalam folder proyek:
   ```bash
   php -S localhost:8000
   ```
4. Buka browser dan akses:
   * **Aplikasi Utama:** `http://localhost:8000/index.php`
   * **Pengujian OOP:** `http://localhost:8000/uji_oop.php`
