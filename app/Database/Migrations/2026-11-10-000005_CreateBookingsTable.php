<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'booking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'destination_id' => [
                'type' => 'INT',
            ],
            'visit_date' => [
                'type' => 'DATE',
            ],
            'visitor_count' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'total_price' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            // Menggunakan VARCHAR untuk kompatibilitas PostgreSQL ('pending' | 'paid' | 'cancelled')
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
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
        $this->forge->addUniqueKey('booking_code');
        $this->forge->addKey('user_id');
        $this->forge->addKey('destination_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('destination_id', 'destinations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bookings', true);
    }

    public function down()
    {
        $this->forge->dropTable('bookings', true, true);
    }
}
