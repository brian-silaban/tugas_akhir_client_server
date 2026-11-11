# CLAUDE.md — Instruksi Projek IndWalk untuk Claude Code

Claude Code wajib membaca dan mematuhi dokumen spesifikasi, arsitektur, aturan Clean Code Bahasa Indonesia, serta ruleset desain Anti-AI-Slop berikut sebelum membuat atau memodifikasi kode:

- @AGENTS.md
- @GOAL.md
- @PRD.md
- @.agents/rules/clean-code-indonesia.md
- @.agents/rules/anti-ai-slop.md

## Catatan Penting untuk Claude Code
1. **Wajib Clean Code Berbahasa Indonesia**: Seluruh nama variabel (`$destinasiUtama`, `$daftarKategori`), *array key* (`'harga_tiket'`, `'garis_lintang'`, `'garis_bujur'`), nama tabel & kolom database, fungsi buatan sendiri, dan komentar kode wajib menggunakan Bahasa Indonesia sesuai `.agents/rules/clean-code-indonesia.md`.
2. **Nama Platform & Identitas**: **IndWalk** (*Explore Indonesia Together*), logo di `public/assets/IndWalk_ Explore Indonesia Together.png` dengan warna utama **Deep Ocean Teal** (`#0B6E77`), **Forest Moss Green** (`#2D5A36`), latar **Warm Stone** (`#FAF8F5`), dan CTA **Terracotta** (`#C2410C`).
3. **Database PostgreSQL**: Gunakan 7 tabel berbahasa Indonesia (`pengguna`, `kategori`, `destinasi`, `galeri_destinasi`, `pemesanan`, `ulasan`, `favorit`) yang sudah didefinisikan di `PRD.md` dan `app/Database/Migrations/`.
4. **Anti-AI-Slop Check**: Saat membuat atau mengedit file View di `app/Views/`, wajib lulus **10-Point Sloplint Checklist** di `.agents/rules/anti-ai-slop.md`.
