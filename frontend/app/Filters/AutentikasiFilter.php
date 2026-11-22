<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AutentikasiFilter — Memastikan pengunjung sudah login (session 'sudah_masuk' = true)
 * sebelum mengakses halaman yang membutuhkan autentikasi (rute Member).
 *
 * Jika belum login, pengguna diarahkan ke halaman /masuk dengan pesan peringatan.
 */
class AutentikasiFilter implements FilterInterface
{
    /**
     * Dijalankan SEBELUM request diteruskan ke Controller.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Periksa apakah pengguna sudah masuk (login)
        $sudahMasuk = session()->get('sudah_masuk');

        if (! $sudahMasuk) {
            return redirect()->to('/masuk')
                ->with('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }
    }

    /**
     * Dijalankan SETELAH Controller selesai memproses response.
     * Tidak ada logika tambahan yang diperlukan di sini.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu memproses apa pun setelah response
    }
}
