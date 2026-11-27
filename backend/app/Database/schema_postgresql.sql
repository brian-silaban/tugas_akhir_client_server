-- ============================================================================
-- JejakSumut — Provinsi Sumatera Utara
-- Skema Database PostgreSQL (10 Tabel + 2 View + Stored Function/Procedure + Trigger)
-- Catatan: Bisa dijalankan via `php spark migrate` ATAU dieksekusi langsung
-- di PostgreSQL (pgAdmin / DBeaver / psql).
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

CREATE INDEX IF NOT EXISTS pengguna_email_lower ON pengguna (LOWER(email));

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
    harga_tiket INTEGER NOT NULL DEFAULT 0 CHECK (harga_tiket >= 0),
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
CREATE INDEX IF NOT EXISTS destinasi_rating_ulasan ON destinasi (rata_rating DESC, jumlah_ulasan DESC, id ASC);

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
    id_destinasi INTEGER NOT NULL REFERENCES destinasi(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    tanggal_kunjungan DATE NOT NULL,
    jumlah_pengunjung INTEGER NOT NULL DEFAULT 1 CHECK (jumlah_pengunjung >= 1),
    total_harga INTEGER NOT NULL DEFAULT 0 CHECK (total_harga >= 0),
    status VARCHAR(20) NOT NULL DEFAULT 'menunggu' CHECK (status IN ('menunggu', 'lunas', 'dibatalkan')),
    dibuat_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS pemesanan_id_pengguna ON pemesanan(id_pengguna);
CREATE INDEX IF NOT EXISTS pemesanan_id_destinasi ON pemesanan(id_destinasi);
CREATE INDEX IF NOT EXISTS pemesanan_status_dibuat_pada ON pemesanan (status, dibuat_pada DESC, id DESC);

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

-- 11. View ke-2: Ringkasan Pemesanan Tiket (Aman tanpa kata_sandi pengguna)
CREATE OR REPLACE VIEW v_ringkasan_pemesanan AS
SELECT
    p.id,
    p.kode_pemesanan,
    p.id_pengguna,
    u.nama AS nama_pemesan,
    u.email AS email_pemesan,
    p.id_destinasi,
    d.nama AS nama_destinasi,
    d.slug AS slug_destinasi,
    d.kota AS kota_destinasi,
    kb.nama_kabupaten_kota,
    d.harga_tiket AS harga_satuan,
    p.tanggal_kunjungan,
    p.jumlah_pengunjung,
    p.total_harga,
    p.status,
    p.dibuat_pada,
    p.diperbarui_pada
FROM pemesanan p
JOIN pengguna u ON u.id = p.id_pengguna
JOIN destinasi d ON d.id = p.id_destinasi
JOIN kabupaten_kota kb ON kb.kode_kabupaten = d.kode_kabupaten;

-- Kunci harga dalam transaksi pembelian dengan hak akses minimum.
CREATE OR REPLACE FUNCTION fn_kunci_destinasi_pemesanan(p_id_destinasi INTEGER)
RETURNS TABLE (id INTEGER, harga_tiket INTEGER)
LANGUAGE sql
SECURITY DEFINER
SET search_path = pg_catalog, pg_temp
AS $$
    SELECT d.id, d.harga_tiket
    FROM public.destinasi d
    WHERE d.id = p_id_destinasi
    FOR SHARE OF d;
$$;

REVOKE EXECUTE ON FUNCTION fn_kunci_destinasi_pemesanan(INTEGER) FROM PUBLIC;

-- 12. Stored Function: Pembuat Kode Pemesanan Unik (JSM-YYYYMMDD-XXX + Advisory Lock)
CREATE OR REPLACE FUNCTION fn_buat_kode_pemesanan(p_tanggal DATE DEFAULT CURRENT_DATE)
RETURNS VARCHAR(40)
LANGUAGE plpgsql
AS $$
DECLARE
    v_prefix VARCHAR(20);
    v_urutan INTEGER;
BEGIN
    PERFORM pg_advisory_xact_lock(hashtext('kode_pemesanan_' || TO_CHAR(p_tanggal, 'YYYYMMDD')));

    v_prefix := 'JSM-' || TO_CHAR(p_tanggal, 'YYYYMMDD') || '-';

    SELECT COALESCE(
        MAX(CAST(SPLIT_PART(kode_pemesanan, '-', 3) AS INTEGER)),
        0
    ) + 1
    INTO v_urutan
    FROM pemesanan
    WHERE kode_pemesanan ~ ('^JSM-' || TO_CHAR(p_tanggal, 'YYYYMMDD') || '-[0-9]+$');

    RETURN v_prefix || LPAD(v_urutan::TEXT, 3, '0');
END;
$$;

-- 13. Stored Procedure: Verifikasi Status Pemesanan (dengan Row Locking FOR UPDATE)
CREATE OR REPLACE PROCEDURE sp_verifikasi_pemesanan(
    p_id_pemesanan INTEGER,
    p_status_baru VARCHAR(20)
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_status_saat_ini VARCHAR(20);
BEGIN
    IF p_status_baru NOT IN ('menunggu', 'lunas', 'dibatalkan') THEN
        RAISE EXCEPTION 'Status pemesanan tidak valid: %. Gunakan menunggu, lunas, atau dibatalkan.', p_status_baru;
    END IF;

    SELECT status
    INTO v_status_saat_ini
    FROM pemesanan
    WHERE id = p_id_pemesanan
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Data pemesanan dengan ID % tidak ditemukan.', p_id_pemesanan;
    END IF;

    IF v_status_saat_ini = 'dibatalkan' AND p_status_baru = 'lunas' THEN
        RAISE EXCEPTION 'Pemesanan yang sudah dibatalkan tidak dapat diubah menjadi lunas.';
    END IF;

    IF v_status_saat_ini = 'lunas' AND p_status_baru = 'menunggu' THEN
        RAISE EXCEPTION 'Pemesanan yang sudah lunas tidak dapat dikembalikan ke menunggu.';
    END IF;

    UPDATE pemesanan
    SET status = p_status_baru,
        diperbarui_pada = CURRENT_TIMESTAMP
    WHERE id = p_id_pemesanan;
END;
$$;

-- 14. Trigger Function & Trigger: Sinkronisasi Otomatis rata_rating & jumlah_ulasan Destinasi
CREATE OR REPLACE FUNCTION fn_sinkronkan_rating_destinasi()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public, pg_temp
AS $$
DECLARE
    v_id_destinasi INTEGER;
BEGIN
    IF TG_OP = 'DELETE' THEN
        v_id_destinasi := OLD.id_destinasi;
    ELSE
        v_id_destinasi := NEW.id_destinasi;
    END IF;

    UPDATE destinasi
    SET rata_rating = COALESCE((
            SELECT ROUND(AVG(rating)::NUMERIC, 2)
            FROM ulasan
            WHERE id_destinasi = v_id_destinasi
        ), 0.00),
        jumlah_ulasan = (
            SELECT COUNT(*)
            FROM ulasan
            WHERE id_destinasi = v_id_destinasi
        ),
        diperbarui_pada = CURRENT_TIMESTAMP
    WHERE id = v_id_destinasi;

    IF TG_OP = 'UPDATE' AND OLD.id_destinasi IS DISTINCT FROM NEW.id_destinasi THEN
        UPDATE destinasi
        SET rata_rating = COALESCE((
                SELECT ROUND(AVG(rating)::NUMERIC, 2)
                FROM ulasan
                WHERE id_destinasi = OLD.id_destinasi
            ), 0.00),
            jumlah_ulasan = (
                SELECT COUNT(*)
                FROM ulasan
                WHERE id_destinasi = OLD.id_destinasi
            ),
            diperbarui_pada = CURRENT_TIMESTAMP
        WHERE id = OLD.id_destinasi;
    END IF;

    RETURN NULL;
END;
$$;

DROP TRIGGER IF EXISTS trg_sinkronkan_rating_destinasi ON ulasan;
CREATE TRIGGER trg_sinkronkan_rating_destinasi
AFTER INSERT OR UPDATE OR DELETE ON ulasan
FOR EACH ROW
EXECUTE FUNCTION fn_sinkronkan_rating_destinasi();

-- 15. Trigger Function & Triggers: Otomatis Mengisi Kolom diperbarui_pada
CREATE OR REPLACE FUNCTION fn_perbarui_waktu_ubah()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.diperbarui_pada := CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_pengguna_diperbarui_pada ON pengguna;
CREATE TRIGGER trg_pengguna_diperbarui_pada
BEFORE UPDATE ON pengguna FOR EACH ROW EXECUTE FUNCTION fn_perbarui_waktu_ubah();

DROP TRIGGER IF EXISTS trg_kategori_diperbarui_pada ON kategori;
CREATE TRIGGER trg_kategori_diperbarui_pada
BEFORE UPDATE ON kategori FOR EACH ROW EXECUTE FUNCTION fn_perbarui_waktu_ubah();

DROP TRIGGER IF EXISTS trg_destinasi_diperbarui_pada ON destinasi;
CREATE TRIGGER trg_destinasi_diperbarui_pada
BEFORE UPDATE ON destinasi FOR EACH ROW EXECUTE FUNCTION fn_perbarui_waktu_ubah();

DROP TRIGGER IF EXISTS trg_pemesanan_diperbarui_pada ON pemesanan;
CREATE TRIGGER trg_pemesanan_diperbarui_pada
BEFORE UPDATE ON pemesanan FOR EACH ROW EXECUTE FUNCTION fn_perbarui_waktu_ubah();

DROP TRIGGER IF EXISTS trg_ulasan_diperbarui_pada ON ulasan;
CREATE TRIGGER trg_ulasan_diperbarui_pada
BEFORE UPDATE ON ulasan FOR EACH ROW EXECUTE FUNCTION fn_perbarui_waktu_ubah();
