<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        // Membuat tabel ulasan dan rating pengunjung (1 sampai 5 bintang)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'id_pengguna' => [
                'type' => 'INT',
            ],
            'id_destinasi' => [
                'type' => 'INT',
            ],
            'rating' => [
                'type'    => 'SMALLINT',
                'default' => 5,
            ],
            'komentar' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('id_pengguna');
        $this->forge->addKey('id_destinasi');
        $this->forge->addForeignKey('id_pengguna', 'pengguna', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_destinasi', 'destinasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ulasan', true);
    }

    public function down()
    {
        $this->forge->dropTable('ulasan', true, true);
    }
}
