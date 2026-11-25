<?php

namespace App\Filters;

use App\Libraries\TokenApi;
use App\Models\PenggunaModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminApiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $idPengguna = (new TokenApi())->bacaPengguna($request->getHeaderLine('Authorization'));
        $pengguna = $idPengguna === null ? null : (new PenggunaModel())->select('id, peran')->find($idPengguna);

        if ($pengguna === null) {
            return $this->tolak(401, 'Token autentikasi tidak valid atau kedaluwarsa. Silakan masuk kembali.');
        }

        if ($pengguna['peran'] !== 'admin') {
            return $this->tolak(403, 'Hanya administrator yang dapat mengakses endpoint ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function tolak(int $kode, string $pesan): ResponseInterface
    {
        $respons = service('response')->setStatusCode($kode)
            ->setHeader('Cache-Control', 'no-store')
            ->setJSON(['status' => 'gagal', 'pesan' => $pesan, 'data' => null]);

        if ($kode === 401) {
            $respons->setHeader('WWW-Authenticate', 'Bearer');
        }

        return $respons;
    }
}
