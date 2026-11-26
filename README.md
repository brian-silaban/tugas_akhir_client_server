# 🎒 JejakSumut — Jelajahi Pesona Sumatera Utara
**Repo Projek Akhir Kelompok Mata Kuliah Manajemen Database Client-Server (CodeIgniter 4 + PostgreSQL)**

> [!IMPORTANT]
> **KALIAN BACA DULU SEBENTAR INI WOK, SEDIKIT NYA INI BIAR PAHAM APA AJA ISINYA:**  
> Semua yang ada di repo ini (mulai dari nama web **JejakSumut**, isi tabel database wisata Sumatera Utara, desain halaman, sampai pembagian tugas) **BUKAN PATOKAN MATI**. Ini sengaja kubuat untuk **pondasi awal** aja biar kita gak mulai dari awal. Kalau ada yang kurang sreg atau mau diubah, langsung ingfokan ke grup (tapi kalo bisa kita bicarakan lewat discord aja biar enak).

---

## 🧩 Kenapa Ada 2 Folder (`backend/` & `frontend/`)?
Sesuai arahan dosen kita dan rubrik di [`Form_Evaluasi_Proyek_MDCS.docx.pdf`](Form_Evaluasi_Proyek_MDCS.docx.pdf) supaya **integrasi REST API Client-Server-nya kelihatan jelas**, projek ini sengaja dibagi jadi **2 folder CodeIgniter 4 terpisah**:

1. **Folder [`backend/`](backend/) (Server REST API + Database PostgreSQL — Jalan di Port `8080`)**:
   - Ini **mesin belakang (Server)** kita. Cuma dia yang boleh nyambung ke database **PostgreSQL**.
   - Dia **nggak punya tampilan web HTML**, kerjanya murni ngeluarin data **JSON REST API** wisata Sumatera Utara buat dipakai sama `frontend/` kita sendiri dan juga dipakai sama kelompok lain (Penyedia Tur Guide, Kuliner, Penginapan, Transportasi).
2. **Folder [`frontend/`](frontend/) (Client Web Tampilan JejakSumut — Jalan di Port `8081`)**:
   - Ini **tampilan web (Client)** yang dilihat pengunjung.
   - Dia **nggak punya database sama sekali**, jadi setiap kali mau nampilin tempat wisata Sumut, login, atau pesan tiket, dia wajib minta data lewat **REST API** ke `backend/`.

---

## 🖥️ Soal Landing Page yang Ada di `frontend/` Sekarang
Kalau kalian masuk ke folder `frontend` trus ketik `php spark serve --port 8081` di cmd/terminal, bakal muncul **Landing Page statis** (desain dari Google Stitch + sudah dirapikan pakai aturan Clean Code Bahasa Indonesia).
- **Kenapa dibuat?** Cuma buat *preview* / gambaran awal aja cam mana bentuk web wisata kita nanti, sekaligus ngetes biar waktu kalian baru *clone* repo ini, webnya langsung bisa dibuka di browser **tanpa harus pusing setting PostgreSQL dulu**.
- Desain yang sekarang masih bisa kita kembangkan atau ubah bareng-bareng sesuai hasil diskusi kelompok!

---

## ⚡ Cara Jalanin Projek di Laptop Masing-Masing

### A. Kalau Cuma Mau Nengok Tampilan Web (`frontend`) Dulu:
```bash
# 1. Clone repo ini (cukup sekali di awal, atau lewat VS Code langsung)
git clone <url-repo-github-kita>
cd tugas_akhir_client_server

# 2. Masuk ke folder frontend & install vendor kalau belum ada
cd frontend
cp env .env
composer install

# 3. Nyalakan server Frontend di port 8081
php spark serve --port 8081
```
Buka **`http://localhost:8081`** di browser. Langsung nongol Landing Page-nya!

### B. Cara Jalanin Server REST API (`backend`):
> 💡 **Info Penting:** Server Backend & Database PostgreSQL kita sudah jalan **online 24 jam di VM Azure** (`https://api-jejaksumut.vulnera.web.id`)! Jadi kalau mau nyoba dari laptop masing-masing atau ngetes kode baru:
Buka terminal baru, lalu jalankan:
```bash
cd backend
cp env .env
# (Buka .env, hapus tanda # di bagian database.default.* & minta password-nya di grup WA)
composer install
php spark serve --port 8080
```
Buka **`http://localhost:8080`** di browser (atau **`https://api-jejaksumut.vulnera.web.id`** untuk versi online VM Azure), nanti bakal muncul teks **JSON** status API Server kita.

---

