# Polimorfisme dalam Pemrograman Berorientasi Objek
 
Repositori ini berisi tiga contoh polimorfisme dalam **PHP**, **C++**, dan **Java**. Repositori ini juga berisi satu aplikasi CRUD data mahasiswa berbasis **PHP OOP** yang menerapkan polimorfisme pada perhitungan UKT.
 
## Apa Itu Polimorfisme
 
Polimorfisme membuat satu pemanggilan method menghasilkan perilaku yang berbeda. Perilaku itu mengikuti kelas dari objek yang dipanggil. Kode pemanggil tidak perlu tahu kelas turunan yang dipakai.
 
Pada ketiga contoh, kelas induk `Hewan` punya method `suara()`. Kelas turunan `Kucing`, `Anjing`, dan `Ayam` meng-*override* method itu dengan suara masing-masing. Fungsi `bunyikan()` menerima objek bertipe `Hewan` dan memanggil `suara()`. Hasilnya mengikuti jenis objek yang dikirim.
 
| Kelas | Hasil `suara()` |
|---|---|
| `Hewan` | Suara hewan |
| `Kucing` | Meong! |
| `Anjing` | Guk guk! |
| `Ayam` | Kukuruyuk! |
 
## Struktur Folder
 
```text
Polimorfisme/
├── ex1.php              # Contoh polimorfisme dengan PHP
├── ex2.cpp              # Contoh polimorfisme dengan C++
├── ex3.java             # Contoh polimorfisme dengan Java
├── README.md
└── P9_CRUD_Array/       # Aplikasi CRUD data mahasiswa (PHP OOP)
    ├── index.php
    ├── Mahasiswa.php
    ├── DataMahasiswa.php
    ├── uji_oop.php
    └── README.md
```
 
## Contoh 1 sampai 3: Polimorfisme pada Hewan
 
Ketiga contoh membuat tiga objek (`Kucing`, `Anjing`, `Ayam`) lalu mengirim masing-masing ke fungsi `bunyikan()`.
 
Keluaran ketiga contoh sama:
 
```text
Meong!
Guk guk!
Kukuruyuk!
```
 
Di PHP, setiap baris diakhiri tag `<br>` agar turun baris di browser.
 
### Perbedaan Penerapan
 
| Aspek | PHP (`ex1.php`) | C++ (`ex2.cpp`) | Java (`ex3.java`) |
|---|---|---|---|
| Pewarisan | `extends` | `: public` | `extends` |
| Override method | Nama method sama, tanpa kata kunci khusus | `virtual` di induk, `override` di turunan | Anotasi `@Override` |
| Parameter `bunyikan()` | `Hewan $objek` | `Hewan &objek` (referensi) | `Hewan objek` |
| Penyimpanan objek | Array `$daftarHewan` | Tiga variabel terpisah | Array `Hewan[]` |
| Cetak keluaran | `echo` | `cout` | `System.out.println` |
| Destruktor induk | Tidak perlu | `virtual ~Hewan() = default` | Tidak perlu |
 
## Cara Menjalankan
 
### PHP (`ex1.php`)
 
Butuh PHP 8.0 atau lebih baru.
 
```bash
php ex1.php
```
 
### C++ (`ex2.cpp`)
 
Butuh `g++`.
 
```bash
g++ ex2.cpp -o ex2
./ex2
```
 
### Java (`ex3.java`)
 
Butuh JDK. Kelas publik di file ini bernama `Main`, jadi ganti nama file menjadi `Main.java`.
 
```bash
javac Main.java
java Main
```
 
## P9_CRUD_Array: Aplikasi CRUD Data Mahasiswa
 
Aplikasi web PHP native untuk menambah, melihat, mengubah, dan menghapus data mahasiswa. Data disimpan di array dan session PHP, tanpa database.
 
Polimorfisme muncul pada perhitungan UKT:
 
| Kelas | `jenis()` | `hitungUKT()` |
|---|---|---|
| `Mahasiswa` (induk) | `reguler` | Rp 5.000.000 |
| `MahasiswaReguler` | `reguler` | Rp 5.000.000 (diwarisi tanpa perubahan) |
| `MahasiswaBeasiswa` | `beasiswa` | Rp 3.000.000 (potongan 40%) |
 
Fungsi `tampilUKT(Mahasiswa $m)` menerima objek `Mahasiswa` apa pun. Fungsi ini menampilkan UKT sesuai jenis objeknya, tanpa pengecekan jenis di dalam fungsi.
 
Cara menjalankan:
 
```bash
cd P9_CRUD_Array
php -S localhost:8000
```
 
Buka `http://localhost:8000/index.php` di browser. Penjelasan lengkap ada di [P9_CRUD_Array/README.md](P9_CRUD_Array/README.md).
 