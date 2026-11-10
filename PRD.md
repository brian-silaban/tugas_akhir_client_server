# 📋 PRD.md — Product Requirements Document (IndWalk)

Dokumen ini adalah acuan teknis tunggal (*Single Source of Truth*) untuk **5 anggota kelompok** dan seluruh **AI Coding Agent (Claude Code, CODEX, Antigravity)** dalam membangun aplikasi **IndWalk: Explore Indonesia Together**.

---

## 1. Spesifikasi Lingkungan & Teknologi
- **Framework Backend**: CodeIgniter 4 (PHP 8.2+)
- **Database Utama**: **PostgreSQL** (`DBDriver = Postgre`, Port `5432`)
- **Frontend**: View PHP CI4 + Tailwind CSS + Vanilla JS / Alpine.js + Lucide SVG Icons
- **Format Pertukaran Data Antar-Kelompok**: RESTful API (JSON over HTTP/HTTPS)

---

## 2. Daftar Role Pengguna & Hak Akses

| Role | Deskripsi | Hak Akses Fitur |
| :--- | :--- | :--- |
| **Guest** (Tanpa Login) | Pengunjung publik | Melihat Beranda, mencari & memfilter katalog wisata, melihat detail destinasi, melihat ulasan, dan melihat rekomendasi mitra dari REST API kelompok lain. |
| **Member** (`role = 'member'`) | Wisatawan terdaftar | Seluruh akses Guest + menyimpan destinasi ke **Wishlist**, membuat **Booking Tiket**, melihat **E-Ticket Saya**, membatalkan booking *pending*, dan menulis **Review & Rating**. |
| **Admin** (`role = 'admin'`) | Pengelola platform IndWalk | Mengakses Dashboard Admin, CRUD Kategori & Destinasi (+ Galeri Foto), memverifikasi status Booking (`pending` $\rightarrow$ `paid` / `cancelled`), menghapus ulasan spam, dan memantau/menguji koneksi REST API kelompok lain. |

---

## 3. Referensi Skema Database PostgreSQL (7 Tabel Inti)
Seluruh file migrasi sudah tersedia di [`app/Database/Migrations/`](app/Database/Migrations/) dan file SQL murni di [`app/Database/schema_postgresql.sql`](app/Database/schema_postgresql.sql):

1. **`users`**: `id` (PK), `name`, `email` (Unique), `password_hash`, `role` (`'admin'` | `'member'`), `avatar`, `created_at`, `updated_at`
2. **`categories`**: `id` (PK), `name`, `slug` (Unique), `description`, `created_at`, `updated_at`
3. **`destinations`**: `id` (PK), `category_id` (FK), `name`, `slug` (Unique), `city` (Indeks pencarian & kunci parameter REST API), `province`, `address`, `latitude`, `longitude`, `ticket_price`, `open_time`, `close_time`, `description`, `facilities` (JSON string), `thumbnail`, `rating_avg`, `review_count`, `created_at`, `updated_at`
4. **`destination_galleries`**: `id` (PK), `destination_id` (FK), `image_url`, `caption`, `created_at`
5. **`bookings`**: `id` (PK), `booking_code` (Unique, format `IND-YYYYMMDD-XXX`), `user_id` (FK), `destination_id` (FK), `visit_date`, `visitor_count`, `total_price`, `status` (`'pending'` | `'paid'` | `'cancelled'`), `created_at`, `updated_at`
6. **`reviews`**: `id` (PK), `user_id` (FK), `destination_id` (FK), `rating` (1–5), `comment`, `created_at`, `updated_at`
7. **`wishlists`**: `id` (PK), `user_id` (FK), `destination_id` (FK), `created_at` (Unique: `user_id + destination_id`)

---

## 4. Kontrak & Spesifikasi REST API (Inti Client-Server)

### A. IndWalk Sebagai API Provider (Server untuk Kelompok Lain)
Kelompok lain (Kuliner, Penginapan, Transportasi, dll.) dapat memanggil endpoint berikut:

#### 1. `GET /api/v1/destinations`
- **Query Params Opsional**:
  - `?city=Magelang` (Filter berdasarkan kota — *case-insensitive*)
  - `?category=cagar-budaya` (Filter berdasarkan slug kategori)
  - `?max_price=50000` (Filter batas harga tiket)
  - `?limit=10`
