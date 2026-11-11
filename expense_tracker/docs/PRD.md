# Project Requirements Document (PRD): Expense Tracker

| Field | Value |
|---|---|
| Status | Draft |
| Author | Muhammad Arief P.M |
| Version | 0.2 |

## 1. Overview

Expense Tracker adalah aplikasi web/mobile untuk membantu pengguna mencatat, melihat, mengedit, dan menghapus pengeluaran harian. Pengguna dapat mengelompokkan pengeluaran berdasarkan kategori dan melihat ringkasan total pengeluaran. Aplikasi ini juga ditujukan sebagai pemenuhan studi kasus implementasi Pemrograman Berorientasi Objek (OOP).

### Problem Statement

Pengguna kesulitan melacak ke mana uang mereka digunakan karena belum memiliki pencatatan pengeluaran yang terstruktur.

### Goals

- Pengguna dapat mencatat satu pengeluaran dalam waktu kurang dari 10 detik.
- Pengguna dapat melihat total pengeluaran per bulan.
- Data pengeluaran tersimpan secara persisten dan tidak hilang saat aplikasi ditutup.
- Mengimplementasikan kaidah Pemrograman Berorientasi Objek (OOP) secara ketat sesuai standar studi kasus.

### Out of Scope untuk Versi 1

- Multi-user dan login.
- Integrasi bank atau e-wallet.
- Ekspor data ke PDF atau Excel.
- Pengaturan anggaran dan notifikasi peringatan.
- Multi-currency.
- Sinkronisasi offline antarperangkat.

### Success Metrics

| Metric | Target |
|---|---|
| Waktu untuk menambah satu entry | Kurang dari 10 detik |
| Waktu memuat daftar pengeluaran | Kurang dari 1 detik |
| Kehilangan data | 0% (zero data loss 100%) |

## 2. Requirements

### 2.1 User Stories

| ID | User Story |
|---|---|
| US-1 | Sebagai pengguna, saya ingin menambah pengeluaran berisi nama, nominal, kategori, dan tanggal agar dapat mencatat belanja hari ini. |
| US-2 | Sebagai pengguna, saya ingin melihat daftar semua pengeluaran agar mengetahui ke mana uang saya digunakan. |
| US-3 | Sebagai pengguna, saya ingin mengedit pengeluaran yang salah input agar data tetap akurat. |
| US-4 | Sebagai pengguna, saya ingin menghapus pengeluaran yang tidak valid agar daftar tetap bersih. |
| US-5 | Sebagai pengguna, saya ingin mencari atau menyaring pengeluaran berdasarkan kategori atau bulan agar mudah menemukan data tertentu. |
| US-6 | Sebagai pengguna, saya ingin menambah atau menghapus kategori tertentu. |

### 2.2 Functional Requirements

| ID | Requirement | Priority | Acceptance Criteria |
|---|---|---|---|
| FR-1 | Pengguna dapat membuat entry pengeluaran baru. | Must | Pengguna mengisi form dan menekan Save. Entry baru muncul di daftar. |
| FR-2 | Pengguna dapat melihat semua entry dalam bentuk tabel atau daftar. | Must | Setiap baris menampilkan nama, nominal, kategori, dan tanggal. |
| FR-3 | Pengguna dapat mengedit entry yang sudah ada. | Must | Pengguna memilih Edit. Form menampilkan data lama. Setelah disimpan, data terbarui. |
| FR-4 | Pengguna dapat menghapus entry. | Must | Pengguna memilih Hapus dan mengonfirmasi tindakan. Entry hilang dari daftar. |
| FR-5 | Aplikasi menampilkan total pengeluaran keseluruhan dan per bulan. | Must | Total diperbarui otomatis setiap ada operasi CRUD. |
| FR-6 | Pengguna dapat memfilter daftar berdasarkan kategori. | Should | Pengguna memilih kategori melalui dropdown dan daftar menampilkan hasil yang sesuai. |
| FR-7 | Pengguna dapat mencari berdasarkan nama pengeluaran. | Should | Pengguna memasukkan teks dan daftar terfilter secara real-time. |
| FR-8 | Aplikasi dapat mengurutkan pengeluaran berdasarkan tanggal. | Should | Daftar menggunakan urutan tanggal terbaru terlebih dahulu (descending). |
| FR-8 | Pengguna dapat menambah atau menghapus kategori. | Should | Pengguna melakukan penambahan pengurangan kategori. |

### 2.3 Non-Functional & Technical Requirements

| ID | Area | Requirement |
|---|---|---|
| NFR-1 | Performance | Daftar pengeluaran harus dirender dalam waktu kurang dari 1 detik untuk 500 entry. |
| NFR-2 | Data persistence | Data tidak hilang saat aplikasi di-refresh atau di-restart. |
| NFR-3 | UI | Antarmuka responsive dengan pendekatan mobile-first. |
| NFR-4 | Validation | Nominal harus lebih dari 0 dan nama pengeluaran tidak boleh kosong. |
| **TR-1** | **Programming Paradigm** | **Sistem harus dibangun sepenuhnya menggunakan paradigma Pemrograman Berorientasi Objek (OOP).** |
| **TR-2** | **Language** | **Kode program wajib disusun dalam bahasa PHP.** |

### 2.4 Input Fields per Expense

