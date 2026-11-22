<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateObjectsAndConstraintsK2 extends Migration
{
    public function up()
    {
        // 1. Menambahkan CHECK constraints pada level PostgreSQL agar validasi data terjaga di database
        $this->db->query("
            DO $$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_pengguna_peran') THEN
                    ALTER TABLE pengguna
                        ADD CONSTRAINT chk_pengguna_peran CHECK (peran IN ('admin', 'member'));
                END IF;

                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_destinasi_harga_non_negatif') THEN
                    ALTER TABLE destinasi
                        ADD CONSTRAINT chk_destinasi_harga_non_negatif CHECK (harga_tiket >= 0);
                END IF;

                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_pemesanan_jumlah_positif') THEN
                    ALTER TABLE pemesanan
                        ADD CONSTRAINT chk_pemesanan_jumlah_positif CHECK (jumlah_pengunjung >= 1);
                END IF;

                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_pemesanan_total_non_negatif') THEN
                    ALTER TABLE pemesanan
                        ADD CONSTRAINT chk_pemesanan_total_non_negatif CHECK (total_harga >= 0);
                END IF;

                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_pemesanan_status') THEN
                    ALTER TABLE pemesanan
                        ADD CONSTRAINT chk_pemesanan_status CHECK (status IN ('menunggu', 'lunas', 'dibatalkan'));
                END IF;

                IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_ulasan_rating') THEN
                    ALTER TABLE ulasan
                        ADD CONSTRAINT chk_ulasan_rating CHECK (rating BETWEEN 1 AND 5);
                END IF;
            END $$;
        ");

        // 2. Membuat SQL View ke-2: v_ringkasan_pemesanan (tanpa kolom kata_sandi)
        $this->db->query("
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
            JOIN kabupaten_kota kb ON kb.kode_kabupaten = d.kode_kabupaten
        ");

        // 3. Stored Function: fn_buat_kode_pemesanan (generator kode unik JSM-YYYYMMDD-XXX + advisory lock)
        $this->db->query("
            CREATE OR REPLACE FUNCTION fn_buat_kode_pemesanan(p_tanggal DATE DEFAULT CURRENT_DATE)
            RETURNS VARCHAR(40)
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_prefix VARCHAR(20);
                v_urutan INTEGER;
            BEGIN
                -- Kunci sementara per tanggal agar dua transaksi bersamaan tidak mendapat nomor urut kembar
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
            $$
        ");

        // 4. Stored Procedure: sp_verifikasi_pemesanan (ubah status pesanan dengan row locking FOR UPDATE)
        $this->db->query("
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

                -- Kunci baris pemesanan selama transaksi berjalan untuk mencegah race condition
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
            $$
        ");

        // 5. Trigger Function 1: fn_sinkronkan_rating_destinasi (SECURITY DEFINER agar user publik tetap least-privilege)
        $this->db->query("
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
            $$
        ");

        $this->db->query('DROP TRIGGER IF EXISTS trg_sinkronkan_rating_destinasi ON ulasan');
        $this->db->query("
            CREATE TRIGGER trg_sinkronkan_rating_destinasi
            AFTER INSERT OR UPDATE OR DELETE ON ulasan
            FOR EACH ROW
            EXECUTE FUNCTION fn_sinkronkan_rating_destinasi()
        ");

        // 6. Trigger Function 2: fn_perbarui_waktu_ubah (otomatis mengisi diperbarui_pada saat UPDATE)
        $this->db->query("
            CREATE OR REPLACE FUNCTION fn_perbarui_waktu_ubah()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                NEW.diperbarui_pada := CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$
        ");

        $daftarTabelTimestamp = ['pengguna', 'kategori', 'destinasi', 'pemesanan', 'ulasan'];
        foreach ($daftarTabelTimestamp as $namaTabel) {
            $namaTrigger = 'trg_' . $namaTabel . '_diperbarui_pada';
            $this->db->query("DROP TRIGGER IF EXISTS {$namaTrigger} ON {$namaTabel}");
            $this->db->query("
                CREATE TRIGGER {$namaTrigger}
                BEFORE UPDATE ON {$namaTabel}
                FOR EACH ROW
                EXECUTE FUNCTION fn_perbarui_waktu_ubah()
            ");
        }
    }

    public function down()
    {
        $daftarTabelTimestamp = ['pengguna', 'kategori', 'destinasi', 'pemesanan', 'ulasan'];
        foreach ($daftarTabelTimestamp as $namaTabel) {
            $namaTrigger = 'trg_' . $namaTabel . '_diperbarui_pada';
            $this->db->query("DROP TRIGGER IF EXISTS {$namaTrigger} ON {$namaTabel}");
        }

        $this->db->query('DROP TRIGGER IF EXISTS trg_sinkronkan_rating_destinasi ON ulasan');
        $this->db->query('DROP FUNCTION IF EXISTS fn_perbarui_waktu_ubah()');
        $this->db->query('DROP FUNCTION IF EXISTS fn_sinkronkan_rating_destinasi()');
        $this->db->query('DROP PROCEDURE IF EXISTS sp_verifikasi_pemesanan(INTEGER, VARCHAR)');
        $this->db->query('DROP FUNCTION IF EXISTS fn_buat_kode_pemesanan(DATE)');
        $this->db->query('DROP VIEW IF EXISTS v_ringkasan_pemesanan');

        $this->db->query('ALTER TABLE pengguna DROP CONSTRAINT IF EXISTS chk_pengguna_peran');
        $this->db->query('ALTER TABLE destinasi DROP CONSTRAINT IF EXISTS chk_destinasi_harga_non_negatif');
        $this->db->query('ALTER TABLE pemesanan DROP CONSTRAINT IF EXISTS chk_pemesanan_jumlah_positif');
        $this->db->query('ALTER TABLE pemesanan DROP CONSTRAINT IF EXISTS chk_pemesanan_total_non_negatif');
        $this->db->query('ALTER TABLE pemesanan DROP CONSTRAINT IF EXISTS chk_pemesanan_status');
        $this->db->query('ALTER TABLE ulasan DROP CONSTRAINT IF EXISTS chk_ulasan_rating');
    }
}
