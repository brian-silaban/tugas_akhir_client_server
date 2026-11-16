# 📋 PRD.md — Product Requirements Document (IndWalk)

Dokumen ini adalah acuan teknis tunggal (*Single Source of Truth*) untuk **5 anggota kelompok** dan seluruh **AI Coding Agent (Claude Code, CODEX, Antigravity)** dalam membangun aplikasi **IndWalk: Explore Indonesia Together**.

---

## 1. Arsitektur 2 Projek Terpisah (`backend/` & `frontend/`)
Sesuai arahan dosen pengampu mata kuliah **Client-Server**, aplikasi dipisahkan menjadi **2 projek CodeIgniter 4** di dalam satu repositori agar komunikasi **REST API** terlihat jelas dari hulu ke hilir:

1. **Folder [`backend/`](backend/) — Server REST API & Database PostgreSQL (Default Port `8080`)**:
   - Bertugas sebagai **Server** yang mengelola koneksi ke **PostgreSQL**, validasi data, serta menyediakan endpoint **RESTful JSON API** baik untuk `frontend/` kita sendiri maupun untuk kelompok lain.
   - Dapat di-deploy terpusat di **Cloud VM Azure** agar bisa langsung diakses oleh seluruh anggota kelompok dan kelompok lain lewat internet.
2. **Folder [`frontend/`](frontend/) — Client Web UI IndWalk (Default Port `8081`)**:
   - Bertugas sebagai **Client** yang menampilkan antarmuka pengguna (Tailwind CSS + View CI4).
   - **Dilarang mengakses database PostgreSQL secara langsung**. Seluruh data diambil dan dikirim melalui HTTP Request (`CURLRequest`) ke REST API `backend/` dan REST API kelompok mitra.
3. **Standar Penulisan Kode**:
   - **Wajib Clean Code Berbahasa Indonesia** (lihat [`.agents/rules/clean-code-indonesia.md`](.agents/rules/clean-code-indonesia.md)) — seluruh nama variabel (`$destinasiUtama`), *array key* (`'harga_tiket'`, `'garis_lintang'`), nama tabel/kolom database, dan komentar wajib menggunakan Bahasa Indonesia.

---

## 2. Daftar Role Pengguna & Hak Akses

| Peran (`peran`) | Deskripsi | Hak Akses Fitur |
| :--- | :--- | :--- |
| **Guest** (Tanpa Login) | Pengunjung publik | Melihat Beranda, mencari & memfilter katalog wisata, melihat detail destinasi, melihat ulasan, dan melihat rekomendasi mitra dari REST API kelompok lain. |
| **Member** (`peran = 'member'`) | Wisatawan terdaftar | Seluruh akses Guest + menyimpan destinasi ke **Favorit**, membuat **Pemesanan Tiket**, melihat **E-Ticket Saya**, membatalkan pesanan berstatus `menunggu`, dan menulis **Ulasan & Rating**. |
| **Admin** (`peran = 'admin'`) | Pengelola platform IndWalk | Mengakses Dasbor Admin, CRUD Kategori & Destinasi (+ Galeri Foto), memverifikasi status Pemesanan (`menunggu` $\rightarrow$ `lunas` / `dibatalkan`), menghapus ulasan spam, dan memantau koneksi REST API kelompok lain. |

---

## 3. Referensi Skema Database PostgreSQL (7 Tabel Inti di `backend/`)
Seluruh file migrasi sudah tersedia di [`backend/app/Database/Migrations/`](backend/app/Database/Migrations/), seeder di [`backend/app/Database/Seeds/IndWalkSeeder.php`](backend/app/Database/Seeds/IndWalkSeeder.php), dan file SQL murni di [`backend/app/Database/schema_postgresql.sql`](backend/app/Database/schema_postgresql.sql):