| Field | Type | Required | Example |
|---|---|---|---|
| Nama | Text | Ya | Makan siang |
| Nominal | Number (Rp) | Ya | 25000 |
| Kategori | Enum/Select | Ya | Makanan, Transportasi, Belanja, Lainnya(user isi keterangan) |
| Tanggal | Date | Ya | 2026-11-09 |
| Catatan | Text | Tidak | Bersama teman |

## 3. Core Features

### 3.1 Expense Management (CRUD)

- Create: menambahkan pengeluaran dengan nama, nominal, kategori, tanggal, dan catatan opsional.
- Read: menampilkan seluruh pengeluaran dalam tabel atau daftar.
- Update: mengubah data pengeluaran yang sudah tersimpan.
- Delete: menghapus pengeluaran setelah pengguna melakukan konfirmasi.

### 3.2 Expense Summary

- Menampilkan total seluruh pengeluaran.
- Menampilkan total pengeluaran per bulan.
- Memperbarui total secara otomatis setelah data dibuat, diubah, atau dihapus.

### 3.3 Search, Filter, and Sort

- Memfilter pengeluaran berdasarkan kategori atau waktu.
- Mencari pengeluaran berdasarkan nama secara real-time.
- Mengurutkan daftar berdasarkan tanggal, dengan data terbaru sebagai urutan default.

## 4. User Flow

### 4.1 Menambahkan Pengeluaran

1. Pengguna membuka halaman daftar pengeluaran.
2. Pengguna memilih tombol atau menu untuk menambah pengeluaran.
3. Aplikasi menampilkan form pengeluaran.
4. Pengguna mengisi nama, nominal, kategori, dan tanggal. Pengguna dapat mengisi catatan sebagai informasi opsional.
5. Aplikasi memvalidasi nama agar tidak kosong dan nominal agar lebih dari 0.
6. Pengguna menekan Save.
7. Aplikasi menyimpan entry dan menampilkannya pada daftar.
8. Aplikasi memperbarui ringkasan total pengeluaran.

### 4.2 Melihat Pengeluaran

1. Pengguna membuka halaman daftar pengeluaran.
2. Aplikasi menampilkan entry dalam bentuk tabel atau daftar.
3. Setiap entry menampilkan nama, nominal, kategori, dan tanggal.
4. Aplikasi menampilkan ringkasan total pengeluaran keseluruhan dan per bulan.

## 5. Architecture & Code Implementation

### 5.1 Gambaran Arsitektur Konseptual

1. **Frontend:** Menyediakan halaman antarmuka pengguna berbasis HTML/CSS/JS.
2. **Backend:** Menangani operasi CRUD, validasi data, pencarian/filter, pengurutan, serta perhitungan total pengeluaran melalui bahasa pemrograman **PHP**.
3. **Database:** Menyimpan data expense secara persisten.

### 5.2 Ketentuan Studi Kasus Kode (Wajib)

Mengacu pada syarat pengembangan aplikasi kecil, struktur kode (Backend) harus mengikuti aturan berikut:

1. **Bahasa Pemrograman:** Seluruh kode sistem wajib disusun dalam bahasa **PHP**.
2. **Kelas & Atribut:** Aplikasi memiliki setidaknya **1 kelas induk** (parent class) dan **1 kelas anak** (turunan).
3. **Kapasitas Kelas:** Tiap kelas (baik induk maupun anak) wajib memiliki **minimal 5 atribut** dan **minimal 4 method** (behavior atau function).
4. **Instansiasi:** Terdapat penerapan instansiasi objek (perubahan dari cetakan kelas menjadi objek nyata di dalam kode).
5. **Enkapsulasi:** Terdapat penerapan *encapsulation* (penggunaan _access modifier_ seperti `private`, `protected`, dan `public` untuk menyembunyikan atau melindungi atribut/method).
6. **Inheritance:** Terdapat penerapan *inheritance* (pewarisan sifat dan method dari kelas induk ke kelas anak dengan kata kunci `extends`).

### 5.3 Batasan Versi 1

- Fitur autentikasi tidak termasuk dalam versi 1.
- Aplikasi belum mendukung multi-user.
- Integrasi bank/e-wallet dan sinkronisasi offline antarperangkat tidak termasuk dalam cakupan.

## 6. Database Schema

### 6.1 Entity: Expense

| Column | Data Type | Constraint / Description |
|---|---|---|
| id | UUID atau Auto-increment | Primary identifier untuk setiap expense. |
| name | String | Maksimal 100 karakter. Wajib diisi. |
| amount | Decimal | Nominal pengeluaran. Wajib lebih dari 0. |
| category | Enum | Salah satu dari `makanan`, `transportasi`, `belanja`, atau `lainnya`. |
| type | String | Digunakan jika menerapkan Inheritance (contoh: Cash/Credit). |
| date | Date | Tanggal pengeluaran. Wajib diisi. |
| note | String | Opsional (nullable), maksimal 255 karakter. |
| created_at | DateTime | Waktu data dibuat. |
| updated_at | DateTime | Waktu data terakhir diperbarui. |

## 7. Tech Stack

Pilihan teknologi berikut telah disesuaikan dengan syarat wajib studi kasus.

| Layer | Options |
|---|---|
| Frontend | HTML, CSS, JavaScript (Bisa dipadukan dengan framework CSS seperti Bootstrap atau Tailwind) |
| Backend | **PHP (Native OOP atau framework berbasis PHP ringan)** |
| Database | MySQL, SQLite, atau PostgreSQL |
| Authentication | Tidak digunakan pada versi ini karena berada di luar scope |