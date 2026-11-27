<?php

namespace App\Filters;

use App\Controllers\Api\AutentikasiApiController;
use App\Models\PenggunaModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * PeranAdminFilter – Filter REST API Backend JejakSumut.
 * Memeriksa keabsahan token HMAC-SHA256 DAN memverifikasi peran admin terbaru di database.
 * Juga menerapkan Rate Limiting: maks. 60 request/menit per IP.
 *
 * Anggota 3 – Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class PeranAdminFilter implements FilterInterface
{
    /**
     * Dijalankan sebelum controller diproses.
     * Tolak dengan 401 jika token tidak valid/akun tidak ada, 403 jika peran bukan admin.
     *
     * @param array<string, mixed>|null $arguments
     * @return ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $respons = Services::response();

        // --- Rate Limiting: 60 request per menit per IP ---
        $throttler  = Services::throttler();
        $kunciIp    = md5($request->getIPAddress() . '_admin');
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
                'Akses ditolak. Token autentikasi tidak ditemukan.'
            );
        }

        $dataToken = AutentikasiApiController::verifikasiHeaderOtorisasi($headerOtorisasi);
        if ($dataToken === null) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_UNAUTHORIZED,
                'Akses ditolak. Token tidak valid atau sudah kedaluwarsa.'
            );
        }

        // Cek ulang akun, kata sandi, & peran terbaru dari database agar pencabutan hak admin atau ubah sandi langsung berlaku
        $penggunaAktif = (new PenggunaModel())->select('id, peran, kata_sandi')->find((int) $dataToken['id_pengguna']);
        if ($penggunaAktif === null) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_UNAUTHORIZED,
                'Akses ditolak. Akun administrator tidak ditemukan.'
            );
        }

        if (! AutentikasiApiController::cocokkanSidikSandiToken($dataToken, (string) ($penggunaAktif['kata_sandi'] ?? ''))) {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_UNAUTHORIZED,
                'Akses ditolak. Sesi autentikasi telah berakhir karena perubahan kata sandi. Silakan masuk kembali.'
            );
        }

        if (($penggunaAktif['peran'] ?? '') !== 'admin') {
            return $this->buatResponsGagal(
                $respons,
                ResponseInterface::HTTP_FORBIDDEN,
                'Akses ditolak. Halaman ini hanya dapat diakses oleh Administrator.'
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
     * @param array<string, mixed>|null $arguments
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
