<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        // Membuat tabel pengguna (untuk role 'admin' dan 'member')
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'kata_sandi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // Menggunakan VARCHAR agar kompatibel penuh dengan PostgreSQL ('admin' | 'member')
            'peran' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'member',
            ],
            'foto_profil' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('pengguna', true);
    }

    public function down()
    {
        $this->forge->dropTable('pengguna', true, true);
    }
}
