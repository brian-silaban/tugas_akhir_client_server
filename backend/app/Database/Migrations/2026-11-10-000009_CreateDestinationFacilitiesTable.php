<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDestinationFacilitiesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('destinasi_fasilitas')) {
            return;
        }

        // Membuat tabel pivot relasi many-to-many antara destinasi dan fasilitas
        $this->forge->addField([
            'id_destinasi' => [
                'type' => 'INT',
            ],
            'id_fasilitas' => [
                'type' => 'INT',
            ],
        ]);

        $this->forge->addKey(['id_destinasi', 'id_fasilitas'], true);
        $this->forge->addKey('id_fasilitas');
        $this->forge->addForeignKey('id_destinasi', 'destinasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_fasilitas', 'fasilitas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('destinasi_fasilitas', true);
    }

    public function down()
    {
        $this->forge->dropTable('destinasi_fasilitas', true, true);
    }
}
