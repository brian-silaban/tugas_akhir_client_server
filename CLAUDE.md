# CLAUDE.md — Instruksi Projek JejakSumut untuk Claude Code

Claude Code wajib membaca dan mematuhi dokumen spesifikasi, arsitektur, aturan Clean Code Bahasa Indonesia, serta ruleset desain Anti-AI-Slop berikut sebelum membuat atau memodifikasi kode:

- @AGENTS.md
- @GOAL.md
- @PRD.md
- @.agents/rules/clean-code-indonesia.md
- @.agents/rules/anti-ai-slop.md

## Catatan Penting untuk Claude Code
1. **Wajib Clean Code Berbahasa Indonesia**: Seluruh nama variabel (`$destinasiUtama`, `$daftarKategori`), *array key* (`'harga_tiket'`, `'garis_lintang'`, `'garis_bujur'`), nama tabel & kolom database, fungsi buatan sendiri, dan komentar kode wajib menggunakan Bahasa Indonesia sesuai `.agents/rules/clean-code-indonesia.md`.
2. **Nama Platform & Cakupan Wilayah**: **JejakSumut** (*Jelajahi Pesona Sumatera Utara*) — berfokus pada destinasi wisata di Provinsi **Sumatera Utara** dengan warna utama **Toba Ocean Teal** (`#0B6E77`), **Leuser Forest Green** (`#2D5A36`), latar **Warm Stone** (`#FAF8F5`), dan CTA **Terracotta** (`#C2410C`).
3. **Pemisahan Backend & Frontend**: `backend/` (Port `8080`) murni melayani JSON REST API & koneksi PostgreSQL; `frontend/` (Port `8081`) murni menampilkan Web UI tanpa koneksi langsung ke database.
4. **Anti-AI-Slop Check**: Saat membuat atau mengedit file View di `frontend/app/Views/`, wajib lulus **10-Point Sloplint Checklist** di `.agents/rules/anti-ai-slop.md`.