## 🤖 "Kok banyak kali file md beserak"
Karena aku asumsikan di kelompok kita ada yang pakai **Claude Code**, **CODEX**, dan **Antigravity** (ya walaupun aku tau kebanyakan make **Antigravity**), file-file ini sengaja kubuat sebagai **"otak bersama"** buat AI kita. Jadi pas kalian nyuruh AI bikin fitur, AI-nya langsung paham struktur projek kita dan nggak ngarang tabel atau desain sendiri:

| Nama File / Folder | Gunanya Buat Apa? |
| :--- | :--- |
| **[`GOAL.md`](GOAL.md)** | Visi web **JejakSumut** (fokus wisata Sumatera Utara), warna brand, dan arsitektur pisah **`backend/` & `frontend/`** lewat REST API. |
| **[`PRD.md`](PRD.md)** | Rencana fitur lengkap, daftar route API & Web, format JSON REST API buat dibagi ke kelompok lain, dan **usulan pembagian tugas 5 orang**. |
| **[`Form_Evaluasi_Proyek_MDCS.docx.pdf`](Form_Evaluasi_Proyek_MDCS.docx.pdf)** | Lembar penilaian & checklist demo resmi dari dosen penguji (`K1` sampai `K10`). |
| **[`AGENTS.md`](AGENTS.md)** | Instruksi otomatis yang dibaca sama **Antigravity** & **CODEX** waktu kalian ngoding. |
| **[`CLAUDE.md`](CLAUDE.md)** | Instruksi otomatis khusus buat yang pakai **Claude Code**. |
| **[`.agents/`](.agents/)** | Folder berisi rules **`anti-ai-slop.md`** (biar desain UI bikinan AI nggak kelihatan pasaran/kaku) dan **`clean-code-indonesia.md`** (biar semua variabel, array key, & kode memakai Bahasa Indonesia yang gampang kita baca). |

---

## 🇮🇩 Aturan Ngoding Kita: Clean Code Berbahasa Indonesia
Di dalam folder [`.agents/rules/clean-code-indonesia.md`](.agents/rules/clean-code-indonesia.md), sudah dipasang aturan supaya setiap kali kita nyuruh AI bikin kode (mau di `backend` ataupun `frontend`):
1. **Nama Variabel & Fungsi Pakai Bahasa Indonesia:** Contohnya `$destinasiUtama`, `$daftarKategori`, `hitungTotalHarga()` (bukan bahasa Inggris yang bikin pusing pas baca).
2. **Array Key & Nama Kolom Database Seragam Bahasa Indonesia:** Contohnya `$destinasi['harga_tiket']`, `$destinasi['garis_lintang']`, `$destinasi['jam_buka']`.
3. **Kodenya Mudah Dibaca (*Clean Code*):** AI dilarang menumpuk logika rumit jadi 1 baris pendek yang susah dimengerti, dan wajib ngasih komentar penjelas berbahasa Indonesia supaya waktu ditanya dosen pas demo/sidang kita semua paham alurnya! (Contoh nyatanya bisa kalian tengok langsung di [`frontend/app/Views/welcome_message.php`](frontend/app/Views/welcome_message.php)).

---

## 🗄️ Desain Database (PostgreSQL) & Alur Tabel
Struktur **9 tabel utama + 2 SQL View + Trigger & Stored Procedure/Function (Full Bahasa Indonesia)** sudah tersedia di folder **[`backend/app/Database/`](backend/app/Database/)**:
1. **`kabupaten_kota`**: Data 33 kabupaten & kota di Provinsi Sumatera Utara (`kode_kabupaten`, `nama_kabupaten_kota`, `jenis_daerah`, `ibu_kota`).
2. **`pengguna`**: Data akun yang login (`admin` pengelola & `member` wisatawan).
3. **`kategori`**: 6 kategori wisata Sumatera Utara (Wisata Alam, Bahari & Pantai, Budaya & Sejarah, Religi, Buatan & Edukasi, Desa Wisata).
4. **`destinasi`**: Data objek wisata Sumatera Utara. Ada kolom **`kota`** & **`kode_kabupaten`** (misal: *Samosir*, *Medan*, *Karo*, *Langkat*) yang dipakai buat nyambungin **REST API kelompok lain** (Penyedia Tur Guide, Kuliner, Penginapan, Transportasi) di kabupaten/kota yang sama!
5. **`fasilitas`** & **`destinasi_fasilitas`**: Master fasilitas & tabel relasi fasilitas tiap destinasi.
6. **`galeri_destinasi`**: Foto-foto tambahan tiap tempat wisata.
7. **`pemesanan`**: Data pemesanan tiket kunjungan dari member.
8. **`ulasan`**: Rating bintang (1–5) & komentar pengunjung (otomatis memperbarui `rata_rating` & `jumlah_ulasan` di `destinasi` lewat Trigger).
9. **`favorit`**: Daftar tempat wisata yang disimpan/di-*bookmark* member.

