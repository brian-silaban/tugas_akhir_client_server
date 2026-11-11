# 📋 PRD.md — Product Requirements Document (IndWalk)

Dokumen ini adalah acuan teknis tunggal (*Single Source of Truth*) untuk **5 anggota kelompok** dan seluruh **AI Coding Agent (Claude Code, CODEX, Antigravity)** dalam membangun aplikasi **IndWalk: Explore Indonesia Together**.

---

## 1. Spesifikasi Lingkungan & Standar Kode
- **Framework Backend**: CodeIgniter 4 (PHP 8.2+)
- **Database Utama**: **PostgreSQL** (`DBDriver = Postgre`, Port `5432`)
- **Frontend**: View PHP CI4 + Tailwind CSS + Vanilla JS / Alpine.js + Lucide SVG Icons
- **Format Pertukaran Data Antar-Kelompok**: RESTful API (JSON over HTTP/HTTPS)
- **Standar Penulisan Kode**: **Wajib Clean Code Berbahasa Indonesia** (lihat [`.agents/rules/clean-code-indonesia.md`](.agents/rules/clean-code-indonesia.md)) — seluruh nama variabel (`$destinasiUtama`), *array key* (`'harga_tiket'`, `'garis_lintang'`), nama tabel/kolom database, dan komentar wajib menggunakan Bahasa Indonesia.

---

## 2. Daftar Role Pengguna & Hak Akses

| Peran (`peran`) | Deskripsi | Hak Akses Fitur |
| :--- | :--- | :--- |
| **Guest** (Tanpa Login) | Pengunjung publik | Melihat Beranda, mencari & memfilter katalog wisata, melihat detail destinasi, melihat ulasan, dan melihat rekomendasi mitra dari REST API kelompok lain. |
| **Member** (`peran = 'member'`) | Wisatawan terdaftar | Seluruh akses Guest + menyimpan destinasi ke **Favorit**, membuat **Pemesanan Tiket**, melihat **E-Ticket Saya**, membatalkan pesanan berstatus `menunggu`, dan menulis **Ulasan & Rating**. |
| **Admin** (`peran = 'admin'`) | Pengelola platform IndWalk | Mengakses Dashboard Admin, CRUD Kategori & Destinasi (+ Galeri Foto), memverifikasi status Pemesanan (`menunggu` $\rightarrow$ `lunas` / `dibatalkan`), menghapus ulasan spam, dan memantau koneksi REST API kelompok lain. |

---

## 3. Referensi Skema Database PostgreSQL (7 Tabel Inti Berbahasa Indonesia)
Seluruh file migrasi sudah tersedia di [`app/Database/Migrations/`](app/Database/Migrations/) dan file SQL murni di [`app/Database/schema_postgresql.sql`](app/Database/schema_postgresql.sql):

1. **`pengguna`**: `id` (PK), `nama`, `email` (Unique), `kata_sandi`, `peran` (`'admin'` | `'member'`), `foto_profil`, `dibuat_pada`, `diperbarui_pada`
2. **`kategori`**: `id` (PK), `nama`, `slug` (Unique), `deskripsi`, `dibuat_pada`, `diperbarui_pada`
3. **`destinasi`**: `id` (PK), `id_kategori` (FK ke `kategori.id`), `nama`, `slug` (Unique), `kota` (Indeks pencarian & kunci parameter REST API), `provinsi`, `alamat`, `garis_lintang`, `garis_bujur`, `harga_tiket`, `jam_buka`, `jam_tutup`, `deskripsi`, `fasilitas` (JSON string), `foto_utama`, `rata_rating`, `jumlah_ulasan`, `dibuat_pada`, `diperbarui_pada`
4. **`galeri_destinasi`**: `id` (PK), `id_destinasi` (FK ke `destinasi.id`), `url_foto`, `keterangan`, `dibuat_pada`
5. **`pemesanan`**: `id` (PK), `kode_pemesanan` (Unique, format `IND-YYYYMMDD-XXX`), `id_pengguna` (FK ke `pengguna.id`), `id_destinasi` (FK ke `destinasi.id`), `tanggal_kunjungan`, `jumlah_pengunjung`, `total_harga`, `status` (`'menunggu'` | `'lunas'` | `'dibatalkan'`), `dibuat_pada`, `diperbarui_pada`
6. **`ulasan`**: `id` (PK), `id_pengguna` (FK ke `pengguna.id`), `id_destinasi` (FK ke `destinasi.id`), `rating` (1–5), `komentar`, `dibuat_pada`, `diperbarui_pada`
7. **`favorit`**: `id` (PK), `id_pengguna` (FK ke `pengguna.id`), `id_destinasi` (FK ke `destinasi.id`), `dibuat_pada` (Unique: `id_pengguna + id_destinasi`)

---

## 4. Kontrak & Spesifikasi REST API (Inti Client-Server)

### A. IndWalk Sebagai API Provider (Server untuk Kelompok Lain)
Kelompok lain (Kuliner, Penginapan, Transportasi, dll.) dapat memanggil endpoint berikut:

#### 1. `GET /api/v1/destinasi`
- **Query Params Opsional**:
  - `?kota=Magelang` (Filter berdasarkan kota — *case-insensitive*)
  - `?kategori=cagar-budaya` (Filter berdasarkan slug kategori)
  - `?harga_maksimal=50000` (Filter batas harga tiket)
  - `?batas=10`
- **Contoh Response `200 OK`**:
  ```json
  {
    "status": "berhasil",
    "penyedia": "IndWalk Tourism API",
    "jumlah_data": 1,
    "data": [
      {
        "id": 1,
        "nama": "Candi Borobudur",
        "slug": "candi-borobudur",
        "kategori": "Cagar Budaya",
        "kota": "Magelang",
        "provinsi": "Jawa Tengah",
        "alamat": "Jl. Badrawati, Kw. Candi Borobudur, Borobudur, Kab. Magelang",
        "koordinat": {
          "garis_lintang": -7.6078738,
          "garis_bujur": 110.2037513
        },
        "harga_tiket": 50000,
        "jam_operasional": "06:30 - 16:30",
        "rata_rating": 4.9,
        "jumlah_ulasan": 1420,
        "foto_utama": "https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80",
        "url_detail": "http://localhost:8080/destinasi/candi-borobudur"
      }
    ]
  }
  ```

#### 2. `GET /api/v1/destinasi/{slug_atau_id}`
- Mengembalikan detail lengkap 1 destinasi beserta array `fasilitas`, `galeri`, dan `ulasan_terbaru`.

#### 3. `GET /api/v1/kategori`
- Mengembalikan daftar seluruh kategori wisata beserta jumlah destinasi di tiap kategori.

---

### B. IndWalk Sebagai API Consumer (Client yang Mengambil Data Kelompok Lain)
Di halaman Detail Destinasi (`/destinasi/{slug}`), IndWalk memanggil library `App\Libraries\LayananApiMitra` dengan parameter `kota` (misal: `Magelang`, `Malang`, `Banyuwangi`):

1. **Desain Fleksibel Menghadapi Perubahan Tema Kelompok Lain**:
   - Konfigurasi endpoint kelompok mitra disimpan di `.env`:
     ```ini
     # Endpoint API Kelompok Mitra (Aktifkan MODE_CADANGAN_MOCK=true selama API kelompok lain belum siap)
     MODE_CADANGAN_MOCK     = true
     URL_MITRA_KULINER      = "http://localhost:8081/api/kuliner"
     URL_MITRA_PENGINAPAN   = "http://localhost:8082/api/penginapan"
     URL_MITRA_TRANSPORTASI = "http://localhost:8083/api/transportasi"
     ```
2. **Mekanisme Timeout & Fallback Otomatis**:
   - Setiap request HTTP (`CURLRequest`) dibatasi `timeout => 3` detik.
   - Jika server kelompok lain belum siap, mati, atau mengembalikan error, `LayananApiMitra` otomatis mengembalikan data **Mock Fallback** berlabel `"sumber_data": "simulasi_fallback"` sehingga UI halaman Detail Wisata tetap menampilkan kartu rekomendasi dengan rapi.

---

## 5. Peta Routing & Halaman (`app/Config/Routes.php`)

### Rute Publik (Guest & Member)
- `GET /` $\rightarrow$ `HomeController::index` (Beranda: Hero Search, Bento Destinasi Unggulan, Kategori)
- `GET /destinasi` $\rightarrow$ `DestinasiController::index` (Katalog Eksplorasi + Filter Kota, Kategori, Harga)
- `GET /destinasi/(:segment)` $\rightarrow$ `DestinasiController::tampil/$1` (Detail Wisata + Galeri + Ulasan + **Widget REST API Kelompok Lain**)

### Rute Autentikasi
- `GET /masuk` & `POST /masuk` $\rightarrow$ `AutentikasiController::masuk` & `AutentikasiController::prosesMasuk`
- `GET /daftar` & `POST /daftar` $\rightarrow$ `AutentikasiController::daftar` & `AutentikasiController::prosesDaftar`
- `GET /keluar` $\rightarrow$ `AutentikasiController::keluar`

### Rute Member (Wajib Login)
- `POST /pemesanan` $\rightarrow$ `PemesananController::simpan` (Buat pemesanan tiket baru)
- `GET /pemesanan-saya` $\rightarrow$ `PemesananController::index` (Daftar E-Ticket & riwayat pemesanan)
- `POST /pemesanan-saya/(:num)/batal` $\rightarrow$ `PemesananController::batalkan/$1` (Batalkan pesanan berstatus `menunggu`)
- `POST /ulasan` $\rightarrow$ `UlasanController::simpan` (Kirim rating 1–5 & komentar, lalu hitung ulang `rata_rating` destinasi)
- `POST /favorit/ubah` $\rightarrow$ `FavoritController::ubah` (Tambah/hapus destinasi dari daftar favorit)
- `GET /favorit-saya` $\rightarrow$ `FavoritController::index` (Daftar destinasi favorit saya)

