<?php
/**
 * Tampilan Beranda (Landing Page Statis) — IndWalk: Explore Indonesia Together
 *
 * File ini disusun menggunakan standar Clean Code Berbahasa Indonesia
 * (.agents/rules/clean-code-indonesia.md) dan aturan desain Anti-AI-Slop
 * (.agents/rules/anti-ai-slop.md) sebagai gambaran awal untuk seluruh anggota kelompok.
 */

if (! function_exists('formatHargaRupiah')) {
    /**
     * Memformat angka harga tiket menjadi format mata uang Rupiah yang rapi.
     */
    function formatHargaRupiah(int $hargaTiket): string
    {
        if ($hargaTiket > 0) {
            $hargaTerformat = 'Rp ' . number_format($hargaTiket, 0, ',', '.');
        } else {
            $hargaTerformat = 'Gratis';
        }

        return $hargaTerformat;
    }
}

if (! function_exists('formatKoordinatLokasi')) {
    /**
     * Memformat titik garis lintang dan garis bujur menjadi teks koordinat GPS.
     */
    function formatKoordinatLokasi(float $garisLintang, float $garisBujur): string
    {
        if ($garisLintang < 0) {
            $arahLintang = 'S';
        } else {
            $arahLintang = 'N';
        }

        if ($garisBujur < 0) {
            $arahBujur = 'W';
        } else {
            $arahBujur = 'E';
        }

        $nilaiLintang = number_format(abs($garisLintang), 4, '.', '');
        $nilaiBujur   = number_format(abs($garisBujur), 4, '.', '');

        return $nilaiLintang . '° ' . $arahLintang . ', ' . $nilaiBujur . '° ' . $arahBujur;
    }
}

// Daftar menu navigasi utama pada bagian header
$daftarMenuNavigasi = [
    ['label' => 'Beranda', 'tautan' => '#', 'path_data' => 'beranda', 'aktif' => true],
    ['label' => 'Destinasi Unggulan', 'tautan' => '#destinasi-kurator', 'path_data' => 'destinasi-unggulan', 'aktif' => false],
    ['label' => 'Kategori Wisata', 'tautan' => '#katalog', 'path_data' => 'kategori-wisata', 'aktif' => false],
    ['label' => 'Panduan Perjalanan', 'tautan' => '#jelajah-kepulauan', 'path_data' => 'panduan-perjalanan', 'aktif' => false],
    ['label' => 'Komunitas', 'tautan' => '#komunitas-jurnal', 'path_data' => 'komunitas', 'aktif' => false],
];

// Daftar kata kunci pencarian populer di bawah kolom pencarian Hero
$tagPencarianPopuler = [
    'Raja Ampat',
    'Tana Toraja',
    'Danau Paisupok',
    'Banda Neira',
];

// Data ringkasan statistik platform pada bagian Hero
$daftarStatistikUtama = [
    [
        'angka'       => '1.200+',
        'keterangan'  => 'Destinasi Terkurasi',
        'kelas_warna' => 'text-primary',
    ],
    [
        'angka'       => '480+',
        'keterangan'  => 'Pemandu Lokal Berlisensi',
        'kelas_warna' => 'text-secondary',
    ],
    [
        'angka'       => '98%',
        'keterangan'  => 'Ulasan Petualang Positif',
        'kelas_warna' => 'text-tertiary-container',
    ],
];

// Data destinasi utama pada kartu sorotan Hero (Kanan)
$destinasiUtama = [
    'nama'             => 'Pulau Padar & Perairan Komodo',
    'kawasan'          => 'Taman Nasional Komodo',
    'label_editor'     => 'Pilihan Editor Minggu Ini',
    'kota'             => 'Manggarai Barat',
    'provinsi'         => 'NTT',
    'garis_lintang'    => -8.5833,
    'garis_bujur'      => 119.4833,
    'rata_rating'      => 4.9,
    'jumlah_ulasan'    => 1420,
    'label_harga'      => 'Tiket Masuk Konservasi',
    'harga_tiket'      => 150000,
    'daftar_fasilitas' => ['Trekking Bukit', 'Diving Manta Ray', 'Fauna Endemik'],
    'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAJt-F3o83WMR2NF_kbbtek4zBMELOi1ek1Z0rQDUq4rHcKaT0DJDu3LXRsb5M_JeDE7xYgZMTAQzeufh2TqLUE4lNowebUjc6vyc99mQlkv-Qjx2CJBwxtTCBIbEwXhdYm0VqMGh4nYXSV8IwpZMQe_BFtjG3Slnp7K6KjtSHq7fC6pYsOzhjMxWXJSJ5I7xrQtNIkjnnMnUC9Yyj-6ZABIZ8pxrbNUBH2_j-RBNw',
    'alt_foto'         => 'Pemandangan udara Pulau Padar dan Taman Nasional Komodo di Nusa Tenggara Timur',
];

// Data destinasi unggulan utama (Kartu Besar Kiri pada Bento Grid)
$destinasiUnggulanUtama = [
    'nama'                => 'Candi Borobudur: Mahakarya Arsitektur Dinasti Syailendra',
    'kategori'            => 'Warisan Dunia UNESCO',
    'lokasi'              => 'Kecamatan Borobudur, Kab. Magelang, Jawa Tengah',
    'deskripsi'           => 'Monumen Buddha terbesar di dunia yang dibangun abad ke-8. Rasakan keheningan spiritual saat kabut pagi perlahan beranjak menyingkap relief karmawibhangga dan stupa berlubang yang menghadap panorama pegunungan Menoreh.',
    'jam_buka'            => '06.30 – 17.00 WIB',
    'ringkasan_fasilitas' => 'Pemandu, Kursi Roda, Musholla',
    'rata_rating'         => 4.95,
    'jumlah_ulasan'       => 3840,
    'label_harga'         => 'Tiket Reguler Domestik',
    'harga_tiket'         => 50000,
    'url_foto'            => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAkWbufXuExA8mAVo4-PHVbIJKOFN4YCwXEbYFUzxmQC3yVaSaS7hJZ5bRP3T3LSNxLa9zhxTix-gPpUg8SZydndKeReobdqYr3b040G0H1215GdqRaxaDKKQWt-KapZQL84bzjpnbQyMtMgGsvDwv-o1y26mk6Fb8asBVLU0CQDiBuRJe4debROen0s1cu88vrnvR3s0jUx_FROWDO6ptPjaWrlTzYKQasDnsX_7g',
    'alt_foto'            => 'Matahari terbit di atas stupa Candi Borobudur Magelang Jawa Tengah',
];

