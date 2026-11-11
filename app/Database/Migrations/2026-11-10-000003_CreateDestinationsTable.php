<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDestinationsTable extends Migration
{
    public function up()
    {
        // Membuat tabel utama destinasi wisata
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'id_kategori' => [
                'type' => 'INT',
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // Kolom kota menjadi kunci parameter saat memanggil REST API kelompok lain
            'kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'provinsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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
            'foto_utama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('kota');
        $this->forge->addKey('id_kategori');
        $this->forge->addForeignKey('id_kategori', 'kategori', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('destinasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('destinasi', true, true);
    }
}
