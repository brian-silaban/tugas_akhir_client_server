<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RedirectResponse;

class PemesananAdminController extends BasisAdminController
{
    public function index(): string
    {
        $status = $this->request->getGet('status') ?? '';
        $hasil = $this->layananApi->kirimGet('/admin/pemesanan', [
            'halaman' => $this->nomorHalaman(),
            'status' => $status,
        ]);

        return view('pages/admin/pemesanan', [
            'judul_halaman' => 'Pemesanan',
            'pesan_api' => $hasil['pesan_error'],
            'daftar_pemesanan' => $hasil['data']['daftar'] ?? [],
            'paginasi' => $hasil['data']['paginasi'] ?? [],
            'filter_status' => is_string($status) ? $status : '',
        ]);
    }

    public function ubahStatus(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimPatch('/pemesanan/' . $id . '/status', [
            'status' => $this->request->getPost('status'),
        ]);

        $statusFilter = $this->request->getPost('filter_status');
        $halaman = filter_var($this->request->getPost('halaman'), FILTER_VALIDATE_INT);
        $parameter = ['halaman' => min(1000000, max(1, (int) $halaman))];
        if (in_array($statusFilter, ['menunggu', 'lunas', 'dibatalkan'], true)) {
            $parameter['status'] = $statusFilter;
        }

        return $this->hasilAksi($hasil, 'admin/pemesanan?' . http_build_query($parameter));
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
