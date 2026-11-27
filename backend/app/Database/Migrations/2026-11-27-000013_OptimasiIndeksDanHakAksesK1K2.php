<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OptimasiIndeksDanHakAksesK1K2 extends Migration
{
    public function up()
    {
        // Indeks komposit dan fungsional untuk mempercepat kueri katalog, pemesanan, dan login
        $this->db->query('CREATE INDEX IF NOT EXISTS pengguna_email_lower ON pengguna (LOWER(email))');
        $this->db->query('CREATE INDEX IF NOT EXISTS destinasi_rating_ulasan ON destinasi (rata_rating DESC, jumlah_ulasan DESC, id ASC)');
        $this->db->query('CREATE INDEX IF NOT EXISTS pemesanan_status_dibuat_pada ON pemesanan (status, dibuat_pada DESC, id DESC)');

        // Pastikan role publik memiliki hak eksekusi prosedur pembatalan tiket milik sendiri
        $this->db->query(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'jejaksumut_app_publik')
       AND EXISTS (SELECT 1 FROM pg_proc WHERE proname = 'sp_verifikasi_pemesanan') THEN
        GRANT EXECUTE ON PROCEDURE sp_verifikasi_pemesanan(INTEGER, VARCHAR) TO jejaksumut_app_publik;
    END IF;
END;
$$;
SQL);
    }

    public function down()
    {
        $this->db->query('DROP INDEX IF EXISTS pemesanan_status_dibuat_pada');
        $this->db->query('DROP INDEX IF EXISTS destinasi_rating_ulasan');
        $this->db->query('DROP INDEX IF EXISTS pengguna_email_lower');
    }
}
