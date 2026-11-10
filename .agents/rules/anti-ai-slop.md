---
name: anti-ai-slop-design
description: Ultimate Anti-AI-Slop UI/UX Design Ruleset (disarikan dari Anthropic frontend-design, pbakaus/impeccable, Nutlope/hallmark sloplint, dan ui-ux-pro-max) khusus untuk Web Wisata.
trigger: always_on
---

# 🛡️ Ultimate Anti-AI-Slop UI/UX Ruleset (Tourism Web Edition)

Ruleset ini menggabungkan standar dari 4 repositori Anti-AI-Slop terbaik di dunia:
1. **`anthropics/skills` (`frontend-design`)** — *Aesthetic Direction & Subject-Matter Grounding*
2. **`pbakaus/impeccable`** — *The 5 Hallmark AI-Slop Anti-Patterns*
3. **`Nutlope/hallmark` (`sloplint`)** — *Macrostructures & 60+ Quality Gate Rules*
4. **`ui-ux-pro-max`** — *Intentional Design Tokens & Information Density*

---

## 1. The 5 Hallmark "AI Slop" Anti-Patterns (DILARANG KERAS)
*(Diadaptasi dari `pbakaus/impeccable`)*

AI yang dibiarkan tanpa aturan akan selalu jatuh ke probabilitas statistik yang sama. **Dilarang keras menghasilkan 5 pola ini:**

1. **🚫 Inter / Default Sans-Serif di Semua Elemen (`Overused Font Trap`)**
   - Jangan gunakan `Inter`, `Roboto`, atau `Arial` sekaligus untuk Heading, Body, dan Label tanpa kontras karakter tipografi.
2. **🚫 Gradien Ungu-ke-Biru / Neon SaaS (`Purple-to-Blue Gradient Cliché`)**
   - Dilarang keras memakai `bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500` pada teks judul, tombol utama, atau latar belakang hero. Web wisata bukan aplikasi crypto atau B2B AI SaaS.
3. **🚫 Kartu di Dalam Kartu di Dalam Kartu (`Endless Nested Cards`)**
   - Hindari membungkus setiap grup elemen kecil dengan `border rounded-xl p-6 shadow-md bg-white` di dalam kontainer yang juga berupa kartu. Gunakan *whitespace*, garis pembatas tipis (`divide-y divide-stone-200`), atau perubahan latar halus.
4. **🚫 Teks Abu-Abu Pudar & Kontras Rendah di Atas Foto (`Low-Contrast Typography`)**
   - Jangan menaruh `text-gray-400` di atas latar putih, atau teks putih telanjang di atas foto terang tanpa *scrim/gradient overlay* gelap (`bg-gradient-to-t from-black/80 via-black/30 to-transparent`).
5. **🚫 Grid 3-Kolom Ikon Kotak Bulat (`The Mechanical Icon Tile Grid`)**
   - Dilarang membuat section berisi 3 kartu identik yang masing-masing diawali kotak kecil `w-12 h-12 rounded-lg bg-emerald-500/10` berisi ikon Lucide acak + judul 2 kata + kalimat generik.

---

## 2. Macrostructure & Layout Rules (Anti-Template)
*(Diadaptasi dari `Nutlope/hallmark` & `anthropics/frontend-design`)*

Jangan gunakan urutan kerangka halaman yang selalu sama (*Centered Hero $\rightarrow$ 3-Card Feature Grid $\rightarrow$ Testimonial $\rightarrow$ CTA Banner*). Gunakan struktur yang sesuai dengan dunia **Pariwisata & Eksplorasi**:

- **Editorial Split atau Immersive Search Hero**:
  - Buka halaman dengan fotografi lanskap nyata yang kuat atau layout asimetris, langsung disertai **Interactive Search & Filter Bar** (Lokasi, Kategori Wisata, Rentang Budget) yang fungsional.
- **Asymmetric / Bento Destination Showcase**:
  - Jangan tampilkan 6 kartu dengan ukuran identik terus-menerus. Gunakan *Bento Grid* atau *Featured Editorial Highlight*: 1 destinasi unggulan berukuran `col-span-2 row-span-2` berdampingan dengan daftar destinasi kurasi.
- **Document-Led & Itinerary Storytelling**:
  - Untuk halaman detail wisata, gunakan struktur editorial seperti majalah perjalanan (*Monocle* / *Lonely Planet* / *Airbnb*): galeri foto proporsional di atas, diikuti kolom informasi utama di kiri (deskripsi naratif, fasilitas, itinerary/rundown, peta lokasi) dan *sticky info/booking card* di kanan.

---

## 3. Aesthetic Direction & Design Tokens (Khusus Web Wisata)
*(Diadaptasi dari `anthropics/frontend-design` & `ui-ux-pro-max`)*

### A. Tone & Persona Visual: *Warm Organic & Editorial Travel*
- **Base Surface (Background)**:
  - Gunakan *Warm Alabaster / Stone Off-White* (`#FAF8F5` atau `bg-stone-50`), **bukan** putih klinis `#FFFFFF` di seluruh layar atau dark-mode abu-abu kebiruan (`bg-slate-900`).
- **Elevated Surface (Card / Modal)**:
  - `#FFFFFF` murni dengan border tipis hangat (`border-stone-200/80`) tanpa drop-shadow tebal yang kotor.
- **Primary Brand Color**:
  - *Deep Forest Pine* (`#1B4332` / `emerald-900`) atau *Archipelago Ocean* (`#0F2C3F`).
- **Accent / Action Color**:
  - *Terracotta / Sunset Clay* (`#C2410C` / `orange-700`) untuk CTA penting atau *Warm Amber* (`#D97706`) khusus untuk indikator rating bintang.
