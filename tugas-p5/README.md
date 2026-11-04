# Praktikum PBO - Pertemuan 05: Enkapsulasi

Repositori ini berisi kode hasil pengerjaan Praktikum Pemrograman Berorientasi Objek (PBO) Pertemuan 05. Fokus utama pada modul ini adalah mengimplementasikan **Enkapsulasi** pada sistem CRUD Data Mahasiswa menggunakan PHP 8+. Antarmuka aplikasi telah disesuaikan agar tampil lebih modern dan responsif.

## Fitur Utama

1. **Class `Mahasiswa` dengan Enkapsulasi Penuh**:
   - Seluruh properti dikonversi menjadi `private`.
   - Akses data menggunakan metode `getter`.
   - Perubahan data dikontrol melalui metode `setter` dengan validasi ketat (contoh: `setSemester()` hanya menerima angka 1-14).
   - Properti kunci (`$nim`) dirancang *read-only* (tanpa `setter`) untuk mencegah perubahan identitas mahasiswa.

2. **Sistem CRUD & Fungsionalitas Ekstra**:
   - **Create**: Menambah data mahasiswa baru.
   - **Read**: Menampilkan data mahasiswa dalam bentuk tabel dinamis.
   - **Update**: Mengubah data mahasiswa yang sudah ada (kolom NIM akan terkunci/readonly saat proses edit berlangsung).
   - **Delete**: Menghapus data mahasiswa.
   - **Export CSV**: Mengunduh seluruh data mahasiswa yang ada di tabel ke dalam format `.csv`.

3. **Antarmuka Web Modern (`index.php`)**:
   - Desain UI yang bersih dan responsif menggunakan font Inter dan tata letak CSS Grid.
   - Form input disusun rapi dalam kotak bergaya kartu (*card*).
   - Menampilkan alert error/sukses secara real-time.
   - Tabel otomatis merender *empty state* jika data belum ada.
   - Penyimpanan data sementara beroperasi murni menggunakan PHP Session.

## Cara Menjalankan Aplikasi

1. Pastikan lingkungan pengembangan lokal Anda (XAMPP/MAMP/Laragon) mendukung minimal **PHP 8.0**.
2. Unduh atau *clone* repositori ini ke direktori *root* server lokal Anda (contoh: `C:/xampp/htdocs/tugas-p5`).
3. Aktifkan modul Apache melalui panel kontrol aplikasi *local server* Anda.
4. Buka peramban web (browser) dan akses URL: `http://localhost/tugas-p5/index.php`

## Struktur File

- `Mahasiswa.php`: Berisi definisi class Mahasiswa beserta aturan enkapsulasinya (Constructor, Getter, Setter, dan Validasi).
- `index.php`: Antarmuka pengguna (UI) utama dan pengendali logika sistem (termasuk fitur Export CSV).
- `README.md`: Dokumentasi repositori proyek.