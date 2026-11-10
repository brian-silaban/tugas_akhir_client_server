# 🎯 GOAL.md — Visi & Tujuan Utama Projek IndWalk

> **Nama Platform**: **IndWalk** (*Explore Indonesia Together*)  
> **Logo Resmi**: `public/assets/IndWalk_ Explore Indonesia Together.png`  
> **Mata Kuliah**: Client-Server (Tugas Akhir Kelompok — 5 Orang)  
> **Tech Stack Utama**: CodeIgniter 4 (PHP 8.2+), PostgreSQL, Tailwind CSS

---

## 1. Mengapa IndWalk Dibuat? (Visi Produk)
**IndWalk** adalah platform direktori kurasi, eksplorasi, dan pemesanan tiket destinasi wisata Nusantara yang dirancang dengan pendekatan **Warm Organic & Editorial Travel** (setara *Airbnb*, *Indonesia.travel*, dan *Lonely Planet*).

Berbeda dengan website wisata generik yang hanya menampilkan foto dan teks promosi kosong, **IndWalk** mengutamakan **kepadatan informasi nyata (*High Information Density*)**:
- Transparansi harga tiket masuk dan jam operasional.
- Titik koordinat, kota/kabupaten, dan fasilitas riil di lapangan.
- Ulasan dan rating terverifikasi dari wisatawan.
- **Ekosistem Perjalanan Terpadu (Client-Server):** Menampilkan rekomendasi lintas-layanan (Kuliner/Rumah Makan, Penginapan, Transportasi, dll.) di sekitar lokasi wisata melalui integrasi **REST API** dengan sistem kelompok lain.

---

## 2. Dua Pilar Keberhasilan Projek (Target Nilai A)

### Pilar A — Arsitektur Client-Server yang Tangguh (Inti Penilaian Dosen)
Dalam mata kuliah Client-Server, aplikasi **IndWalk** wajib menjalankan dua peran sekaligus secara simultan:

1. **Sebagai REST API Provider (Server untuk Kelompok Lain):**
   - Menyediakan endpoint JSON publik yang bersih, konsisten, dan terdokumentasi (`/api/v1/destinations`) agar kelompok lain (seperti kelompok Penginapan, Kuliner, atau Transportasi) dapat mengambil daftar destinasi wisata berdasarkan kota (`?city=Magelang`), kategori, atau ID.
2. **Sebagai REST API Consumer (Client yang Mengambil Data Kelompok Lain):**
   - Saat pengguna membuka halaman **Detail Destinasi** di kota tertentu (misalnya *Banyuwangi*, *Magelang*, atau *Malang*), server IndWalk memanggil REST API milik kelompok lain untuk menampilkan:
     - 🍽️ **Kuliner / Rumah Makan Terdekat** (API Kelompok Kuliner)
     - 🏨 **Penginapan & Homestay Sekitar** (API Kelompok Penginapan)
     - 🚐 **Layanan Transportasi / Rute** (API Kelompok Transportasi)
3. **Strategi Antisipasi Perubahan / Keterlambatan Kelompok Lain (*Resilient Adapter + Mock Fallback*):**
   - Karena kelompok lain masih dalam tahap perencanaan awal dan berpotensi mengubah tema/struktur JSON, IndWalk menggunakan arsitektur **`PartnerApiService`** dengan fitur **Auto-Fallback & Mock Mode**:
     - Selama API kelompok lain belum siap (atau jika server mereka *down/timeout* saat presentasi), IndWalk otomatis menyajikan data *Mock JSON* lokal yang realistis sehingga halaman web **tidak pernah crash (Error 500)** dan tetap tampil utuh.
     - Jika ada kelompok yang berganti tema, kita cukup mengubah konfigurasi URL dan label kategori di `.env` / modul adapter tanpa mengubah struktur utama aplikasi.

---

### Pilar B — Kualitas Desain "Anti-AI-Slop" & Identitas Visual IndWalk
Seluruh tampilan antarmuka wajib mengikuti palet warna dari logo resmi **IndWalk** dan mematuhi aturan di [`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md):

| Token Visual | Kode Warna / Font | Penggunaan |
| :--- | :--- | :--- |
| **Background Utama** | `#FAF8F5` (`bg-stone-50`) | Latar halaman hangat (bukan putih silau `#FFFFFF`) |
| **IndWalk Ocean Teal** | `#0B6E77` (`teal-700` / `cyan-800`) | Warna brand dari kata **"Ind"** & ikon traveler pada logo |
| **IndWalk Forest Green** | `#2D5A36` (`emerald-900`) | Warna brand dari kata **"Walk"** & siluet gunung pada logo |
| **Highland Olive** | `#6A8E3F` (`lime-700`) | Aksen sekunder untuk badge kategori alam & lingkungan |
| **Terracotta CTA** | `#C2410C` (`orange-700`) | Tombol aksi utama (Pesan Tiket / Cari Destinasi) |
| **Warm Amber** | `#D97706` (`amber-600`) | Indikator bintang rating (`★ 4.9`) |
| **Tipografi** | *Playfair Display* / *Fraunces* + *Plus Jakarta Sans* | Kombinasi Serif Editorial untuk judul & Sans bersih untuk data/harga |

---

## 3. Definisi Selesai (*Definition of Done*)
Projek dinyatakan **100% siap kumpul dan siap demo** apabila:
1. Seluruh **7 tabel database** berjalan mulus di **PostgreSQL** melalui `php spark migrate` & `php spark db:seed IndWalkSeeder`.
2. Alur pengunjung dari **Cari Wisata $\rightarrow$ Lihat Detail (+ Data API Kelompok Lain) $\rightarrow$ Login $\rightarrow$ Booking Tiket $\rightarrow$ Lihat E-Ticket $\rightarrow$ Beri Review** berjalan tanpa error.
3. Endpoint REST API Server (`GET /api/v1/destinations`) mengembalikan JSON valid dengan kode HTTP `200 OK`.
4. Konsumsi REST API Client dari kelompok lain berjalan lancar, baik dalam mode **Live API** maupun **Fallback/Mock Mode** ketika server mitra mati.
5. Seluruh halaman lulus **10-Point Sloplint Checklist** di `.agents/rules/anti-ai-slop.md`.
