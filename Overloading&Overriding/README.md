# Latihan Pemrograman Berorientasi Objek (PBO) - PHP

Repository ini berisi kumpulan kode latihan konsep dasar **Pemrograman Berorientasi Objek (Object-Oriented Programming)** menggunakan bahasa pemrograman **PHP**.

## Daftar Materi & Contoh Kode

### 1. Method Overloading dengan Magic Method `__call()` (`overloading.php`)

PHP tidak mendukung *method overloading* secara tradisional (mendefinisikan beberapa method dengan nama yang sama tetapi berbeda jumlah/tipe parameter dalam satu class). Sebagai gantinya, PHP menyediakan *magic method* `__call()` yang otomatis dipanggil ketika mengakses method yang tidak tersedia atau tidak dapat diakses.

* **Class:** `Kalkulator`
* **Konsep:** Simulasi *Method Overloading* dinamis berdasarkan jumlah argumen (`2` atau `3` argumen pada method `tambah()`), serta penanganan error menggunakan `BadMethodCallException`.
* **Contoh Output:**
  ```text
  30
  60
  ```

### 2. Inheritance (Pewarisan) & Method Overriding (`overriding.php`)

Konsep pewarisan sifat dari *parent class* (induk) ke *child class* (turunan) menggunakan kata kunci `extends`, serta menimpa (*override*) perilaku method dari parent class agar sesuai dengan kebutuhan masing-masing child class.

* **Parent Class:** `Hewan`
* **Child Class:** `Kucing`, `Anjing`
* **Konsep:** Pewarisan (*Inheritance*) dan *Method Overriding* pada method `suara()`.
* **Contoh Output:**
  ```text
  Hewan mengeluarkan suara
  Meong!
  Guk guk!
  ```

## Cara Menjalankan Kode dengan XAMPP

Pastikan **PHP** (minimal versi 8.0 karena menggunakan tipe `mixed`) sudah terinstal atau gunakan **XAMPP**, dan simpan folder proyek ini di dalam direktori `C:\xampp\htdocs\belajar-pbo`.

### Opsi A: Melalui Browser (Apache)

1. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache**.
2. Buka browser dan akses URL berikut (sesuaikan jika file berada di dalam subfolder):
   * `http://localhost/belajar-pbo/overloading.php`
   * `http://localhost/belajar-pbo/overriding.php`

### Opsi B: Melalui Terminal / Git Bash (PHP CLI)

1. Buka terminal dan arahkan ke direktori proyek:
   ```bash
   cd /c/xampp/htdocs/belajar-pbo
   ```
2. Jalankan file PHP menggunakan executable PHP dari XAMPP:
   ```bash
   /c/xampp/php/php.exe overloading.php
   /c/xampp/php/php.exe overriding.php
   ```