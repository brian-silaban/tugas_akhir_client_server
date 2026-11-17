<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ---------------------------------------------------------------
// Rute Autentikasi (Anggota 3)
// ---------------------------------------------------------------
$routes->get('/masuk',  'AutentikasiController::masuk');
$routes->post('/masuk', 'AutentikasiController::prosesMasuk');
$routes->get('/daftar',  'AutentikasiController::daftar');
$routes->post('/daftar', 'AutentikasiController::prosesDaftar');
$routes->get('/keluar',  'AutentikasiController::keluar');

// ---------------------------------------------------------------
// Rute Member — Wajib Login (Anggota 3: filter 'autentikasi')
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'autentikasi'], static function ($routes) {
    // Pemesanan tiket (Anggota 4)
    $routes->post('/pemesanan',                 'PemesananController::simpan');
    $routes->get('/pemesanan-saya',             'PemesananController::index');
    $routes->post('/pemesanan-saya/(:num)/batal', 'PemesananController::batalkan/$1');

    // Ulasan & rating (Anggota 4)
    $routes->post('/ulasan', 'UlasanController::simpan');

    // Favorit destinasi (Anggota 3)
    $routes->post('/favorit/ubah', 'FavoritController::ubah');
    $routes->get('/favorit-saya',  'FavoritController::index');
});

// ---------------------------------------------------------------
// Rute Admin — Wajib Login + Peran Admin (Anggota 3: filter 'admin')
// ---------------------------------------------------------------
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/',          'Admin\DasborController::index');
    $routes->get('destinasi',  'Admin\DestinasiAdminController::index');
    $routes->get('pemesanan',  'Admin\PemesananAdminController::index');
});