```mermaid
erDiagram
    KABUPATEN_KOTA ||--o{ DESTINASI : "lokasi"
    KATEGORI ||--o{ DESTINASI : "punya"
    DESTINASI ||--o{ DESTINASI_FASILITAS : "punya fasilitas"
    FASILITAS ||--o{ DESTINASI_FASILITAS : "terpasang di"
    PENGGUNA ||--o{ PEMESANAN : "pesan tiket"
    PENGGUNA ||--o{ ULASAN : "kasih ulasan"
    PENGGUNA ||--o{ FAVORIT : "simpan favorit"
    DESTINASI ||--o{ GALERI_DESTINASI : "punya galeri"
    DESTINASI ||--o{ PEMESANAN : "dipesan di"
    DESTINASI ||--o{ ULASAN : "diulas di"
    DESTINASI ||--o{ FAVORIT : "disimpan di"

    KABUPATEN_KOTA {
        string kode_kabupaten PK
        string nama_kabupaten_kota UK
        string jenis_daerah
        string ibu_kota
    }
    PENGGUNA {
        int id PK
        string nama
        string email UK
        string peran "admin / member"
    }
    KATEGORI {
        int id PK
        string nama
        string slug UK
    }
    DESTINASI {
        int id PK
        int id_kategori FK
        string kode_kabupaten FK
        string nama
        string kota "Kunci sambungan REST API"
        int harga_tiket
        decimal garis_lintang
        decimal garis_bujur
        decimal rata_rating
    }
    FASILITAS {
        int id PK
        string nama UK
        string slug UK
    }
    DESTINASI_FASILITAS {
        int id_destinasi FK
        int id_fasilitas FK
    }
    GALERI_DESTINASI {
        int id PK
        int id_destinasi FK
        string url_foto
    }
    PEMESANAN {
        int id PK
        string kode_pemesanan UK
        int id_pengguna FK
        int id_destinasi FK
        date tanggal_kunjungan
        int total_harga
        string status "menunggu / lunas / dibatalkan"
    }
    ULASAN {
        int id PK
        int id_pengguna FK
        int id_destinasi FK
        smallint rating "1-5"
        text komentar
    }
    FAVORIT {
        int id PK
        int id_pengguna FK
        int id_destinasi FK
    }
```

### ☁️ Database Sudah Online di VM Azure
Kabar bagus coy: **Database PostgreSQL (`jejaksumut_db`) kita disiapkan aktif di VM Azure aku** (`70.153.136.38:5432`), lengkap beserta seluruh tabel, objek database (View, Trigger, Stored Procedure/Function), dan data wisata Sumatera Utara!

1. **Cara Menyambungkan `backend/` di Laptop Masing-Masing:**
   - Masuk ke folder `backend/`, lalu copy file template env (`cp env .env`).
   - Buka `backend/.env`, cari bagian **`DATABASE`**, lalu **hapus tanda `#`** dan isi password-nya (sudah ku siapkan IP & user-nya di [`backend/env`](backend/env)):
     ```ini
     database.default.hostname = 70.153.136.38
     database.default.database = jejaksumut_db
     database.default.username = jejaksumut_user
     database.default.password = (Tanya aku di Grup WA)
     database.default.charset  = utf8
     database.default.DBDriver = Postgre
     database.default.port     = 5432
     ```
2. **Cara Melihat Struktur & Isi Tabel Lewat pgAdmin / DBeaver:**
   - Buka **pgAdmin** atau **DBeaver** di laptop kalian $\rightarrow$ Register Server baru.
   - Masukkan **Host**: `70.153.136.38`, **Port**: `5432`, **Database**: `jejaksumut_db`, **Username**: `jejaksumut_user`, **Password**: *(minta di grup WA)*.
   - Semua tabel dan datanya langsung kelihatan di `jejaksumut_db -> Schemas -> public -> Tables`!
3. **⚠️ Aturan Wajib Karena Database Dipakai Bersama Berlima:**
   - **DILARANG** menjalankan `php spark migrate:refresh` seenaknya tanpa izin grup, karena itu bakal **menghapus semua data** di VM Azure!
   - Kalau mau ubah struktur tabel (tambah kolom/tabel), tetap buat lewat file **Migration** di `backend/app/Database/Migrations/` lalu `git push` ke GitHub (jangan ubah struktur diam-diam lewat pgAdmin saja biar kawan lain tidak bingung).

