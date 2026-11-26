<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Halaman status backend (menampilkan info koneksi PostgreSQL dalam JSON)
$routes->get('/', 'Home::index');

// ============================================================
// GRUP RUTE REST API v1 — JejakSumut Backend
// ============================================================
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // --- Destinasi Wisata Publik (Anggota 1) ---
    $routes->get('destinasi', 'DestinasiApiController::index');
    $routes->get('destinasi/(:segment)', 'DestinasiApiController::show/$1');

    // --- Kategori & Wilayah Kabupaten/Kota (Anggota 1) ---
    $routes->get('kategori', 'KategoriApiController::index');
    $routes->get('kabupaten-kota', 'KategoriApiController::kabupatenKota');

    // --- Autentikasi & Akun (Anggota 3) ---
    $routes->post('auth/masuk',              'AutentikasiApiController::masuk');
    $routes->post('auth/daftar',             'AutentikasiApiController::daftar');
    $routes->post('auth/ganti-sandi',        'AutentikasiApiController::gantiSandi');
    $routes->post('auth/lupa-sandi',         'AutentikasiApiController::lupaSandi');
    $routes->post('auth/reset-sandi',        'AutentikasiApiController::resetSandi');
    $routes->post('autentikasi/masuk',       'AutentikasiApiController::masuk');
    $routes->post('autentikasi/daftar',      'AutentikasiApiController::daftar');
    $routes->post('autentikasi/ganti-sandi', 'AutentikasiApiController::gantiSandi');
    $routes->post('autentikasi/lupa-sandi',  'AutentikasiApiController::lupaSandi');
    $routes->post('autentikasi/reset-sandi', 'AutentikasiApiController::resetSandi');

    // --- Favorit (Anggota 3) – dilindungi filter autentikasiToken ---
    $routes->get('favorit/(:num)',  'FavoritApiController::show/$1',  ['filter' => 'autentikasiToken']);
    $routes->get('favorit',         'FavoritApiController::index',    ['filter' => 'autentikasiToken']);
    $routes->post('favorit/ubah',   'FavoritApiController::ubah',     ['filter' => 'autentikasiToken']);

    // ================================================================
    // MODUL ANGGOTA 4 — Pemesanan Tiket & Ulasan
    // ================================================================

    // --- Pemesanan Tiket – dilindungi filter autentikasiToken ---
    // GET  /api/v1/pemesanan?id_pengguna=X  → Daftar riwayat tiket pengguna
    $routes->get('pemesanan', 'PemesananApiController::index', ['filter' => 'autentikasiToken']);

    // GET  /api/v1/pemesanan/{id}            → Detail 1 tiket untuk E-Ticket
    $routes->get('pemesanan/(:num)', 'PemesananApiController::show/$1', ['filter' => 'autentikasiToken']);

    // POST /api/v1/pemesanan                 → Buat pesanan tiket baru
    $routes->post('pemesanan', 'PemesananApiController::create', ['filter' => 'autentikasiToken']);

    // PATCH /api/v1/pemesanan/{id}/batal    → Batalkan tiket (status menunggu)
    $routes->patch('pemesanan/(:num)/batal', 'PemesananApiController::batalkan/$1', ['filter' => 'autentikasiToken']);

    // --- Ulasan & Rating Bintang ---
    // GET  /api/v1/ulasan?id_destinasi=X     → Daftar ulasan destinasi
    $routes->get('ulasan', 'UlasanApiController::index');

    // POST /api/v1/ulasan                    → Kirim ulasan & rating bintang baru (Member)
    $routes->post('ulasan', 'UlasanApiController::create', ['filter' => 'autentikasiToken']);

    // ================================================================
    // MODUL ADMIN (Anggota 5) – dilindungi filter peranAdmin
    // ================================================================
    $routes->group('', ['filter' => 'peranAdmin'], static function (RouteCollection $routes) {
        $routes->get('admin/statistik', 'AdminApiController::statistik');
        $routes->get('admin/pemesanan', 'AdminApiController::daftarPemesanan');
        $routes->get('admin/ulasan', 'AdminApiController::daftarUlasan');
        $routes->patch('pemesanan/(:num)/status', 'AdminApiController::ubahStatusPemesanan/$1');
        $routes->delete('ulasan/(:num)', 'AdminApiController::hapusUlasan/$1');

        $routes->get('admin/destinasi/(:num)', 'DestinasiAdminApiController::show/$1');
        $routes->post('destinasi', 'DestinasiAdminApiController::create');
        $routes->put('destinasi/(:num)', 'DestinasiAdminApiController::update/$1');
        $routes->delete('destinasi/(:num)', 'DestinasiAdminApiController::delete/$1');
    });
});
