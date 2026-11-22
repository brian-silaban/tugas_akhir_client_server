<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegenciesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('kabupaten_kota')) {
            return;
        }

        // Membuat tabel wilayah administrasi kabupaten/kota di Provinsi Sumatera Utara
        $this->forge->addField([
            'kode_kabupaten' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'nama_kabupaten_kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'jenis_daerah' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'ibu_kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
        ]);

        $this->forge->addKey('kode_kabupaten', true);
        $this->forge->addUniqueKey('nama_kabupaten_kota');
        $this->forge->createTable('kabupaten_kota', true);
    }

    public function down()
    {
        $this->forge->dropTable('kabupaten_kota', true, true);
    }
}
