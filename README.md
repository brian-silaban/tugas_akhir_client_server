# 🎒 IndWalk — Explore Indonesia Together
**Repo Projek Akhir Kelompok Mata Kuliah Client-Server (CodeIgniter 4 + PostgreSQL)**

<p align="center">
  <img src="public/assets/IndWalk_ Explore Indonesia Together.png" alt="Logo IndWalk" width="320">
</p>

> [!IMPORTANT]
> **KALIAN BACA DULU SEBENTAR INI WOK, SEDIKIT NYA INI BIAR PAHAM APA AJA ISINYA:**  
> Semua yang ada di repo ini (mulai dari nama web, isi tabel database, desain halaman, sampai pembagian tugas) **BUKAN PATOKAN MATI**. Ini sengaja kubuat untuk **pondasi awal** aja biar kita gak mulai dari awal. Kalau ada yang kurang sreg atau mau diubah, langsung ingfokan ke grup (tapi kalo bisa kita bicarakan lewat discord aja biar enak).

---

## 🖥️ Soal Landing Page yang Ada Sekarang
Kalau kalian nengok View, trus kalian ketik ini di cmd/terminal (`php spark serve`), bakal muncul **Landing Page statis**.
- **Kenapa dibuat?** Cuma buat *preview* kasar / gambaran awal aja cam mana bentuk web wisata kita nanti, sekaligus ngetes biar waktu kalian baru *clone* repo ini, webnya langsung bisa dibuka di browser **tanpa harus pusing setting PostgreSQL dulu**.
- **"Anjing burik kali kontol tampilan apa ini modal AI bah!"** Santai ya bujang, desain yang sekarang memang masih sementara. Nanti kita rombak bersamaan dengan hasil diskusi kelompok!

---

## ⚡ Cara Jalanin Projek di Laptop Masing-Masing

```bash
# 1. Clone repo ini (cukup sekali di awal)
git clone <url-repo-github-kita>
cd tugas_akhir_client_server
# Sebenarnya bisa langsung lewat VS Code untuk nge clone dari github tanpa ngetik kode clone ini. Cari di gugel caranya cam mana.

# 2. Copy file env dan ubah .env
cp env .env

# 3. Kalau vendor belum ada / error, jalankan:
composer install

# 4. Nyalakan server lokal
php spark serve
```
Buka **`http://localhost:8080`** di browser. Done!

---

## 🤖 "Kok banyak kali file md beserak kontol"
Karena aku asumsikan di kelompok kita ada yang pakai **Claude Code**, **CODEX**, dan **Antigravity** (ya walaupun aku tau kebanyakan make **Antigravity**), file-file ini sengaja kubuat sebagai **"otak bersama"** buat AI kita. Jadi pas kalian nyuruh AI bikin fitur, AI-nya langsung paham struktur projek kita dan nggak ngarang tabel atau desain sendiri:

| Nama File / Folder | Gunanya Buat Apa Mek? |
| :--- | :--- |
| **[`GOAL.md`](GOAL.md)** | Visi web **IndWalk**, warna brand dari logo, dan trik **Client-Server REST API** (biar web kita tetap aman pakai data simulasi kalau API kelompok lain belum jadi / mati pas demo). |
| **[`PRD.md`](PRD.md)** | Rencana fitur lengkap, daftar route URL, format JSON REST API buat dibagi ke kelompok lain, dan **usulan pembagian tugas 5 orang**. |
| **[`AGENTS.md`](AGENTS.md)** | Instruksi otomatis yang dibaca sama **Antigravity** & **CODEX** waktu kalian ngoding. |
| **[`CLAUDE.md`](CLAUDE.md)** | Instruksi otomatis khusus buat yang pakai **Claude Code**. |
| **[`.agents/`](.agents/)** | Folder berisi rules **`anti-ai-slop.md`** (biar desain UI bikinan AI nggak kelihatan pasaran/kaku) dan plugin **`ponytail`** (mode ngoding hemat & anti ribet). |

---