- **Contoh Response `200 OK`**:
  ```json
  {
    "status": "success",
    "provider": "IndWalk Tourism API",
    "count": 1,
    "data": [
      {
        "id": 1,
        "name": "Candi Borobudur",
        "slug": "candi-borobudur",
        "category": "Cagar Budaya",
        "city": "Magelang",
        "province": "Jawa Tengah",
        "address": "Jl. Badrawati, Kw. Candi Borobudur, Borobudur, Kab. Magelang",
        "coordinates": {
          "latitude": -7.6078738,
          "longitude": 110.2037513
        },
        "ticket_price": 50000,
        "operational_hours": "06:30 - 16:30",
        "rating_avg": 4.9,
        "review_count": 1420,
        "thumbnail": "https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80",
        "detail_url": "http://localhost:8080/destinations/candi-borobudur"
      }
    ]
  }
  ```

#### 2. `GET /api/v1/destinations/{slug_or_id}`
- Mengembalikan detail lengkap 1 destinasi beserta array `facilities`, `galleries`, dan `recent_reviews`.

#### 3. `GET /api/v1/categories`
- Mengembalikan daftar seluruh kategori wisata beserta jumlah destinasi di tiap kategori.

---

### B. IndWalk Sebagai API Consumer (Client yang Mengambil Data Kelompok Lain)
Di halaman Detail Destinasi (`/destinations/{slug}`), IndWalk memanggil service `App\Libraries\PartnerApiService` dengan parameter `city` (misal: `Magelang`, `Malang`, `Banyuwangi`):

1. **Desain Fleksibel Menghadapi Perubahan Tema Kelompok Lain**:
   - Konfigurasi endpoint kelompok mitra disimpan di `.env`:
     ```ini
     # Partner API Endpoints (Kosongkan atau set USE_PARTNER_MOCK=true jika API kelompok lain belum siap)
     PARTNER_API_MOCK_FALLBACK = true
     PARTNER_KULINER_URL       = "http://localhost:8081/api/kuliner"
     PARTNER_PENGINAPAN_URL    = "http://localhost:8082/api/penginapan"
     PARTNER_TRANSPORTASI_URL  = "http://localhost:8083/api/transportasi"
     ```
2. **Mekanisme Timeout & Fallback Otomatis**:
   - Setiap request HTTP (`CURLRequest`) dibatasi `timeout => 3` detik.
   - Jika server kelompok lain belum siap, mati, atau mengembalikan error, `PartnerApiService` otomatis mengembalikan data **Mock Fallback** berlabel `"source": "simulasi_fallback"` sehingga UI halaman Detail Wisata tetap menampilkan kartu rekomendasi dengan rapi.

---

## 5. Peta Routing & Halaman (`app/Config/Routes.php`)

### Rute Publik (Guest & Member)
- `GET /` $\rightarrow$ `HomeController::index` (Beranda: Hero Search, Bento Destinasi Unggulan, Kategori)
- `GET /destinations` $\rightarrow$ `DestinationController::index` (Katalog Eksplorasi + Filter Kota, Kategori, Harga, Sortir)
- `GET /destinations/(:segment)` $\rightarrow$ `DestinationController::show/$1` (Detail Wisata + Galeri + Ulasan + **Widget REST API Kelompok Lain**)

### Rute Autentikasi
- `GET /login` & `POST /login` $\rightarrow$ `AuthController::login` & `AuthController::attemptLogin`
- `GET /register` & `POST /register` $\rightarrow$ `AuthController::register` & `AuthController::attemptRegister`
- `GET /logout` $\rightarrow$ `AuthController::logout`

### Rute Member (Wajib Login)
- `POST /bookings` $\rightarrow$ `BookingController::store` (Buat pemesanan tiket baru)
- `GET /my-bookings` $\rightarrow$ `BookingController::index` (Daftar E-Ticket & riwayat pemesanan)
- `POST /my-bookings/(:num)/cancel` $\rightarrow$ `BookingController::cancel/$1` (Batalkan pesanan berstatus `pending`)
- `POST /reviews` $\rightarrow$ `ReviewController::store` (Kirim rating 1–5 & ulasan, lalu hitung ulang `rating_avg` destinasi)
- `POST /wishlists/toggle` $\rightarrow$ `WishlistController::toggle` (Tambah/hapus destinasi dari daftar simpanan)
- `GET /my-wishlists` $\rightarrow$ `WishlistController::index` (Daftar destinasi favorit saya)

### Rute Admin (`role = 'admin'`)
- `GET /admin` $\rightarrow$ `Admin\DashboardController::index` (Ringkasan metrik & status koneksi REST API Mitra)
- `GET /admin/destinations` $\rightarrow$ `Admin\DestinationAdminController::index` (CRUD Destinasi & Kategori)
- `GET /admin/bookings` $\rightarrow$ `Admin\BookingAdminController::index` (Verifikasi pembayaran tiket `pending` $\rightarrow$ `paid`)

