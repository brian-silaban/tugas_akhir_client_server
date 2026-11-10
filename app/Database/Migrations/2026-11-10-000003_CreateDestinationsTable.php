<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDestinationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'category_id' => [
                'type' => 'INT',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // Kolom city menjadi jembatan parameter untuk integrasi REST API kelompok lain
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'province' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'ticket_price' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'open_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'close_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            // Disimpan sebagai JSON string di TEXT agar kompatibel penuh di semua versi PostgreSQL & MySQL
            'facilities' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'thumbnail' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'rating_avg' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,2',
                'default'    => 0.00,
            ],
            'review_count' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('city');
        $this->forge->addKey('category_id');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('destinations', true);
    }

    public function down()
    {
        $this->forge->dropTable('destinations', true, true);
    }
}
