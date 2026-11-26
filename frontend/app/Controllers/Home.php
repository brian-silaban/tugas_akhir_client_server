<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;

/**
 * Controller halaman Beranda JejakSumut.
 * Seluruh data diambil dari REST API Backend — tanpa akses database langsung.
 *
 * Anggota 1 (joxtian) — Beranda Sumatera Utara
 */
class Home extends BaseController
{
    public function index(): string
    {
        $layananApi = new LayananApiBackend();

        // Ambil data wilayah kabupaten/kota dan sorotan utama (Danau Toba)
        $daftarKabupatenKota = $layananApi->ambilDaftarKabupatenKota();
        $hasilHero           = $layananApi->ambilDetailDestinasi('danau-toba');
        $terhubungBackend    = $hasilHero['terhubung'];
        $pesanErrorApi       = $hasilHero['pesan_error'];

        // Ambil 3 destinasi unggulan untuk Bento Grid
        $daftarSorotanBento = [];
        foreach (['air-terjun-ponot', 'pulau-samosir', 'istana-maimun'] as $slugSorotan) {
            $detailSorotan = $layananApi->ambilDetailDestinasi($slugSorotan);
            if (! empty($detailSorotan['data'])) {
                $daftarSorotanBento[] = $detailSorotan['data'];
            }
        }

        // Fallback ringan hanya bila data hero atau sorotan bento belum lengkap
        $destinasiHero = $hasilHero['data'] ?? null;
        if ($destinasiHero === null || count($daftarSorotanBento) < 3) {
            $hasilCadangan    = $layananApi->ambilDaftarDestinasi(['batas' => 3, 'halaman' => 1]);
            $terhubungBackend = $terhubungBackend || $hasilCadangan['terhubung'];
            if ($pesanErrorApi === null) {
                $pesanErrorApi = $hasilCadangan['pesan_error'];
            }

            if ($destinasiHero === null) {
                $destinasiHero = $hasilCadangan['data'][0] ?? null;
            }
            if (count($daftarSorotanBento) < 3 && ! empty($hasilCadangan['data'])) {
                $daftarSorotanBento = array_slice($hasilCadangan['data'], 0, 3);
            }
        }

        return view('welcome_message', [
            'judulHalaman'        => 'JejakSumut — Jelajahi Pesona Sumatera Utara',
            'menuAktif'           => 'beranda',
            'terhubungBackend'    => $terhubungBackend,
            'pesanErrorApi'       => $pesanErrorApi,
            'daftarKabupatenKota' => $daftarKabupatenKota,
            'destinasiHero'       => $destinasiHero,
            'daftarSorotanBento'  => $daftarSorotanBento,
        ]);
    }
}
