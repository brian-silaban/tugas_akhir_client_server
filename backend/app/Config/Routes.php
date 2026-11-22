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
    // $routes->post('destinasi',         'DestinasiApiController::create');
    // $routes->put('destinasi/(:num)',   'DestinasiApiController::update/$1');
    // $routes->delete('destinasi/(:num)','DestinasiApiController::delete/$1');

    // --- Kategori & Wilayah Kabupaten/Kota (Anggota 1) ---
    $routes->get('kategori', 'KategoriApiController::index');
    $routes->get('kabupaten-kota', 'KategoriApiController::kabupatenKota');

    // --- Autentikasi & Akun (Anggota 3) ---
    $routes->post('autentikasi/masuk',  'AutentikasiApiController::masuk');
    $routes->post('autentikasi/daftar', 'AutentikasiApiController::daftar');

    // --- Favorit (Anggota 3) ---
    $routes->get('favorit',         'FavoritApiController::index');
    $routes->post('favorit/ubah',   'FavoritApiController::ubah');

    // ================================================================
    // MODUL ANGGOTA 4 — Pemesanan Tiket & Ulasan
    // ================================================================

    // --- Pemesanan Tiket ---
    // GET  /api/v1/pemesanan?id_pengguna=X  → Daftar riwayat tiket pengguna
    $routes->get('pemesanan', 'PemesananApiController::index');

    // GET  /api/v1/pemesanan/{id}            → Detail 1 tiket untuk E-Ticket
    $routes->get('pemesanan/(:num)', 'PemesananApiController::show/$1');

    // POST /api/v1/pemesanan                 → Buat pesanan tiket baru
    $routes->post('pemesanan', 'PemesananApiController::create');

    // PATCH /api/v1/pemesanan/{id}/batal    → Batalkan tiket (status menunggu)
    $routes->patch('pemesanan/(:num)/batal', 'PemesananApiController::batalkan/$1');

    // --- Ulasan & Rating Bintang ---
    // GET  /api/v1/ulasan?id_destinasi=X     → Daftar ulasan destinasi
    $routes->get('ulasan', 'UlasanApiController::index');

    // POST /api/v1/ulasan                    → Kirim ulasan & rating bintang baru
    $routes->post('ulasan', 'UlasanApiController::create');

    // --- Admin (Anggota 5) ---
    // $routes->get('admin/statistik',               'AdminApiController::statistik');
    // $routes->patch('pemesanan/(:num)/status',      'AdminApiController::ubahStatusPemesanan/$1');
    // $routes->delete('ulasan/(:num)',               'AdminApiController::hapusUlasan/$1');
});

