<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDestinationsTable extends Migration
{
    public function up()
    {
        // Membuat tabel utama destinasi wisata Sumatera Utara
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'id_kategori' => [
                'type' => 'INT',
            ],
            'kode_kabupaten' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 220,
            ],
            'sub_kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'klasifikasi_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            // Kolom kota menjadi kunci parameter saat memanggil REST API kelompok lain
            'kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'provinsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Sumatera Utara',
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'garis_lintang' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'garis_bujur' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'harga_tiket' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'label_harga_tiket' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'keterangan_tiket' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'jam_buka' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'jam_tutup' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
            ],
            // Disimpan sebagai JSON string pada kolom TEXT agar kompatibel penuh di PostgreSQL
            'fasilitas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pengelola' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'no_telp' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'website_sosial' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_utama' => [
                'type' => 'TEXT',
            ],
            'sumber_data' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'sumber_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rata_rating' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,2',
                'default'    => 0.00,
            ],
            'jumlah_ulasan' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'dibuat_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'diperbarui_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('id_kategori');
        $this->forge->addKey('kode_kabupaten');
        $this->forge->addKey('kota');
        $this->forge->addKey('harga_tiket');
        $this->forge->addForeignKey('id_kategori', 'kategori', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('kode_kabupaten', 'kabupaten_kota', 'kode_kabupaten', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('destinasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('destinasi', true, true);
    }
}
