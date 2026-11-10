<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IndWalkSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Seed Users (Admin & Member) - Tanpa hardcode ID agar sequence SERIAL PostgreSQL tetap sinkron
        $users = [
            [
                'name'          => 'Admin IndWalk',
                'email'         => 'admin@indwalk.id',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'          => 'admin',
                'avatar'        => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Raka Pratama',
                'email'         => 'raka@indwalk.id',
                'password_hash' => password_hash('member123', PASSWORD_DEFAULT),
                'role'          => 'member',
                'avatar'        => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Nadia Kusuma',
                'email'         => 'nadia@indwalk.id',
                'password_hash' => password_hash('member123', PASSWORD_DEFAULT),
                'role'          => 'member',
                'avatar'        => null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];
        $this->db->table('users')->insertBatch($users);

        // 2. Seed Categories
        $categories = [
            [
                'name'        => 'Cagar Budaya',
                'slug'        => 'cagar-budaya',
                'description' => 'Warisan sejarah, candi, keraton, dan desa adat Nusantara.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Pegunungan & Geopark',
                'slug'        => 'pegunungan-geopark',
                'description' => 'Kaldera vulkanik, jalur pendakian, kawah, dan dataran tinggi asri.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Wisata Bahari',
                'slug'        => 'wisata-bahari',
                'description' => 'Pantai, kepulauan, taman laut, dan spot penyelaman kelas dunia.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Hutan & Air Terjun',
                'slug'        => 'hutan-air-terjun',
                'description' => 'Ekowisata hutan tropis, air terjun tersembunyi, dan sungai alami.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];
        $this->db->table('categories')->insertBatch($categories);

        // Ambil map slug kategori -> id
        $catRows = $this->db->table('categories')->get()->getResultArray();
        $catMap  = array_column($catRows, 'id', 'slug');

        // 3. Seed Destinations
        $destinations = [
            [
                'category_id'  => $catMap['cagar-budaya'],
                'name'         => 'Candi Borobudur',
                'slug'         => 'candi-borobudur',
                'city'         => 'Magelang',
                'province'     => 'Jawa Tengah',
                'address'      => 'Jl. Badrawati, Kw. Candi Borobudur, Borobudur, Kab. Magelang',
                'latitude'     => -7.6078738,
                'longitude'    => 110.2037513,
                'ticket_price' => 50000,
                'open_time'    => '06:30:00',
                'close_time'   => '16:30:00',
                'description'  => 'Monumen Buddha terbesar di dunia yang dibangun pada abad ke-8 dengan 2.672 panel relief dan 504 arca Buddha, dikelilingi lanskap Perbukitan Menoreh.',
                'facilities'   => json_encode(['Parkir Luas', 'Musholla', 'Toilet Bersih', 'Pemandu Resmi', 'Akses Kursi Roda', 'Pusat Kuliner']),
                'thumbnail'    => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80',
                'rating_avg'   => 4.90,
                'review_count' => 1420,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'category_id'  => $catMap['pegunungan-geopark'],
                'name'         => 'Kawah Ijen',
                'slug'         => 'kawah-ijen',
                'city'         => 'Banyuwangi',
                'province'     => 'Jawa Timur',
                'address'      => 'Pos Paltuding, Tamansari, Licin, Kab. Banyuwangi',
                'latitude'     => -8.0583000,
                'longitude'    => 114.2425000,
                'ticket_price' => 15000,
                'open_time'    => '02:00:00',
                'close_time'   => '12:00:00',
                'description'  => 'Danau kawah asam terbesar di dunia dengan warna hijau toska yang ikonik serta fenomena api biru (Blue Fire) dini hari di perbatasan Banyuwangi dan Bondowoso.',
                'facilities'   => json_encode(['Area Parkir Paltuding', 'Warung Kopi Hangat', 'Toilet', 'Penyewaan Masker Gas', 'Pos Kesehatan']),
                'thumbnail'    => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1200&q=80',
                'rating_avg'   => 4.85,
                'review_count' => 980,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'category_id'  => $catMap['wisata-bahari'],
                'name'         => 'Pulau Padar, Taman Nasional Komodo',
                'slug'         => 'pulau-padar-komodo',
                'city'         => 'Labuan Bajo',
                'province'     => 'Nusa Tenggara Timur',
                'address'      => 'Kawasan Taman Nasional Komodo, Manggarai Barat',
                'latitude'     => -8.6594440,
                'longitude'    => 119.5588890,
                'ticket_price' => 150000,
                'open_time'    => '05:30:00',
                'close_time'   => '17:30:00',
                'description'  => 'Bukit sabana eksotis yang mempertemukan tiga teluk dengan pasir berwarna putih, hitam, dan merah muda (pink beach) dalam satu panorama.',
                'facilities'   => json_encode(['Dermaga Kapal', 'Jalur Anak Tangga', 'Pos Ranger TNK', 'Spot Fotografi']),
                'thumbnail'    => 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80',
                'rating_avg'   => 4.95,
                'review_count' => 1150,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'category_id'  => $catMap['cagar-budaya'],
                'name'         => 'Desa Adat Penglipuran',
                'slug'         => 'desa-adat-penglipuran',
                'city'         => 'Bangli',
                'province'     => 'Bali',
                'address'      => 'Jl. Penglipuran, Kubu, Kec. Bangli, Kabupaten Bangli',
                'latitude'     => -8.4222000,
                'longitude'    => 115.3592000,
                'ticket_price' => 25000,
                'open_time'    => '08:00:00',
                'close_time'   => '18:30:00',
                'description'  => 'Desa adat terbersih dengan tata ruang tradisional konsep Tri Mandala dan hutan bambu seluas 45 hektar yang terjaga kelestariannya.',
                'facilities'   => json_encode(['Parkir Luas', 'Toilet Bersih', 'Hutan Bambu', 'Warung Loloh Cemcem', 'Homestay Warga']),
                'thumbnail'    => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',
                'rating_avg'   => 4.80,
                'review_count' => 860,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'category_id'  => $catMap['pegunungan-geopark'],
                'name'         => 'Taman Nasional Bromo Tengger Semeru',
                'slug'         => 'bromo-tengger-semeru',
                'city'         => 'Malang',
                'province'     => 'Jawa Timur',
                'address'      => 'Kawasan Kaldera Tengger, Malang - Probolinggo - Pasuruan',
                'latitude'     => -7.9424936,
                'longitude'    => 112.9530122,
                'ticket_price' => 34000,
                'open_time'    => '00:01:00',
                'close_time'   => '23:59:00',
                'description'  => 'Hamparan lautan pasir vulkanik seluas 10 kilometer persegi dengan lanskap matahari terbit dari Penanjakan dan budaya masyarakat suku Tengger.',
                'facilities'   => json_encode(['Sewa Jeep Hardtop', 'Musholla', 'Toilet', 'Warung Hangat', 'Gardu Pandang Sunrise']),
                'thumbnail'    => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80',
                'rating_avg'   => 4.92,
                'review_count' => 2130,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'category_id'  => $catMap['hutan-air-terjun'],
                'name'         => 'Air Terjun Tumpak Sewu',
                'slug'         => 'air-terjun-tumpak-sewu',
                'city'         => 'Lumajang',
                'province'     => 'Jawa Timur',
                'address'      => 'Jl. Raya Sidomulyo, Besukcukit, Pronojiwo, Kab. Lumajang',
                'latitude'     => -8.2302778,
                'longitude'    => 112.9175000,
                'ticket_price' => 20000,
                'open_time'    => '07:00:00',
                'close_time'   => '15:00:00',
                'description'  => 'Formasi air terjun bertirai setengah lingkaran setinggi 120 meter yang dialiri Sungai Glidik langsung dari kaki Gunung Semeru.',
                'facilities'   => json_encode(['Gardu Panorama', 'Pemandu Lokal', 'Parkir', 'Kamar Bilas', 'Musholla']),
                'thumbnail'    => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1200&q=80',
                'rating_avg'   => 4.88,
                'review_count' => 640,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];
        $this->db->table('destinations')->insertBatch($destinations);
    }
}
