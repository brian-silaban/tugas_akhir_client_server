<?php

namespace App\Controllers\Api;

use App\Models\FavoritModel;
use App\Models\DestinasiModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * FavoritApiController — Endpoint REST API Favorit Destinasi JejakSumut (Backend).
 *
 * Anggota 3 — Autentikasi, Sesi Pengguna & Fitur Favorit
 */
class FavoritApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * GET /api/v1/favorit/(:num)
     * Mengambil daftar destinasi yang difavoritkan oleh pengguna berdasarkan id_pengguna.
     */
    public function show($idPengguna = null): ResponseInterface
    {
        if (empty($idPengguna) || ! is_numeric($idPengguna)) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Parameter id_pengguna wajib disertakan dan berupa angka.',
                'data'   => [],
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $favoritModel  = new FavoritModel();
        $daftarFavorit = $favoritModel->ambilFavoritPengguna((int) $idPengguna);

        return $this->respond([
            'status'      => 'berhasil',
            'jumlah_data' => count($daftarFavorit),
            'data'        => $daftarFavorit,
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * GET /api/v1/favorit?id_pengguna=X
     * Mengambil daftar destinasi yang difavoritkan oleh pengguna.
     */
    public function index(): ResponseInterface
    {
        $idPengguna = $this->request->getGet('id_pengguna');

        if (empty($idPengguna) || ! is_numeric($idPengguna)) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Parameter id_pengguna wajib disertakan dan berupa angka.',
                'data'   => [],
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $favoritModel = new FavoritModel();
        $daftarFavorit = $favoritModel->ambilFavoritPengguna((int) $idPengguna);

        return $this->respond([
            'status'      => 'berhasil',
            'jumlah_data' => count($daftarFavorit),
            'data'        => $daftarFavorit,
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * POST /api/v1/favorit/ubah
     * Menambah atau menghapus destinasi dari favorit (toggle).
     */
    public function ubah(): ResponseInterface
    {
        $inputJson = $this->request->getJSON(true);
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        $idPengguna  = (int) ($inputData['id_pengguna'] ?? 0);
        $idDestinasi = (int) ($inputData['id_destinasi'] ?? 0);

        if ($idPengguna <= 0 || $idDestinasi <= 0) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Parameter id_pengguna dan id_destinasi wajib disertakan dan valid.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $favoritModel = new FavoritModel();
        $hasil = $favoritModel->ubahFavorit($idPengguna, $idDestinasi);

        return $this->respond($hasil, ResponseInterface::HTTP_OK);
    }
}
