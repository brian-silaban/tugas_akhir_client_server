<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    /**
     * Endpoint utama Server Backend JejakSumut (REST API).
     * Mengembalikan informasi status layanan dalam format JSON.
     */
    public function index(): ResponseInterface
    {
        $informasiLayanan = [
            'status'          => 'sukses',
            'nama_layanan'    => 'JejakSumut Backend REST API Server',
            'cakupan_wilayah' => 'Provinsi Sumatera Utara',
            'versi_api'       => 'v1',
            'basis_data'      => 'PostgreSQL',
            'daftar_rute'     => [
                'GET /api/v1/destinasi'        => 'Mengambil daftar destinasi wisata Sumatera Utara (mendukung filter ?kota= & ?kategori=)',
                'GET /api/v1/destinasi/{slug}' => 'Mengambil detail lengkap satu destinasi wisata Sumatera Utara',
                'GET /api/v1/kategori'         => 'Mengambil daftar kategori wisata Sumatera Utara',
            ],
        ];

        return $this->response->setJSON($informasiLayanan);
    }
}
