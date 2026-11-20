<?php

namespace App\Controllers;

use App\Models\DestinasiModel;
use App\Models\KabupatenKotaModel;
use App\Models\KategoriModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Home extends BaseController
{
    /**
     * Endpoint utama Server Backend JejakSumut (REST API).
     * Mengembalikan informasi status layanan dan ringkasan data PostgreSQL dalam format JSON.
     */
    public function index(): ResponseInterface
    {
        $ringkasanDatabase = [
            'status_koneksi'       => 'terputus',
            'total_destinasi'      => 0,
            'total_kabupaten_kota' => 0,
            'total_kategori'       => 0,
        ];

        try {
            $destinasiModel     = new DestinasiModel();
            $kabupatenKotaModel = new KabupatenKotaModel();
            $kategoriModel      = new KategoriModel();

            $ringkasanDatabase = [
                'status_koneksi'       => 'terhubung',
                'total_destinasi'      => $destinasiModel->countAllResults(),
                'total_kabupaten_kota' => $kabupatenKotaModel->countAllResults(),
                'total_kategori'       => $kategoriModel->countAllResults(),
            ];
        } catch (Throwable $kesalahan) {
            $ringkasanDatabase['pesan_error'] = $kesalahan->getMessage();
        }

        $informasiLayanan = [
            'status'          => 'sukses',
            'nama_layanan'    => 'JejakSumut Backend REST API Server',
            'cakupan_wilayah' => 'Provinsi Sumatera Utara',
            'versi_api'       => 'v1',
            'basis_data'      => 'PostgreSQL (jejaksumut_db)',
            'ringkasan_data'  => $ringkasanDatabase,
            'daftar_rute'     => [
                'GET /api/v1/destinasi'        => 'Mengambil daftar destinasi wisata Sumatera Utara (mendukung filter ?kota= & ?kategori=)',
                'GET /api/v1/destinasi/{slug}' => 'Mengambil detail lengkap satu destinasi wisata Sumatera Utara',
                'GET /api/v1/kategori'         => 'Mengambil daftar kategori wisata Sumatera Utara',
            ],
        ];

        return $this->response->setJSON($informasiLayanan);
    }
}
