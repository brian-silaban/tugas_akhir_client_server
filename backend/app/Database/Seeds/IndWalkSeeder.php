<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IndWalkSeeder extends Seeder
{
    public function run()
    {
        // Memanggil seeder utama JejakSumut (971 destinasi wisata Sumatera Utara)
        $this->call(JejakSumutSeeder::class);
    }
}
