<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Controller halaman Katalog Eksplorasi & Detail Destinasi Sumatera Utara.
 * Seluruh data diambil melalui REST API Backend — tanpa query SQL langsung.
 *
 * Anggota 1 (joxtian) — Katalog & Detail Destinasi
 */
class DestinasiController extends BaseController
{
    /**
     * GET /destinasi
     * Menampilkan katalog destinasi lengkap dengan filter dan paginasi 12 item per halaman.
     */
    public function index(): string
    {
        $layananApi = new LayananApiBackend();

        $filterAktif = [
            'kata_kunci'     => trim((string) $this->request->getGet('q')),
            'kategori'       => trim((string) $this->request->getGet('kategori')),
            'kode_kabupaten' => trim((string) $this->request->getGet('kabupaten')),
            'harga_maksimal' => trim((string) $this->request->getGet('harga_maksimal')),
            'urutan'         => trim((string) ($this->request->getGet('urutan') ?: 'terbaik')),
            'halaman'        => max(1, (int) ($this->request->getGet('halaman') ?? 1)),
            'batas'          => 12,
        ];

        $hasilKatalog        = $layananApi->ambilDaftarDestinasi($filterAktif);
        $daftarKategori      = $layananApi->ambilDaftarKategori();
        $daftarKabupatenKota = $layananApi->ambilDaftarKabupatenKota();

        return view('pages/destinasi/katalog', [
            'judulHalaman'        => 'Katalog Wisata Sumatera Utara — JejakSumut',
            'menuAktif'           => 'katalog',
            'terhubungBackend'    => $hasilKatalog['terhubung'],
            'pesanErrorApi'       => $hasilKatalog['pesan_error'],
            'daftarDestinasi'     => $hasilKatalog['data'],
            'paginasi'            => $hasilKatalog['paginasi'],
            'daftarKategori'      => $daftarKategori,
            'daftarKabupatenKota' => $daftarKabupatenKota,
            'filterAktif'         => $filterAktif,
        ]);
    }

    /**
     * GET /destinasi/{slug}
     * Menampilkan detail lengkap satu destinasi wisata Sumatera Utara beserta
     * form pemesanan & komponen ulasan (Anggota 4) serta slot modul mitra/favorit.
     */
    public function detail(string $slug): string
    {
        $layananApi  = new LayananApiBackend();
        $hasilDetail = $layananApi->ambilDetailDestinasi($slug);

        if (! $hasilDetail['terhubung']) {
            return view('pages/destinasi/detail', [
                'judulHalaman'     => 'Koneksi Server Terputus — JejakSumut',
                'menuAktif'        => 'katalog',
                'terhubungBackend' => false,
                'pesanErrorApi'    => $hasilDetail['pesan_error'],
                'destinasi'        => null,
            ]);
        }

        if ($hasilDetail['status_kode'] === 404 || empty($hasilDetail['data'])) {
            throw PageNotFoundException::forPageNotFound('Destinasi wisata tidak ditemukan.');
        }

        $destinasi = $hasilDetail['data'];

        // Periksa apakah destinasi ini sudah disimpan di daftar favorit pengguna yang login
        $sudahDifavorit  = false;
        $idPenggunaLogin = (int) session('id_pengguna');
        $tokenApi        = (string) session('token_api');

        if ($idPenggunaLogin > 0 && $tokenApi !== '') {
            $hasilFavorit = $layananApi->kirimGet('/favorit/' . $idPenggunaLogin, [], $tokenApi);
            if ($hasilFavorit['terhubung'] && $hasilFavorit['berhasil'] && is_array($hasilFavorit['data'])) {
                $idDestinasiSaatIni = (int) ($destinasi['id'] ?? 0);
                foreach ($hasilFavorit['data'] as $itemFavorit) {
                    $idFavoritDestinasi = (int) ($itemFavorit['id_destinasi'] ?? $itemFavorit['id'] ?? 0);
                    if ($idFavoritDestinasi > 0 && $idFavoritDestinasi === $idDestinasiSaatIni) {
                        $sudahDifavorit = true;
                        break;
                    }
                }
            }
        }

        return view('pages/destinasi/detail', [
            'judulHalaman'     => ($destinasi['nama'] ?? 'Detail Destinasi') . ' — JejakSumut',
            'menuAktif'        => 'katalog',
            'terhubungBackend' => true,
            'pesanErrorApi'    => null,
            'destinasi'        => $destinasi,
            'sudahDifavorit'   => $sudahDifavorit,
        ]);
    }
}
