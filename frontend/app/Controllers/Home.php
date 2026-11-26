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

        // Ambil daftar kabupaten/kota dan 6 destinasi populer untuk slide utama Hero
        $daftarKabupatenKota = $layananApi->ambilDaftarKabupatenKota();
        $slugPopulerHero     = [
            'danau-toba',
            'pulau-samosir',
            'air-terjun-ponot',
            'istana-maimun',
            'bukit-lawang',
            'lembah-bakkara',
        ];

        $petaDetailSlug   = [];
        $daftarSlideHero  = [];
        $terhubungBackend = false;
        $pesanErrorApi    = null;

        foreach ($slugPopulerHero as $slugItem) {
            $hasilDetail = $layananApi->ambilDetailDestinasi($slugItem);
            if (! empty($hasilDetail['terhubung'])) {
                $terhubungBackend = true;
            }
            if ($pesanErrorApi === null && ! empty($hasilDetail['pesan_error'])) {
                $pesanErrorApi = $hasilDetail['pesan_error'];
            }
            if (! empty($hasilDetail['data']) && is_array($hasilDetail['data'])) {
                $petaDetailSlug[$slugItem] = $hasilDetail['data'];
                $daftarSlideHero[]         = $hasilDetail['data'];
            }
        }

        // Gunakan ulang hasil fetch di atas untuk 3 destinasi unggulan Bento Grid
        $daftarSorotanBento = [];
        foreach (['air-terjun-ponot', 'pulau-samosir', 'istana-maimun'] as $slugSorotan) {
            if (! empty($petaDetailSlug[$slugSorotan])) {
                $daftarSorotanBento[] = $petaDetailSlug[$slugSorotan];
            }
        }

        // Fallback bila daftar slide hero atau sorotan bento belum lengkap
        if ($daftarSlideHero === [] || count($daftarSorotanBento) < 3) {
            $hasilCadangan    = $layananApi->ambilDaftarDestinasi(['batas' => 6, 'halaman' => 1]);
            $terhubungBackend = $terhubungBackend || ! empty($hasilCadangan['terhubung']);
            if ($pesanErrorApi === null && ! empty($hasilCadangan['pesan_error'])) {
                $pesanErrorApi = $hasilCadangan['pesan_error'];
            }

            $dataCadangan = is_array($hasilCadangan['data'] ?? null) ? $hasilCadangan['data'] : [];
            if ($daftarSlideHero === [] && $dataCadangan !== []) {
                $daftarSlideHero = $dataCadangan;
            }
            if (count($daftarSorotanBento) < 3 && $dataCadangan !== []) {
                $daftarSorotanBento = array_slice($dataCadangan, 0, 3);
            }
        }

        $destinasiHero = $daftarSlideHero[0] ?? null;

        return view('welcome_message', [
            'judulHalaman'        => 'JejakSumut — Jelajahi Pesona Sumatera Utara',
            'menuAktif'           => 'beranda',
            'terhubungBackend'    => $terhubungBackend,
            'pesanErrorApi'       => $pesanErrorApi,
            'daftarKabupatenKota' => $daftarKabupatenKota,
            'destinasiHero'       => $destinasiHero,
            'daftarSlideHero'     => $daftarSlideHero,
            'daftarSorotanBento'  => $daftarSorotanBento,
        ]);
    }
}
