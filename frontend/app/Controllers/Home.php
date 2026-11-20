<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;

/**
 * Controller halaman Beranda JejakSumut.
 * Seluruh data diambil dari REST API Backend (port 8080) — tanpa akses database langsung.
 *
 * Anggota 1 (joxtian) — Beranda Sumatera Utara
 */
class Home extends BaseController
{
    public function index(): string
    {
        $layananApi = new LayananApiBackend();

        // Ambil daftar kategori & kabupaten/kota dari REST API Backend
        $daftarKategori      = $layananApi->ambilDaftarKategori();
        $daftarKabupatenKota = $layananApi->ambilDaftarKabupatenKota();

        // Ambil data katalog preview (6 item) dan sorotan utama (Danau Toba)
        $hasilKatalog = $layananApi->ambilDaftarDestinasi(['batas' => 6, 'halaman' => 1]);
        $hasilHero    = $layananApi->ambilDetailDestinasi('danau-toba');

        $destinasiHero = $hasilHero['data'] ?? ($hasilKatalog['data'][0] ?? null);

        // Ambil 3 destinasi unggulan untuk Bento Grid
        $daftarSorotanBento = [];
        foreach (['air-terjun-ponot', 'pulau-samosir', 'taman-alam-lumbini'] as $slugSorotan) {
            $detailSorotan = $layananApi->ambilDetailDestinasi($slugSorotan);
            if (! empty($detailSorotan['data'])) {
                $daftarSorotanBento[] = $detailSorotan['data'];
            }
        }

        if (count($daftarSorotanBento) < 3 && ! empty($hasilKatalog['data'])) {
            $daftarSorotanBento = array_slice($hasilKatalog['data'], 0, 3);
        }

        $totalDestinasi = (int) ($hasilKatalog['paginasi']['total_data'] ?? count($hasilKatalog['data']));

        return view('welcome_message', [
            'judulHalaman'           => 'JejakSumut — Jelajahi Pesona Sumatera Utara',
            'menuAktif'              => 'beranda',
            'terhubungBackend'       => $hasilKatalog['terhubung'],
            'pesanErrorApi'          => $hasilKatalog['pesan_error'],
            'totalDestinasi'         => $totalDestinasi,
            'totalKabupatenKota'     => count($daftarKabupatenKota),
            'totalKategori'          => count($daftarKategori),
            'daftarKategori'         => $daftarKategori,
            'daftarKabupatenKota'    => $daftarKabupatenKota,
            'destinasiHero'          => $destinasiHero,
            'daftarSorotanBento'     => $daftarSorotanBento,
            'daftarKatalogDestinasi' => $hasilKatalog['data'],
        ]);
    }
}
