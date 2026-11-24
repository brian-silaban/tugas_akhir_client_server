<?php

namespace App\Controllers\Admin;

use App\Libraries\LayananApiMitra;

class DasborController extends BasisAdminController
{
    public function index(): string
    {
        $hasil = $this->layananApi->kirimGet('/admin/statistik');

        return view('pages/admin/dasbor', [
            'judul_halaman' => 'Dasbor',
            'statistik' => $hasil['data'] ?? [],
            'pesan_api' => $hasil['pesan_error'],
            'status_backend' => $this->layananApi->cekKoneksiBackend(),
            'status_mitra' => (new LayananApiMitra())->cekStatusSemuaMitra(),
        ]);
    }
}
