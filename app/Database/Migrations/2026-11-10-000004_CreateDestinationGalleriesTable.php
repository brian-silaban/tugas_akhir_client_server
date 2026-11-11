<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDestinationGalleriesTable extends Migration
{
    public function up()
    {
        // Membuat tabel galeri foto pendukung tiap destinasi
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'id_destinasi' => [
                'type' => 'INT',
            ],
            'url_foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'dibuat_pada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('id_destinasi');
        $this->forge->addForeignKey('id_destinasi', 'destinasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('galeri_destinasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('galeri_destinasi', true, true);
    }
}