### Rute Admin (`peran = 'admin'`)
- `GET /admin` $\rightarrow$ `Admin\DasborController::index` (Ringkasan metrik & status koneksi REST API Mitra)
- `GET /admin/destinasi` $\rightarrow$ `Admin\DestinasiAdminController::index` (CRUD Destinasi & Kategori)
- `GET /admin/pemesanan` $\rightarrow$ `Admin\PemesananAdminController::index` (Verifikasi pembayaran tiket `menunggu` $\rightarrow$ `lunas`)

### Rute REST API (`Api` Namespace)
- `GET /api/v1/destinasi` $\rightarrow$ `Api\DestinasiApiController::index`
- `GET /api/v1/destinasi/(:segment)` $\rightarrow$ `Api\DestinasiApiController::tampil/$1`
- `GET /api/v1/kategori` $\rightarrow$ `Api\DestinasiApiController::kategori`

---

## 6. Pembagian Tugas 5 Anggota Kelompok (Bebas Konflik Git)

Agar 5 orang dapat bekerja paralel di GitHub tanpa saling menimpa file (*merge conflict*), pembagian kerja diatur per modul:

### 👤 Anggota 1 — Core Layout, Beranda & Katalog Destinasi
- **Tanggung Jawab**:
  - Menyusun master layout `app/Views/layout/main.php`, `navbar.php` (dengan logo IndWalk), dan `footer.php`.
  - Membangun halaman Beranda (`/`) dan Katalog Pencarian/Filter (`/destinasi`) serta kerangka Halaman Detail (`/destinasi/{slug}`).
- **File Miliknya**:
  - `app/Controllers/HomeController.php`, `app/Controllers/DestinasiController.php`
  - `app/Models/DestinasiModel.php`, `app/Models/KategoriModel.php`, `app/Models/GaleriDestinasiModel.php`
  - `app/Views/layout/*`, `app/Views/pages/home.php`, `app/Views/pages/destinasi/*`

### 👤 Anggota 2 — Spesialis Client-Server & Integrasi REST API
- **Tanggung Jawab**:
  - Membangun **REST API Server IndWalk** (`/api/v1/destinasi`) agar bisa diakses kelompok lain.
  - Membangun **`LayananApiMitra`** untuk mengambil data dari API kelompok Kuliner, Penginapan, dan Transportasi (lengkap dengan *timeout handling* & *Mock Fallback*).
  - Membuat komponen View bagian bawah halaman Detail Destinasi untuk menampilkan kartu data dari API kelompok lain.
- **File Miliknya**:
  - `app/Controllers/Api/DestinasiApiController.php`
  - `app/Libraries/LayananApiMitra.php`
  - `app/Views/components/rekomendasi_mitra.php`

### 👤 Anggota 3 — Autentikasi, Sesi Pengguna, Filter Peran & Favorit
- **Tanggung Jawab**:
  - Membangun fitur Masuk (Login), Daftar (Register), Keluar (Logout), dan Filter proteksi halaman (`AutentikasiFilter` & `AdminFilter`).
  - Membangun fitur tambah/hapus **Favorit** destinasi dan halaman `/favorit-saya`.
- **File Miliknya**:
  - `app/Controllers/AutentikasiController.php`, `app/Controllers/FavoritController.php`
  - `app/Filters/AutentikasiFilter.php`, `app/Filters/AdminFilter.php`
  - `app/Models/PenggunaModel.php`, `app/Models/FavoritModel.php`
  - `app/Views/pages/autentikasi/*`, `app/Views/pages/member/favorit.php`

### 👤 Anggota 4 — Transaksi Pemesanan Tiket, E-Ticket & Sistem Ulasan
- **Tanggung Jawab**:
  - Membangun fitur form Pemesanan Tiket di halaman detail, generator `kode_pemesanan` (`IND-YYYYMMDD-XXX`), dan halaman **E-Ticket Saya** (`/pemesanan-saya`).
  - Membangun fitur input Rating (1–5) & Ulasan serta meng-update kolom `rata_rating` dan `jumlah_ulasan` otomatis di tabel `destinasi`.
- **File Miliknya**:
  - `app/Controllers/PemesananController.php`, `app/Controllers/UlasanController.php`
  - `app/Models/PemesananModel.php`, `app/Models/UlasanModel.php`
  - `app/Views/pages/member/pemesanan.php`, `app/Views/components/bagian_ulasan.php`

### 👤 Anggota 5 — Dasbor Admin & Manajemen Operasional
- **Tanggung Jawab**:
  - Membangun halaman Dasbor Admin (`/admin`) dengan kartu statistik nyata.
  - Membangun halaman CRUD Destinasi & Kategori (`/admin/destinasi`) serta halaman Verifikasi Pemesanan (`/admin/pemesanan`).
  - Membangun panel cek kesehatan endpoint REST API kelompok lain di Dasbor Admin.
- **File Miliknya**:
  - `app/Controllers/Admin/DasborController.php`
  - `app/Controllers/Admin/DestinasiAdminController.php`
  - `app/Controllers/Admin/PemesananAdminController.php`
  - `app/Views/pages/admin/*`
