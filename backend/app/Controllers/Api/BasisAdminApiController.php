<?php

namespace App\Controllers\Api;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;

abstract class BasisAdminApiController extends ResourceController
{
    protected $format = 'json';

    protected function koneksiAdmin(): BaseConnection
    {
        $konfigurasi = config(Database::class);

        return Database::connect($konfigurasi->admin === [] ? null : 'admin');
    }

    protected function bacaMasukan(): array
    {
        if (! str_contains($this->request->getHeaderLine('Content-Type'), 'application/json')) {
            return $this->request->getRawInput();
        }

        $masukan = json_decode($this->request->getBody(), true);

        return is_array($masukan) ? $masukan : [];
    }

    protected function berhasil(mixed $data, string $pesan = '', int $kode = 200): ResponseInterface
    {
        return $this->responsAdmin('berhasil', $pesan, $data, $kode);
    }

    protected function gagal(int $kode, string $pesan, ?array $validasi = null): ResponseInterface
    {
        return $this->responsAdmin('gagal', $pesan, $validasi, $kode);
    }

    private function responsAdmin(string $status, string $pesan, mixed $data, int $kode): ResponseInterface
    {
        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->respond(['status' => $status, 'pesan' => $pesan, 'data' => $data], $kode);
    }
}
