<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Halaman beranda JejakSumut
$routes->get('/', 'Home::index');

// ============================================================
// RUTE FRONTEND CLIENT — JejakSumut
// ============================================================

// --- Destinasi Wisata (Anggota 1) ---
// $routes->get('destinasi',           'DestinasiController::index');
// $routes->get('destinasi/(:segment)', 'DestinasiController::detail/$1');

// --- Autentikasi & Akun (Anggota 3) ---
// $routes->get('masuk',   'AutentikasiController::tampilFormMasuk');
// $routes->post('masuk',  'AutentikasiController::prosesMasuk');
// $routes->get('daftar',  'AutentikasiController::tampilFormDaftar');
// $routes->post('daftar', 'AutentikasiController::prosesDaftar');
// $routes->get('keluar',  'AutentikasiController::keluar');

// --- Favorit (Anggota 3) ---
// $routes->get('favorit',          'FavoritController::index');
// $routes->post('favorit/ubah',    'FavoritController::ubah');

// ================================================================
// MODUL ANGGOTA 4 — Pemesanan Tiket & Ulasan (Frontend Client)
// ================================================================

// --- Halaman Tiket Saya ---
// GET  /pemesanan                → Daftar riwayat tiket pengguna
$routes->get('pemesanan', 'PemesananController::index');

// GET  /pemesanan/etiket/{id}   → Halaman E-Ticket digital
$routes->get('pemesanan/etiket/(:num)', 'PemesananController::tampilEtiket/$1');

// POST /pemesanan/buat          → Proses form pemesanan tiket baru
$routes->post('pemesanan/buat', 'PemesananController::buat');

// POST /pemesanan/batal/{id}    → Batalkan tiket
$routes->post('pemesanan/batal/(:num)', 'PemesananController::batal/$1');

// --- Ulasan & Rating ---
// POST /ulasan/kirim            → Kirim ulasan baru dari form di halaman detail
$routes->post('ulasan/kirim', 'UlasanController::kirim');

// --- Admin Dashboard (Anggota 5) ---
// $routes->get('admin',                        'Admin\DasborController::index');
// $routes->get('admin/pemesanan',               'Admin\PemesananAdminController::index');
// $routes->post('admin/pemesanan/(:num)/status', 'Admin\PemesananAdminController::ubahStatus/$1');
// $routes->get('admin/destinasi',               'Admin\DestinasiAdminController::index');
