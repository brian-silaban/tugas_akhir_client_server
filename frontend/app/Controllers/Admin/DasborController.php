<?php

namespace App\Controllers\Admin;

use App\Libraries\LayananApiMitra;

class DasborController extends BasisAdminController
{
    public function index(): string
    {
        $hasil = $this->layananApi->kirimGet('/admin/statistik');
        $hasilPemesanan = $this->layananApi->kirimGet('/admin/pemesanan', ['halaman' => 1]);
        $daftarPemesanan = $hasilPemesanan['data']['daftar'] ?? [];

        return view('pages/admin/dasbor', [
            'judul_halaman' => 'Dasbor',
            'statistik' => $hasil['data'] ?? [],
            'pemesanan_terbaru' => is_array($daftarPemesanan) ? array_slice($daftarPemesanan, 0, 5) : [],
            'pesan_api' => $hasil['pesan_error'],
            'status_backend' => $this->layananApi->cekKoneksiBackend(),
            'status_mitra' => (new LayananApiMitra())->cekStatusSemuaMitra(),
        ]);
    }
}
