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
