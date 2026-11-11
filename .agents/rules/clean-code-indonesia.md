---
name: clean-code-indonesia
description: Standar wajib penulisan Clean Code berbahasa Indonesia (nama variabel, array key, kolom database, fungsi, dan komentar) yang mudah dibaca dan dipahami seluruh anggota kelompok.
trigger: always_on
---

# 🇮🇩 Aturan Wajib: Clean Code Berbahasa Indonesia (Standar Kelompok IndWalk)

Seluruh kode yang ditulis di repositori ini **WAJIB** menggunakan **Bahasa Indonesia** dan mengutamakan **keterbacaan (*readability*)** agar seluruh 5 anggota kelompok dapat memahami, memodifikasi, dan menjelaskan alur kode dengan lancar saat presentasi/sidang dosen.

---

## 1. Standar Penamaan Bahasa Indonesia (`Naming Convention`)

### A. Nama Variabel PHP & JavaScript (`$camelCase` Bahasa Indonesia)
Gunakan nama variabel deskriptif dalam Bahasa Indonesia, **bukan** Bahasa Inggris:
- ❌ **SALAH (Inggris / Singkatan Tidak Jelas):**
  `$heroDestination`, `$featuredDestinations`, `$catList`, `$usr`, `$res`, `$qty`
- ✅ **BENAR (Bahasa Indonesia Jelas):**
  `$destinasiUtama`, `$destinasiUnggulan`, `$daftarKategori`, `$dataPengguna`, `$hasilPencarian`, `$jumlahPengunjung`

### B. Nama Array Key & Kolom Database (`snake_case` Bahasa Indonesia)
Seluruh *array key* pada Controller, Model, dan View **WAJIB** memakai Bahasa Indonesia dan selaras dengan nama kolom di tabel PostgreSQL:
- ❌ **SALAH:**
  `$item['ticket_price']`, `$item['latitude']`, `$item['longitude']`, `$item['open_time']`, `$item['rating_avg']`, `$item['review_count']`
- ✅ **BENAR:**
  `$item['harga_tiket']`, `$item['garis_lintang']`, `$item['garis_bujur']`, `$item['jam_buka']`, `$item['rata_rating']`, `$item['jumlah_ulasan']`

### C. Nama Fungsi / Method Buatan Sendiri (`camelCase` Bahasa Indonesia)
Untuk fungsi atau method buatan sendiri di dalam Controller, Model, atau Library:
- ❌ **SALAH:** `getPopularDestinations()`, `calculateTotalPrice()`, `fetchPartnerData()`
- ✅ **BENAR:** `ambilDestinasiPopuler()`, `hitungTotalHarga()`, `ambilDataMitra()`
- *(Catatan: Method bawaan wajib CodeIgniter 4 seperti `index()`, `findAll()`, `where()`, `insert()`, `esc()`, `base_url()`, serta properti bawaan CI4 seperti `protected $table`, `protected $primaryKey`, `protected $allowedFields` tetap menggunakan nama asli bawaan framework agar CI4 berjalan normal).*

---

## 2. Prinsip Clean Code Ramah Mahasiswa (Bukan Kode "Pintar" yang Membingungkan)

1. **Keterbacaan Lebih Penting daripada Baris Pendek (*Readability over Brevity*):**
   - **DILARANG** memadatkan logika kompleks ke dalam 1 baris panjang (*nested ternary* `? : ? :` atau rantai `array_map`/`array_reduce` berlapis).
   - Pecah proses menjadi langkah-langkah berurutan dengan variabel penjelas yang mudah diikuti mata.
   - **Contoh Buruk (Susah Dibaca):**
     ```php
     $label = $d['harga_tiket'] > 0 ? 'Rp ' . number_format($d['harga_tiket'], 0, ',', '.') . ($d['diskon'] ? ' (Promo)' : '') : 'Gratis';
     ```
   - **Contoh Baik (Clean Code):**
     ```php
     // Format tampilan harga tiket agar mudah dibaca pengunjung
     if ($destinasi['harga_tiket'] > 0) {
         $hargaTerformat = 'Rp ' . number_format($destinasi['harga_tiket'], 0, ',', '.');
     } else {
         $hargaTerformat = 'Gratis';
     }
     ```

2. **Komentar Penjelas Berbahasa Indonesia:**
   - Berikan komentar singkat 1 baris (`// ...`) dalam Bahasa Indonesia di atas blok logika penting, perhitungan harga, filter pencarian, atau pemanggilan REST API.
   - Tujuannya agar setiap anggota kelompok langsung mengerti fungsi blok kode tersebut dalam sekali baca.

3. **Anti Over-Engineering (Tetap Sederhana & Lurus):**
   - Gunakan arsitektur standar MVC CodeIgniter 4 (`Controller` $\rightarrow$ `Model` $\rightarrow$ `View`).
   - Jangan membuat *Design Pattern* berlapis-lapis (seperti *Repository*, *DTO*, *Factory*, *Trait* abstrak) atau meng-install library tambahan yang tidak diminta.
   - Satu fungsi hanya mengerjakan satu tugas yang jelas (*Single Responsibility*).
