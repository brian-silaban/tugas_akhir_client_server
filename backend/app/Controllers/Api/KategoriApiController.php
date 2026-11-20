<?php

namespace App\Controllers\Api;

use App\Models\KabupatenKotaModel;
use App\Models\KategoriModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * REST API Controller untuk daftar kategori dan wilayah kabupaten/kota Sumatera Utara.
 *
 * Anggota 1 (joxtian) — Referensi Kategori & Wilayah Sumatera Utara
 */
class KategoriApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/v1/kategori
     * Mengembalikan daftar seluruh kategori wisata beserta jumlah objek wisata.
     */
    public function index(): ResponseInterface
    {
        $kategoriModel  = new KategoriModel();
        $daftarKategori = $kategoriModel->ambilSemuaDenganJumlahDestinasi();

        foreach ($daftarKategori as &$kategori) {
            $kategori['id']               = (int) $kategori['id'];
            $kategori['jumlah_destinasi'] = (int) $kategori['jumlah_destinasi'];
        }
        unset($kategori);

        return $this->respond([
            'status'      => 'berhasil',
            'penyedia'    => 'JejakSumut Tourism API',
            'jumlah_data' => count($daftarKategori),
            'data'        => $daftarKategori,
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * GET /api/v1/kabupaten-kota
     * Mengembalikan daftar 33 kabupaten/kota Sumatera Utara beserta jumlah objek wisata.
     */
    public function kabupatenKota(): ResponseInterface
    {
        $kabupatenKotaModel  = new KabupatenKotaModel();
        $daftarKabupatenKota = $kabupatenKotaModel->ambilSemuaDenganJumlahDestinasi();

        foreach ($daftarKabupatenKota as &$wilayah) {
            $wilayah['jumlah_destinasi'] = (int) $wilayah['jumlah_destinasi'];
        }
        unset($wilayah);

        return $this->respond([
            'status'      => 'berhasil',
            'penyedia'    => 'JejakSumut Tourism API',
            'jumlah_data' => count($daftarKabupatenKota),
            'data'        => $daftarKabupatenKota,
        ], ResponseInterface::HTTP_OK);
    }
}