1. **`pengguna`**: `id` (PK), `nama`, `email` (Unique), `kata_sandi`, `peran` (`'admin'` | `'member'`), `foto_profil`, `dibuat_pada`, `diperbarui_pada`
2. **`kategori`**: `id` (PK), `nama`, `slug` (Unique), `deskripsi`, `dibuat_pada`, `diperbarui_pada`
3. **`destinasi`**: `id` (PK), `id_kategori` (FK ke `kategori.id`), `nama`, `slug` (Unique), `kota` (Indeks pencarian & kunci parameter REST API), `provinsi`, `alamat`, `garis_lintang`, `garis_bujur`, `harga_tiket`, `jam_buka`, `jam_tutup`, `deskripsi`, `fasilitas` (JSON string), `foto_utama`, `rata_rating`, `jumlah_ulasan`, `dibuat_pada`, `diperbarui_pada`
4. **`galeri_destinasi`**: `id` (PK), `id_destinasi` (FK ke `destinasi.id`), `url_foto`, `keterangan`, `dibuat_pada`
5. **`pemesanan`**: `id` (PK), `kode_pemesanan` (Unique, format `IND-YYYYMMDD-XXX`), `id_pengguna` (FK ke `pengguna.id`), `id_destinasi` (FK ke `destinasi.id`), `tanggal_kunjungan`, `jumlah_pengunjung`, `total_harga`, `status` (`'menunggu'` | `'lunas'` | `'dibatalkan'`), `dibuat_pada`, `diperbarui_pada`
6. **`ulasan`**: `id` (PK), `id_pengguna` (FK ke `pengguna.id`), `id_destinasi` (FK ke `destinasi.id`), `rating` (1–5), `komentar`, `dibuat_pada`, `diperbarui_pada`
7. **`favorit`**: `id` (PK), `id_pengguna` (FK ke `pengguna.id`), `id_destinasi` (FK ke `destinasi.id`), `dibuat_pada` (Unique: `id_pengguna + id_destinasi`)

---

## 4. Kontrak & Spesifikasi REST API (`backend/`)

### A. Endpoint REST API di `backend/` (Dipakai oleh `frontend/` & 4 Kelompok Mitra)