// Daftar 2 destinasi unggulan pendamping (Kartu Bertumpuk Kanan pada Bento Grid)
$daftarDestinasiUnggulanPendamping = [
    [
        'nama'          => 'Kawah Ijen & Fenomena Api Biru',
        'kategori'      => 'Geopark & Vulkanik',
        'kelas_badge'   => 'bg-surface-container-lowest/90 backdrop-blur-sm text-tertiary-container font-bold',
        'lokasi'        => 'Banyuwangi, Jawa Timur',
        'deskripsi'     => 'Puncak vulkanik spektakuler dengan danau kawah asam tercantik di dunia dan fenomena api biru langka di kaldera belerang.',
        'jam_buka'      => '02.00 WIB (Blue Fire)',
        'ikon_jam'      => 'nightlight',
        'info_tambahan' => '8°C – 14°C',
        'ikon_info'     => 'thermostat',
        'rata_rating'   => 4.88,
        'jumlah_ulasan' => 2190,
        'harga_tiket'   => 20000,
        'url_foto'      => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBual-AfAM7eIHLJW-_6mVEwXlIE5NDSeSm0x-R4p0fwIHR15pmMS03phJcd47GXDxWUvaFp09cCjL2bqqWHaQ7s5U3YrsDT-aYItE7pXRjphzISLftLsowaqOjMw-EwdF13ykSZLqY4HAjncqhjaLLoRArSe8kdpkpYCaOPivB5ghiAjC7tS6RnZhVmYAJ4Lt0abYufaKbuB6fiRVId7v0Xnto2OJ5gYMKCOlcuIQ',
        'alt_foto'      => 'Danau kawah vulkanik pirus Kawah Ijen di Banyuwangi Jawa Timur',
    ],
    [
        'nama'          => 'Terasering Subak Tegallalang',
        'kategori'      => 'Agrowisata & Lanskap',
        'kelas_badge'   => 'bg-secondary text-on-secondary shadow-sm',
        'lokasi'        => 'Gianyar, Ubud, Bali',
        'deskripsi'     => 'Harmoni tata kelola air komunal Subak Bali yang telah diakui peradaban dunia, dikelilingi ribuan pohon kelapa asri.',
        'jam_buka'      => '08.00 – 18.00 WITA',
        'ikon_jam'      => 'wb_sunny',
        'info_tambahan' => 'Jalur Trekking Subak',
        'ikon_info'     => 'hiking',
        'rata_rating'   => 4.82,
        'jumlah_ulasan' => 1760,
        'harga_tiket'   => 25000,
        'url_foto'      => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDoCutxtBU9_Fh5bbP7fYzUEILVXsYb6zrTw2TozBZH-KfqgAT5t0FnK7NI1Ji1zl-ln0weO4HpR2KLJKvBuctL0k9tD4PlwoHRaNQ_mR3FfLsKiqOI3T9y4XzaJHvgbuhCV3u8F3puw1PK6RMOdXT3fSz9td7bQ5X5Z4sUsXb_KiXiQLptl7kJzsm_zEbwEZCwGwWLiCbOqr_htlCZ8Z3HEkST0IXiFkKzw_SL24g',
        'alt_foto'      => 'Hamparan sawah terasering hijau Subak Tegallalang di Ubud Gianyar Bali',
    ],
];

// Daftar tombol filter kategori pada bagian Katalog Nusantara
$daftarFilterKategori = [
    ['label' => 'Semua Destinasi (128)', 'aktif' => true],
    ['label' => 'Wisata Bahari (42)', 'aktif' => false],
    ['label' => 'Alam & Geopark (36)', 'aktif' => false],
    ['label' => 'Budaya & Sejarah (28)', 'aktif' => false],
    ['label' => 'Ekowisata & Rimba (22)', 'aktif' => false],
];

// Daftar 6 destinasi wisata pada grid Eksplorasi Katalog Nusantara
$daftarKatalogDestinasi = [
    [
        'nama'             => 'Kepulauan Raja Ampat',
        'kategori'         => 'Wisata Bahari',
        'kelas_badge'      => 'bg-surface-container-lowest/90 backdrop-blur-sm text-primary font-bold',
        'lokasi'           => 'Kab. Raja Ampat, Papua Barat Daya',
        'jam_buka'         => 'Waktu Terbaik: Okt – Apr',
        'deskripsi'        => 'Jantung segitiga terumbu karang bumi dengan keanekaragaman hayati laut terkaya di dunia.',
        'daftar_fasilitas' => ['Perahu Sewa', 'Homestay Lokal', 'Alat Selam'],
        'rata_rating'      => 4.98,
        'jumlah_ulasan'    => 920,
        'label_harga'      => 'Tarif Konservasi',
        'harga_tiket'      => 300000,
        'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuA1QLu1JDTQzVSVcx2sYivwhnZ_NZHrtiMI_qJtXzMFLM3I0_i8u5-Ii_lb7Vyp2Xw7Ok5Zij9g7WhHYdOc5cedXYxS35u3mdtHe9auAnq-JuqUqXRh07BEY0iI9oD1zIsf8ZYq6_H48R52ov5zF9-a8r3WRp3L9gvbGJZPXrMV8_0VLcAqp3Ux7wDbEY7fdMR2-3RUWB5UoM11p4xtcu7VqzCHA7DSXQlpc1xv2iY',
        'alt_foto'         => 'Gugusan pulau karst Wayag di Kepulauan Raja Ampat Papua Barat Daya',
    ],
    [
        'nama'             => 'Danau Toba & Pulau Samosir',
        'kategori'         => 'Geopark Kaldera',
        'kelas_badge'      => 'bg-surface-container-lowest/90 backdrop-blur-sm text-secondary font-bold',
        'lokasi'           => 'Toba Samosir, Sumatera Utara',
        'jam_buka'         => 'Buka Setiap Hari',
        'deskripsi'        => 'Telusuri warisan budaya luhur Batak Toba, Rumah Bolon megah, dan keajaiban kaldera purba raksasa.',
        'daftar_fasilitas' => ['Feri Penyeberangan', 'Tepi Danau', 'Resto Halal'],
        'rata_rating'      => 4.87,
        'jumlah_ulasan'    => 1640,
        'label_harga'      => 'Tiket Kawasan',
        'harga_tiket'      => 15000,
        'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDuf8-hbhks4cFzx3OLeREDy7YTvNaC8YvC0AnGKkQWPFgPKEU941tN0BVcl_lSnfQ4VZruY2F62uh4dxYxUuWnMyd5wtVOOL5xChFUcUdIwiBnt4VQK017GlSbyZ4mzzPxzItvZ4bXMsAug7_suS3Lntm2wa2Dk4SD-2qS50mDJuuiEbnmj6MnsmOtC6ubXRZKcAZMbKEVHUy2jxGigAq0DKVhyPMTdvwGM3yenKg',
        'alt_foto'         => 'Panorama kaldera vulkanik Danau Toba dan Pulau Samosir di Sumatera Utara',
    ],
    [
        'nama'             => 'Pantai Tangsi (Pink Beach)',
        'kategori'         => 'Wisata Bahari',
        'kelas_badge'      => 'bg-surface-container-lowest/90 backdrop-blur-sm text-primary font-bold',
        'lokasi'           => 'Jerowaru, Lombok Timur, NTB',
        'jam_buka'         => '07.00 – 18.00 WITA',
        'deskripsi'        => 'Keajaiban pasir merah muda alami berkat serpihan mikroorganisme karang Foraminifera merah.',
        'daftar_fasilitas' => ['Gazebo Bambu', 'Kelapa Muda', 'Spot Foto Tebing'],
        'rata_rating'      => 4.84,
        'jumlah_ulasan'    => 850,
        'label_harga'      => 'Tiket Masuk',
        'harga_tiket'      => 25000,
        'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBAzYpDnCnVstjTZtMV1ZR2fpaqxPwJOsIH08AD4VbRH2nOR1Sh4kLOXpLm1F7oWLN0Sxm0YHbqN601Ku-LJYScc75H1xycT3VCfd8UcMybBkXEd67VUqSEI8kv43kyc795qNZ9pIApZv0rP8Dy_x91oMqyzrxDbcmYcX8N47rauE3G-OnNnLp-ZvlHWghQE3ncvRWHbBtajYHpMffDyQbzIPYczbReqgJ9U1mH-TI',
        'alt_foto'         => 'Pantai Tangsi Pink Beach dengan pasir merah muda alami di Lombok Timur NTB',
    ],
    [
        'nama'             => 'Gunung Bromo & Lautan Pasir',
        'kategori'         => 'Alam & Vulkanik',
        'kelas_badge'      => 'bg-surface-container-lowest/90 backdrop-blur-sm text-tertiary-container font-bold',
        'lokasi'           => 'TNBTS, Jawa Timur',
        'jam_buka'         => 'Buka 24 Jam',
        'deskripsi'        => 'Saksikan matahari terbit magis di Penanjakan dan gemuruh kawah aktif di tengah lautan pasir kaldera Tengger.',
        'daftar_fasilitas' => ['Jeep 4x4', 'Sewa Kuda', 'Kopi Tengger'],
        'rata_rating'      => 4.91,
        'jumlah_ulasan'    => 3210,
        'label_harga'      => 'Tiket Hari Kerja',
        'harga_tiket'      => 29000,
        'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDj6pu0GBTBtu_YeZj06pAk3NjaeXss2HtuubF6YlWMoFzYQN6S0_5ZWuHMoHrg2c5AvFX2hLaC8tKOGMAgDECWyQYHxKiDqkywi8-kfKJ5DQC82-HUq2r3DaQR1ggiuxOOK_SEkGmfI0xT3nija2Fx6cbS7nM7PCeiq5JtW9kpuJPTZvzh6XtmScyA7reWKxpRYXcLT293ea1DiaoQH2UBb_u4JLIhLbZimkzhlT0',
        'alt_foto'         => 'Kaldera Gunung Bromo dan lautan pasir Tengger saat matahari terbit di Jawa Timur',
    ],
    [
        'nama'             => 'Desa Adat Penglipuran',
        'kategori'         => 'Budaya & Warisan',
        'kelas_badge'      => 'bg-secondary text-on-secondary shadow-sm',
        'lokasi'           => 'Kec. Bangli, Bali',
        'jam_buka'         => '08.00 – 18.30 WITA',
        'deskripsi'        => 'Diakui sebagai salah satu desa terbersih di dunia yang memegang teguh filosofi Tri Hita Karana.',
        'daftar_fasilitas' => ['Hutan Bambu', 'Kerajinan Tangan', 'Parkir Luas'],
        'rata_rating'      => 4.93,
        'jumlah_ulasan'    => 2430,
        'label_harga'      => 'Tiket Masuk',
        'harga_tiket'      => 25000,
        'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBFiZSZW6kpK1Du-tnf5U5Gb6JUrsanZCGvxHCXauVCk-DCmo28dqp0oIWhWJXKZrL95fl3LCKOnfiZ_B1fNWWRz0hTPxzmRcnFCQC03l31glAZNSJzxkb9bZyz5WqVJE7NwNv6WJU0dx0J8DG2ipnylof6kC7u3Cw9HUYA-tfLlkvayLY_zZ2PJ98ZCg_Nho6BZBjlg5E74WL_ZeLaXMk0ya2cWZQlEr5cYKr0osQ',
        'alt_foto'         => 'Jalur desa adat Penglipuran dengan gerbang tradisional angkul-angkul di Bangli Bali',
    ],
    [
        'nama'             => 'Kepulauan Derawan & Danau Kakaban',
        'kategori'         => 'Ekowisata Bahari',
        'kelas_badge'      => 'bg-secondary text-on-secondary shadow-sm',
        'lokasi'           => 'Kab. Berau, Kalimantan Timur',
        'jam_buka'         => 'Konservasi Bahari',
        'deskripsi'        => 'Berenang bersama ribuan ubur-ubur tanpa sengat di Danau Kakaban dan habitat peneluran penyu hijau.',
        'daftar_fasilitas' => ['Diving Center', 'Pemandu Lokal', 'Speedboat'],
        'rata_rating'      => 4.90,
        'jumlah_ulasan'    => 780,
        'label_harga'      => 'Retribusi Dermaga',
        'harga_tiket'      => 20000,
        'url_foto'         => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAy7AP2hYABilutqDOcoQPvZtqaBop9XbvSepsc-6s8q-AW_1tZAlUtzhyZnYcnInKQ784ZtWHYstr4w5vtYN1W8Z9ygvOrXulEbqBtisou4z1CQTpRCtWNt4Op3-viglWxiFictCq7-IYecIhJ1kotS2C7kvFVhT4cygOrHlC4lrxqQRBsl97sXlvGSwaykOltxBmETS0zy6XXJgUwNhyoMmzCH_GUVtsNnQqlVq0',
        'alt_foto'         => 'Perairan laguna jernih Kepulauan Derawan dan Danau Kakaban di Berau Kalimantan Timur',
    ],
];

