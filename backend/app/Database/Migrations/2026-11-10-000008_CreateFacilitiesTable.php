<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacilitiesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('fasilitas')) {
            return;
        }

        // Membuat tabel master fasilitas wisata
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nama');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('fasilitas', true);
    }

    public function down()
    {
        $this->forge->dropTable('fasilitas', true, true);
    }
}
