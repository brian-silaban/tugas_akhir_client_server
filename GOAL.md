# 🎯 GOAL.md — Visi & Tujuan Utama Projek JejakSumut

> **Nama Platform**: **JejakSumut** (*Jelajahi Pesona Sumatera Utara*)  
> **Cakupan Wilayah**: Provinsi **Sumatera Utara** (Danau Toba, Samosir, Medan, Karo, Langkat, Tapanuli, Nias, Simalungun, Deli Serdang, dll.)  
> **Mata Kuliah**: Manajemen Database Client Server (`PLMKB502` — Politeknik Negeri Medan)  
> **Tech Stack Utama**: 2 Aplikasi CodeIgniter 4 Terpisah (`backend/` untuk REST API Server & `frontend/` untuk Client Web UI), PostgreSQL (Cloud VM Azure), Tailwind CSS

---

## 1. Mengapa JejakSumut Dibuat? (Visi Produk)
**JejakSumut** adalah platform direktori kurasi, eksplorasi, dan pemesanan tiket destinasi wisata **Sumatera Utara** yang dirancang dengan pendekatan **Warm Organic & Editorial Travel** (setara *Airbnb*, *Indonesia.travel*, dan *Lonely Planet*).

Berbeda dengan website wisata generik yang hanya menampilkan foto dan teks promosi kosong, **JejakSumut** mengutamakan **kepadatan informasi nyata (*High Information Density*)**:
- Transparansi harga tiket masuk dan jam operasional destinasi wisata di Sumatera Utara.
- Titik koordinat GPS, kabupaten/kota di Sumatera Utara (*Samosir, Toba, Karo, Langkat, Medan, Tapanuli Tengah, Nias Selatan, Simalungun*), dan fasilitas riil di lapangan.
- Ulasan dan rating terverifikasi dari wisatawan.
- **Ekosistem Perjalanan Terpadu Sumatera Utara (Client-Server):** Menampilkan rekomendasi lintas-layanan (**Penyedia Tur Guide**, **Kuliner/Rumah Makan**, **Penginapan**, dan **Transportasi**) di sekitar lokasi wisata Sumatera Utara melalui integrasi **REST API** dengan 4 kelompok mitra di kelas.

---

## 2. Dua Pilar Keberhasilan Projek (Target Skor 4 / Nilai A Sesuai `Form_Evaluasi_Proyek_MDCS.docx.pdf`)

### Pilar A — Arsitektur Client-Server Terpisah (`backend/` & `frontend/`)
Sesuai form evaluasi dosen pengampu (**Pak Donny Sanjaya, M.Kom.**), repositori ini memisahkan secara tegas antara **Database Server (VM Azure)**, **Projek Web API (`backend/`)**, dan **Projek Aplikasi Client (`frontend/`)** agar integrasi **REST API** terlihat nyata pada setiap fitur:

1. **Projek `backend/` — Murni REST API Server & Database PostgreSQL (Port `8080` / Cloud VM Azure):**
   - Satu-satunya aplikasi yang terhubung ke database **PostgreSQL** di VM Azure (bukan di localhost yang sama dengan client).
   - Tidak memiliki tampilan halaman web (`Views` HTML), melainkan murni melayani pertukaran data **JSON REST API**:
     - Menyediakan endpoint internal untuk aplikasi client kita (katalog destinasi Sumut, pencarian, autentikasi token, pemesanan tiket, ulasan, favorit, dan admin).
     - Menyediakan endpoint publik (`/api/v1/destinasi`) sebagai **REST API Provider** untuk diakses oleh 4 kelompok mitra di kelas (Penyedia Tur Guide, Kuliner/Rumah Makan, Penginapan, dan Transportasi) berdasarkan kabupaten/kota di Sumatera Utara (`?kota=Samosir`, `?kota=Medan`, `?kota=Karo`, dll.).
