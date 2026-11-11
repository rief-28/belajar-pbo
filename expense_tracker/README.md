# Expense Tracker (PHP OOP, SQLite)

Aplikasi pencatat pengeluaran. Bagian backend mengikuti `docs/PRD.md`.

## Menjalankan

### Di XAMPP (Apache)

1. Salin atau clone folder proyek ke `C:\xampp\htdocs\expense_tracker`.
2. Buka XAMPP Control Panel, lalu tekan Start pada Apache. MySQL tidak dipakai.
3. Buka http://localhost/expense_tracker/ di browser.

Database SQLite dibuat otomatis di `database/expense_tracker.sqlite` saat halaman pertama dibuka. Folder boleh bernama lain atau berada di subfolder, misalnya `http://localhost/belajar/expense_tracker/`.

Ekstensi `pdo_sqlite` dan `mbstring` biasanya sudah aktif di XAMPP. Bila belum, halaman menampilkan pesan yang menyebut ekstensinya. Klik Config pada baris Apache lalu pilih PHP (php.ini), hapus tanda `;` di depan `extension=pdo_sqlite` dan `extension=mbstring`, simpan, lalu Stop dan Start Apache.

`.htaccess` memakai `AllowOverride`, yang bernilai All secara bawaan untuk htdocs di XAMPP. Bila Apache menampilkan galat 500 sebelum aplikasi muncul, periksa pengaturan itu di `httpd.conf`.

### Di server bawaan PHP (php -S)

Syarat: PHP 8.1 atau lebih baru dengan ekstensi `pdo_sqlite` dan `mbstring`.

Di Windows, kedua ekstensi ini sering belum aktif, terutama bila `php --ini` menampilkan `Loaded Configuration File: (none)` (belum ada php.ini). Gejalanya: halaman berisi "Ekstensi PHP belum aktif", atau log berisi `could not find driver`.

Cara paling mudah di VS Code: tekan Ctrl+Shift+B (task "Jalankan server"), atau ketik `.\serve.bat` di terminal. Skrip ini memeriksa dulu apakah php.ini sudah mengaktifkan kedua ekstensi. Bila belum, ekstensi dimuat lewat opsi baris perintah. Tes backend: `.\serve.bat test` (task "Jalankan tes"). Atau satu perintah di PowerShell:

```
php -d extension_dir="$(Split-Path (Get-Command php).Source)\ext" -d extension=pdo_sqlite -d extension=mbstring -S localhost:8000 router.php
```

Cara tetap: salin `php.ini-development` di folder PHP menjadi `php.ini`, buka, hapus tanda `;` di depan `extension_dir = "ext"`, `extension=pdo_sqlite`, dan `extension=mbstring`, lalu jalankan server seperti di bawah.

```
php -S localhost:8000 router.php
```

Buka http://localhost:8000. Database dibuat otomatis di `database/expense_tracker.sqlite`.
Pakai `router.php` agar file database dan source code tidak bisa diunduh lewat URL.
Di Apache, `.htaccess` sudah membatasi akses (belum diuji di Apache).

Variabel lingkungan:

| Nama | Fungsi | Bawaan |
|---|---|---|
| EXPENSE_DB_PATH | Lokasi file SQLite | database/expense_tracker.sqlite |
| APP_TIMEZONE | Zona waktu untuk "bulan ini" | Asia/Jakarta |

## Tes

```
php tests/run.php         # tes unit dan database (memori)
.\serve.bat test          # sama, untuk Windows tanpa php.ini
bash tests/http_smoke.sh  # tes HTTP end-to-end lewat php -S
```

## Route

| Metode | action | Fungsi | Berhasil |
|---|---|---|---|
| GET | index (bawaan) | Daftar, total, filter, cari, urut | 200 |
| GET | create | Form tambah | 200 |
| GET | edit&id= | Form edit | 200, 404 jika id tidak ada |
| POST | store | Simpan baru | 303 ke index, 422 jika input salah |
| POST | update&id= | Simpan perubahan | 303, 404, 422 |
| POST | delete&id= | Hapus (aman diulang) | 303 |
| POST | category_store | Tambah kategori (field `name`) | 303 |
| POST | category_delete | Hapus kategori (field `slug`) | 303 |
| GET | health | Cek database, JSON | 200 atau 503 |

Query filter pada index: `search`, `category`, `month` (YYYY-MM), `sort` (`asc` atau `desc`).
Semua POST wajib membawa `csrf_token`. Token juga dikirim di header respons `X-CSRF-Token` untuk fetch/XHR.
Kegagalan kategori (duplikat, dipakai, bawaan) tampil lewat flash message dengan tipe `danger`.

## Kontrak untuk view (frontend)

Semua view menerima `$csrfToken` dan `$flash` (`['type' => ..., 'message' => ...]` atau null).

