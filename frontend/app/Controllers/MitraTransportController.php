<?php

namespace App\Controllers;

use App\Libraries\LayananApiMitra;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller proxy sisi-server untuk endpoint REST API kelompok mitra Transportasi.
 * Mencegah blokir Mixed Content (HTTPS ke HTTP) sekaligus menjaga API Key tetap di server.
 *
 * Anggota 2 (Brian) — Integrasi REST API Kelompok Mitra
 */
class MitraTransportController extends BaseController
{
    /**
     * GET /mitra/transportasi/lokasi
     * Mengembalikan daftar simpul transportasi (bandara, stasiun, terminal) dari API mitra.
     */
    public function lokasi(): ResponseInterface
    {
        $layananMitra = new LayananApiMitra();
        $hasilLokasi  = $layananMitra->ambilDaftarLokasiTransportasi();

        if (! $hasilLokasi['berhasil']) {
            $kodeHttp = $hasilLokasi['status_kode'] >= 400 ? (int) $hasilLokasi['status_kode'] : ResponseInterface::HTTP_BAD_GATEWAY;

            return $this->response->setStatusCode($kodeHttp)->setJSON([
                'status' => 'gagal',
                'pesan'  => $hasilLokasi['pesan_error'] ?? 'Gagal memuat daftar lokasi transportasi dari mitra.',
                'data'   => [],
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status'          => 'berhasil',
            'waktu_respon_ms' => $hasilLokasi['waktu_respon_ms'],
            'data'            => $hasilLokasi['data'],
        ]);
    }

    /**
     * GET /mitra/transportasi/tarif?id_asal=1&id_tujuan=2&jam=08:30
     * Meneruskan kalkulasi jarak, jadwal, dan estimasi tarif ke API mitra Transportasi.
     */
    public function tarif(): ResponseInterface
    {
        $idAsal   = (int) $this->request->getGet('id_asal');
        $idTujuan = (int) $this->request->getGet('id_tujuan');
        $jam      = trim((string) ($this->request->getGet('jam') ?: '08:30'));

        if ($idAsal <= 0 || $idTujuan <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Parameter id_asal dan id_tujuan wajib berupa angka valid.',
                'data'   => null,
            ]);
        }

        if ($idAsal === $idTujuan) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Titik asal dan titik tujuan tidak boleh sama.',
                'data'   => null,
            ]);
        }

        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $jam) !== 1) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Format jam berangkat harus HH:MM (contoh: 08:30).',
                'data'   => null,
            ]);
        }

        $layananMitra = new LayananApiMitra();
        $hasilTarif   = $layananMitra->cekTarifTransportasi($idAsal, $idTujuan, $jam);

        if (! $hasilTarif['berhasil']) {
            $kodeHttp = $hasilTarif['status_kode'] >= 400 ? (int) $hasilTarif['status_kode'] : ResponseInterface::HTTP_BAD_GATEWAY;

            return $this->response->setStatusCode($kodeHttp)->setJSON([
                'status' => 'gagal',
                'pesan'  => $hasilTarif['pesan_error'] ?? 'Gagal menghitung tarif dari server mitra transportasi.',
                'data'   => null,
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status'          => 'berhasil',
            'waktu_respon_ms' => $hasilTarif['waktu_respon_ms'],
            'data'            => [
                'rute'                  => $hasilTarif['rute'],
                'total_jadwal_tersedia' => $hasilTarif['total_jadwal'],
                'pilihan_transportasi'  => $hasilTarif['pilihan_transportasi'],
            ],
        ]);
    }

    /**
     * GET /mitra/transportasi/rekomendasi?user_lat=...&user_lng=...&wisata_lat=...&wisata_lng=...&nama_wisata=...
     * Meneruskan pencarian rekomendasi rute multi-moda menuju koordinat objek wisata ke API Transportasi.
     */
    public function rekomendasi(): ResponseInterface
    {
        $userLat    = (float) $this->request->getGet('user_lat');
        $userLng    = (float) $this->request->getGet('user_lng');
        $wisataLat  = (float) $this->request->getGet('wisata_lat');
        $wisataLng  = (float) $this->request->getGet('wisata_lng');
        $namaWisata = trim((string) ($this->request->getGet('nama_wisata') ?: 'Destinasi Sumatera Utara'));
        $tripType   = trim((string) ($this->request->getGet('trip_type') ?: 'oneway'));

        if ($userLat === 0.0 || $userLng === 0.0 || $wisataLat === 0.0 || $wisataLng === 0.0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Koordinat asal dan koordinat tujuan wisata wajib diisi dengan valid.',
                'data'   => null,
            ]);
        }

        $layananMitra = new LayananApiMitra();
        $hasilRekom   = $layananMitra->ambilRekomendasiRuteTransportasi(
            $userLat,
            $userLng,
            $wisataLat,
            $wisataLng,
            $namaWisata,
            $tripType
        );

        if (! $hasilRekom['berhasil']) {
            $kodeHttp = $hasilRekom['status_kode'] >= 400 ? (int) $hasilRekom['status_kode'] : ResponseInterface::HTTP_BAD_GATEWAY;

            return $this->response->setStatusCode($kodeHttp)->setJSON([
                'status' => 'gagal',
                'pesan'  => $hasilRekom['pesan_error'] ?? 'Gagal memuat rekomendasi rute dari server mitra transportasi.',
                'data'   => null,
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status'          => 'berhasil',
            'waktu_respon_ms' => $hasilRekom['waktu_respon_ms'],
            'data'            => $hasilRekom['data'],
        ]);
    }
}
