## PBO PERTEMUAN 4 — PHP 8+ / XAMPP
1. Ekstrak folder praktikum-04 ke C:\xampp\htdocs\
2. Jalankan Apache dari XAMPP Control Panel.
3. Buka localhost/praktikum-04/index.php
4. Tambah, tampil, edit, hapus, dan export data mahasiswa.
5. Buka localhost/praktikum-04/demo_konsep.php untuk menguji constructor, overloading property/method, dan destructor.

## BERKAS
### Mahasiswa.php		= class entity; constructor, destructor, magic methods
### DataMahasiswa.php	= class repository dengan method CRUD dan array
### index.php		= form HTML, controller, session, tabel
### demo_konsep.php		= demonstrasi magic methods dan siklus hidup object
### Export.php		= fitur export data Mahasiswa

## CATATAN:
- PHP Session menyimpan object. require_once class HARUS sebelum session_start().
- Jangan gabungkan session praktikum lama yang class-nya berbeda.
- Destructor tidak menghapus data CRUD. Demo menulis ke error log.
- PHP overloading bukan overload berdasarkan signature seperti Java/C++.
- __get/__set hanya contoh untuk property dinamis; atribut inti dideklarasikan eksplisit.
- Penggunaan PHP Session adalah untuk pembelajaran, bukan penyimpanan permanen.
- Fitur yang ditambahkan yaitu fitur Export data Mahasiswa.
