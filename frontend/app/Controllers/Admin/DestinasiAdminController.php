<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\RedirectResponse;

class DestinasiAdminController extends BasisAdminController
{
    public function index(): string
    {
        $kataKunci = $this->request->getGet('kata_kunci');
        $kataKunci = is_string($kataKunci) ? trim($kataKunci) : '';
        $hasil = $this->layananApi->kirimGet('/destinasi', [
            'halaman' => $this->nomorHalaman(),
            'batas' => 20,
            'urutan' => 'terbaru',
            'kata_kunci' => $kataKunci,
        ]);

        return view('pages/admin/destinasi', [
            'judul_halaman' => 'Destinasi',
            'pesan_api' => $hasil['pesan_error'],
            'daftar_destinasi' => $hasil['data'] ?? [],
            'paginasi' => $hasil['isi']['paginasi'] ?? [],
            'kata_kunci' => $kataKunci,
        ]);
    }

    public function tambah(): string
    {
        return $this->tampilkanFormulir([]);
    }

    public function edit(int $id): string|RedirectResponse
    {
        $hasil = $this->layananApi->kirimGet('/admin/destinasi/' . $id);
        if (! $hasil['berhasil']) {
            return redirect()->to(site_url('admin/destinasi'))->with('error', $hasil['pesan_error']);
        }

        return $this->tampilkanFormulir($hasil['data']);
    }

    public function buat(): RedirectResponse
    {
        $hasil = $this->layananApi->kirimPost('/destinasi', $this->bacaFormulir());
        $tujuan = $hasil['berhasil'] ? 'admin/destinasi' : 'admin/destinasi/tambah';

        return $this->hasilAksi($hasil, $tujuan, true);
    }

    public function ubah(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimPut('/destinasi/' . $id, $this->bacaFormulir());
        $tujuan = $hasil['berhasil'] ? 'admin/destinasi' : 'admin/destinasi/' . $id . '/edit';

        return $this->hasilAksi($hasil, $tujuan, true);
    }

    public function hapus(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimDelete('/destinasi/' . $id);

        return $this->hasilAksi($hasil, 'admin/destinasi');
    }

    private function tampilkanFormulir(array $destinasi): string
    {
        $kategori = $this->layananApi->kirimGet('/kategori');
        $wilayah = $this->layananApi->kirimGet('/kabupaten-kota');

        return view('pages/admin/formulir_destinasi', [
            'judul_halaman' => $destinasi === [] ? 'Tambah destinasi' : 'Edit destinasi',
            'destinasi' => $destinasi,
            'daftar_kategori' => $kategori['data'] ?? [],
            'daftar_wilayah' => $wilayah['data'] ?? [],
            'pesan_api' => $kategori['pesan_error'] ?? $wilayah['pesan_error'],
        ]);
    }

    private function bacaFormulir(): array
    {
        return $this->request->getPost([
            'nama', 'slug', 'id_kategori', 'kode_kabupaten', 'sub_kategori',
            'harga_tiket', 'alamat', 'deskripsi', 'foto_utama', 'jam_buka',
            'jam_tutup', 'garis_lintang', 'garis_bujur',
        ]);
    }
}