// Daftar 6 wilayah kepulauan Indonesia pada bagian Jelajah Berdasarkan Kepulauan
$daftarWilayahKepulauan = [
    [
        'nama_kepulauan'   => 'Sumatera',
        'jumlah_destinasi' => 24,
        'ikon'             => 'forest',
        'kelas_ikon'       => 'bg-primary/10 text-primary',
    ],
    [
        'nama_kepulauan'   => 'Jawa',
        'jumlah_destinasi' => 38,
        'ikon'             => 'temple_buddhist',
        'kelas_ikon'       => 'bg-secondary/10 text-secondary',
    ],
    [
        'nama_kepulauan'   => 'Bali & Nusa Tenggara',
        'jumlah_destinasi' => 32,
        'ikon'             => 'surfing',
        'kelas_ikon'       => 'bg-tertiary-container/10 text-tertiary-container',
    ],
    [
        'nama_kepulauan'   => 'Kalimantan',
        'jumlah_destinasi' => 14,
        'ikon'             => 'park',
        'kelas_ikon'       => 'bg-secondary/10 text-secondary',
    ],
    [
        'nama_kepulauan'   => 'Sulawesi',
        'jumlah_destinasi' => 16,
        'ikon'             => 'kayaking',
        'kelas_ikon'       => 'bg-primary/10 text-primary',
    ],
    [
        'nama_kepulauan'   => 'Maluku & Papua',
        'jumlah_destinasi' => 18,
        'ikon'             => 'anchor',
        'kelas_ikon'       => 'bg-tertiary-container/10 text-tertiary-container',
    ],
];

