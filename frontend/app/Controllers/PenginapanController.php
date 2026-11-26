<?php

namespace App\Controllers;

use App\Libraries\LayananApiMitra;

/**
 * Controller halaman katalog mandiri Penginapan & Hotel Sumatera Utara.
 * Seluruh data diambil secara live dari REST API mitra Singgah Medan melalui server proxy PHP.
 */
class PenginapanController extends BaseController
{
    /**
     * GET /penginapan
     * Menampilkan katalog penginapan beserta filter kecamatan, sarapan, rating, dan modal ulasan.
     */
    public function index(): string
    {
        $layananMitra = new LayananApiMitra();

        $filterAktif = [
            'q'          => trim((string) $this->request->getGet('q')),
            'kecamatan'  => trim((string) $this->request->getGet('kecamatan')),
            'sarapan'    => strtolower(trim((string) $this->request->getGet('sarapan'))),
            'rating_min' => trim((string) $this->request->getGet('rating_min')),
            'halaman'    => max(1, (int) ($this->request->getGet('halaman') ?? 1)),
            'batas'      => 12,
        ];

        $hasilKatalog       = $layananMitra->ambilKatalogPenginapan($filterAktif);
        $hasilKecamatan     = $layananMitra->ambilDaftarKecamatanPenginapan();
        $daftarUlasanSesi   = session('ulasan_hotel_dibuat');
        $daftarIdUlasanSesi = is_array($daftarUlasanSesi) ? array_map('intval', $daftarUlasanSesi) : [];

        return view('pages/mitra/penginapan', [
            'judulHalaman'       => 'Penginapan & Hotel — JejakSumut',
            'menuAktif'          => 'penginapan',
            'hasilKatalog'       => $hasilKatalog,
            'daftarHotel'        => $hasilKatalog['daftar_hotel'] ?? [],
            'metaPaginasi'       => $hasilKatalog['meta'] ?? ['page' => 1, 'limit' => 12, 'total' => 0, 'total_pages' => 1],
            'daftarKecamatan'    => $hasilKecamatan['data'] ?? [],
            'filterAktif'        => $filterAktif,
            'sudahMasuk'         => ! empty(session('id_pengguna')),
            'daftarIdUlasanSesi' => $daftarIdUlasanSesi,
        ]);
    }
}
