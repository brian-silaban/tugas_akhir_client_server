<?php

namespace App\Filters;

use App\Controllers\Api\AutentikasiApiController;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * PeranAdminFilter – Filter REST API Backend JejakSumut.
 * Memeriksa keabsahan token HMAC-SHA256 DAN memastikan peran pengguna adalah 'admin'.
 * Juga menerapkan Rate Limiting: maks. 60 request/menit per IP.
 *
 * Anggota 3 – Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class PeranAdminFilter implements FilterInterface
{
    /**
     * Dijalankan sebelum controller diproses.
     * Tolak dengan 401 jika token tidak valid, 403 jika peran bukan admin.
     *
     * @param array<string, mixed>|null $arguments
     * @return ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = Services::response();

        // --- Rate Limiting: 60 request per menit per IP ---
        $throttler  = Services::throttler();
        $alamatIp   = $request->getIPAddress();
        $batasMenit = 60;

        if (! $throttler->check($alamatIp . '_admin', $batasMenit, MINUTE)) {
            return $response
                ->setStatusCode(ResponseInterface::HTTP_TOO_MANY_REQUESTS)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'gagal',
                    'pesan'  => 'Terlalu banyak permintaan. Silakan coba lagi dalam 1 menit.',
                    'data'   => null,
                ]));
        }

        // --- Verifikasi token dari header Authorization: Bearer <token> ---
        $headerAuthorization = $request->getHeaderLine('Authorization');

        if (empty($headerAuthorization) || ! str_starts_with($headerAuthorization, 'Bearer ')) {
            return $response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'gagal',
                    'pesan'  => 'Akses ditolak. Token autentikasi tidak ditemukan.',
                    'data'   => null,
                ]));
        }

        $token = trim(substr($headerAuthorization, 7));

        if ($token === '') {
            return $response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'gagal',
                    'pesan'  => 'Akses ditolak. Token autentikasi kosong.',
                    'data'   => null,
                ]));
        }

        // Verifikasi tanda tangan dan masa berlaku
        $dataToken = AutentikasiApiController::verifikasiToken($token);

        if ($dataToken === null) {
            return $response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'gagal',
                    'pesan'  => 'Akses ditolak. Token tidak valid atau sudah kedaluwarsa.',
                    'data'   => null,
                ]));
        }

        // Cek apakah pengguna memiliki peran admin
        if (($dataToken['peran'] ?? '') !== 'admin') {
            return $response
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'gagal',
                    'pesan'  => 'Akses ditolak. Halaman ini hanya dapat diakses oleh Administrator.',
                    'data'   => null,
                ]));
        }

        // Simpan data token ke request untuk dipakai controller
        $request->dataToken = $dataToken;

        return null;
    }

    /**
     * @param array<string, mixed>|null $arguments
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