// URL logo utama IndWalk (menggunakan aset lokal di public/assets/)
$urlLogoIndWalk = base_url('assets/IndWalk_ Explore Indonesia Together.png');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="shell-type" content="web_standard" />
    <title>IndWalk — Explore Indonesia Together</title>

    <!-- Google Fonts: Playfair Display (Heading Serif) & Plus Jakarta Sans (Body Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap"
        rel="stylesheet"
    />

    <!-- Reset Dasar Halaman -->
    <style>
        @layer base {
            html,
            body {
                margin: 0;
                padding: 0;
            }
            body {
                overscroll-behavior: none;
            }
            main > :first-child {
                margin-top: 0 !important;
            }
            main > :last-child {
                margin-bottom: 0 !important;
            }
        }
        ::-webkit-scrollbar {
            display: none;
        }
    </style>

    <!-- Tailwind CSS CDN & Konfigurasi Design Tokens Resmi IndWalk -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "outline": "#6f797a",
                        "error-container": "#ffdad6",
                        "surface-tint": "#006972",
                        "tertiary": "#8c2900",
                        "on-secondary-fixed-variant": "#23502d",
                        "secondary": "#3b6843",
                        "inverse-surface": "#33302d",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#f4ece8",
                        "tertiary-fixed": "#ffdbd0",
                        "tertiary-container": "#b53801",
                        "inverse-on-surface": "#f7efeb",
                        "on-surface": "#1e1b19",
                        "surface": "#fff8f5",
                        "surface-container-low": "#faf2ee",
                        "on-primary-container": "#9eedf7",
                        "on-error-container": "#93000a",
                        "on-tertiary-container": "#ffd9cd",
                        "surface-variant": "#e9e1dd",
                        "on-secondary-fixed": "#00210a",
                        "on-error": "#ffffff",
                        "surface-dim": "#e0d8d5",
                        "on-surface-variant": "#3f494a",
                        "on-secondary": "#ffffff",
                        "surface-container-high": "#eee7e3",
                        "on-tertiary": "#ffffff",
                        "inverse-primary": "#84d3dd",
                        "on-primary": "#ffffff",
                        "primary-container": "#0b6e77",
                        "tertiary-fixed-dim": "#ffb59d",
                        "primary-fixed-dim": "#84d3dd",
                        "outline-variant": "#bec8ca",
                        "on-secondary-container": "#3f6c47",
                        "secondary-fixed": "#bcefc0",
                        "on-primary-fixed": "#001f23",
                        "on-tertiary-fixed-variant": "#832600",
                        "on-background": "#1e1b19",
                        "surface-bright": "#fff8f5",
                        "background": "#fff8f5",
                        "primary-fixed": "#a0eff9",
                        "secondary-fixed-dim": "#a1d3a5",
                        "secondary-container": "#b9ecbd",
                        "on-primary-fixed-variant": "#004f56",
                        "error": "#ba1a1a",
                        "on-tertiary-fixed": "#390c00",
                        "surface-container-highest": "#e9e1dd",
                        "primary": "#00545b"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "space-xs": "0.25rem",
                        "space-sm": "0.5rem",
                        "space-md": "1rem",
                        "space-lg": "1.5rem",
                        "space-xl": "2.5rem",
                        "gutter-mobile": "1rem",
                        "margin-mobile": "1rem",
                        "gutter": "1.5rem",
                        "margin": "2.5rem"
                    },
                    fontFamily: {
                        "body-lg": ["Plus Jakarta Sans"],
                        "display-hero": ["Playfair Display"],
                        "headline-md": ["Playfair Display"],
                        "metric-price": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Playfair Display"],
                        "title-editorial": ["Playfair Display"],
                        "display-hero-mobile": ["Playfair Display"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Playfair Display"],
                        "headline-sm": ["Playfair Display"],
                        "label-caps": ["Plus Jakarta Sans"],
                        "metric-rating": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "body-lg": ["16px", { "lineHeight": "26px", "fontWeight": "400" }],
                        "display-hero": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "metric-price": ["18px", { "lineHeight": "22px", "fontWeight": "700" }],
                        "label-md": ["13px", { "lineHeight": "16px", "letterSpacing": "0.01em", "fontWeight": "600" }],
                        "headline-lg": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.015em", "fontWeight": "600" }],
                        "title-editorial": ["18px", { "lineHeight": "24px", "fontWeight": "500" }],
                        "display-hero-mobile": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "body-sm": ["12px", { "lineHeight": "18px", "fontWeight": "400" }],
                        "body-md": ["14px", { "lineHeight": "22px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["26px", { "lineHeight": "34px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "label-caps": ["11px", { "lineHeight": "14px", "letterSpacing": "0.08em", "fontWeight": "700" }],
                        "metric-rating": ["14px", { "lineHeight": "18px", "fontWeight": "700" }]
                    }
                }
            }
        };
    </script>
</head>

