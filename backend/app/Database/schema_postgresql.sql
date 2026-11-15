-- ============================================================================
-- IndWalk: Explore Indonesia Together
-- Skema Database PostgreSQL (7 Tabel Inti — Full Bahasa Indonesia)
-- Catatan: Bisa dijalankan via `php spark migrate` ATAU dieksekusi langsung
-- di PostgreSQL (pgAdmin / DBeaver / Supabase / psql).
-- ============================================================================

-- 1. Tabel Pengguna
CREATE TABLE IF NOT EXISTS pengguna (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    kata_sandi VARCHAR(255) NOT NULL,
    peran VARCHAR(20) NOT NULL DEFAULT 'member' CHECK (peran IN ('admin', 'member')),
    foto_profil VARCHAR(255) NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Kategori
CREATE TABLE IF NOT EXISTS kategori (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    deskripsi TEXT NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Destinasi
CREATE TABLE IF NOT EXISTS destinasi (
    id SERIAL PRIMARY KEY,
    id_kategori INTEGER NOT NULL REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    nama VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    kota VARCHAR(100) NOT NULL,
    provinsi VARCHAR(100) NOT NULL,
    alamat TEXT NULL,
    garis_lintang DECIMAL(10, 7) NULL,
    garis_bujur DECIMAL(10, 7) NULL,
    harga_tiket INTEGER NOT NULL DEFAULT 0,
    jam_buka TIME NULL,
    jam_tutup TIME NULL,
    deskripsi TEXT NOT NULL,
    fasilitas TEXT NULL,
    foto_utama VARCHAR(255) NOT NULL,
    rata_rating DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    jumlah_ulasan INTEGER NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_destinasi_kota ON destinasi(kota);
CREATE INDEX IF NOT EXISTS idx_destinasi_id_kategori ON destinasi(id_kategori);

-- 4. Tabel Galeri Destinasi
CREATE TABLE IF NOT EXISTS galeri_destinasi (
    id SERIAL PRIMARY KEY,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    url_foto VARCHAR(255) NOT NULL,
    keterangan VARCHAR(180) NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_galeri_destinasi_id_destinasi ON galeri_destinasi(id_destinasi);

-- 5. Tabel Pemesanan
CREATE TABLE IF NOT EXISTS pemesanan (
    id SERIAL PRIMARY KEY,
    kode_pemesanan VARCHAR(40) NOT NULL UNIQUE,
    id_pengguna INTEGER NOT NULL REFERENCES pengguna(id) ON UPDATE CASCADE ON DELETE CASCADE,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    tanggal_kunjungan DATE NOT NULL,
    jumlah_pengunjung INTEGER NOT NULL DEFAULT 1,
    total_harga INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'menunggu' CHECK (status IN ('menunggu', 'lunas', 'dibatalkan')),
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_pemesanan_id_pengguna ON pemesanan(id_pengguna);
CREATE INDEX IF NOT EXISTS idx_pemesanan_id_destinasi ON pemesanan(id_destinasi);

-- 6. Tabel Ulasan
CREATE TABLE IF NOT EXISTS ulasan (
    id SERIAL PRIMARY KEY,
    id_pengguna INTEGER NOT NULL REFERENCES pengguna(id) ON UPDATE CASCADE ON DELETE CASCADE,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    rating SMALLINT NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT NOT NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_ulasan_id_pengguna ON ulasan(id_pengguna);
CREATE INDEX IF NOT EXISTS idx_ulasan_id_destinasi ON ulasan(id_destinasi);

-- 7. Tabel Favorit
CREATE TABLE IF NOT EXISTS favorit (
    id SERIAL PRIMARY KEY,
    id_pengguna INTEGER NOT NULL REFERENCES pengguna(id) ON UPDATE CASCADE ON DELETE CASCADE,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_favorit_pengguna_destinasi UNIQUE (id_pengguna, id_destinasi)
);
