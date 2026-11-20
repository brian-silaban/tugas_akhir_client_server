<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        // Urutan seeding utama JejakSumut berjalan idempotent
        $this->call(JejakSumutSeeder::class);
    }
}
