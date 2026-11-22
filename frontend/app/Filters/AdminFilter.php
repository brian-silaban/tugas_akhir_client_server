<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AdminFilter — Memastikan pengguna sudah login DAN memiliki peran 'admin'
 * sebelum mengakses halaman di area administrasi (/admin/*).
 *
 * Jika belum login, diarahkan ke /masuk.
 * Jika sudah login tapi bukan admin, diarahkan ke / dengan pesan akses ditolak.
 */
class AdminFilter implements FilterInterface
{
    /**
     * Dijalankan SEBELUM request diteruskan ke Controller.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Langkah 1: Periksa apakah pengguna sudah login
        $sudahMasuk = session()->get('sudah_masuk');

        if (! $sudahMasuk) {
            return redirect()->to('/masuk')
                ->with('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.');
        }

        // Langkah 2: Periksa apakah peran pengguna adalah admin
        $peranPengguna = session()->get('peran');

        if ($peranPengguna !== 'admin') {
            return redirect()->to('/')
                ->with('error', 'Akses ditolak. Halaman ini hanya dapat diakses oleh administrator.');
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
