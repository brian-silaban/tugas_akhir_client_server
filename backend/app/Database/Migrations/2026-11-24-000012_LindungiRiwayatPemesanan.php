<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LindungiRiwayatPemesanan extends Migration
{
    public function up()
    {
        $this->gantiRelasi('RESTRICT');

        // Lock baris harga untuk transaksi tiket tanpa memberi role publik hak UPDATE destinasi.
        $this->db->query(<<<'SQL'
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

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'jejaksumut_app_publik') THEN
        GRANT EXECUTE ON FUNCTION fn_kunci_destinasi_pemesanan(INTEGER) TO jejaksumut_app_publik;
    END IF;
END;
$$;
SQL);
    }

    public function down()
    {
        $this->db->query('DROP FUNCTION IF EXISTS fn_kunci_destinasi_pemesanan(INTEGER)');
        $this->gantiRelasi('CASCADE');
    }

    private function gantiRelasi(string $aksiHapus): void
    {
        // Nama berbeda antara skema SQL manual dan migrasi bawaan CI4.
        $this->db->query("
            ALTER TABLE pemesanan
                DROP CONSTRAINT IF EXISTS pemesanan_id_destinasi_fkey,
                DROP CONSTRAINT IF EXISTS pemesanan_id_destinasi_foreign,
                ADD CONSTRAINT pemesanan_id_destinasi_foreign
                    FOREIGN KEY (id_destinasi) REFERENCES destinasi(id)
                    ON UPDATE CASCADE ON DELETE {$aksiHapus}
        ");
    }
}
