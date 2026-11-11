<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class IndWalkSeeder extends Seeder
{
    public function run()
    {
        $waktuSekarang = date('Y-m-d H:i:s');

        // 1. Mengisi data awal tabel pengguna (Admin dan Member)
        $daftarPengguna = [
            [
                'nama'            => 'Admin IndWalk',
                'email'           => 'admin@indwalk.id',
                'kata_sandi'      => password_hash('admin123', PASSWORD_DEFAULT),
                'peran'           => 'admin',
                'foto_profil'     => null,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'nama'            => 'Raka Pratama',
                'email'           => 'raka@indwalk.id',
                'kata_sandi'      => password_hash('member123', PASSWORD_DEFAULT),
                'peran'           => 'member',
                'foto_profil'     => null,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'nama'            => 'Nadia Kusuma',
                'email'           => 'nadia@indwalk.id',
                'kata_sandi'      => password_hash('member123', PASSWORD_DEFAULT),
                'peran'           => 'member',
                'foto_profil'     => null,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
        ];
        $this->db->table('pengguna')->insertBatch($daftarPengguna);

        // 2. Mengisi data awal tabel kategori wisata
        $daftarKategori = [
            [
                'nama'            => 'Cagar Budaya',
                'slug'            => 'cagar-budaya',
                'deskripsi'       => 'Warisan sejarah, candi, keraton, dan desa adat Nusantara.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'nama'            => 'Pegunungan & Geopark',
                'slug'            => 'pegunungan-geopark',
                'deskripsi'       => 'Kaldera vulkanik, jalur pendakian, kawah, dan dataran tinggi asri.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'nama'            => 'Wisata Bahari',
                'slug'            => 'wisata-bahari',
                'deskripsi'       => 'Pantai, kepulauan, taman laut, dan spot penyelaman kelas dunia.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'nama'            => 'Hutan & Air Terjun',
                'slug'            => 'hutan-air-terjun',
                'deskripsi'       => 'Ekowisata hutan tropis, air terjun tersembunyi, dan sungai alami.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
        ];
        $this->db->table('kategori')->insertBatch($daftarKategori);

        // Mengambil pemetaan slug kategori ke id kategori dari database
        $barisKategori = $this->db->table('kategori')->get()->getResultArray();
        $petaKategori  = array_column($barisKategori, 'id', 'slug');

        // 3. Mengisi data awal tabel destinasi wisata Nusantara
        $daftarDestinasi = [
            [
                'id_kategori'     => $petaKategori['cagar-budaya'],
                'nama'            => 'Candi Borobudur',
                'slug'            => 'candi-borobudur',
                'kota'            => 'Magelang',
                'provinsi'        => 'Jawa Tengah',
                'alamat'          => 'Jl. Badrawati, Kw. Candi Borobudur, Borobudur, Kab. Magelang',
                'garis_lintang'   => -7.6078738,
                'garis_bujur'     => 110.2037513,
                'harga_tiket'     => 50000,
                'jam_buka'        => '06:30:00',
                'jam_tutup'       => '16:30:00',
                'deskripsi'       => 'Monumen Buddha terbesar di dunia yang dibangun pada abad ke-8 dengan 2.672 panel relief dan 504 arca Buddha, dikelilingi lanskap Perbukitan Menoreh.',
                'fasilitas'       => json_encode(['Parkir Luas', 'Musholla', 'Toilet Bersih', 'Pemandu Resmi', 'Akses Kursi Roda', 'Pusat Kuliner']),
                'foto_utama'      => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80',
                'rata_rating'     => 4.90,
                'jumlah_ulasan'   => 1420,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id_kategori'     => $petaKategori['pegunungan-geopark'],
                'nama'            => 'Kawah Ijen',
                'slug'            => 'kawah-ijen',
                'kota'            => 'Banyuwangi',
                'provinsi'        => 'Jawa Timur',
                'alamat'          => 'Pos Paltuding, Tamansari, Licin, Kab. Banyuwangi',
                'garis_lintang'   => -8.0583000,
                'garis_bujur'     => 114.2425000,
                'harga_tiket'     => 15000,
                'jam_buka'        => '02:00:00',
                'jam_tutup'       => '12:00:00',
                'deskripsi'       => 'Danau kawah asam terbesar di dunia dengan warna hijau toska yang ikonik serta fenomena api biru (Blue Fire) dini hari di perbatasan Banyuwangi dan Bondowoso.',
                'fasilitas'       => json_encode(['Area Parkir Paltuding', 'Warung Kopi Hangat', 'Toilet', 'Penyewaan Masker Gas', 'Pos Kesehatan']),
                'foto_utama'      => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1200&q=80',
                'rata_rating'     => 4.85,
                'jumlah_ulasan'   => 980,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id_kategori'     => $petaKategori['wisata-bahari'],
                'nama'            => 'Pulau Padar, Taman Nasional Komodo',
                'slug'            => 'pulau-padar-komodo',
                'kota'            => 'Labuan Bajo',
                'provinsi'        => 'Nusa Tenggara Timur',
                'alamat'          => 'Kawasan Taman Nasional Komodo, Manggarai Barat',
                'garis_lintang'   => -8.6594440,
                'garis_bujur'     => 119.5588890,
                'harga_tiket'     => 150000,
                'jam_buka'        => '05:30:00',
                'jam_tutup'       => '17:30:00',
                'deskripsi'       => 'Bukit sabana eksotis yang mempertemukan tiga teluk dengan pasir berwarna putih, hitam, dan merah muda (pink beach) dalam satu panorama.',
                'fasilitas'       => json_encode(['Dermaga Kapal', 'Jalur Anak Tangga', 'Pos Ranger TNK', 'Spot Fotografi']),
                'foto_utama'      => 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80',
                'rata_rating'     => 4.95,
                'jumlah_ulasan'   => 1150,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id_kategori'     => $petaKategori['cagar-budaya'],
                'nama'            => 'Desa Adat Penglipuran',
                'slug'            => 'desa-adat-penglipuran',
                'kota'            => 'Bangli',
                'provinsi'        => 'Bali',
                'alamat'          => 'Jl. Penglipuran, Kubu, Kec. Bangli, Kabupaten Bangli',
                'garis_lintang'   => -8.4222000,
                'garis_bujur'     => 115.3592000,
                'harga_tiket'     => 25000,
                'jam_buka'        => '08:00:00',
                'jam_tutup'       => '18:30:00',
                'deskripsi'       => 'Desa adat terbersih dengan tata ruang tradisional konsep Tri Mandala dan hutan bambu seluas 45 hektar yang terjaga kelestariannya.',
                'fasilitas'       => json_encode(['Parkir Luas', 'Toilet Bersih', 'Hutan Bambu', 'Warung Loloh Cemcem', 'Homestay Warga']),
                'foto_utama'      => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',
                'rata_rating'     => 4.80,
                'jumlah_ulasan'   => 860,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id_kategori'     => $petaKategori['pegunungan-geopark'],
                'nama'            => 'Taman Nasional Bromo Tengger Semeru',
                'slug'            => 'bromo-tengger-semeru',
                'kota'            => 'Malang',
                'provinsi'        => 'Jawa Timur',
                'alamat'          => 'Kawasan Kaldera Tengger, Malang - Probolinggo - Pasuruan',
                'garis_lintang'   => -7.9424936,
                'garis_bujur'     => 112.9530122,
                'harga_tiket'     => 34000,
                'jam_buka'        => '00:01:00',
                'jam_tutup'       => '23:59:00',
                'deskripsi'       => 'Hamparan lautan pasir vulkanik seluas 10 kilometer persegi dengan lanskap matahari terbit dari Penanjakan dan budaya masyarakat suku Tengger.',
                'fasilitas'       => json_encode(['Sewa Jeep Hardtop', 'Musholla', 'Toilet', 'Warung Hangat', 'Gardu Pandang Sunrise']),
                'foto_utama'      => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80',
                'rata_rating'     => 4.92,
                'jumlah_ulasan'   => 2130,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id_kategori'     => $petaKategori['hutan-air-terjun'],
                'nama'            => 'Air Terjun Tumpak Sewu',
                'slug'            => 'air-terjun-tumpak-sewu',
                'kota'            => 'Lumajang',
                'provinsi'        => 'Jawa Timur',
                'alamat'          => 'Jl. Raya Sidomulyo, Besukcukit, Pronojiwo, Kab. Lumajang',
                'garis_lintang'   => -8.2302778,
                'garis_bujur'     => 112.9175000,
                'harga_tiket'     => 20000,
                'jam_buka'        => '07:00:00',
                'jam_tutup'       => '15:00:00',
                'deskripsi'       => 'Formasi air terjun bertirai setengah lingkaran setinggi 120 meter yang dialiri Sungai Glidik langsung dari kaki Gunung Semeru.',
                'fasilitas'       => json_encode(['Gardu Panorama', 'Pemandu Lokal', 'Parkir', 'Kamar Bilas', 'Musholla']),
                'foto_utama'      => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1200&q=80',
                'rata_rating'     => 4.88,
                'jumlah_ulasan'   => 640,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
        ];
        $this->db->table('destinasi')->insertBatch($daftarDestinasi);
    }
}
