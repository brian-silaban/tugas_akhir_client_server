<?php

namespace App\Controllers\Admin;

use App\Libraries\LayananApiMitra;
use CodeIgniter\HTTP\RedirectResponse;

class DasborController extends BasisAdminController
{
    public function index(): string|RedirectResponse
    {
        $paksaSegarkan = $this->request->getGet('segarkan') === '1';
        $hasil         = $this->layananApi->kirimGet('/admin/statistik');

        if (($hasil['status_kode'] ?? 0) === 401) {
            session()->destroy();

            return redirect()->to(site_url('masuk'))
                ->withCookies()
                ->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
        }

        $hasilPemesanan  = $this->layananApi->kirimGet('/admin/pemesanan', ['halaman' => 1, 'batas' => 5]);
        $daftarPemesanan = $hasilPemesanan['data']['daftar'] ?? [];

        // Bentuk ringkasan status backend dari hasil statistik agar tidak menembak endpoint tambahan
        if ($hasil['terhubung'] && $hasil['berhasil']) {
            $pesanStatusBackend = 'Terhubung ke REST API Backend JejakSumut.';
        } elseif ($hasil['terhubung']) {
            $pesanStatusBackend = $hasil['pesan_error'] ?? 'Server Backend merespons dengan status error.';
        } else {
            $pesanStatusBackend = $hasil['pesan_error'] ?? 'Tidak dapat terhubung ke server REST API Backend.';
        }

        $statusBackend = [
            'terhubung'       => $hasil['terhubung'],
            'status_kode'     => $hasil['status_kode'],
            'url_dasar'       => $this->layananApi->ambilUrlDasarApi(),
            'waktu_respon_ms' => $hasil['waktu_respon_ms'],
            'pesan'           => $pesanStatusBackend,
        ];

        return view('pages/admin/dasbor', [
            'judul_halaman'     => 'Dasbor',
            'statistik'         => $hasil['data'] ?? [],
            'pemesanan_terbaru' => is_array($daftarPemesanan) ? array_slice($daftarPemesanan, 0, 5) : [],
            'pesan_api'         => $hasil['pesan_error'],
            'status_backend'    => $statusBackend,
            'status_mitra'      => (new LayananApiMitra())->cekStatusSemuaMitra('Medan', $paksaSegarkan),
        ]);
    }
}