2. **Projek `frontend/` — Murni Client Web UI (Port `8081`):**
   - Tidak memiliki koneksi langsung ke database PostgreSQL.
   - Seluruh halaman web yang dilihat pengguna mengambil dan mengirim data melalui **HTTP Request (REST API JSON)** ke server `backend/` serta memanggil REST API milik 4 kelompok mitra (**REST API Consumer**):
     - 🧭 **Penyedia Tur Guide Lokal Sumut** (API Kelompok Tur Guide)
     - 🍽️ **Kuliner / Rumah Makan Khas Sumut** (API Kelompok Kuliner)
     - 🏨 **Penginapan & Homestay Sekitar** (API Kelompok Penginapan)
     - 🚐 **Layanan Transportasi / Rute Sumut** (API Kelompok Transportasi)
3. **Strategi Antisipasi Perubahan / Keterlambatan Kelompok Lain (*Resilient Adapter + Mock Fallback*):**
   - Karena kelompok lain masih dalam tahap pengembangan, `frontend/` menggunakan library **`LayananApiMitra`** dengan fitur **Auto-Fallback & Mock Mode**:
     - Selama API kelompok lain belum siap (atau jika server mereka *down/timeout* saat presentasi), aplikasi otomatis menyajikan data *Mock JSON* lokal Sumatera Utara yang realistis sehingga halaman web **tidak pernah crash (Error 500)** dan tetap tampil utuh.

---

### Pilar B — Kualitas Desain "Anti-AI-Slop" & Identitas Visual JejakSumut
Seluruh tampilan antarmuka di `frontend/` wajib mematuhi aturan di [`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md):

| Token Visual | Kode Warna / Font | Penggunaan |
| :--- | :--- | :--- |
| **Background Utama** | `#FAF8F5` / `#FFF8F5` (`bg-surface`) | Latar halaman hangat (bukan putih silau `#FFFFFF`) |
| **Toba Ocean Teal** | `#0B6E77` / `#00545B` (`primary`) | Warna utama terinspirasi danau kaldera Toba & pesisir Nias |
| **Leuser Forest Green** | `#2D5A36` / `#3B6843` (`secondary`) | Warna kedua terinspirasi rimba Taman Nasional Gunung Leuser & Bukit Barisan |
| **Highland Olive** | `#6A8E3F` (`lime-700`) | Aksen sekunder untuk badge kategori alam & ekowisata |
| **Terracotta CTA** | `#C2410C` / `#B53801` (`tertiary-container`) | Tombol aksi utama (Pesan Tiket / Cari Destinasi) |
| **Warm Amber** | `#D97706` (`amber-600`) | Indikator bintang rating (`★ 4.9`) |
| **Tipografi** | *Playfair Display* / *Fraunces* + *Plus Jakarta Sans* | Kombinasi Serif Editorial untuk judul & Sans bersih untuk data/harga |

---

## 3. Definisi Selesai (*Definition of Done* — Mengacu `Form_Evaluasi_Proyek_MDCS.docx.pdf`)
Projek dinyatakan **100% siap kumpul dan siap demo** apabila:
1. **K1 & K2 (Database Server — 20%):** Database PostgreSQL berjalan di server terpisah (VM Azure), memenuhi minimal **3NF**, memiliki constraint (`PK`, `FK`, `UNIQUE`, `CHECK`) & `INDEX`, memakai user database dengan *least privilege*, menerapkan **minimal 3 objek database (`VIEW`, `TRIGGER`, `STORED FUNCTION/PROCEDURE`)**, serta menerapkan transaksi ACID (`COMMIT` / `ROLLBACK`).
2. **K3, K4, K5 (Web API — 30%):** Endpoint RESTful konsisten (`/api/v1/...`), menerapkan autentikasi token (`401 Unauthorized` jika tanpa token & `403 Forbidden` jika role tidak sesuai), proteksi SQL Injection via Query Builder/Prepared Statements, serta validasi input (`422 Unprocessable Entity`) dengan format JSON seragam (`status`, `message`, `data`).
3. **K6 & K7 (Integrasi Client & Konkurensi — 25%):** Aplikasi Client menjalankan operasi CRUD melalui Web API (tanpa koneksi database langsung), data langsung sinkron, dan konflik update ditangani (*locking/versioning*).
4. **K8, K9, K10 (Presentasi, Demo, Dokumentasi & Showcase — 25%):** Seluruh kode mematuhi **Clean Code Berbahasa Indonesia**, dilengkapi koleksi Postman/Swagger, ERD, script SQL, serta diunggah ke Git repository dan `https://showcase.trpl.polmed.ac.id/`.
