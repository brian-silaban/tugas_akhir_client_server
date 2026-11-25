<?php

namespace App\Filters;

use App\Controllers\Api\AutentikasiApiController;
use App\Models\PenggunaModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * AutentikasiTokenFilter – Filter REST API Backend JejakSumut.
 * Memeriksa keberadaan, keabsahan token HMAC-SHA256, dan keberadaan pengguna di DB.
 * Juga menerapkan Rate Limiting: maks. 60 request/menit per IP.
 *
 * Anggota 3 – Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class AutentikasiTokenFilter implements FilterInterface
{
    /**
     * Dijalankan sebelum controller diproses.
     * Tolak request jika token tidak ada, tidak valid, kedaluwarsa, atau melebihi batas rate limit.
     *
     * @param array<string, mixed>|null $arguments
     * @return ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $respons = Services::response();

        // --- Rate Limiting: 60 request per menit per IP ---
        $throttler  = Services::throttler();
        $kunciIp    = md5($request->getIPAddress());
        $batasMenit = 60;

        if (! $throttler->check($kunciIp, $batasMenit, MINUTE)) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_TOO_MANY_REQUESTS,
                'Terlalu banyak permintaan. Silakan coba lagi dalam 1 menit.'
            );
        }

        // --- Verifikasi token dari header Authorization: Bearer <token> ---
        $headerOtorisasi = $request->getHeaderLine('Authorization');
        if ($headerOtorisasi === '' || preg_match('/\ABearer\s+(.+)\z/i', trim($headerOtorisasi)) !== 1) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_UNAUTHORIZED,
                'Akses ditolak. Token autentikasi tidak ditemukan atau format tidak valid.'
            );
        }

        $dataToken = AutentikasiApiController::verifikasiHeaderOtorisasi($headerOtorisasi);
        if ($dataToken === null) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_UNAUTHORIZED,
                'Akses ditolak. Token tidak valid atau sudah kedaluwarsa. Silakan masuk kembali.'
            );
        }

        // Pastikan akun pengguna masih ada di database
        $penggunaAktif = (new PenggunaModel())->select('id, peran')->find((int) $dataToken['id_pengguna']);
        if ($penggunaAktif === null) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_UNAUTHORIZED,
                'Akses ditolak. Akun pengguna tidak ditemukan atau sudah tidak aktif.'
            );
        }

        return null;
    }

    /**
     * Menyusun respons JSON penolakan dengan header keamanan yang sesuai.
     */
    private function buatResponsGagal(ResponseInterface $respons, int $kodeStatus, string $pesan): ResponseInterface
    {
        $respons
            ->setStatusCode($kodeStatus)
            ->setHeader('Cache-Control', 'no-store')
            ->setContentType('application/json')
            ->setBody((string) json_encode([
                'status' => 'gagal',
                'pesan'  => $pesan,
                'data'   => null,
            ]));

        if ($kodeStatus === ResponseInterface::HTTP_UNAUTHORIZED) {
            $respons->setHeader('WWW-Authenticate', 'Bearer');
        }

        return $respons;
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
