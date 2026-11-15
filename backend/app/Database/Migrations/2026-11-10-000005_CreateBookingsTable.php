<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        // Membuat tabel pemesanan tiket kunjungan wisata
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'kode_pemesanan' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'id_pengguna' => [
                'type' => 'INT',
            ],
            'id_destinasi' => [
                'type' => 'INT',
            ],
            'tanggal_kunjungan' => [
                'type' => 'DATE',
            ],
            'jumlah_pengunjung' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'total_harga' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            // Status pemesanan: 'menunggu' | 'lunas' | 'dibatalkan'
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'menunggu',
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
        $this->forge->addUniqueKey('kode_pemesanan');
        $this->forge->addKey('id_pengguna');
        $this->forge->addKey('id_destinasi');
        $this->forge->addForeignKey('id_pengguna', 'pengguna', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_destinasi', 'destinasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pemesanan', true);
    }

    public function down()
    {
        $this->forge->dropTable('pemesanan', true, true);
    }
}