<body class="bg-surface font-body-md text-on-surface antialiased">
    <!-- =====================================================================
         BAGIAN HEADER & NAVIGASI UTAMA (STICKY TOP NAVBAR)
         ===================================================================== -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-md border-b border-surface-variant/40">
        <div class="h-20 max-w-7xl mx-auto px-6 flex items-center justify-between gap-space-md">
            <!-- Identitas Brand & Logo IndWalk -->
            <a href="<?= base_url('/') ?>" class="flex items-center gap-3 shrink-0">
                <img
                    src="<?= esc($urlLogoIndWalk) ?>"
                    alt="IndWalk Logo"
                    class="h-8 w-auto object-contain"
                />
                <div class="flex flex-col">
                    <span class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-none">
                        IndWalk
                    </span>
                    <span class="font-label-caps text-label-caps text-tertiary-container uppercase mt-0.5">
                        Nusantara
                    </span>
                </div>
            </a>

            <!-- Tautan Navigasi Tengah -->
            <nav
                class="hidden xl:flex items-center gap-1"
                data-active-classes="bg-primary-container text-on-primary font-label-md rounded-lg"
            >
                <?php foreach ($daftarMenuNavigasi as $menuItem) : ?>
                    <?php
                    if ($menuItem['aktif']) {
                        $kelasNavigasi = 'px-3 py-2 transition-all bg-primary-container text-on-primary font-label-md rounded-lg';
                    } else {
                        $kelasNavigasi = 'font-label-md text-label-md px-3 py-2 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all';
                    }
                    ?>
                    <a
                        href="<?= esc($menuItem['tautan']) ?>"
                        data-path="<?= esc($menuItem['path_data']) ?>"
                        class="<?= esc($kelasNavigasi) ?>"
                        <?= $menuItem['aktif'] ? 'aria-current="page"' : '' ?>
                    >
                        <?= esc($menuItem['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Tombol Aksi Kanan (Mata Uang, Masuk, Jelajahi Sekarang) -->
            <div class="flex items-center gap-space-sm sm:gap-3 shrink-0">
                <div class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container text-on-surface-variant font-label-md text-label-md">
                    <span class="text-tertiary-container font-bold">Rp</span>
                    <span>IDR</span>
                </div>
                <a
                    href="#masuk"
                    data-path="login"
                    class="hidden md:inline-flex items-center justify-center px-4 py-2 rounded-lg border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-all"
                >
                    Masuk
                </a>
                <a
                    href="#destinasi-kurator"
                    data-path="destinasi-unggulan"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-[0_1px_3px_0_rgba(28,25,23,0.04)]"
                >
                    Jelajahi Sekarang
                </a>
                <a href="#profil" data-path="profil-petualang" class="flex items-center shrink-0 ml-1">
                    <img
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDeiO-qKMIBqy30kLvxlFMODby1p8aOdFQ0mIyG90PWEpaWO__UE948okMlpXhJ5ENI1ZLxKrlU9t4sU6v-hlbR4Swc_aPTwDnlWFjzz-OvpiN6b_1a4tK9WNRou-JjKJ0YVn-88mqx3cpPTTBjkKJpeCKMa3pUJbtSpFNv7APUZadHSDscLaj7BLzM0V-Y56cRZa5ujbKczsnYgClQ33I4aCyuBCV3mb6e_RJQ-rs"
                        alt="Profil Petualang"
                        class="w-8 h-8 rounded-full object-cover ring-2 ring-surface-variant"
                    />
                </a>
            </div>
        </div>
    </header>

    <!-- =====================================================================
         KONTEN UTAMA HALAMAN BERANDA
         ===================================================================== -->
    <main class="w-full pt-20 bg-surface min-h-[calc(100vh-80px)]">
        <div class="flex flex-col w-full">

            <!-- 1. SEKSI HERO ASIMETRIS & PENCARIAN DESTINASI -->
            <div class="relative w-full overflow-hidden">
                <div class="absolute -top-40 right-10 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute top-96 -left-32 w-80 h-80 bg-tertiary-container/5 rounded-full blur-3xl pointer-events-none"></div>

                <section class="max-w-7xl mx-auto px-6 pt-8 pb-16">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

                        <!-- Kolom Kiri (7 Kolom): Judul Editorial & Bar Pencarian 4 Kolom -->
                        <div class="lg:col-span-7 flex flex-col justify-center">
                            <div class="inline-flex items-center gap-2 self-start px-3.5 py-1.5 rounded-full bg-secondary-container/50 text-on-secondary-container">
                                <span class="text-xs">✦</span>
                                <span class="font-label-caps text-label-caps uppercase tracking-wider text-secondary">
                                    KURASI WISATA NUSANTARA 2025
                                </span>
                            </div>

                            <h1 class="font-display-hero text-display-hero text-on-surface tracking-tight mt-4 max-w-2xl">
                                Temukan Lanskap Nusantara yang Belum Banyak Dijamah
                            </h1>

                            <p class="font-body-lg text-body-lg text-on-surface-variant mt-5 max-w-xl leading-relaxed">
                                Jelajahi keajaiban tersembunyi dari gugusan pulau Flores hingga kedalaman rimba Papua. Temukan panduan terverifikasi, peta rute GPS, dan kontribusi komunitas lokal untuk petualangan yang bertanggung jawab.
                            </p>

                            <!-- Kartu Pencarian & Filter Interaktif (4 Kolom) -->
                            <div class="bg-surface-container-lowest rounded-2xl p-3 shadow-md mt-8 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                <!-- Input 1: Lokasi / Tujuan -->
                                <div class="sm:col-span-3 flex flex-col px-3 py-2 rounded-xl hover:bg-surface-container transition-colors">
                                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-primary">location_on</span>
                                        Destinasi
                                    </span>
                                    <input
                                        type="text"
                                        value="Labuan Bajo, NTT"
                                        placeholder="Tujuan Anda"
                                        class="font-label-md text-label-md text-on-surface bg-transparent focus:outline-none truncate mt-0.5"
                                    />
                                    <span class="font-body-sm text-body-sm text-outline truncate text-[11px] leading-tight">
                                        Manggarai Barat
                                    </span>
                                </div>

                                <!-- Input 2: Pilihan Kategori Wisata -->
                                <div class="sm:col-span-3 flex flex-col px-3 py-2 rounded-xl hover:bg-surface-container transition-colors">
                                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-secondary">explore</span>
                                        Kategori
                                    </span>
                                    <select class="font-label-md text-label-md text-on-surface bg-transparent focus:outline-none mt-0.5 cursor-pointer">
                                        <option selected>Bahari &amp; Coral</option>
                                        <option>Geopark &amp; Vulkanik</option>
                                        <option>Warisan Budaya</option>
                                        <option>Ekowisata Rimba</option>
                                    </select>
                                    <span class="font-body-sm text-body-sm text-outline truncate text-[11px] leading-tight">
                                        42 Rute Tersedia
                                    </span>
                                </div>

                                <!-- Input 3: Pilihan Rentang Budget -->
                                <div class="sm:col-span-3 flex flex-col px-3 py-2 rounded-xl hover:bg-surface-container transition-colors">
                                    <span class="font-label-caps text-label-caps text-on-surface-variant uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-tertiary-container">payments</span>
                                        Rentang Budget
                                    </span>
                                    <select class="font-label-md text-label-md text-on-surface bg-transparent focus:outline-none mt-0.5 cursor-pointer">
                                        <option selected>Rp 500rb - 2jt</option>
                                        <option>Di bawah Rp 500rb</option>
                                        <option>Rp 2jt - Rp 5jt</option>
                                        <option>Ekspedisi Khusus</option>
                                    </select>
                                    <span class="font-body-sm text-body-sm text-outline truncate text-[11px] leading-tight">
                                        Per Wisatawan
                                    </span>
                                </div>

                                <!-- Input 4: Tombol Aksi Cari Destinasi -->
                                <div class="sm:col-span-3">
                                    <button
                                        type="button"
                                        class="w-full py-3.5 px-4 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md flex items-center justify-center gap-2 transition-all shadow-sm"
                                    >
                                        <span>Cari Destinasi</span>
                                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tag Pencarian Cepat -->
                            <div class="flex flex-wrap items-center gap-2 mt-4 text-on-surface-variant">
                                <span class="font-label-caps text-label-caps uppercase text-outline">Populer:</span>
                                <?php foreach ($tagPencarianPopuler as $namaTag) : ?>
                                    <a
                                        href="#katalog"
                                        class="font-label-md text-label-md px-2.5 py-1 rounded-full bg-surface-container hover:bg-surface-container-high transition-colors text-on-surface"
                                    >
                                        <?= esc($namaTag) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <!-- Baris Ringkasan Statistik Platform -->
                            <div class="grid grid-cols-3 gap-4 mt-8 pt-6 bg-surface-container-low/70 rounded-2xl p-4">
                                <?php foreach ($daftarStatistikUtama as $itemStatistik) : ?>
                                    <div class="flex flex-col">
                                        <div class="font-headline-md text-headline-md <?= esc($itemStatistik['kelas_warna']) ?> font-bold tabular-nums">
                                            <?= esc($itemStatistik['angka']) ?>
                                        </div>
                                        <span class="font-body-sm text-body-sm text-on-surface-variant">
                                            <?= esc($itemStatistik['keterangan']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Kolom Kanan (5 Kolom): Kartu Foto Sorotan Destinasi Utama -->
                        <div class="lg:col-span-5 relative">
                            <div class="relative h-[560px] w-full rounded-3xl overflow-hidden shadow-xl bg-surface-container group">
                                <img
                                    src="<?= esc($destinasiUtama['url_foto']) ?>"
                                    alt="<?= esc($destinasiUtama['alt_foto']) ?>"
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                />

                                <!-- Badge Atas & Tombol Simpan Favorit -->
                                <div class="absolute top-5 left-5 right-5 flex items-center justify-between z-10">
                                    <span class="font-label-caps text-label-caps px-3.5 py-1.5 rounded-full bg-surface-container-lowest/90 text-primary font-bold shadow-sm backdrop-blur-sm">
                                        <?= esc($destinasiUtama['label_editor']) ?>
                                    </span>
                                    <button
                                        type="button"
                                        aria-label="Simpan rute"
                                        class="w-10 h-10 rounded-full bg-surface-container-lowest/90 hover:bg-surface-container-lowest text-on-surface flex items-center justify-center shadow-sm transition-all"
                                    >
                                        <span class="material-symbols-outlined text-lg">bookmark</span>
                                    </button>
                                </div>

                                <!-- Overlay Gelap Bawah (Kontras Terjamin di Atas Foto) -->
                                <div class="absolute inset-x-0 bottom-0 pt-28 pb-6 px-6 bg-gradient-to-t from-inverse-surface via-inverse-surface/80 to-transparent text-inverse-on-surface flex flex-col justify-end">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-label-caps text-label-caps text-secondary-fixed uppercase tracking-wider">
                                            <?= esc($destinasiUtama['kawasan']) ?>
                                        </span>
                                        <span class="text-surface-variant text-xs">•</span>
                                        <span class="font-metric-rating text-metric-rating text-tertiary-fixed-dim flex items-center gap-0.5 tabular-nums">
                                            <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">star</span>
                                            <?= esc((string) $destinasiUtama['rata_rating']) ?> (<?= esc(number_format($destinasiUtama['jumlah_ulasan'], 0, ',', '.')) ?> ulasan)
                                        </span>
                                    </div>

                                    <h2 class="font-headline-lg text-headline-lg text-inverse-on-surface leading-tight">
                                        <?= esc($destinasiUtama['nama']) ?>
                                    </h2>

                                    <p class="font-body-sm text-body-sm text-surface-variant mt-1.5 flex items-center gap-1.5 font-mono tabular-nums">
                                        <span class="material-symbols-outlined text-xs">my_location</span>
                                        <?= esc(formatKoordinatLokasi($destinasiUtama['garis_lintang'], $destinasiUtama['garis_bujur'])) ?> • <?= esc($destinasiUtama['kota']) ?>, <?= esc($destinasiUtama['provinsi']) ?>
                                    </p>

                                    <!-- Daftar Tag Aktivitas / Fasilitas Unggulan -->
                                    <div class="flex flex-wrap gap-1.5 mt-3">
                                        <?php foreach ($destinasiUtama['daftar_fasilitas'] as $namaFasilitas) : ?>
                                            <span class="font-body-sm text-body-sm px-2.5 py-0.5 rounded-md bg-inverse-surface/60 text-inverse-on-surface backdrop-blur-sm">
                                                <?= esc($namaFasilitas) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Informasi Harga Tiket & Tombol Lihat Rute -->
                                    <div class="flex items-center justify-between mt-5 pt-4 bg-inverse-surface/40 backdrop-blur-md rounded-xl px-4 py-2.5">
                                        <div class="flex flex-col">
                                            <span class="font-label-caps text-label-caps text-surface-variant uppercase">
                                                <?= esc($destinasiUtama['label_harga']) ?>
                                            </span>
                                            <span class="font-metric-price text-metric-price text-inverse-on-surface tabular-nums">
                                                <?= esc(formatHargaRupiah($destinasiUtama['harga_tiket'])) ?>
                                                <span class="font-body-sm text-body-sm text-surface-variant font-normal">/ orang</span>
                                            </span>
                                        </div>
                                        <a
                                            href="#borobudur"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary-container text-on-primary hover:bg-primary font-label-md text-label-md transition-colors shadow-sm"
                                        >
                                            <span>Lihat Rute</span>
                                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>

            <!-- 2. SEKSI BENTO GRID ASIMETRIS (DESTINASI UNGGULAN PILIHAN KURATOR) -->
            <section id="destinasi-kurator" class="w-full bg-surface-container-low py-16">
                <div class="max-w-7xl mx-auto px-6">
                    <!-- Judul Seksi Bento Grid -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-tertiary-container"></span>
                                <span class="font-label-caps text-label-caps uppercase text-tertiary-container tracking-wider">
                                    TERPOPULER DI INDONESIA
                                </span>
                            </div>
                            <h2 class="font-headline-lg text-headline-lg text-on-surface mt-2 max-w-xl">
                                Mahakarya Alam &amp; Warisan Sejarah yang Wajib Dikunjungi
                            </h2>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-md">
                            Tiga destinasi dengan indeks kelestarian dan kepuasan pengunjung tertinggi musim ini, dirancang untuk pengalaman napak tilas mendalam.
                        </p>
                    </div>

                    <!-- Susunan Bento Grid (7 Kolom Kiri + 5 Kolom Kanan) -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-10">

                        <!-- Kartu Bento Utama Kiri (7 Kolom: Candi Borobudur) -->
                        <article class="lg:col-span-7 bg-surface-container-lowest rounded-3xl overflow-hidden shadow-sm flex flex-col justify-between group">
                            <div class="relative h-80 sm:h-96 w-full overflow-hidden">
                                <img
                                    src="<?= esc($destinasiUnggulanUtama['url_foto']) ?>"
                                    alt="<?= esc($destinasiUnggulanUtama['alt_foto']) ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                />
                                <div class="absolute top-4 left-4">
                                    <span class="font-label-caps text-label-caps px-3 py-1.5 rounded-full bg-secondary text-on-secondary shadow-sm">
                                        <?= esc($destinasiUnggulanUtama['kategori']) ?>
                                    </span>
                                </div>
                                <div class="absolute bottom-4 right-4 bg-surface-container-lowest/90 backdrop-blur-md px-3 py-1.5 rounded-xl text-on-surface shadow-sm flex items-center gap-1 font-metric-rating text-metric-rating tabular-nums">
                                    <span class="material-symbols-outlined text-sm text-tertiary-container" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span><?= esc((string) $destinasiUnggulanUtama['rata_rating']) ?></span>
                                    <span class="text-outline font-normal text-xs">
                                        (<?= esc(number_format($destinasiUnggulanUtama['jumlah_ulasan'], 0, ',', '.')) ?> ulasan)
                                    </span>
                                </div>
                            </div>

                            <div class="p-6 sm:p-8 flex flex-col justify-between flex-grow">
                                <div>
                                    <div class="flex items-center gap-2 text-outline font-body-sm text-body-sm">
                                        <span class="material-symbols-outlined text-sm text-primary">pin_drop</span>
                                        <span><?= esc($destinasiUnggulanUtama['lokasi']) ?></span>
                                    </div>
                                    <h3 id="borobudur" class="font-headline-md text-headline-md text-on-surface mt-2">
                                        <?= esc($destinasiUnggulanUtama['nama']) ?>
                                    </h3>
                                    <p class="font-body-md text-body-md text-on-surface-variant mt-3 leading-relaxed">
                                        <?= esc($destinasiUnggulanUtama['deskripsi']) ?>
                                    </p>

                                    <!-- Kotak Informasi Jam Operasional & Fasilitas -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6 pt-6 bg-surface-container/50 rounded-2xl p-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="material-symbols-outlined text-primary text-xl">schedule</span>
                                            <div class="flex flex-col">
                                                <span class="font-label-caps text-label-caps text-outline uppercase">Jam Buka</span>
                                                <span class="font-label-md text-label-md text-on-surface tabular-nums">
                                                    <?= esc($destinasiUnggulanUtama['jam_buka']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <span class="material-symbols-outlined text-secondary text-xl">accessible_forward</span>
                                            <div class="flex flex-col">
                                                <span class="font-label-caps text-label-caps text-outline uppercase">Fasilitas</span>
                                                <span class="font-label-md text-label-md text-on-surface">
                                                    <?= esc($destinasiUnggulanUtama['ringkasan_fasilitas']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bagian Bawah Kartu: Harga Tiket & Tombol Rencanakan Kunjungan -->
                                <div class="flex items-center justify-between mt-8 pt-4">
                                    <div>
                                        <span class="font-label-caps text-label-caps text-outline uppercase block">
                                            <?= esc($destinasiUnggulanUtama['label_harga']) ?>
                                        </span>
                                        <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                            <?= esc(formatHargaRupiah($destinasiUnggulanUtama['harga_tiket'])) ?>
                                            <span class="font-body-sm text-body-sm text-on-surface-variant font-normal">/ orang</span>
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        class="px-5 py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container transition-colors font-label-md text-label-md flex items-center gap-2"
                                    >
                                        <span>Rencanakan Kunjungan</span>
                                        <span class="material-symbols-outlined text-sm">north_east</span>
                                    </button>
                                </div>
                            </div>
                        </article>

                        <!-- Kolom Kanan Bento (5 Kolom: 2 Kartu Pendamping Bertumpuk) -->
                        <div class="lg:col-span-5 flex flex-col gap-6">
                            <?php foreach ($daftarDestinasiUnggulanPendamping as $destinasiPendamping) : ?>
                                <article class="bg-surface-container-lowest rounded-3xl overflow-hidden shadow-sm flex flex-col sm:flex-row lg:flex-col group">
                                    <div class="relative h-52 sm:w-1/2 lg:w-full overflow-hidden shrink-0">
                                        <img
                                            src="<?= esc($destinasiPendamping['url_foto']) ?>"
                                            alt="<?= esc($destinasiPendamping['alt_foto']) ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                        />
                                        <div class="absolute top-3 left-3">
                                            <span class="font-label-caps text-label-caps px-3 py-1 rounded-full <?= esc($destinasiPendamping['kelas_badge']) ?>">
                                                <?= esc($destinasiPendamping['kategori']) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-6 flex flex-col justify-between flex-grow">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-body-sm text-body-sm text-outline flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-xs text-primary">pin_drop</span>
                                                    <?= esc($destinasiPendamping['lokasi']) ?>
                                                </span>
                                                <span class="font-metric-rating text-metric-rating text-on-surface flex items-center gap-0.5 tabular-nums">
                                                    <span class="material-symbols-outlined text-xs text-tertiary-container" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    <?= esc((string) $destinasiPendamping['rata_rating']) ?> (<?= esc(number_format($destinasiPendamping['jumlah_ulasan'], 0, ',', '.')) ?>)
                                                </span>
                                            </div>

                                            <h3 class="font-headline-sm text-headline-sm text-on-surface mt-2">
                                                <?= esc($destinasiPendamping['nama']) ?>
                                            </h3>

                                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5 line-clamp-2">
                                                <?= esc($destinasiPendamping['deskripsi']) ?>
                                            </p>

                                            <div class="flex items-center gap-3 mt-3 text-on-surface-variant font-body-sm text-body-sm tabular-nums">
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-xs"><?= esc($destinasiPendamping['ikon_jam']) ?></span>
                                                    <?= esc($destinasiPendamping['jam_buka']) ?>
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-xs"><?= esc($destinasiPendamping['ikon_info']) ?></span>
                                                    <?= esc($destinasiPendamping['info_tambahan']) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between mt-4 pt-3">
                                            <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                                <?= esc(formatHargaRupiah($destinasiPendamping['harga_tiket'])) ?>
                                                <span class="font-body-sm text-body-sm text-outline font-normal">/ orang</span>
                                            </span>
                                            <span class="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">
                                                arrow_forward
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>
            </section>

            <!-- 3. SEKSI FILTER KATEGORI & KATALOG DESTINASI NUSANTARA -->
            <section id="katalog" class="w-full max-w-7xl mx-auto px-6 py-16">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-primary">travel_explore</span>
                            <span class="font-label-caps text-label-caps uppercase text-primary tracking-wider">
                                KURASI LENGKAP
                            </span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">
                            Eksplorasi Katalog Nusantara
                        </h2>
                    </div>

                    <!-- Dropdown Pengurutan Katalog -->
                    <div class="flex items-center gap-3 self-start md:self-auto">
                        <div class="flex items-center gap-2 px-3 py-2 bg-surface-container rounded-xl">
                            <span class="material-symbols-outlined text-sm text-outline">sort</span>
                            <select class="bg-transparent font-label-md text-label-md text-on-surface focus:outline-none cursor-pointer">
                                <option>Rating Tertinggi</option>
                                <option>Paling Banyak Dikunjungi</option>
                                <option>Tarif Terendah</option>
                                <option>Rute Paling Menantang</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Baris Pill Filter Kategori Wisata -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                    <?php foreach ($daftarFilterKategori as $filterItem) : ?>
                        <?php
                        if ($filterItem['aktif']) {
                            $kelasTombolFilter = 'px-4 py-2 rounded-full bg-primary-container text-on-primary font-label-md text-label-md whitespace-nowrap shadow-sm';
                        } else {
                            $kelasTombolFilter = 'px-4 py-2 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface font-label-md text-label-md whitespace-nowrap transition-colors';
                        }
                        ?>
                        <button type="button" class="<?= esc($kelasTombolFilter) ?>">
                            <?= esc($filterItem['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Grid Katalog 3 Kolom -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                    <?php foreach ($daftarKatalogDestinasi as $destinasi) : ?>
                        <article class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between group hover:shadow-md transition-shadow">
                            <div>
                                <!-- Foto Destinasi & Badge Kategori -->
                                <div class="relative h-56 w-full overflow-hidden">
                                    <img
                                        src="<?= esc($destinasi['url_foto']) ?>"
                                        alt="<?= esc($destinasi['alt_foto']) ?>"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                    />
                                    <span class="absolute top-3 left-3 font-label-caps text-label-caps px-3 py-1 rounded-full <?= esc($destinasi['kelas_badge']) ?>">
                                        <?= esc($destinasi['kategori']) ?>
                                    </span>
                                </div>

                                <!-- Detail Lokasi, Rating, Deskripsi & Tag Fasilitas -->
                                <div class="p-5">
                                    <div class="flex items-center justify-between text-outline font-body-sm text-body-sm">
                                        <span class="truncate flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs text-primary">pin_drop</span>
                                            <?= esc($destinasi['lokasi']) ?>
                                        </span>
                                        <span class="font-metric-rating text-metric-rating text-on-surface flex items-center gap-0.5 shrink-0 tabular-nums">
                                            <span class="material-symbols-outlined text-xs text-tertiary-container" style="font-variation-settings: 'FILL' 1;">star</span>
                                            <?= esc(number_format($destinasi['rata_rating'], 2, '.', '')) ?> (<?= esc(number_format($destinasi['jumlah_ulasan'], 0, ',', '.')) ?>)
                                        </span>
                                    </div>

                                    <h3 class="font-headline-sm text-headline-sm text-on-surface mt-2 group-hover:text-primary transition-colors">
                                        <?= esc($destinasi['nama']) ?>
                                    </h3>

                                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5">
                                        <?= esc($destinasi['jam_buka']) ?> • <?= esc($destinasi['deskripsi']) ?>
                                    </p>

                                    <div class="flex flex-wrap gap-1.5 mt-3">
                                        <?php foreach ($destinasi['daftar_fasilitas'] as $fasilitasItem) : ?>
                                            <span class="font-body-sm text-body-sm px-2 py-0.5 rounded bg-surface-container text-on-surface-variant">
                                                <?= esc($fasilitasItem) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Kartu: Harga Tiket & Tombol Detail -->
                            <div class="px-5 pb-5 pt-3 flex items-center justify-between">
                                <div>
                                    <span class="font-label-caps text-label-caps text-outline uppercase block">
                                        <?= esc($destinasi['label_harga']) ?>
                                    </span>
                                    <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                        <?= esc(formatHargaRupiah($destinasi['harga_tiket'])) ?>
                                        <span class="font-body-sm text-body-sm text-outline font-normal">/ orang</span>
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    aria-label="Lihat detail <?= esc($destinasi['nama']) ?>"
                                    class="w-9 h-9 rounded-lg bg-surface-container hover:bg-primary-container hover:text-on-primary text-on-surface flex items-center justify-center transition-colors"
                                >
                                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Tombol Muat Lebih Banyak Destinasi -->
                <div class="flex justify-center mt-12">
                    <button
                        type="button"
                        class="px-6 py-3 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md flex items-center gap-2 transition-all shadow-sm"
                    >
                        <span>Muat Lebih Banyak Destinasi (122 lainnya)</span>
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </button>
                </div>
            </section>

            <!-- 4. SEKSI JELAJAH BERDASARKAN KEPULAUAN NUSANTARA -->
            <section id="jelajah-kepulauan" class="w-full bg-surface-container-low/60 py-16">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="flex flex-col items-center text-center">
                        <span class="font-label-caps text-label-caps uppercase text-secondary tracking-wider">
                            GEOGRAFI NUSANTARA
                        </span>
                        <h2 class="font-headline-lg text-headline-lg text-on-surface mt-2">
                            Jelajah Berdasarkan Kepulauan
                        </h2>
                        <p class="font-body-md text-body-md text-on-surface-variant max-w-lg mt-2">
                            Dari barat ke timur, setiap gugusan pulau menyimpan keunikan ekosistem, adat istiadat, dan keramahan masyarakatnya.
                        </p>
                    </div>

                    <!-- Grid 6 Gugusan Kepulauan -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-10">
                        <?php foreach ($daftarWilayahKepulauan as $wilayah) : ?>
                            <a
                                href="#katalog"
                                class="flex flex-col items-center p-5 rounded-2xl bg-surface-container-lowest hover:bg-surface-container transition-all text-center group shadow-sm"
                            >
                                <div class="w-12 h-12 rounded-full <?= esc($wilayah['kelas_ikon']) ?> flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <span class="material-symbols-outlined"><?= esc($wilayah['ikon']) ?></span>
                                </div>
                                <span class="font-label-md text-label-md text-on-surface">
                                    <?= esc($wilayah['nama_kepulauan']) ?>
                                </span>
                                <span class="font-body-sm text-body-sm text-outline mt-0.5 tabular-nums">
                                    <?= esc((string) $wilayah['jumlah_destinasi']) ?> Destinasi
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- 5. SEKSI KOMUNITAS & JURNAL PERJALANAN -->
            <section id="komunitas-jurnal" class="max-w-7xl mx-auto px-6 py-16 w-full">
                <div class="bg-surface-container-high rounded-3xl p-8 sm:p-12 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-tertiary-container/10 pointer-events-none blur-2xl"></div>

                    <div class="flex flex-col max-w-2xl relative z-10">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-secondary text-base">award_star</span>
                            <span class="font-label-caps text-label-caps uppercase text-secondary tracking-wider">
                                INISIATIF EKSPEDISI BERSAMA
                            </span>
                        </div>
                        <h2 class="font-headline-lg text-headline-lg text-on-surface">
                            Bagikan Catatan Perjalanan Anda &amp; Dapatkan Poin Konservasi IndWalk
                        </h2>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-3">
                            Setiap ulasan rute, titik koordinat baru, dan foto etis yang Anda unggah membantu mendanai bibit karang dan pemberdayaan komunitas pemandu lokal di pulau-pulau terpencil nusantara.
                        </p>
                        <div class="flex items-center gap-6 mt-6">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-xl">loyalty</span>
                                <span class="font-label-md text-label-md text-on-surface">
                                    Reward Tiket &amp; Perlengkapan
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary text-xl">nature_people</span>
                                <span class="font-label-md text-label-md text-on-surface">
                                    Dukungan Warga Setempat
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pasangan Tombol Primary (Solid) & Secondary (Surface) sesuai Sloplint -->
                    <div class="flex flex-col sm:flex-row gap-3 shrink-0 relative z-10 w-full sm:w-auto">
                        <button
                            type="button"
                            class="px-6 py-3.5 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm text-center"
                        >
                            Mulai Tulis Jurnal
                        </button>
                        <button
                            type="button"
                            class="px-6 py-3.5 rounded-xl bg-surface-container-lowest hover:bg-surface-container text-on-surface font-label-md text-label-md transition-colors text-center"
                        >
                            Pelajari Sistem Poin
                        </button>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- =====================================================================
         BAGIAN FOOTER HALAMAN
         ===================================================================== -->
    <footer class="w-full bg-surface-container-low border-t border-surface-variant/40">
        <div class="max-w-7xl mx-auto px-6 py-space-xl">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-space-xl">

                <!-- Kolom 1: Tentang IndWalk -->
                <div class="lg:col-span-4 flex flex-col gap-space-md">
                    <div class="flex items-center gap-3">
                        <img
                            src="<?= esc($urlLogoIndWalk) ?>"
                            alt="IndWalk Logo"
                            class="h-8 w-auto object-contain"
                        />
                        <span class="font-headline-sm text-headline-sm text-on-surface">IndWalk</span>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-sm">
                        IndWalk — Platform kurasi pariwisata nusantara untuk petualang autentik.
                    </p>
                    <div class="flex items-center gap-2 text-on-surface-variant font-body-sm text-body-sm">
                        <span class="material-symbols-outlined text-secondary text-base">verified</span>
                        <span>Ekowisata Terverifikasi &amp; Komunitas Lokal</span>
                    </div>
                </div>

                <!-- Kolom 2: Tautan Jelajah Wilayah -->
                <div class="lg:col-span-2 flex flex-col gap-space-sm">
                    <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider mb-2">
                        Jelajah Wilayah
                    </h3>
                    <ul class="flex flex-col gap-2 font-body-md text-body-md text-on-surface-variant">
                        <?php foreach ($daftarWilayahKepulauan as $wilayahFooter) : ?>
                            <li>
                                <a href="#katalog" class="hover:text-on-surface transition-colors">
                                    <?= esc($wilayahFooter['nama_kepulauan']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Kolom 3: Tautan Kategori & Bantuan -->
                <div class="lg:col-span-3 flex flex-col gap-space-sm">
                    <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider mb-2">
                        Kategori
                    </h3>
                    <ul class="flex flex-col gap-2 font-body-md text-body-md text-on-surface-variant">
                        <li><a href="#katalog" class="hover:text-on-surface transition-colors">Bahari</a></li>
                        <li><a href="#katalog" class="hover:text-on-surface transition-colors">Geopark &amp; Gunung</a></li>
                        <li><a href="#katalog" class="hover:text-on-surface transition-colors">Budaya &amp; Warisan</a></li>
                        <li><a href="#katalog" class="hover:text-on-surface transition-colors">Kuliner Lokal</a></li>
                    </ul>

                    <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider mt-4 mb-2">
                        Bantuan &amp; Kebijakan
                    </h3>
                    <ul class="flex flex-col gap-2 font-body-md text-body-md text-on-surface-variant">
                        <li><a href="#" class="hover:text-on-surface transition-colors">Panduan Wisata</a></li>
                        <li><a href="#" class="hover:text-on-surface transition-colors">Standar Etika Alam</a></li>
                        <li><a href="#" class="hover:text-on-surface transition-colors">Hubungi Kami</a></li>
                        <li><a href="#" class="hover:text-on-surface transition-colors">Privasi</a></li>
                    </ul>
                </div>

                <!-- Kolom 4: Langganan Kabar Ekspedisi -->
                <div class="lg:col-span-3 flex flex-col gap-space-md">
                    <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider">
                        Kabar Ekspedisi
                    </h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        Dapatkan jurnal mingguan rute tersembunyi dan kabar konservasi nusantara.
                    </p>
                    <form class="flex flex-col gap-2" onsubmit="event.preventDefault();">
                        <input
                            type="email"
                            placeholder="Masukkan alamat surel..."
                            class="w-full px-3 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant/60 font-body-sm text-body-sm text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:border-primary"
                        />
                        <button
                            type="submit"
                            class="w-full py-2.5 rounded-lg bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors"
                        >
                            Langganan
                        </button>
                    </form>
                </div>

            </div>

            <!-- Baris Hak Cipta Bawah -->
            <div class="mt-space-xl pt-space-lg border-t border-surface-variant/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="font-body-sm text-body-sm text-on-surface-variant text-center sm:text-left">
                    © 2025 IndWalk Nusantara Mandiri. Dibuat dengan bangga untuk pariwisata Indonesia.
                </p>
                <div class="flex items-center gap-4 font-body-sm text-body-sm text-on-surface-variant">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-secondary">eco</span>
                        Berkelanjutan
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm text-primary">explore</span>
                        Kepulauan Nusantara
                    </span>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
