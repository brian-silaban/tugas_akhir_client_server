<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller halaman web modul Pemesanan Tiket JejakSumut.
 * Seluruh data diambil lewat LayananApiBackend (REST API backend) dengan token Bearer
 * dari sesi login sehingga frontend tidak mengakses PostgreSQL secara langsung.
 */
class PemesananController extends BaseController
{
    // GET /pemesanan: daftar riwayat tiket milik pengguna yang sedang login.
    public function index(): string|RedirectResponse
    {
        $idPengguna = (int) session('id_pengguna');
        if ($idPengguna <= 0) {
            return redirect()->to('/masuk')->with('pesan_error', 'Silakan masuk terlebih dahulu untuk melihat tiket kamu.');
        }

        $layananApi = new LayananApiBackend();
        $hasilApi   = $layananApi->ambilDaftarPemesanan($idPengguna, (string) session('token_api'));

        $daftarTiket = [];
        $pesanError  = '';
        if ($hasilApi['berhasil'] && is_array($hasilApi['data'])) {
            $daftarTiket = $hasilApi['data'];
        } else {
            $pesanError = $hasilApi['pesan_error'] ?? 'Gagal memuat data tiket. Silakan coba lagi.';
        }

        return view('pages/member/pemesanan', [
            'judulHalaman'  => 'Tiket Saya | JejakSumut',
            'judul_halaman' => 'Tiket Saya',
            'menuAktif'     => 'pemesanan',
            'daftar_tiket'  => $daftarTiket,
            'pesan_error'   => $pesanError,
        ]);
    }

    // GET /pemesanan/etiket/{id}: halaman E-Ticket untuk satu transaksi.
    public function tampilEtiket(int $id): string|RedirectResponse
    {
        $idPengguna = (int) session('id_pengguna');
        if ($idPengguna <= 0) {
            return redirect()->to('/masuk');
        }

        $layananApi = new LayananApiBackend();
        $hasilApi   = $layananApi->ambilDetailPemesanan($id, (string) session('token_api'));

        if (! $hasilApi['berhasil'] || ! is_array($hasilApi['data'])) {
            $pesanError = $hasilApi['pesan_error'] ?? 'Tiket tidak ditemukan.';

            return redirect()->to('/pemesanan')->with('pesan_error', $pesanError);
        }

        $dataEtiket = $hasilApi['data'];

        // Cegah pengguna membuka E-Ticket milik akun lain.
        if ((int) ($dataEtiket['id_pengguna'] ?? 0) !== $idPengguna) {
            return redirect()->to('/pemesanan')->with('pesan_error', 'Tiket ini bukan milik akun kamu.');
        }

        $kodeTiket = (string) ($dataEtiket['kode_pemesanan'] ?? '');

        return view('pages/member/etiket', [
            'judulHalaman'  => 'E-Ticket ' . $kodeTiket . ' | JejakSumut',
            'judul_halaman' => 'E-Ticket ' . $kodeTiket,
            'menuAktif'     => 'pemesanan',
            'data_etiket'   => $dataEtiket,
        ]);
    }

    // POST /pemesanan/buat: meneruskan form pemesanan ke POST /api/v1/pemesanan.
    public function buat(): RedirectResponse
    {
        $idPengguna = (int) session('id_pengguna');
        if ($idPengguna <= 0) {
            return redirect()->to('/masuk')->with('pesan_error', 'Silakan masuk terlebih dahulu untuk memesan tiket.');
        }

        $dataPemesanan = [
            'id_pengguna'       => $idPengguna,
            'id_destinasi'      => (int) $this->request->getPost('id_destinasi'),
            'tanggal_kunjungan' => (string) $this->request->getPost('tanggal_kunjungan'),
            'jumlah_pengunjung' => (int) $this->request->getPost('jumlah_pengunjung'),
        ];

        $layananApi = new LayananApiBackend();
        $hasilApi   = $layananApi->buatPemesanan($dataPemesanan, (string) session('token_api'));

        // Arahkan langsung ke E-Ticket setelah backend mengembalikan status 201 Created.
        $idTiketBaru = (int) ($hasilApi['data']['id'] ?? 0);
        if ($hasilApi['berhasil'] && $idTiketBaru > 0) {
            return redirect()
                ->to('/pemesanan/etiket/' . $idTiketBaru)
                ->with('pesan_sukses', 'Tiket berhasil dipesan.');
        }

        $pesanError = $hasilApi['pesan_error'] ?? 'Pemesanan gagal, silakan coba lagi.';

        return redirect()->back()->withInput()->with('pesan_error', $pesanError);
    }

    // POST /pemesanan/batal/{id}: meneruskan pembatalan ke PATCH /api/v1/pemesanan/{id}/batal.
    public function batal(int $id): RedirectResponse
    {
        $idPengguna = (int) session('id_pengguna');
        if ($idPengguna <= 0) {
            return redirect()->to('/masuk');
        }

        $layananApi = new LayananApiBackend();
        $hasilApi   = $layananApi->batalkanPemesanan($id, (string) session('token_api'));

        if ($hasilApi['berhasil']) {
            return redirect()->to('/pemesanan')->with('pesan_sukses', 'Tiket berhasil dibatalkan.');
        }

        $pesanError = $hasilApi['pesan_error'] ?? 'Gagal membatalkan tiket.';

        return redirect()->back()->with('pesan_error', $pesanError);
    }
}
