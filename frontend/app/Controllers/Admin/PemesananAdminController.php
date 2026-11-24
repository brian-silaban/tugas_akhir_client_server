<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RedirectResponse;

class PemesananAdminController extends BasisAdminController
{
    public function index(): string
    {
        $hasil = $this->layananApi->kirimGet('/admin/pemesanan', ['halaman' => $this->nomorHalaman()]);

        return view('pages/admin/pemesanan', [
            'judul_halaman' => 'Pemesanan',
            'pesan_api' => $hasil['pesan_error'],
            'daftar_pemesanan' => $hasil['data']['daftar'] ?? [],
            'paginasi' => $hasil['data']['paginasi'] ?? [],
        ]);
    }

    public function ubahStatus(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimPatch('/pemesanan/' . $id . '/status', [
            'status' => $this->request->getPost('status'),
        ]);

        return $this->hasilAksi($hasil, 'admin/pemesanan');
    }

    public function ulasan(): string
    {
        $hasil = $this->layananApi->kirimGet('/admin/ulasan', ['halaman' => $this->nomorHalaman()]);

        return view('pages/admin/ulasan', [
            'judul_halaman' => 'Moderasi ulasan',
            'pesan_api' => $hasil['pesan_error'],
            'daftar_ulasan' => $hasil['data']['daftar'] ?? [],
            'paginasi' => $hasil['data']['paginasi'] ?? [],
        ]);
    }

    public function hapusUlasan(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimDelete('/ulasan/' . $id);

        return $this->hasilAksi($hasil, 'admin/ulasan');
    }
}