- **Text Hierarchy**:
  - Heading: `#1C1917` (`stone-900`)
  - Body Copy: `#44403C` (`stone-700`) — nyaman dibaca panjang
  - Metadata / Caption: `#78716C` (`stone-500`)

### B. Tipografi Berkarakter (Font Pairing)
Pilih salah satu kombinasi Google Fonts berikut dan terapkan secara konsisten:
1. **Pasangan Editorial Klasik (Direkomendasikan untuk Wisata Budaya & Alam)**:
   - **Display / Heading (`h1`–`h3`)**: `'Playfair Display'`, `'Fraunces'`, atau `'Instrument Serif'` (`font-normal` atau `font-semibold`, `tracking-tight`, `leading-[1.1]`).
   - **UI / Body / Data**: `'Plus Jakarta Sans'` atau `'DM Sans'`.
2. **Pasangan Modern Nusantara**:
   - **Display / Heading**: `'Plus Jakarta Sans'` (`font-bold` / `font-extrabold`, `tracking-tight`).
   - **Body & Angka/Harga**: `'Inter'` atau `'DM Sans'` dengan `tabular-nums` untuk harga tiket dan rating.

---

## 4. Kepadatan Informasi Nyata (`High Information Density`)
Salah satu ciri paling mencolok dari UI buatan AI adalah **isi kartu yang kosong dan mengambang** (hanya judul + kalimat klise *"Nikmati keindahan alam yang memukau bersama keluarga"*).

**Setiap komponen Destinasi Wisata WAJIB memuat metadata spesifik & fungsional:**
- **Visual**: Foto nyata resolusi tinggi dengan aspect ratio konsisten (`aspect-[4/3]` atau `aspect-[16/10]`), efek transisi halus saat hover (`group-hover:scale-105 transition-transform duration-500 ease-out`).
- **Kategori Spesifik**: Badge tunggal yang fungsional (contoh: `Bahari`, `Cagar Budaya`, `Geopark`, `Kuliner Legendaris`).
- **Geolokasi Jelas**: Kabupaten/Kota atau Kecamatan (contoh: `Magelang, Jawa Tengah` atau `Ubud, Gianyar`).
- **Metrik Kepercayaan**: Rating desimal + jumlah ulasan dengan `tabular-nums` (contoh: `★ 4.8 (1.240)`).
- **Transparansi Harga**: Format mata uang Indonesia yang rapi (contoh: `Rp 25.000 / orang` atau `Gratis`).
- **Indikator Operasional (pada halaman detail)**: Jam buka (`07.00 – 17.00 WIB`), estimasi durasi kunjungan (`2–3 Jam`), serta daftar fasilitas nyata (Parkir, Musholla, Toilet, Akses Kursi Roda).

---

## 5. Checklist `Sloplint` (Wajib Diperiksa Sebelum Menulis Kode UI)
*(Diadaptasi dari `Nutlope/hallmark` `sloplint.mjs`)*

Sebelum menyelesaikan file View (`app/Views/...`), pastikan 10 poin ini **LULUS**:
1. **[ ] Tidak ada dua tombol Primary (filled) bersebelahan**: Jika ada 2 tombol aksi, satu harus *Primary* (solid fill) dan satu lagi *Secondary/Ghost* (outline atau teks).
2. **[ ] Tidak ada placeholder gradien kosong**: Jangan mengganti gambar destinasi dengan kotak gradien warna-warni; gunakan URL foto nyata dari Unsplash/lokal yang relevan.
3. **[ ] Tidak ada spam Emoji sebagai pengganti ikon UI**: Jangan menaruh emoji (🚀, ✨, 🔥, 🌟) di judul section atau tombol navigasi. Gunakan SVG inline bersih (Lucide / Heroicons dengan `stroke-width="1.75"`).
4. **[ ] Kontras teks di atas gambar terjamin**: Setiap teks di atas foto wajib dilapisi gradient overlay gelap (`from-black/75 via-black/25 to-transparent`) atau berada di dalam pill solid.
5. **[ ] Copywriting bebas bahasa promosi AI**: Hapus kata-kata klise seperti *"Solusi terdepan"*, *"Pengalaman tak terlupakan"*, *"Revolusioner"*, *"Jelajahi surga tersembunyi bersama platform kami"*. Gunakan bahasa informatif, deskriptif, dan jujur.
6. **[ ] Radius sudut konsisten**: Jangan mencampur `rounded-none`, `rounded-md`, dan `rounded-3xl` secara acak. Gunakan skala konsisten (misal: `rounded-2xl` untuk kontainer kartu luar, `rounded-xl` untuk gambar/elemen dalam, `rounded-lg` untuk tombol/input).
7. **[ ] Semua state interaktif terdefinisi**: Setiap tombol, link, input form, dan kartu yang bisa diklik wajib memiliki state `:hover`, `:focus-visible`, dan `:active` yang halus.
8. **[ ] Alignment & Grid disiplin**: Elemen dalam satu baris harus sejajar secara vertikal (`items-center` atau `items-baseline`), dan tinggi kartu dalam satu baris grid harus seragam (`flex flex-col justify-between h-full`).
9. **[ ] Angka menggunakan proporsi rapi**: Harga (`Rp 150.000`), rating (`4.9`), dan jam buka menggunakan `tabular-nums` agar sejajar dan mudah dipindai mata.
10. **[ ] Responsif tanpa overflow horizontal**: Pastikan layout di layar mobile (`375px`) tidak bocor ke samping (`overflow-x-hidden` pada wrapper bila perlu, dan grid menyusut ke `grid-cols-1`).
