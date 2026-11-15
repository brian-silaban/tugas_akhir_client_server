<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    /**
     * Endpoint utama Server Backend IndWalk (REST API).
     * Mengembalikan informasi status layanan dalam format JSON.
     */
    public function index(): ResponseInterface
    {
        $informasiLayanan = [
            'status'         => 'sukses',
            'nama_layanan'   => 'IndWalk Backend REST API Server',
            'versi_api'      => 'v1',
            'basis_data'     => 'PostgreSQL',
            'daftar_rute'    => [
                'GET /api/v1/destinasi'        => 'Mengambil daftar destinasi wisata (mendukung filter ?kota= & ?kategori=)',
                'GET /api/v1/destinasi/{slug}' => 'Mengambil detail lengkap satu destinasi wisata',
                'GET /api/v1/kategori'         => 'Mengambil daftar kategori wisata',
            ],
        ];

        return $this->response->setJSON($informasiLayanan);
    }
}