## 🐴 Cara Pakai Plugin `Ponytail` (Mode Ngoding Anti-Ribet)
Di dalam folder `.agents/plugins/ponytail` (Apa itu Ponytail? Cari di gugel), sudah kupasang plugin **Ponytail** (*Lazy Senior Dev Mode*). Gunanya buat maksa AI nulis kode yang **paling simpel, pendek, dan nggak kebanyakan gaya** (*over-engineering*), jadi kodenya gampang dibaca sama satu kelompok.

### Kapan Harus Dipakai & Kapan Jangan?
| Kerjaan yang Lagi Dibuat | Pakai Ponytail? | Alasannya |
| :--- | :---: | :--- |
| **Bikin Desain Visual / Tampilan UI Awal** | ❌ **Jangan Dulu** | Kalau dinyalain pas lagi mendesain halaman dari nol, AI-nya bakal terlalu "pelit" dan tampilannya jadi polosan kali. Biarkan AI fokus ikut aturan `anti-ai-slop.md` dulu. |
| **Ngoding Logika JS / Form di Frontend** | ✅ **Pakai (`/ponytail lite`)** | Biar AI nggak nyuruh *install* library JS yang aneh-aneh dan cukup pakai HTML/JS bawaan yang simpel. |
| **Ngoding Backend (Controller, Model, REST API)** | ✅ **Pakai (`/ponytail full`)** | Biar kodenya *to-the-point*, nggak bikin banyak file ribet yang nggak perlu, dan gampang kita pahami. |

### Cara Memanggilnya di Chat AI:
- **Memulai Ponytail:** Ketik **`/ponytail`** di chat (atau tulis *"pakai mode ponytail"*).
  - `/ponytail lite` $\rightarrow$ Tetap bikin sesuai permintaan, sambil ngasih tahu cara yang lebih simpel.
  - `/ponytail full` $\rightarrow$ *(Default)* Langsung pilih solusi paling ringkas & tolak fitur tambahan yang nggak diminta.
  - `/ponytail ultra` $\rightarrow$ Mode super irit baris kode.
- **Perintah Tambahan Ponytail (Opsional):**
  - `/ponytail-review` $\rightarrow$ Suruh AI ngecek kode kita sebelum di-*push* ke GitHub.
  - `/ponytail-debt` $\rightarrow$ Cari bagian kode yang kepanjangan/ribet buat diringkas.
- **Mematikan Ponytail (Balik ke Mode Normal):**
  - Cukup ketik **`stop ponytail`** atau **`normal mode`** di chat.

**Kalo gak mau pake Ponytail (misalnya karna kurang paham memakainya atau gak mau ribet) bilang aja ya wok.**

---

## 🗄️ Desain Database (PostgreSQL) & Alur Tabel
Contoh **7 tabel utama** sudah ku siapkan di folder **[`app/Database/`](app/Database/)**. Konsepnya simpel:
1. **`users`**: Data akun yang login (`admin` pengelola & `member` wisatawan).
2. **`categories`**: Kategori wisata (Bahari, Cagar Budaya, Pegunungan, dll).
3. **`destinations`**: Data tempat wisata. Ada kolom **`city`** (misal: *Magelang*, *Malang*) yang dipakai buat nyambungin **REST API kelompok lain** (Kuliner, Penginapan, Transportasi) di kota yang sama!
4. **`destination_galleries`**: Foto-foto tambahan tiap tempat wisata.
5. **`bookings`**: Data pemesanan tiket kunjungan dari member.
6. **`reviews`**: Rating bintang (1–5) & ulasan pengunjung.
7. **`wishlists`**: Daftar tempat wisata yang di-*bookmark* member.

