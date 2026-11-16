# 🎯 GOAL.md — Visi & Tujuan Utama Projek IndWalk

> **Nama Platform**: **IndWalk** (*Explore Indonesia Together*)  
> **Logo Resmi**: `frontend/public/assets/IndWalk_ Explore Indonesia Together.png`  
> **Mata Kuliah**: Client-Server (Tugas Akhir Kelompok — 5 Orang)  
> **Tech Stack Utama**: 2 Aplikasi CodeIgniter 4 Terpisah (`backend/` untuk REST API Server & `frontend/` untuk Client Web UI), PostgreSQL, Tailwind CSS

---

## 1. Mengapa IndWalk Dibuat? (Visi Produk)
**IndWalk** adalah platform direktori kurasi, eksplorasi, dan pemesanan tiket destinasi wisata Nusantara yang dirancang dengan pendekatan **Warm Organic & Editorial Travel** (setara *Airbnb*, *Indonesia.travel*, dan *Lonely Planet*).

Berbeda dengan website wisata generik yang hanya menampilkan foto dan teks promosi kosong, **IndWalk** mengutamakan **kepadatan informasi nyata (*High Information Density*)**:
- Transparansi harga tiket masuk dan jam operasional.
- Titik koordinat, kota/kabupaten, dan fasilitas riil di lapangan.
- Ulasan dan rating terverifikasi dari wisatawan.
- **Ekosistem Perjalanan Terpadu (Client-Server):** Menampilkan rekomendasi lintas-layanan (**Penyedia Tur Guide**, **Kuliner/Rumah Makan**, **Penginapan**, dan **Transportasi**) di sekitar lokasi wisata melalui integrasi **REST API** dengan 4 kelompok mitra di kelas.

---

## 2. Dua Pilar Keberhasilan Projek (Target Nilai A)

### Pilar A — Arsitektur Client-Server Terpisah (`backend/` & `frontend/`)
Sesuai arahan dosen pengampu mata kuliah **Client-Server**, repositori ini memisahkan secara tegas antara **Projek Backend (`backend/`)** dan **Projek Frontend (`frontend/`)** agar integrasi **REST API** terlihat nyata pada setiap fitur:

1. **Projek `backend/` — Murni REST API Server & Database PostgreSQL (Port `8080` / Cloud VM Azure):**
   - Satu-satunya aplikasi yang terhubung ke database **PostgreSQL** (`indwalk_db`).
   - Tidak memiliki tampilan halaman web (`Views` HTML), melainkan murni melayani pertukaran data **JSON REST API**:
     - Menyediakan endpoint internal untuk aplikasi `frontend/` kita sendiri (katalog destinasi, pencarian, autentikasi, pemesanan tiket, ulasan, favorit, dan admin).
     - Menyediakan endpoint publik (`/api/v1/destinasi`) sebagai **REST API Provider** untuk diakses oleh 4 kelompok mitra di kelas (Penyedia Tur Guide, Kuliner/Rumah Makan, Penginapan, dan Transportasi) berdasarkan kota (`?kota=Magelang`) atau kategori.
2. **Projek `frontend/` — Murni Client Web UI (Port `8081`):**
   - Tidak memiliki koneksi langsung ke database PostgreSQL.
   - Seluruh halaman web yang dilihat pengguna mengambil dan mengirim data melalui **HTTP Request (REST API JSON)** ke server `backend/` serta memanggil REST API milik 4 kelompok mitra (**REST API Consumer**):
     - 🧭 **Penyedia Tur Guide Lokal** (API Kelompok Tur Guide)
     - 🍽️ **Kuliner / Rumah Makan Terdekat** (API Kelompok Kuliner)
     - 🏨 **Penginapan & Homestay Sekitar** (API Kelompok Penginapan)
     - 🚐 **Layanan Transportasi / Rute** (API Kelompok Transportasi)
3. **Strategi Antisipasi Perubahan / Keterlambatan Kelompok Lain (*Resilient Adapter + Mock Fallback*):**
   - Karena kelompok lain masih dalam tahap perencanaan awal dan berpotensi mengubah tema/struktur JSON, `frontend/` menggunakan library **`LayananApiMitra`** dengan fitur **Auto-Fallback & Mock Mode**:
     - Selama API kelompok lain belum siap (atau jika server mereka *down/timeout* saat presentasi), aplikasi otomatis menyajikan data *Mock JSON* lokal yang realistis sehingga halaman web **tidak pernah crash (Error 500)** dan tetap tampil utuh.

---

### Pilar B — Kualitas Desain "Anti-AI-Slop" & Identitas Visual IndWalk
Seluruh tampilan antarmuka di `frontend/` wajib mengikuti palet warna dari logo resmi **IndWalk** dan mematuhi aturan di [`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md):

| Token Visual | Kode Warna / Font | Penggunaan |
| :--- | :--- | :--- |
| **Background Utama** | `#FAF8F5` / `#FFF8F5` (`bg-surface`) | Latar halaman hangat (bukan putih silau `#FFFFFF`) |
| **IndWalk Ocean Teal** | `#0B6E77` / `#00545B` (`primary`) | Warna brand dari kata **"Ind"** & ikon traveler pada logo |
| **IndWalk Forest Green** | `#2D5A36` / `#3B6843` (`secondary`) | Warna brand dari kata **"Walk"** & siluet gunung pada logo |
| **Highland Olive** | `#6A8E3F` (`lime-700`) | Aksen sekunder untuk badge kategori alam & lingkungan |
| **Terracotta CTA** | `#C2410C` / `#B53801` (`tertiary-container`) | Tombol aksi utama (Pesan Tiket / Cari Destinasi) |
| **Warm Amber** | `#D97706` (`amber-600`) | Indikator bintang rating (`★ 4.9`) |
| **Tipografi** | *Playfair Display* / *Fraunces* + *Plus Jakarta Sans* | Kombinasi Serif Editorial untuk judul & Sans bersih untuk data/harga |

---

## 3. Definisi Selesai (*Definition of Done*)
Projek dinyatakan **100% siap kumpul dan siap demo** apabila:
1. Seluruh **7 tabel database** di `backend/` berjalan mulus di **PostgreSQL** (lokal atau Cloud VM Azure) melalui `php spark migrate` & `php spark db:seed IndWalkSeeder`.
2. Server `backend/` melayani seluruh request REST API dalam format JSON dengan kode HTTP yang tepat (`200 OK`, `201 Created`, `400 Bad Request`, `404 Not Found`).
3. Aplikasi `frontend/` menampilkan antarmuka web dengan mengambil data dari REST API `backend/` dan REST API kelompok lain (baik mode **Live API** maupun **Fallback/Mock Mode**).
4. Seluruh kode mematuhi aturan **Clean Code Berbahasa Indonesia** ([`.agents/rules/clean-code-indonesia.md`](.agents/rules/clean-code-indonesia.md)) dan **10-Point Sloplint Checklist** ([`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md)).
