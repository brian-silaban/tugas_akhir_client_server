<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWishlistsTable extends Migration
{
    public function up()
    {
        // Membuat tabel favorit (daftar destinasi yang disimpan pengguna)
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
            'dibuat_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_pengguna', 'id_destinasi']);
        $this->forge->addForeignKey('id_pengguna', 'pengguna', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_destinasi', 'destinasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('favorit', true);
    }

    public function down()
    {
        $this->forge->dropTable('favorit', true, true);
    }
}