---

### Cara Import Database ke PostgreSQL Lokal (Opsional / Cadangan Kalau Offline)
Kalau suatu saat kalian mau bikin database cadangan di PostgreSQL lokal laptop masing-masing:

#### Cara 1: Lewat Terminal/CMD di Folder `backend/` (Sekaligus Isi Data Wisata Sumut Otomatis)
1. Buka file `backend/.env`, ubah bagian database ke lokal kalian:
   ```ini
   database.default.hostname = localhost
   database.default.database = jejaksumut_db
   database.default.username = postgres
   database.default.password = password_postgres_kalian
   database.default.charset  = utf8
   database.default.DBDriver = Postgre
   database.default.port     = 5432
   ```
2. Jalankan perintah ini di terminal/cmd dari dalam folder **`backend/`**:
   ```bash
   cd backend
   php spark migrate
   php spark db:seed MainSeeder
   ```
   *(Otomatis langsung ke-buat seluruh tabel, View, Trigger, Stored Function/Procedure + akun contoh + ratusan destinasi wisata Sumatera Utara!)*

#### Cara 2: Buat yang MALAS Setting CLI (Langsung via pgAdmin / DBeaver)
1. Buka **pgAdmin** atau **DBeaver**, pilih database `jejaksumut_db`.
2. Buka Query Tool, lalu *copy-paste* dan *run* isi file **[`backend/app/Database/schema_postgresql.sql`](backend/app/Database/schema_postgresql.sql)**. Langsung jadi seluruh tabel, view, trigger, dan stored procedure-nya!

---

## 🌿 Cheat Sheet Git Simpel (Biar Nggak Bentrok / Conflict Pas Push!)

Karena kita kerja **berlima**, ikuti **3 tips ini ya coy** biar kode kita nggak ketimpa:

### 1. Selalu `git pull` Dulu SEBELUM Mulai Ngoding!
Jangan langsung ngoding dulu ya. Tarik dulu update terbaru dari kawan yang lain:
```bash
git pull origin main
```

### 2. Pake Branch Sendiri (cari di gugel apa itu Branch)
Jangan keroyokan ngoding langsung di branch `main`. Bikin branch pakai namamu atau fiturmu:
```bash
# Bikin & pindah ke branch baru (cukup sekali pas mulai fitur)
git checkout -b fitur-booking

# Cek file apa aja yang kau ubah
git status

# Simpan & push ke GitHub
git add .
git commit -m "bikin halaman booking tiket"
git push origin fitur-booking
```
*(Habis itu tinggal digabung / Merge lewat Pull Request di GitHub).*

### 3. Kalau Mau Push Langsung ke `main`, Wajib Urutan Ini:
```bash
git add .
git commit -m "pesan commit singkat dan jelas"
git pull origin main   # <-- WAJIB tarik dulu sebelum push!
git push origin main
```
> 🚫 **Ingat ini:** Jangan pernah pake `git push -f` (*force push*) ke `main` karena itu bakal menghapus kerjaan kawan yang sudah masuk duluan!

## Demo Panel Pengelola sebagai Client Kedua (K6)

### Perlindungan konflik edit destinasi (K7)

`GET /api/v1/admin/destinasi/{id}` mengembalikan `data.versi_data`. Kirim nilai tersebut bersama isian pada `PUT /api/v1/destinasi/{id}`. Panel Admin meneruskannya otomatis melalui input tersembunyi. Client API yang belum mengirim versi perlu mengambil data terbaru terlebih dahulu.

```json
{
  "harga_tiket": 25000,
  "versi_data": "salin nilai versi_data dari respons GET admin destinasi"
}
```

Versi adalah SHA-256 dari ID dan isian destinasi yang dapat diedit Admin. Backend memeriksanya setelah memperoleh `SELECT ... FOR UPDATE`, kemudian menyimpan perubahan dalam transaksi yang sama. Versi yang berbeda menghasilkan `409` dengan `data.konflik = "versi_data"`. Versi kosong atau tidak valid menghasilkan `422`, sedangkan ID yang sudah dihapus menghasilkan `404`. Konflik slug tetap menggunakan `409` tanpa penanda konflik versi.

Tidak ada migrasi database baru untuk perubahan ini. Rating dan waktu perubahan tidak masuk perhitungan versi agar trigger ulasan tidak membatalkan formulir Admin. Setelah perubahan backend dan frontend dipasang, muat ulang formulir edit yang masih terbuka.

