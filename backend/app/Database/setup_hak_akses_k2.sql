-- ============================================================================
-- JejakSumut — Pengaturan User Database & Least Privilege (Rubrik K2)
--
-- Tujuan:
-- Memisahkan akun database berdasarkan peran aplikasi sehingga aplikasi web
-- TIDAK berjalan memakai akun superuser ('postgres') atau owner DDL.
--
-- Cara Menjalankan (sebagai superuser postgres setelah migrasi selesai):
--   sudo -u postgres psql -d jejaksumut_db -f backend/app/Database/setup_hak_akses_k2.sql
-- (Ganti kata sandi di bawah sebelum dieksekusi di VM Azure)
-- ============================================================================

-- 1. Membuat Role Aplikasi secara idempotent
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'jejaksumut_app_publik') THEN
        CREATE USER jejaksumut_app_publik WITH PASSWORD 'passwordpublik123!';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'jejaksumut_app_admin') THEN
        CREATE USER jejaksumut_app_admin WITH PASSWORD 'passwordadmin123!';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'jejaksumut_mitra_readonly') THEN
        CREATE USER jejaksumut_mitra_readonly WITH PASSWORD 'passwordmitra123!';
    END IF;
END $$;

-- 2. Kunci schema public agar role non-owner tidak dapat membuat objek/tabel sembarangan (DDL)
REVOKE CREATE ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO jejaksumut_app_publik, jejaksumut_app_admin, jejaksumut_mitra_readonly;

-- Reset seluruh hak akses sebelumnya agar bersih sesuai prinsip Least Privilege
REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA public FROM jejaksumut_app_publik, jejaksumut_app_admin, jejaksumut_mitra_readonly;
REVOKE ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public FROM jejaksumut_app_publik, jejaksumut_app_admin, jejaksumut_mitra_readonly;
REVOKE ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public FROM jejaksumut_app_publik, jejaksumut_app_admin, jejaksumut_mitra_readonly;
REVOKE ALL PRIVILEGES ON ALL PROCEDURES IN SCHEMA public FROM jejaksumut_app_publik, jejaksumut_app_admin, jejaksumut_mitra_readonly;

-- ============================================================================
-- A. ROLE 1: jejaksumut_app_publik (Aplikasi Client 1 — Pengunjung & Member)
--    - Hanya READ (SELECT) pada data master wisata & view.
--    - Tidak dapat mengubah/menghapus destinasi, kategori, atau kabupaten_kota.
--    - Tidak memiliki hak DELETE pada tabel pemesanan (pembatalan memakai UPDATE status)
--      demi menjaga jejak audit transaksi.
--    - Pembaruan rata_rating destinasi tetap berjalan aman lewat trigger SECURITY DEFINER.
-- ============================================================================
GRANT SELECT ON
    kabupaten_kota,
    kategori,
    destinasi,
    fasilitas,
    destinasi_fasilitas,
    galeri_destinasi,
    v_wisata_lengkap,
    v_ringkasan_pemesanan
TO jejaksumut_app_publik;

GRANT SELECT, INSERT, UPDATE, DELETE ON pengguna, ulasan, favorit TO jejaksumut_app_publik;
GRANT SELECT, INSERT, UPDATE ON pemesanan TO jejaksumut_app_publik;

GRANT USAGE, SELECT ON SEQUENCE
    pengguna_id_seq,
    pemesanan_id_seq,
    ulasan_id_seq,
    favorit_id_seq
TO jejaksumut_app_publik;

GRANT EXECUTE ON FUNCTION fn_kunci_destinasi_pemesanan(INTEGER) TO jejaksumut_app_publik;
GRANT EXECUTE ON FUNCTION fn_buat_kode_pemesanan(DATE) TO jejaksumut_app_publik;
GRANT EXECUTE ON PROCEDURE sp_verifikasi_pemesanan(INTEGER, VARCHAR) TO jejaksumut_app_publik;

-- ============================================================================
-- B. ROLE 2: jejaksumut_app_admin (Aplikasi Client 2 — Panel Admin Pengelola)
--    - CRUD penuh pada data master wisata (kategori, destinasi, fasilitas, galeri).
--    - Verifikasi pemesanan (SELECT, UPDATE) lewat Stored Procedure sp_verifikasi_pemesanan.
--    - Moderasi ulasan spam (SELECT, DELETE).
--    - Tetap TIDAK memiliki hak DDL (DROP, ALTER, TRUNCATE).
-- ============================================================================
GRANT SELECT ON kabupaten_kota, pengguna, v_wisata_lengkap, v_ringkasan_pemesanan TO jejaksumut_app_admin;
GRANT SELECT, INSERT, UPDATE, DELETE ON
    kategori,
    destinasi,
    fasilitas,
    destinasi_fasilitas,
    galeri_destinasi
TO jejaksumut_app_admin;

GRANT SELECT, UPDATE ON pemesanan TO jejaksumut_app_admin;
GRANT SELECT, DELETE ON ulasan TO jejaksumut_app_admin;

GRANT USAGE, SELECT ON SEQUENCE
    kategori_id_seq,
    destinasi_id_seq,
    fasilitas_id_seq,
    galeri_destinasi_id_seq
TO jejaksumut_app_admin;

GRANT EXECUTE ON PROCEDURE sp_verifikasi_pemesanan(INTEGER, VARCHAR) TO jejaksumut_app_admin;

-- ============================================================================
-- C. ROLE 3: jejaksumut_mitra_readonly (Akses Read-Only Katalog Publik)
--    - Hanya membaca informasi katalog wisata Sumatera Utara dan SQL View.
--    - Sama sekali tidak bisa melihat data sensitif (pengguna, pemesanan, favorit).
-- ============================================================================
GRANT SELECT ON
    kabupaten_kota,
    kategori,
    destinasi,
    fasilitas,
    destinasi_fasilitas,
    galeri_destinasi,
    v_wisata_lengkap
TO jejaksumut_mitra_readonly;