```mermaid
erDiagram
    USERS ||--o{ BOOKINGS : "pesan tiket"
    USERS ||--o{ REVIEWS : "kasih ulasan"
    USERS ||--o{ WISHLISTS : "simpan favorit"
    CATEGORIES ||--o{ DESTINATIONS : "punya"
    DESTINATIONS ||--o{ DESTINATION_GALLERIES : "punya galeri"
    DESTINATIONS ||--o{ BOOKINGS : "dipesan di"
    DESTINATIONS ||--o{ REVIEWS : "diulas di"
    DESTINATIONS ||--o{ WISHLISTS : "disimpan di"

    USERS {
        int id PK
        string name
        string email UK
        string role "admin / member"
    }
    CATEGORIES {
        int id PK
        string name
        string slug UK
    }
    DESTINATIONS {
        int id PK
        int category_id FK
        string name
        string city "Kunci sambungan REST API"
        int ticket_price
        decimal rating_avg
    }
    DESTINATION_GALLERIES {
        int id PK
        int destination_id FK
        string image_url
    }
    BOOKINGS {
        int id PK
        string booking_code UK
        int user_id FK
        int destination_id FK
        date visit_date
        int total_price
        string status "pending / paid / cancelled"
    }
    REVIEWS {
        int id PK
        int user_id FK
        int destination_id FK
        smallint rating "1-5"
        text comment
    }
    WISHLISTS {
        int id PK
        int user_id FK
        int destination_id FK
    }
```

### Cara Import Database ke PostgreSQL Masing-Masing
Pertama, buat database kosong dulu di PostgreSQL kalian (misal namanya `indwalk_db`), lalu pilih **salah satu** cara paling gampang buat kalian:

#### Cara 1: Lewat Terminal/CMD CI4 (Sekaligus Isi Data Contoh Otomatis)
1. Buka file `.env`, cari bagian database, hapus tanda `#` di depannya, dan sesuaikan:
   ```ini
   database.default.hostname = localhost
   database.default.database = indwalk_db
   database.default.username = postgres
   database.default.password = password_postgres_kalian
   database.default.DBDriver = Postgre
   database.default.port     = 5432
   ```
2. Jalankan perintah ini di terminal/cmd:
   ```bash
   php spark migrate
   php spark db:seed IndWalkSeeder
   ```
   *(Otomatis langsung ke-buat 7 tabel + akun contoh + 6 data destinasi wisata!)*

#### Cara 2: Buat yang MALAS Setting CLI (Langsung via pgAdmin / DBeaver)
1. Buka **pgAdmin** atau **DBeaver**, pilih database `indwalk_db`.
2. Buka Query Tool, lalu *copy-paste* dan *run* isi file **[`app/Database/schema_postgresql.sql`](app/Database/schema_postgresql.sql)**. Langsung jadi ke-7 tabelnya!

---

## 🌿 Cheat Sheet Git Simpel (Biar Nggak Bentrok / Conflict Pas Push!)

Karena kita kerja **berlima**, ikuti **3 tips ini ya coy**  biar kode kita nggak ketimpa:

### 1. Selalu `git pull` Dulu SEBELUM Mulai Ngoding!
Jangan langsung ngoding di kode basi kemarin ya bujang. Tarik dulu update terbaru dari kawan yang lain:
```bash
git pull origin main
```

### 2. Pake Branch Sendiri (cari di gugel apa itu Branch)
Jangan keroyokan ngoding langsung di branch `main`. Bikin branch pakai namamu atau fiturmu:
```bash
# Bikin & pindah ke branch baru (cukup sekali pas mulai fitur)
git checkout -b fitur-booking

# Cek file apa aja yang kau ubah
git status

# Simpan & push ke GitHub
git add .
git commit -m "bikin halaman booking tiket"
git push origin fitur-booking
```
*(Habis itu tinggal digabung / Merge lewat Pull Request di GitHub).*

### 3. Kalau Mau Push Langsung ke `main`, Wajib Urutan Ini:
```bash
git add .
git commit -m "pesan commit singkat dan jelas"
git pull origin main   # <-- WAJIB tarik dulu sebelum push!
git push origin main
```
> 🚫 **Ingat ini mek:** Jangan pernah pake `git push -f` (*force push*) ke `main` karena itu bakal menghapus kerjaan kawan yang sudah masuk duluan!