#### 1. `GET /api/v1/destinasi`
*(Endpoint publik untuk dikonsumsi oleh kelompok **Penyedia Tur Guide**, **Kuliner/Rumah Makan**, **Penginapan**, dan **Transportasi**)*
- **Query Params Opsional**:
  - `?kota=Magelang` (Filter berdasarkan kota — *case-insensitive*)
  - `?kategori=cagar-budaya` (Filter berdasarkan slug kategori)
  - `?harga_maksimal=50000` (Filter batas harga tiket)
  - `?kata_kunci=borobudur` (Pencarian nama/deskripsi)
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
        "url_detail": "http://localhost:8081/destinasi/candi-borobudur"
      }
    ]
  }
  ```

#### 2. Endpoint Lainnya pada `backend/` (`/api/v1/...`)
- **Kategori & Detail Destinasi**:
  - `GET /api/v1/kategori` — Daftar seluruh kategori wisata.
  - `GET /api/v1/destinasi/{slug_atau_id}` — Detail lengkap 1 destinasi beserta `fasilitas`, `galeri`, dan `ulasan`.
  - `POST /api/v1/destinasi`, `PUT /api/v1/destinasi/{id}`, `DELETE /api/v1/destinasi/{id}` — Endpoint CRUD Destinasi untuk Admin.
- **Autentikasi**:
  - `POST /api/v1/autentikasi/masuk` — Validasi email & kata sandi pengguna.
  - `POST /api/v1/autentikasi/daftar` — Registrasi member baru.
- **Transaksi Member & Admin**:
  - `GET /api/v1/pemesanan` & `POST /api/v1/pemesanan` — Daftar & pembuatan pesanan tiket.
  - `PATCH /api/v1/pemesanan/{id}/status` — Perubahan status pesanan (`menunggu` / `lunas` / `dibatalkan`).
  - `POST /api/v1/ulasan` — Kirim rating & komentar ulasan baru.
  - `GET /api/v1/favorit/{id_pengguna}` & `POST /api/v1/favorit/ubah` — Daftar & tambah/hapus favorit.

---

### B. Integrasi 4 REST API Kelompok Mitra di `frontend/`
Di halaman Detail Destinasi (`/destinasi/{slug}`), aplikasi `frontend/` memanggil library `frontend/app/Libraries/LayananApiMitra.php` dengan parameter `kota` (misal: `Magelang`, `Malang`, `Banyuwangi`) untuk mengambil data dari **4 kelompok mitra di kelas**:
1. **Kelompok Penyedia Tur Guide** (`PARTNER_TUR_GUIDE_API_URL`)
2. **Kelompok Kuliner / Rumah Makan** (`PARTNER_KULINER_API_URL`)
3. **Kelompok Penginapan** (`PARTNER_HOTEL_API_URL`)
4. **Kelompok Transportasi** (`PARTNER_TRANSPORT_API_URL`)

Konfigurasi endpoint dan mekanisme fallback disimpan di `frontend/.env`:
1. **Konfigurasi di `frontend/.env`** (sesuai contoh di [`frontend/env`](frontend/env)):
   ```ini
   INDWALK_BACKEND_API_URL   = 'http://localhost:8080/api/v1'
   PARTNER_KULINER_API_URL   = 'http://localhost:8082/api/kuliner'
   PARTNER_HOTEL_API_URL     = 'http://localhost:8083/api/penginapan'
   PARTNER_TRANSPORT_API_URL = 'http://localhost:8084/api/transportasi'
   PARTNER_TUR_GUIDE_API_URL = 'http://localhost:8085/api/tur_guide'
   PARTNER_API_TIMEOUT       = 3
   MODE_CADANGAN_MOCK        = true
   ```
2. **Mekanisme Timeout & Fallback Otomatis**:
   - Setiap request HTTP dibatasi `PARTNER_API_TIMEOUT = 3` detik.
   - Jika server salah satu dari 4 kelompok mitra belum siap atau mati saat demo, `LayananApiMitra` otomatis mengembalikan data **Mock Fallback** berlabel `"sumber_data": "simulasi_fallback"` sehingga halaman tidak pernah *error*.

---

## 5. Pembagian Tugas 5 Anggota Kelompok (Bebas Konflik Git)

Dengan pemisahan folder `backend/` dan `frontend/`, 5 anggota kelompok dapat membagi tugas per fitur (**Full-Stack per Modul** atau **Spesialis Backend/Frontend**) tanpa bentrok file:

### 👤 Anggota 1 — Beranda, Katalog Eksplorasi & Detail Destinasi
- **Backend (`backend/`)**:
  - `backend/app/Models/DestinasiModel.php`, `KategoriModel.php`, `GaleriDestinasiModel.php`
  - `backend/app/Controllers/Api/DestinasiApiController.php` (`GET /api/v1/destinasi`, `GET /api/v1/kategori`, `GET /api/v1/destinasi/{slug}`)
- **Frontend (`frontend/`)**:
  - `frontend/app/Controllers/Home.php`, `frontend/app/Controllers/DestinasiController.php`
  - `frontend/app/Views/welcome_message.php`, `frontend/app/Views/pages/destinasi/*`

### 👤 Anggota 2 — Integrasi Client-Server (HTTP Client Backend & 4 API Kelompok Mitra)
- **Frontend (`frontend/`)**:
  - `frontend/app/Libraries/LayananApiBackend.php` (Helper pemanggil REST API `backend/`)
  - `frontend/app/Libraries/LayananApiMitra.php` (Pemanggil REST API 4 kelompok mitra: **Penyedia Tur Guide**, **Kuliner/Rumah Makan**, **Penginapan**, dan **Transportasi** + Mock Fallback)
  - `frontend/app/Views/components/rekomendasi_mitra.php` (Tampilan kartu rekomendasi 4 layanan mitra di halaman Detail Wisata)
- **Backend (`backend/`)**:
  - Dokumentasi & pengujian kontrak JSON `/api/v1/destinasi` untuk dibagikan ke kelompok lain + deployment ke VM Azure.

### 👤 Anggota 3 — Autentikasi, Sesi Pengguna & Fitur Favorit
- **Backend (`backend/`)**:
  - `backend/app/Models/PenggunaModel.php`, `backend/app/Models/FavoritModel.php`
  - `backend/app/Controllers/Api/AutentikasiApiController.php`, `backend/app/Controllers/Api/FavoritApiController.php`
- **Frontend (`frontend/`)**:
  - `frontend/app/Controllers/AutentikasiController.php`, `frontend/app/Controllers/FavoritController.php`
  - `frontend/app/Filters/AutentikasiFilter.php`, `frontend/app/Filters/AdminFilter.php`
  - `frontend/app/Views/pages/autentikasi/*`, `frontend/app/Views/pages/member/favorit.php`

### 👤 Anggota 4 — Transaksi Pemesanan Tiket, E-Ticket & Sistem Ulasan
- **Backend (`backend/`)**:
  - `backend/app/Models/PemesananModel.php`, `backend/app/Models/UlasanModel.php`
  - `backend/app/Controllers/Api/PemesananApiController.php`, `backend/app/Controllers/Api/UlasanApiController.php`
- **Frontend (`frontend/`)**:
  - `frontend/app/Controllers/PemesananController.php`, `frontend/app/Controllers/UlasanController.php`
  - `frontend/app/Views/pages/member/pemesanan.php`, `frontend/app/Views/components/bagian_ulasan.php`

### 👤 Anggota 5 — Dasbor Admin & Manajemen Operasional (CRUD Wisata & Verifikasi Tiket)
- **Backend (`backend/`)**:
  - Endpoint Admin pada `backend/app/Controllers/Api/AdminApiController.php` (Statistik dasbor, tambah/edit/hapus destinasi, verifikasi status pemesanan).
- **Frontend (`frontend/`)**:
  - `frontend/app/Controllers/Admin/DasborController.php`, `DestinasiAdminController.php`, `PemesananAdminController.php`
  - `frontend/app/Views/pages/admin/*`
