# AGENTS.md — IndWalk: Explore Indonesia Together (Tugas Akhir Client-Server)

Dokumen ini adalah panduan utama (*Single Source of Truth*) untuk seluruh AI Coding Agent (**Antigravity**, **Claude Code**, dan **OpenAI CODEX**) yang bekerja di repositori ini.

---

## 0. Dokumen Acuan Wajib (Baca Sebelum Menulis Kode)
1. **[`GOAL.md`](GOAL.md)** — Visi produk **IndWalk**, arsitektur terpisah **Backend (`backend/`) & Frontend (`frontend/`)** via REST API + Mock Fallback, serta token warna resmi dari logo IndWalk.
2. **[`PRD.md`](PRD.md)** — Spesifikasi fitur lengkap, skema **7 tabel PostgreSQL berbahasa Indonesia**, kontrak JSON REST API, daftar routing untuk Backend & Frontend, dan pembagian modul untuk 5 anggota kelompok.
3. **[`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md)** — Ruleset wajib Anti-AI-Slop UI/UX Design & 10-Point Sloplint Checklist.
4. **[`.agents/rules/clean-code-indonesia.md`](.agents/rules/clean-code-indonesia.md)** — Ruleset wajib penulisan **Clean Code Berbahasa Indonesia** (nama variabel, *array key*, kolom database, fungsi buatan, dan komentar).

---

## 1. Konteks & Arsitektur Projek (Terpisah Backend & Frontend)
- **Nama Platform**: **IndWalk** (*Explore Indonesia Together*)
- **Logo**: `frontend/public/assets/IndWalk_ Explore Indonesia Together.png`
- **Tech Stack**:
  - **Backend (`backend/`)**: PHP 8.2+ dengan **CodeIgniter 4 (CI4)** khusus penyedia **REST API (JSON)** pada Port `8080`.
  - **Database (`backend/app/Database/`)**: **PostgreSQL** (`DBDriver = Postgre`, Port `5432`) — dapat dijalankan lokal atau terpusat di Cloud VM Azure.
  - **Frontend (`frontend/`)**: **CodeIgniter 4 (CI4)** khusus **Client Web UI** pada Port `8081` (Semantic HTML5, Tailwind CSS, Vanilla JS / Alpine.js, Material Symbols / SVG Icons). **Frontend dilarang mengakses database PostgreSQL secara langsung**; seluruh data diambil melalui HTTP Request ke REST API `backend/` dan REST API kelompok mitra.
- **Struktur Direktori Utama**:
  - **`backend/` (Server REST API & Database PostgreSQL)**:
    - `backend/app/Controllers/Api/` — Endpoint REST API JSON (`/api/v1/destinasi`, `/api/v1/kategori`, `/api/v1/pemesanan`, dll.)
    - `backend/app/Models/` — Query database PostgreSQL, `$allowedFields`, dan validasi data
    - `backend/app/Database/Migrations/` & `Seeds/IndWalkSeeder.php` — 7 tabel PostgreSQL (`pengguna`, `kategori`, `destinasi`, `galeri_destinasi`, `pemesanan`, `ulasan`, `favorit`) & data awal
    - `backend/app/Database/schema_postgresql.sql` — Skema DDL SQL murni untuk import cepat
  - **`frontend/` (Client Web Tampilan IndWalk)**:
    - `frontend/app/Controllers/` — Controller halaman web yang memanggil REST API Backend & Mitra lalu meneruskan data ke View
    - `frontend/app/Libraries/` — HTTP Client pemanggil REST API Backend (`LayananApiBackend.php`) & REST API kelompok lain (`LayananApiMitra.php` + Mock Fallback)
    - `frontend/app/Views/` — Template tampilan UI (`welcome_message.php`, `layout/`, `pages/`, `components/`)
    - `frontend/public/` — Document root & aset statis (`assets/`)

---

## 2. 🇮🇩 Wajib Patuhi: Clean Code Berbahasa Indonesia
Seluruh kode PHP dan JavaScript di dalam `backend/` maupun `frontend/` wajib mematuhi **[`.agents/rules/clean-code-indonesia.md`](.agents/rules/clean-code-indonesia.md)**:
1. **Penamaan Variabel (`$camelCase` Bahasa Indonesia)**:
   - Contoh: `$destinasiUtama`, `$daftarDestinasi`, `$daftarKategori`, `$filterPencarian`, `$totalHarga` (bukan `$heroDestination` atau `$catList`).
2. **Penamaan Array Key & Kolom Database (`snake_case` Bahasa Indonesia)**:
   - Contoh: `$destinasi['harga_tiket']`, `$destinasi['garis_lintang']`, `$destinasi['garis_bujur']`, `$destinasi['jam_buka']`, `$destinasi['rata_rating']`, `$destinasi['jumlah_ulasan']`.
3. **Penamaan Fungsi Buatan Sendiri (`camelCase` Bahasa Indonesia)**:
   - Contoh: `ambilDestinasiPopuler()`, `hitungTotalHarga()`, `ambilRekomendasiMitra()`. *(Method bawaan wajib CI4 seperti `index()`, `findAll()`, `esc()`, `base_url()` tetap sesuai bawaan framework).*
4. **Keterbacaan di Atas Baris Pendek (*Readability over Brevity*)**:
   - Dilarang memadatkan logika rumit ke dalam 1 baris (*nested ternary*). Pecah menjadi langkah-langkah jelas dengan komentar singkat Bahasa Indonesia agar mudah dipahami seluruh anggota kelompok.

---

## 3. 🎨 Wajib Patuhi: Ruleset Desain "Anti-AI-Slop" & Brand IndWalk
Seluruh pembuatan dan modifikasi tampilan antarmuka (`frontend/app/Views/**` dan `frontend/public/css/**`) **WAJIB** mematuhi aturan lengkap di:
👉 **[`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md)**

### Ringkasan Prinsip Utama:
1. **Aesthetic Persona (`anthropics/frontend-design`)**:
   - Gunakan gaya **Warm Organic & Editorial Travel** (seperti *Airbnb*, *Indonesia.travel*, *Lonely Planet*), **bukan** template landing page SaaS teknologi.
   - **Palet Warna Resmi IndWalk**:
     - Latar utama: *Warm Stone Off-White* (`#FAF8F5` / `#FFF8F5`)
     - Brand Primary 1 (Kata *"Ind"*): *Deep Ocean Teal* (`#0B6E77` / `#00545B`)
     - Brand Primary 2 (Kata *"Walk"*): *Forest Moss Green* (`#2D5A36` / `#3B6843`)
     - Aksen CTA: *Terracotta* (`#C2410C` / `#B53801`) & Rating *Warm Amber* (`#D97706`)
   - **Tipografi**: Heading Serif (*Playfair Display* / *Fraunces*) + Body Sans (*Plus Jakarta Sans* / *DM Sans*), dengan `tabular-nums` untuk harga dan rating.
2. **Larangan 5 Hallmark AI Slop (`pbakaus/impeccable`)**:
   - **DILARANG** memakai gradien ungu-ke-biru (`from-indigo-500 to-purple-600`).
   - **DILARANG** memakai font `Inter` sendirian untuk semua elemen tanpa hierarki karakter.
   - **DILARANG** membuat kartu di dalam kartu berlapis-lapis (*endless nested cards*).
   - **DILARANG** menaruh teks abu-abu pudar (`text-gray-400`) di atas latar putih atau teks tanpa overlay gelap di atas foto.
   - **DILARANG** membuat section 3 kartu identik berisi ikon di dalam kotak persegi bulat (*the mechanical icon tile grid*).
3. **Macrostructure & Sloplint Quality Gates (`Nutlope/hallmark`)**:
   - Variasikan struktur layout: gunakan *Immersive Search Hero* (dengan filter lokasi, kategori, harga), *Bento Grid* asimetris untuk destinasi unggulan, dan *Editorial Split Layout* (konten kiri + *sticky booking/info card* kanan) untuk halaman detail wisata.
   - Jangan pernah menaruh 2 tombol *Primary filled* bersebelahan, jangan memakai gradien kosong sebagai pengganti foto wisata, dan jangan memakai emoji (🚀, ✨, 🔥) sebagai pengganti ikon UI.

---

## 4. ⚙️ Standar Coding, Pemisahan Client-Server, PostgreSQL & Keamanan CI4
1. **Disiplin Pemisahan Backend vs Frontend**:
   - **`backend/`** hanya mengembalikan respons JSON (`$this->response->setJSON(...)` atau `ResponseTrait`), tidak me-render halaman HTML.
   - **`frontend/`** tidak boleh memiliki query SQL atau mengakses PostgreSQL langsung; seluruh pembacaan/penyimpanan data harus memanggil endpoint REST API di `backend/` menggunakan HTTP Client (`CURLRequest`).
2. **Kompatibilitas PostgreSQL (di `backend/`)**:
   - Jangan gunakan tipe `ENUM` inline di migrasi; gunakan `VARCHAR` dengan default yang jelas.
   - Jangan melakukan hardcode `id` saat `insertBatch` agar sequence `SERIAL` PostgreSQL tidak rusak.
3. **Keamanan Wajib (di `frontend/` & `backend/`)**:
   - **Anti-XSS**: Selalu bungkus output variabel dinamis di View dengan `esc()`, contoh: `<?= esc($destinasi['nama']) ?>`.
   - **Anti-CSRF**: Setiap `<form method="post">` wajib menyertakan `<?= csrf_field() ?>`.
   - **URL & Aset**: Gunakan `base_url()` atau `site_url()` untuk link internal.

---

## 5. Perintah Terminal (CLI)
```bash
# 1. Menjalankan Server Backend REST API (Port 8080) & Migrasi Database
cd backend
php spark migrate
php spark db:seed IndWalkSeeder
php spark serve --port 8080

# 2. Menjalankan Aplikasi Client Frontend Web (Port 8081)
cd frontend
php spark serve --port 8081
```
