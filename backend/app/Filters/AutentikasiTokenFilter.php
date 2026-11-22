<?php

namespace App\Filters;

use App\Controllers\Api\AutentikasiApiController;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * AutentikasiTokenFilter – Filter REST API Backend JejakSumut.
 * Memeriksa keberadaan dan keabsahan token HMAC-SHA256 pada header Authorization.
 * Juga menerapkan Rate Limiting: maks. 60 request/menit per IP.
 *
 * Anggota 3 – Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class AutentikasiTokenFilter implements FilterInterface
{
    /**
     * Dijalankan sebelum controller diproses.
     * Tolak request jika token tidak ada, tidak valid, atau kedaluwarsa.
     * Tolak request jika IP melampaui batas 60 request per menit.
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

        if (! $throttler->check($alamatIp, $batasMenit, MINUTE)) {
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
                    'pesan'  => 'Akses ditolak. Token autentikasi tidak ditemukan atau format tidak valid.',
                    'data'   => null,
                ]));
        }

        // Ambil string token dari header
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

        // Verifikasi tanda tangan dan masa berlaku token
        $dataToken = AutentikasiApiController::verifikasiToken($token);

        if ($dataToken === null) {
            return $response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setContentType('application/json')
                ->setBody(json_encode([
                    'status' => 'gagal',
                    'pesan'  => 'Akses ditolak. Token tidak valid atau sudah kedaluwarsa. Silakan masuk kembali.',
                    'data'   => null,
                ]));
        }

        // Simpan data token ke dalam request agar bisa dibaca controller
        $request->dataToken = $dataToken;

        return null;
    }

    /**
     * Dijalankan setelah controller selesai (tidak digunakan di sini).
     *
     * @param array<string, mixed>|null $arguments
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
