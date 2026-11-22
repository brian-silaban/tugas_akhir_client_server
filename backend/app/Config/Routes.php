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

    // --- Destinasi Wisata (Anggota 1) ---
    $routes->get('destinasi', 'DestinasiApiController::index');
    $routes->get('destinasi/(:segment)', 'DestinasiApiController::show/$1');
    $routes->post('destinasi',           'DestinasiApiController::create',     ['filter' => 'peranAdmin']);
    $routes->put('destinasi/(:num)',     'DestinasiApiController::update/$1',  ['filter' => 'peranAdmin']);
    $routes->delete('destinasi/(:num)',  'DestinasiApiController::delete/$1',  ['filter' => 'peranAdmin']);

    // --- Kategori & Wilayah Kabupaten/Kota (Anggota 1) ---
    $routes->get('kategori', 'KategoriApiController::index');
    $routes->get('kabupaten-kota', 'KategoriApiController::kabupatenKota');

    // --- Autentikasi & Akun (Anggota 3) ---
    $routes->post('auth/masuk',         'AutentikasiApiController::masuk');
    $routes->post('auth/daftar',        'AutentikasiApiController::daftar');
    $routes->post('autentikasi/masuk',  'AutentikasiApiController::masuk');
    $routes->post('autentikasi/daftar', 'AutentikasiApiController::daftar');

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

    // PATCH /api/v1/pemesanan/{id}/status   → Ubah status pesanan (Admin only)
    $routes->patch('pemesanan/(:num)/status', 'AdminApiController::ubahStatusPemesanan/$1', ['filter' => 'peranAdmin']);

    // --- Ulasan & Rating Bintang ---
    // GET  /api/v1/ulasan?id_destinasi=X     → Daftar ulasan destinasi
    $routes->get('ulasan', 'UlasanApiController::index');

    // POST /api/v1/ulasan                    → Kirim ulasan & rating bintang baru (Member)
    $routes->post('ulasan', 'UlasanApiController::create', ['filter' => 'autentikasiToken']);

    // DELETE /api/v1/ulasan/{id}             → Hapus ulasan (Admin only)
    $routes->delete('ulasan/(:num)', 'AdminApiController::hapusUlasan/$1', ['filter' => 'peranAdmin']);

    // --- Admin (Anggota 5) ---
    $routes->get('admin/statistik',               'AdminApiController::statistik', ['filter' => 'peranAdmin']);
    $routes->get('admin/(:any)',                  'AdminApiController::$1',         ['filter' => 'peranAdmin']);
    $routes->post('admin/(:any)',                 'AdminApiController::$1',         ['filter' => 'peranAdmin']);
});

