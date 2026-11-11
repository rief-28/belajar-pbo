# Belajar PBO

Repositori ini berisi tugas dan catatan belajar mata kuliah Pemrograman Berbasis Objek (PBO). Setiap folder memuat kode program dari satu topik atau satu tugas.

## Tentang Repositori

- Penulis: Muhammad Arief P.M
- Program studi: Teknik Informatika, Universitas Muhammadiyah Sukabumi
- Isi: latihan kode, tugas kuliah, dan catatan pembelajaran PBO
- Tujuan: mendokumentasikan proses belajar dan menyimpan kode agar mudah dibuka kembali

## Materi yang Dipelajari

| Topik | Penjelasan singkat |
| --- | --- |
| Class dan Object | Membuat cetakan data dan membuat objek dari cetakan tersebut |
| Constructor | Mengisi nilai awal atribut saat objek dibuat |
| Encapsulation | Mengatur akses atribut dengan public, protected, dan private |
| Inheritance | Mewarisi atribut dan method dari class induk |

## Struktur Folder

```
belajar-pbo/
├── oop/               Class dan Objek
├── praktikum-04/      Konstruktor, destruktor dan Overloading
├── praktikum-5/       Enkapsulasi
├── INHERITANCE/       Inheritance dan CRUD dengan PHP Session
├── expense_tracker/   Aplikasi pencatat pengeluaran (PHP OOP, SQLite)
└── README.md
```

Nama folder di atas memakai huruf besar dan kecil yang sama dengan isi repositori. Ketik persis seperti itu di alamat browser.

## Cara Menjalankan

Kebutuhan:

- XAMPP dengan PHP 8.1 atau lebih baru
- Git

Langkah:

1. Buka XAMPP Control Panel. Klik Start pada Apache. MySQL tidak perlu dinyalakan karena tidak ada tugas yang memakainya.

2. Clone repositori ke folder htdocs.

```bash
cd C:\xampp\htdocs
git clone https://github.com/rief-28/belajar-pbo.git
```

3. Buka folder tugas di browser.

```
http://localhost/belajar-pbo/nama-folder/
```

Contoh: `http://localhost/belajar-pbo/expense_tracker/`

### Catatan untuk expense_tracker

- Memakai database SQLite, bukan MySQL. Berkas database dibuat otomatis saat halaman pertama dibuka.
- Butuh ekstensi PHP `pdo_sqlite` dan `mbstring`. Di XAMPP keduanya biasanya sudah aktif. Bila belum, halaman menampilkan pesan yang menyebut ekstensinya.
- Langkah lengkap, cara menjalankan dengan server bawaan PHP, dan cara menjalankan tes ada di [expense_tracker/README.md](expense_tracker/README.md).

## Daftar Tugas

| No | Tugas | Folder | Status |
| --- | --- | --- | --- |
| 1 | OOP | [oop](oop/) | Selesai |
| 2 | Konstruktor, destruktor dan Overloading | [praktikum-04](praktikum-04/) | Selesai |
| 3 | Enkapsulasi | [praktikum-5](praktikum-5/) | Selesai |
| 4 | Inheritance | [INHERITANCE](INHERITANCE/) | Selesai |
| 5 | Expense Tracker | [expense_tracker](expense_tracker/) | Selesai |

## Catatan

Kode di repositori ini dibuat untuk keperluan belajar. Saran dan masukan terbuka lewat Issues.
