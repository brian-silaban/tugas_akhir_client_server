-- ============================================================================
-- JejakSumut (IndWalk) — Provinsi Sumatera Utara
-- Skema Database PostgreSQL (10 Tabel + 1 View — Full Bahasa Indonesia)
-- Catatan: Bisa dijalankan via `php spark migrate` ATAU dieksekusi langsung
-- di PostgreSQL (pgAdmin / DBeaver / Supabase / psql).
-- ============================================================================

-- 1. Tabel Wilayah Administrasi Sumatera Utara
CREATE TABLE IF NOT EXISTS kabupaten_kota (
    kode_kabupaten VARCHAR(10) PRIMARY KEY,
    nama_kabupaten_kota VARCHAR(100) NOT NULL UNIQUE,
    jenis_daerah VARCHAR(20) NOT NULL,
    ibu_kota VARCHAR(60) NOT NULL
);

-- 2. Tabel Pengguna
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

-- 3. Tabel Kategori
CREATE TABLE IF NOT EXISTS kategori (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    deskripsi TEXT NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabel Destinasi Wisata Sumatera Utara
CREATE TABLE IF NOT EXISTS destinasi (
    id SERIAL PRIMARY KEY,
    id_kategori INTEGER NOT NULL REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    kode_kabupaten VARCHAR(10) NOT NULL REFERENCES kabupaten_kota(kode_kabupaten) ON UPDATE CASCADE ON DELETE RESTRICT,
    nama VARCHAR(180) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    sub_kategori VARCHAR(100) NOT NULL,
    klasifikasi_status VARCHAR(80) NULL,
    kota VARCHAR(100) NOT NULL,
    provinsi VARCHAR(100) NOT NULL DEFAULT 'Sumatera Utara',
    alamat TEXT NULL,
    garis_lintang NUMERIC(10, 7) NULL,
    garis_bujur NUMERIC(10, 7) NULL,
    harga_tiket INTEGER NOT NULL DEFAULT 0,
    label_harga_tiket VARCHAR(100) NOT NULL,
    keterangan_tiket VARCHAR(200) NOT NULL,
    jam_buka TIME NULL,
    jam_tutup TIME NULL,
    deskripsi TEXT NOT NULL,
    fasilitas TEXT NULL,
    pengelola VARCHAR(150) NULL,
    no_telp VARCHAR(60) NULL,
    email VARCHAR(120) NULL,
    website_sosial TEXT NULL,
    foto_utama TEXT NOT NULL,
    sumber_data VARCHAR(200) NULL,
    sumber_url TEXT NULL,
    rata_rating NUMERIC(3, 2) NOT NULL DEFAULT 0.00,
    jumlah_ulasan INTEGER NOT NULL DEFAULT 0,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS destinasi_id_kategori ON destinasi(id_kategori);
CREATE INDEX IF NOT EXISTS destinasi_kode_kabupaten ON destinasi(kode_kabupaten);
CREATE INDEX IF NOT EXISTS destinasi_kota ON destinasi(kota);
CREATE INDEX IF NOT EXISTS destinasi_harga_tiket ON destinasi(harga_tiket);

-- 5. Tabel Master Fasilitas & Relasi Many-to-Many
CREATE TABLE IF NOT EXISTS fasilitas (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS destinasi_fasilitas (
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    id_fasilitas INTEGER NOT NULL REFERENCES fasilitas(id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (id_destinasi, id_fasilitas)
);

CREATE INDEX IF NOT EXISTS destinasi_fasilitas_id_fasilitas ON destinasi_fasilitas(id_fasilitas);

-- 6. Tabel Galeri Destinasi
CREATE TABLE IF NOT EXISTS galeri_destinasi (
    id SERIAL PRIMARY KEY,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    url_foto TEXT NOT NULL,
    keterangan VARCHAR(180) NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS galeri_destinasi_id_destinasi ON galeri_destinasi(id_destinasi);

-- 7. Tabel Pemesanan
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

CREATE INDEX IF NOT EXISTS pemesanan_id_pengguna ON pemesanan(id_pengguna);
CREATE INDEX IF NOT EXISTS pemesanan_id_destinasi ON pemesanan(id_destinasi);

-- 8. Tabel Ulasan
CREATE TABLE IF NOT EXISTS ulasan (
    id SERIAL PRIMARY KEY,
    id_pengguna INTEGER NOT NULL REFERENCES pengguna(id) ON UPDATE CASCADE ON DELETE CASCADE,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    rating SMALLINT NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT NOT NULL,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS ulasan_id_pengguna ON ulasan(id_pengguna);
CREATE INDEX IF NOT EXISTS ulasan_id_destinasi ON ulasan(id_destinasi);

-- 9. Tabel Favorit
CREATE TABLE IF NOT EXISTS favorit (
    id SERIAL PRIMARY KEY,
    id_pengguna INTEGER NOT NULL REFERENCES pengguna(id) ON UPDATE CASCADE ON DELETE CASCADE,
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE CASCADE,
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT favorit_id_pengguna_id_destinasi UNIQUE (id_pengguna, id_destinasi)
);

-- 10. View Gabungan Lengkap Destinasi Sumatera Utara
CREATE OR REPLACE VIEW v_wisata_lengkap AS
SELECT
    d.id,
    d.kode_kabupaten,
    kb.nama_kabupaten_kota,
    kb.jenis_daerah,
    d.nama,
    d.slug,
    kt.nama AS kategori_utama,
    d.sub_kategori,
    d.klasifikasi_status,
    d.alamat,
    d.deskripsi,
    d.harga_tiket,
    d.label_harga_tiket,
    d.keterangan_tiket,
    d.fasilitas,
    d.pengelola,
    d.no_telp,
    d.email,
    d.website_sosial,
    d.garis_lintang,
    d.garis_bujur,
    d.foto_utama,
    d.rata_rating,
    d.jumlah_ulasan,
    d.sumber_data,
    d.sumber_url
FROM destinasi d
JOIN kabupaten_kota kb ON d.kode_kabupaten = kb.kode_kabupaten
JOIN kategori kt ON d.id_kategori = kt.id;
