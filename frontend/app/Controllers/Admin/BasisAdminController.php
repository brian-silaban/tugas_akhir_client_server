<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LayananApiBackend;
use CodeIgniter\HTTP\RedirectResponse;

abstract class BasisAdminController extends BaseController
{
    protected LayananApiBackend $layananApi;

    public function __construct()
    {
        $this->layananApi = (new LayananApiBackend())->aturTokenAutentikasi(session('token_api'));
    }

    protected function hasilAksi(array $hasil, string $tujuan, bool $simpanIsian = false): RedirectResponse
    {
        $pengalihan = redirect()->to(site_url($tujuan))->withCookies();
        if ($hasil['berhasil']) {
            return $pengalihan->with('success', $hasil['pesan'] ?: 'Perubahan berhasil disimpan.');
        }

        if ($simpanIsian) {
            $pengalihan->withInput();
        }

        return $pengalihan
            ->with('error', $hasil['pesan_error'])
            ->with('kesalahan_isian', $hasil['status_kode'] === 422 ? ($hasil['data'] ?? []) : []);
    }

    protected function nomorHalaman(): int
    {
        return min(1000000, max(1, (int) filter_var($this->request->getGet('halaman'), FILTER_VALIDATE_INT)));
    }
}