| View | Variabel |
|---|---|
| expense/index | `$expenses` (array of array: id, name, amount, category, category_name, type, type_label, date, note, dan field khusus tipe), `$filter`, `$filteredTotal`, `$totalOverall`, `$totalThisMonth`, `$monthlyTotals` (month, total, count), `$categories` (slug, name, is_default, expense_count) |
| expense/create, expense/edit | `$expense` (array seperti di atas), `$errors` (field => pesan), `$categories`, `$types` |
| error | `$status`, `$message`, `$requestId` |

Field form: `type` (`CashExpense` atau `CreditExpense`), `name`, `amount`, `category`, `date`, `note`.
CashExpense menambah `cash_received` (opsional). CreditExpense menambah `card_name` (wajib), `installments` (1 sampai 60, bawaan 1), `interest_rate` (0 sampai 100, bawaan 0), `due_date` (opsional).

## Frontend

HTML, CSS, dan JavaScript murni tanpa framework. Mengikuti prototype (gaya, warna, font Plus Jakarta Sans), tetapi hanya memuat fitur yang ada di PRD. Anggaran, statistik, grafik, profil akun, paginasi, dan tombol nominal cepat di prototype tidak dibuat.

Halaman:
- Daftar (`index`): total bulan ini dan keseluruhan, total per bulan (klik untuk menyaring), cari nama real-time, filter kategori dan bulan, urut tanggal, tambah dan hapus kategori.
- Tambah dan edit: nominal dengan pemisah ribuan, pilihan kategori berbentuk kartu, cara bayar Tunai atau Kartu Kredit.
- Hapus selalu meminta konfirmasi lewat dialog.

Catatan:
- Mobile-first. Di layar kecil, tombol Tambah melayang di kanan bawah.
- Tanpa JavaScript, halaman tetap bisa dipakai (cari lewat tombol Terapkan), kecuali konfirmasi hapus yang butuh JavaScript.
- Font dimuat dari Google Fonts. Bila tidak ada internet, tampilan memakai font sistem.
- CSS dan JS dimuat lewat alamat relatif (`public/...`), jadi aplikasi bisa berjalan di akar domain maupun di subfolder, misalnya `http://localhost/expense_tracker/` di Apache XAMPP.

## Aturan validasi

- Nama wajib, maksimal 100 karakter. Nominal bilangan bulat Rupiah, lebih dari 0.
- Tanggal harus nyata (2026-02-30 ditolak). Catatan maksimal 255 karakter.
- Kategori "Lainnya" wajib mengisi catatan (PRD 2.4: user isi keterangan).
- Kategori harus ada di database. Empat kategori bawaan tidak bisa dihapus. Kategori yang masih dipakai tidak bisa dihapus.
- Aturan inti juga dijaga di database (CHECK, FOREIGN KEY, trigger), bukan hanya di PHP.

## Pemetaan ke PRD 5.2 (OOP)

| Ketentuan | Implementasi |
|---|---|
| Kelas induk dan anak | `Expense` (abstrak) dengan anak `CashExpense` dan `CreditExpense` |
| Atribut dan method | Induk: 9 atribut, 24 method. Anak mewarisi atribut induk, lalu menambah milik sendiri (Cash: 1 atribut dan 8 method sendiri. Credit: 4 atribut dan 13 method sendiri). Total termasuk warisan: Cash 10 atribut dan 26 method, Credit 13 atribut dan 31 method. |
| Instansiasi | `ExpenseFactory` membuat objek dari form atau baris database |
| Enkapsulasi | Atribut `protected` dan `private`, akses lewat getter |
| Inheritance | `extends Expense`, method abstrak diimplementasi anak (`getType`, `validateSpecific`, `specificRow`, `specificArray`) |

## Struktur

```
index.php            front controller dan routing
bootstrap.php        autoload, zona waktu, penanganan galat
router.php           router untuk php -S
config/Database.php  koneksi SQLite dan migrasi
database/schema.sql  skema
src/Models           Expense, CashExpense, CreditExpense, ExpenseFactory, ExpenseFilter
src/Repositories     ExpenseRepository, CategoryRepository
src/Controllers      ExpenseController
src/Support          Logger, Session, View, Format (helper tampilan)
views/expense        index, create, edit, _form (bagian bersama form)
views/layouts        header, footer
views/error.php      halaman galat
public/css, public/js  style.css dan app.js
tests/               tes
```

## Operasional

- Log: satu baris JSON per kejadian ke `error_log` (stderr pada `php -S`), dengan `request_id` yang sama dengan header `X-Request-Id`. Setiap request mencatat method, action, status, dan durasi.
- Galat tak terduga: pengguna hanya melihat pesan umum dan kode request. Detail ada di log.
- Database terkunci lebih dari 5 detik menghasilkan 503 dengan `Retry-After`.
- Backup: salin `database/expense_tracker.sqlite` (dan file `-wal` jika ada) saat aplikasi berhenti, atau pakai `sqlite3 .backup`.

## Batasan versi 1

- Tanpa login dan satu pengguna (sesuai PRD). Jangan buka ke jaringan publik.
- Edit memakai last-write-wins.
- Klik Simpan dua kali membuat dua baris. Nonaktifkan tombol saat form dikirim.
- Daftar belum memakai paginasi (cukup untuk target 500 entry).
