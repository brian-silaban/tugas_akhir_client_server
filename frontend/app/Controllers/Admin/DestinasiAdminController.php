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
        if ($hasil['berhasil']) {
            $this->layananApi->bersihkanCacheReferensi();
        }
        $tujuan = $hasil['berhasil'] ? 'admin/destinasi' : 'admin/destinasi/tambah';

        return $this->hasilAksi($hasil, $tujuan, true);
    }

    public function ubah(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimPut('/destinasi/' . $id, $this->bacaFormulir());
        if ($hasil['berhasil']) {
            $this->layananApi->bersihkanCacheReferensi();
        }
        $tujuan = $hasil['berhasil'] ? 'admin/destinasi' : 'admin/destinasi/' . $id . '/edit';

        $pengalihan = $this->hasilAksi($hasil, $tujuan, true);
        if ($hasil['status_kode'] === 409 && ($hasil['data']['konflik'] ?? '') === 'versi_data') {
            $pengalihan->with('konflik_destinasi', $id);
        }

        return $pengalihan;
    }

    public function hapus(int $id): RedirectResponse
    {
        $hasil = $this->layananApi->kirimDelete('/destinasi/' . $id);
        if ($hasil['berhasil']) {
            $this->layananApi->bersihkanCacheReferensi();
        }

        return $this->hasilAksi($hasil, 'admin/destinasi');
    }

    private function tampilkanFormulir(array $destinasi): string
    {
        $daftarKategori = $this->layananApi->ambilDaftarKategori();
        $daftarWilayah  = $this->layananApi->ambilDaftarKabupatenKota();
        $pesanApi       = ($daftarKategori === [] || $daftarWilayah === [])
            ? 'Data referensi kategori atau wilayah gagal dimuat dari server Backend.'
            : null;

        return view('pages/admin/formulir_destinasi', [
            'judul_halaman'   => $destinasi === [] ? 'Tambah destinasi' : 'Edit destinasi',
            'destinasi'       => $destinasi,
            'daftar_kategori' => $daftarKategori,
            'daftar_wilayah'  => $daftarWilayah,
            'pesan_api'       => $pesanApi,
        ]);
    }

    private function bacaFormulir(): array
    {
        return $this->request->getPost([
            'nama', 'slug', 'id_kategori', 'kode_kabupaten', 'sub_kategori',
            'harga_tiket', 'alamat', 'deskripsi', 'foto_utama', 'jam_buka',
            'jam_tutup', 'garis_lintang', 'garis_bujur', 'versi_data',
        ]);
    }
}