Untuk demo, masuk sebagai Admin pada dua browser/profil terpisah (misalnya jendela biasa dan penyamaran), lalu buka destinasi yang sama. Sesi terpisah menjaga token CSRF masing-masing tetap berlaku. Simpan harga pada browser pertama, lalu simpan perubahan pada browser kedua. Browser kedua harus menampilkan konflik dan mempertahankan isian untuk disalin. Klik **Muat data terbaru**, periksa hasil browser pertama, lalu lakukan edit berikutnya. Formulir yang konflik tidak otomatis mendapat versi baru untuk mengirim ulang isian lama.

Pengujian regresi tersedia di `backend/tests/integration/KonflikDestinasiTest.php`. Jalankan pada API dengan database pengujian terpisah. Tes membuat destinasi sementara dan menghapusnya kembali. Siapkan `JEJAKSUMUT_UJI_API_URL` (URL lengkap hingga `/api/v1`) dan `JEJAKSUMUT_UJI_TOKEN_ADMIN` (token login Admin pada database pengujian), lalu jalankan dari `backend/`:

```sh
php vendor/bin/phpunit --no-coverage tests/integration/KonflikDestinasiTest.php
```

Gunakan server dengan lebih dari satu worker untuk menguji permintaan bersamaan. Tanpa kedua variabel lingkungan tersebut, tes integrasi ditandai dilewati; tes yang dilewati bukan bukti keberhasilan pengujian konkurensi. Jangan menyimpan token di repository.

### Menjalankan kedua client

Jalankan satu REST API dan dua proses frontend. Kedua client menggunakan URL API yang sama. Jalankan perintah berikut dari root repositori setelah dependensi Composer di `backend/` dan `frontend/` terpasang.

1. Atur koneksi PostgreSQL di `backend/.env` dan isi `JEJAKSUMUT_KUNCI_TOKEN` dengan hasil `openssl rand -hex 32`. Token login berlaku dua jam. Pengguna yang masih memegang token lama perlu keluar dan masuk ulang.
2. Jalankan `php spark migrate` dari `backend/` untuk memasang relasi `RESTRICT` yang melindungi riwayat pemesanan. Di database demo baru, isi `JEJAKSUMUT_KATA_SANDI_ADMIN_AWAL` (minimal 16 karakter) sebelum `php spark db:seed MainSeeder`, lalu hapus variabel bootstrap tersebut. Admin awal menggunakan email `admin@jejaksumut.id`. Pada database yang sudah pernah di-seed, ganti kata sandi akun admin lama sebelum membuka panel. Seeder mempertahankan akun yang sudah ada.
3. Pada server yang memakai role K2, konfigurasikan `JEJAKSUMUT_DB_ADMIN_USER` dan `JEJAKSUMUT_DB_ADMIN_PASSWORD` di backend sesuai role dari `setup_hak_akses_k2.sql`. Koneksi default tetap memakai role publik. Tanpa konfigurasi role admin terpisah, endpoint admin menggunakan koneksi default. Cara ini dapat dipakai untuk demo dengan database lokal.
4. Jalankan tiap perintah di terminal tersendiri.

   **REST API** pada port 8080.

   ```sh
   cd backend
   env CI_ENVIRONMENT=production app_baseURL=http://localhost:8080/ \
     JEJAKSUMUT_FRONTEND_URL=http://localhost:8081 php spark serve --port 8080
   ```

   **Web Wisatawan** pada port 8081.

   ```sh
   cd frontend
   env CI_ENVIRONMENT=production app_baseURL=http://localhost:8081/ \
     JEJAKSUMUT_MODE_CLIENT=wisatawan JEJAKSUMUT_BACKEND_API_URL=http://localhost:8080/api/v1 \
     JEJAKSUMUT_PANEL_ADMIN_URL=http://localhost:8086/admin php spark serve --port 8081
   ```

   **Panel Pengelola** pada port 8086. Buka `http://localhost:8086/admin`.

   ```sh
   cd frontend
   env CI_ENVIRONMENT=production app_baseURL=http://localhost:8086/ \
     JEJAKSUMUT_MODE_CLIENT=admin JEJAKSUMUT_BACKEND_API_URL=http://localhost:8080/api/v1 \
     session_cookieName=jejaksumut_admin security_cookieName=jejaksumut_admin_csrf \
     php spark serve --port 8086
   ```

Pesan tiket dari Web Wisatawan dan buka menu **Pemesanan** di Panel Pengelola. Klik **Verifikasi lunas**, lalu muat ulang E-Ticket dari client wisatawan. Untuk demo katalog, tambah destinasi atau ubah harga tiket melalui menu **Destinasi**, lalu buka kembali katalog wisatawan. Tombol **Perbarui data** di Dasbor memeriksa ulang REST API backend dan keempat API mitra.
