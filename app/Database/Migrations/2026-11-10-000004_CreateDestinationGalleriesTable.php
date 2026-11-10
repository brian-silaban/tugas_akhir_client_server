<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDestinationGalleriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'destination_id' => [
                'type' => 'INT',
            ],
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'caption' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('destination_id');
        $this->forge->addForeignKey('destination_id', 'destinations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('destination_galleries', true);
    }

    public function down()
    {
        $this->forge->dropTable('destination_galleries', true, true);
    }
}
