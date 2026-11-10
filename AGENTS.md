# AGENTS.md — IndWalk: Explore Indonesia Together (Tugas Akhir Client-Server)

Dokumen ini adalah panduan utama (*Single Source of Truth*) untuk seluruh AI Coding Agent (**Antigravity**, **Claude Code**, dan **OpenAI CODEX**) yang bekerja di repositori ini.

---

## 0. Dokumen Acuan Wajib (Baca Sebelum Menulis Kode)
1. **[`GOAL.md`](GOAL.md)** — Visi produk **IndWalk**, arsitektur Client-Server (API Provider & Consumer + Mock Fallback), dan token warna resmi dari logo IndWalk.
2. **[`PRD.md`](PRD.md)** — Spesifikasi fitur lengkap, skema **7 tabel PostgreSQL**, kontrak JSON REST API, daftar routing, dan pembagian modul untuk 5 anggota kelompok.
3. **[`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md)** — Ruleset wajib Anti-AI-Slop UI/UX Design & 10-Point Sloplint Checklist.

---

## 1. Konteks & Arsitektur Projek
- **Nama Platform**: **IndWalk** (*Explore Indonesia Together*)
- **Logo**: `public/assets/IndWalk_ Explore Indonesia Together.png`
- **Tech Stack**:
  - **Backend**: PHP 8.2+ dengan framework **CodeIgniter 4 (CI4)**
  - **Database**: **PostgreSQL** (`DBDriver = Postgre`, Port `5432`)
  - **Frontend**: Semantic HTML5, Tailwind CSS, Vanilla JS / Alpine.js, SVG Icons (Lucide)
- **Struktur Direktori Utama**:
  - `app/Controllers/` — Orchestrator request & response (`Api/`, `Admin/`, dan publik)
  - `app/Models/` — Query database, `$allowedFields`, dan validasi data
  - `app/Libraries/PartnerApiService.php` — Client HTTP pemanggil REST API kelompok lain + Mock Fallback
  - `app/Database/Migrations/` & `Seeds/IndWalkSeeder.php` — 7 tabel PostgreSQL & data awal
  - `app/Views/` — Template tampilan UI (`layout/`, `pages/`, `components/`)
  - `public/` — Document root & aset statis (`assets/`, `css/`, `js/`)

---

## 2. 🎨 Wajib Patuhi: Ruleset Desain "Anti-AI-Slop" & Brand IndWalk
Seluruh pembuatan dan modifikasi tampilan antarmuka (`app/Views/**` dan `public/css/**`) **WAJIB** mematuhi aturan lengkap di:
👉 **[`.agents/rules/anti-ai-slop.md`](.agents/rules/anti-ai-slop.md)**

### Ringkasan Prinsip Utama:
1. **Aesthetic Persona (`anthropics/frontend-design`)**:
   - Gunakan gaya **Warm Organic & Editorial Travel** (seperti *Airbnb*, *Indonesia.travel*, *Lonely Planet*), **bukan** template landing page SaaS teknologi.
   - **Palet Warna Resmi IndWalk**:
     - Latar utama: *Warm Stone Off-White* (`#FAF8F5` / `bg-stone-50`)
     - Brand Primary 1 (Kata *"Ind"*): *Deep Ocean Teal* (`#0B6E77` / `teal-700`)
     - Brand Primary 2 (Kata *"Walk"*): *Forest Moss Green* (`#2D5A36` / `emerald-900`)
     - Aksen CTA: *Terracotta* (`#C2410C` / `orange-700`) & Rating *Warm Amber* (`#D97706`)
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

## 3. ⚙️ Standar Coding, PostgreSQL & Keamanan CodeIgniter 4
1. **Kompatibilitas PostgreSQL**:
   - Jangan gunakan tipe `ENUM` inline di migrasi; gunakan `VARCHAR` dengan default yang jelas.
   - Jangan melakukan hardcode `id` saat `insertBatch` agar sequence `SERIAL` PostgreSQL tidak rusak.
2. **MVC yang Bersih**:
   - Jangan menulis query SQL mentah di dalam View atau Controller. Tempatkan logika query di `app/Models/` dan logika HTTP client di `app/Libraries/`.
   - Gunakan layout inheritance CI4 (`$this->extend('layout/main')` dan `$this->section('content')`).
3. **Keamanan Wajib**:
   - **Anti-XSS**: Selalu bungkus output variabel dinamis di View dengan `esc()`, contoh: `<?= esc($destination['name']) ?>`.
   - **Anti-CSRF**: Setiap `<form method="post">` wajib menyertakan `<?= csrf_field() ?>`.
   - **URL & Aset**: Gunakan `base_url()` atau `site_url()` untuk link internal dan pemanggilan aset statis.

---

## 4. Perintah Terminal (CLI)
```bash
# Menjalankan development server lokal
php spark serve

# Menjalankan migrasi & seeder database (PostgreSQL)
php spark migrate
php spark db:seed IndWalkSeeder

# Rollback & refresh migrasi
php spark migrate:refresh
```
