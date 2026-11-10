<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWishlistsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'destination_id' => [
                'type' => 'INT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'destination_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('destination_id', 'destinations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wishlists', true);
    }

    public function down()
    {
        $this->forge->dropTable('wishlists', true, true);
    }
}
