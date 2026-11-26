<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Client pengelola dapat dijalankan pada proses dan sesi terpisah.
$modeClient = env('JEJAKSUMUT_MODE_CLIENT', 'gabungan');

$routes->get('masuk', 'AutentikasiController::masuk');
$routes->post('masuk', 'AutentikasiController::prosesMasuk', ['filter' => 'csrf']);
$routes->get('ganti-sandi', 'AutentikasiController::gantiSandi');
$routes->post('ganti-sandi', 'AutentikasiController::prosesGantiSandi', ['filter' => 'csrf']);

if ($modeClient === 'admin') {
    $routes->get('/', static fn () => redirect()->to(site_url('admin')));
} else {
    $routes->get('/', 'Home::index');
    $routes->get('destinasi', 'DestinasiController::index');
    $routes->get('destinasi/(:segment)', 'DestinasiController::detail/$1');
    $routes->get('daftar', 'AutentikasiController::daftar');
    $routes->post('daftar', 'AutentikasiController::prosesDaftar', ['filter' => 'csrf']);
    $routes->get('keluar', 'AutentikasiController::keluar');

    // --- Favorit (Anggota 3) – dilindungi filter autentikasi & CSRF ---
    $routes->get('favorit-saya',     'FavoritController::index',  ['filter' => 'autentikasi']);
    $routes->post('favorit/ubah',    'FavoritController::ubah',   ['filter' => ['autentikasi', 'csrf']]);

    // ================================================================
    // MODUL ANGGOTA 4 – Pemesanan Tiket & Ulasan (Frontend Client)
    // ================================================================

    // --- Halaman Tiket Saya – dilindungi filter autentikasi ---
    // GET  /pemesanan                → Daftar riwayat tiket pengguna
    $routes->get('pemesanan', 'PemesananController::index', ['filter' => 'autentikasi']);

    // GET  /pemesanan/etiket/{id}   → Halaman E-Ticket digital
    $routes->get('pemesanan/etiket/(:num)', 'PemesananController::tampilEtiket/$1', ['filter' => 'autentikasi']);

    // POST /pemesanan/buat          → Proses form pemesanan tiket baru
    $routes->post('pemesanan/buat', 'PemesananController::buat', ['filter' => ['autentikasi', 'csrf']]);

    // POST /pemesanan/batal/{id}    → Batalkan tiket
    $routes->post('pemesanan/batal/(:num)', 'PemesananController::batal/$1', ['filter' => ['autentikasi', 'csrf']]);

    // --- Ulasan & Rating ---
    // POST /ulasan/kirim            → Kirim ulasan baru dari form di halaman detail (Member)
    $routes->post('ulasan/kirim', 'UlasanController::kirim', ['filter' => ['autentikasi', 'csrf']]);

    // --- Proxy API Mitra Transportasi (Anggota 2) ---
    $routes->get('mitra/transportasi/lokasi', 'MitraTransportController::lokasi');
    $routes->get('mitra/transportasi/tarif', 'MitraTransportController::tarif');
}

if ($modeClient !== 'wisatawan') {
    $routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin'], static function (RouteCollection $routes) {
        $routes->get('', 'DasborController::index');
        $routes->get('destinasi', 'DestinasiAdminController::index');
        $routes->get('destinasi/tambah', 'DestinasiAdminController::tambah');
        $routes->get('destinasi/(:num)/edit', 'DestinasiAdminController::edit/$1');
        $routes->get('pemesanan', 'PemesananAdminController::index');
        $routes->get('ulasan', 'PemesananAdminController::ulasan');

        $routes->post('destinasi', 'DestinasiAdminController::buat', ['filter' => 'csrf']);
        $routes->post('destinasi/(:num)', 'DestinasiAdminController::ubah/$1', ['filter' => 'csrf']);
        $routes->post('destinasi/(:num)/hapus', 'DestinasiAdminController::hapus/$1', ['filter' => 'csrf']);
        $routes->post('pemesanan/(:num)/status', 'PemesananAdminController::ubahStatus/$1', ['filter' => 'csrf']);
        $routes->post('ulasan/(:num)/hapus', 'PemesananAdminController::hapusUlasan/$1', ['filter' => 'csrf']);

        $routes->post('keluar', '\App\Controllers\AutentikasiController::keluarAdmin', ['filter' => 'csrf']);
    });
}
