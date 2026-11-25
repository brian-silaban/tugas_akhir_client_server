# JejakSumut — Jelajahi Pesona Sumatera Utara

Proyek akhir Manajemen Database Client-Server menggunakan CodeIgniter 4 dan PostgreSQL. JejakSumut menyediakan katalog wisata, pemesanan tiket, ulasan, favorit, dan Panel Pengelola.

## Layanan daring

- Web Wisatawan: https://jejaksumut.vulnera.web.id
- REST API: https://api-jejaksumut.vulnera.web.id
- Showcase pengumpulan: https://showcase.trpl.polmed.ac.id/

## Arsitektur

Web Wisatawan dan Panel Pengelola menggunakan satu REST API yang terhubung ke PostgreSQL. Kedua client menggunakan kode `frontend/` dengan mode dan proses server terpisah. Hanya `backend/` yang mengakses database.

Alur komunikasi: **Client Wisatawan / Client Admin → HTTP REST API → PostgreSQL**.

Integrasi mitra mencakup layanan tur guide, kuliner, penginapan, dan transportasi. Data cadangan mitra berlabel simulasi harus dibedakan dari respons layanan aktif.

| Direktori | Tanggung jawab |
|---|---|
| `backend/` | REST API, autentikasi, validasi, transaksi, dan database |
| `frontend/` | Antarmuka client dan komunikasi melalui LayananApiBackend |
| `postman/` | Koleksi request, environment, dan dokumentasi API mitra |
| `.agents/rules/` | Standar kode Bahasa Indonesia dan desain antarmuka |

## Fitur utama

- Katalog dengan pencarian, filter, paginasi, dan detail destinasi.
- Registrasi, login, token bertanda tangan, dan favorit.
- Pemesanan, pembatalan, E-Ticket, serta ulasan wisatawan.
- Dasbor Admin, CRUD destinasi, filter pemesanan, verifikasi pembayaran, dan moderasi ulasan.
- Penolakan konflik edit destinasi melalui versi data dan penguncian transaksi.
- Pemantauan koneksi backend serta empat layanan mitra.

## Database PostgreSQL

Sepuluh tabel pada skema SQL: `kabupaten_kota`, `pengguna`, `kategori`, `destinasi`, `fasilitas`, `destinasi_fasilitas`, `galeri_destinasi`, `pemesanan`, `ulasan`, dan `favorit`.

Objek database meliputi:
- View `v_wisata_lengkap` dan `v_ringkasan_pemesanan`.
- Procedure `sp_verifikasi_pemesanan`.
- Function pembuatan kode pemesanan, penguncian destinasi, sinkronisasi rating, dan pembaruan waktu.
- Trigger sinkronisasi rating dan waktu perubahan.
- Role akses terbatas `jejaksumut_app_publik` dan `jejaksumut_app_admin`.

Referensi: [skema SQL](backend/app/Database/schema_postgresql.sql), [migrasi](backend/app/Database/Migrations/), dan [hak akses](backend/app/Database/setup_hak_akses_k2.sql).

## Endpoint utama

Seluruh jalur berikut menggunakan awalan `/api/v1`. Endpoint terlindungi memerlukan `Authorization: Bearer <token>`.

| Metode | Jalur | Kegunaan |
|---|---|---|
| GET | `/destinasi`, `/destinasi/{slug}`, `/kategori`, `/kabupaten-kota` | Katalog publik |
| POST | `/auth/daftar`, `/auth/masuk` | Registrasi dan login |
| GET / POST | `/favorit` / `/favorit/ubah` | Favorit pengguna |
| GET / POST | `/pemesanan` | Riwayat dan pembuatan tiket |
| GET | `/pemesanan/{id}` | Detail tiket |
| PATCH | `/pemesanan/{id}/batal` | Pembatalan oleh pemilik |
| GET / POST | `/ulasan` | Daftar dan pengiriman ulasan |
| GET | `/admin/statistik`, `/admin/pemesanan`, `/admin/ulasan` | Pengelolaan Admin |
| GET | `/admin/destinasi/{id}` | Data edit dan versi destinasi |
| POST | `/destinasi` | Tambah destinasi oleh Admin |
| PUT / DELETE | `/destinasi/{id}` | Edit dan hapus oleh Admin |
| PATCH | `/pemesanan/{id}/status` | Verifikasi oleh Admin |
| DELETE | `/ulasan/{id}` | Moderasi oleh Admin |

Parameter lengkap tersedia pada [koleksi Postman](postman/JejakSumut_API.postman_collection.json). Gunakan ID data yang tersedia dan token akun yang sesuai. GET ulasan bersifat publik; pengiriman ulasan memerlukan token.

## Persiapan instalasi lokal

1. Pasang Git, PHP 8.2 atau lebih baru, Composer, dan PostgreSQL. Aktifkan ekstensi PHP yang diperlukan, termasuk intl, mbstring, curl, dan pgsql.
2. Clone repository ini, lalu jalankan `composer install` masing-masing dari direktori `backend/` dan `frontend/`.
3. Salin file `env` menjadi `.env` pada kedua direktori jika belum ada. Jangan menimpa konfigurasi lokal yang sudah digunakan.
4. Buat database PostgreSQL untuk pengujian. Isi `database.default.hostname`, `database.default.port`, `database.default.database`, `database.default.username`, `database.default.password`, dan `database.default.DBDriver = Postgre` pada `backend/.env`.
5. Atur `app.baseURL` backend dan frontend sesuai port lokal. Arahkan `JEJAKSUMUT_BACKEND_API_URL` frontend ke API lokal dengan akhiran `/api/v1`.
6. Ikuti konfigurasi token, migrasi, seeding, dan pemisahan kedua client pada panduan demo di bawah. Gunakan kredensial pengujian; jangan memasukkan `.env`, kata sandi, atau token ke Git.

## Pembagian peran

| Anggota | Lingkup utama berdasarkan PRD |
|---|---|
| Joxtian | Beranda, katalog, dan detail destinasi |
| Brian | Integrasi backend dan mitra, dokumentasi API, deployment |
| Hafiz | Autentikasi, sesi, hak akses, dan favorit |
| Samuel | Pemesanan, E-Ticket, transaksi, dan ulasan |
| Andreas | Objek database, endpoint Admin, dan Panel Pengelola |

## Kontribusi dan dokumentasi

Baca [PRD](PRD.md), [AGENTS](AGENTS.md), serta aturan dalam `.agents/rules/` sebelum mengubah kode. Ambil pembaruan dengan `git pull --ff-only origin main`, uji perubahan, dan gunakan identitas Git sendiri. Koordinasikan file bersama sebelum push; jangan melakukan force push ke `main`.

Dokumen Showcase yang perlu disiapkan meliputi ERD, laporan, panduan instalasi, koleksi API, slide, dan bukti demo. Keberadaan source code tidak berarti seluruh dokumen sudah diunggah.

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