### Rute REST API (`Api` Namespace)
- `GET /api/v1/destinations` $\rightarrow$ `Api\DestinationApiController::index`
- `GET /api/v1/destinations/(:segment)` $\rightarrow$ `Api\DestinationApiController::show/$1`
- `GET /api/v1/categories` $\rightarrow$ `Api\DestinationApiController::categories`

---

## 6. Pembagian Tugas 5 Anggota Kelompok (Bebas Konflik Git)

Agar 5 orang dapat bekerja paralel di GitHub tanpa saling menimpa file (*merge conflict*), pembagian kerja diatur per modul:

### 👤 Anggota 1 — Core Layout, Beranda & Katalog Destinasi
- **Tanggung Jawab**:
  - Menyusun master layout `app/Views/layout/main.php`, `navbar.php` (dengan logo IndWalk), dan `footer.php`.
  - Membangun halaman Beranda (`/`) dan Katalog Pencarian/Filter (`/destinations`) serta kerangka Halaman Detail (`/destinations/{slug}`).
- **File Miliknya**:
  - `app/Controllers/HomeController.php`, `app/Controllers/DestinationController.php`
  - `app/Models/DestinationModel.php`, `app/Models/CategoryModel.php`, `app/Models/DestinationGalleryModel.php`
  - `app/Views/layout/*`, `app/Views/pages/home.php`, `app/Views/pages/destinations/*`

### 👤 Anggota 2 — Spesialis Client-Server & Integrasi REST API
- **Tanggung Jawab**:
  - Membangun **REST API Server IndWalk** (`/api/v1/destinations`) agar bisa diakses kelompok lain.
  - Membangun **`PartnerApiService`** untuk mengambil data dari API kelompok Kuliner, Penginapan, dan Transportasi (lengkap dengan *timeout handling* & *Mock Fallback*).
  - Membuat komponen View bagian bawah halaman Detail Destinasi untuk menampilkan kartu data dari API kelompok lain.
- **File Miliknya**:
  - `app/Controllers/Api/DestinationApiController.php`
  - `app/Libraries/PartnerApiService.php`
  - `app/Views/components/partner_recommendations.php`

### 👤 Anggota 3 — Autentikasi, User Session, Role Filter & Wishlist
- **Tanggung Jawab**:
  - Membangun fitur Login, Register, Logout, dan Filter proteksi halaman (`AuthFilter` & `AdminFilter`).
  - Membangun fitur tambah/hapus **Wishlist** destinasi dan halaman `/my-wishlists`.
- **File Miliknya**:
  - `app/Controllers/AuthController.php`, `app/Controllers/WishlistController.php`
  - `app/Filters/AuthFilter.php`, `app/Filters/AdminFilter.php`
  - `app/Models/UserModel.php`, `app/Models/WishlistModel.php`
  - `app/Views/pages/auth/*`, `app/Views/pages/member/wishlists.php`

### 👤 Anggota 4 — Transaksi Booking Tiket, E-Ticket & Sistem Review
- **Tanggung Jawab**:
  - Membangun fitur form Booking Tiket di halaman detail, generator `booking_code` (`IND-YYYYMMDD-XXX`), dan halaman **E-Ticket Saya** (`/my-bookings`).
  - Membangun fitur input Rating (1–5) & Ulasan serta meng-update kolom `rating_avg` dan `review_count` otomatis di tabel `destinations`.
- **File Miliknya**:
  - `app/Controllers/BookingController.php`, `app/Controllers/ReviewController.php`
  - `app/Models/BookingModel.php`, `app/Models/ReviewModel.php`
  - `app/Views/pages/member/bookings.php`, `app/Views/components/review_section.php`

### 👤 Anggota 5 — Dashboard Admin & Manajemen Operasional
- **Tanggung Jawab**:
  - Membangun halaman Dashboard Admin (`/admin`) dengan kartu statistik nyata.
  - Membangun halaman CRUD Destinasi & Kategori (`/admin/destinations`) serta halaman Verifikasi Booking (`/admin/bookings`).
  - Membangun panel cek kesehatan endpoint REST API kelompok lain di Dashboard Admin.
- **File Miliknya**:
  - `app/Controllers/Admin/DashboardController.php`
  - `app/Controllers/Admin/DestinationAdminController.php`
  - `app/Controllers/Admin/BookingAdminController.php`
  - `app/Views/pages/admin/*`
