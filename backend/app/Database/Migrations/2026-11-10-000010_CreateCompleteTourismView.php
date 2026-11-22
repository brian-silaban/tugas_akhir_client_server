<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompleteTourismView extends Migration
{
    public function up()
    {
        // Membuat SQL View gabungan lengkap destinasi, kabupaten/kota, dan kategori
        $this->db->query("
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
            JOIN kategori kt ON d.id_kategori = kt.id
        ");
    }

    public function down()
    {
        $this->db->query('DROP VIEW IF EXISTS v_wisata_lengkap');
    }
}
