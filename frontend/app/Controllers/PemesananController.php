<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller halaman web untuk modul Pemesanan Tiket JejakSumut.
 * Semua data diambil dari Backend REST API (port 8080) — TANPA query SQL langsung.
 *
 * Anggota 4 — Frontend: Tiket Saya & E-Ticket Digital
 */
class PemesananController extends BaseController
{
    // URL Backend REST API (dari file .env frontend)
    private string $urlApiBackend;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        // Ambil URL backend dari konfigurasi .env
        $this->urlApiBackend = env('INDWALK_BACKEND_API_URL', 'http://localhost:8080/api/v1');
    }

    // =========================================================================
    // GET /pemesanan
    // Halaman "Tiket Saya" — daftar riwayat semua tiket milik pengguna yang login.
    // =========================================================================
    public function index(): string|RedirectResponse
    {
        // Pastikan pengguna sudah login (sesi dari Anggota 3)
        $idPengguna = session('id_pengguna');
        if (empty($idPengguna)) {
            return redirect()->to('/masuk')->with('pesan_error', 'Silakan login terlebih dahulu untuk melihat tiket kamu.');
        }

        // Ambil daftar tiket dari Backend REST API
        $klienHttp    = \Config\Services::curlrequest();
        $daftarTiket  = [];
        $pesanError   = '';

        try {
            $responApi = $klienHttp->get(
                $this->urlApiBackend . '/pemesanan?id_pengguna=' . $idPengguna,
                ['timeout' => 5]
            );
            $hasilJson  = json_decode($responApi->getBody(), true);
            $daftarTiket = $hasilJson['data'] ?? [];
        } catch (\Throwable $e) {
            $pesanError = 'Gagal memuat data tiket. Silakan coba lagi.';
        }

        return view('pages/member/pemesanan', [
            'judul_halaman' => 'Tiket Saya',
            'daftar_tiket'  => $daftarTiket,
            'pesan_error'   => $pesanError,
        ]);
    }

    // =========================================================================
    // GET /pemesanan/etiket/{id}
    // Halaman E-Ticket digital untuk satu tiket spesifik.
    // =========================================================================
    public function tampilEtiket(int $id): string|RedirectResponse
    {
        $idPengguna = session('id_pengguna');
        if (empty($idPengguna)) {
            return redirect()->to('/masuk');
        }

        $klienHttp   = \Config\Services::curlrequest();
        $dataEtiket  = null;
        $pesanError  = '';

        try {
            $responApi  = $klienHttp->get(
                $this->urlApiBackend . '/pemesanan/' . $id,
                ['timeout' => 5]
            );
            $hasilJson  = json_decode($responApi->getBody(), true);
            $dataEtiket = $hasilJson['data'] ?? null;
        } catch (\Throwable $e) {
            $pesanError = 'Gagal memuat detail tiket.';
        }

        if ($dataEtiket === null) {
            return redirect()->to('/pemesanan')->with('pesan_error', 'Tiket tidak ditemukan.');
        }

        return view('pages/member/etiket', [
            'judul_halaman' => 'E-Ticket ' . ($dataEtiket['kode_pemesanan'] ?? ''),
            'data_etiket'   => $dataEtiket,
        ]);
    }

    // =========================================================================
    // POST /pemesanan/buat
    // Memproses form pemesanan tiket dan mengirim ke Backend REST API.
    // =========================================================================
    public function buat(): RedirectResponse
    {
        $idPengguna = session('id_pengguna');
        if (empty($idPengguna)) {
            return redirect()->to('/masuk');
        }

        // Kumpulkan data dari form
        $dataKirim = [
            'id_pengguna'       => $idPengguna,
            'id_destinasi'      => (int) $this->request->getPost('id_destinasi'),
            'tanggal_kunjungan' => $this->request->getPost('tanggal_kunjungan'),
            'jumlah_pengunjung' => (int) $this->request->getPost('jumlah_pengunjung'),
        ];

        $klienHttp = \Config\Services::curlrequest();

        try {
            // Kirim HTTP POST ke Backend REST API untuk memproses pesanan
            $responApi = $klienHttp->post(
                $this->urlApiBackend . '/pemesanan',
                [
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    'body'    => json_encode($dataKirim),
                    'timeout' => 5,
                ]
            );

            $hasilJson = json_decode($responApi->getBody(), true);

            if ($responApi->getStatusCode() === 201 && ! empty($hasilJson['data']['id'])) {
                // Pemesanan berhasil — arahkan ke halaman E-Ticket
                return redirect()
                    ->to('/pemesanan/etiket/' . $hasilJson['data']['id'])
                    ->with('pesan_sukses', 'Tiket berhasil dipesan! Kode tiket: ' . $hasilJson['data']['kode_pemesanan']);
            }

            // Pemesanan gagal — kembali ke halaman sebelumnya dengan pesan error dari API
            return redirect()->back()->with('pesan_error', $hasilJson['pesan'] ?? 'Pemesanan gagal, silakan coba lagi.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('pesan_error', 'Koneksi ke server bermasalah. Coba lagi nanti.');
        }
    }

    // =========================================================================
    // POST /pemesanan/batal/{id}
    // Memproses pembatalan tiket melalui Backend REST API.
    // =========================================================================
    public function batal(int $id): RedirectResponse
    {
        $idPengguna = session('id_pengguna');
        if (empty($idPengguna)) {
            return redirect()->to('/masuk');
        }

        $klienHttp = \Config\Services::curlrequest();

        try {
            $responApi = $klienHttp->patch(
                $this->urlApiBackend . '/pemesanan/' . $id . '/batal',
                ['headers' => ['Accept' => 'application/json'], 'timeout' => 5]
            );

            $hasilJson = json_decode($responApi->getBody(), true);

            if ($responApi->getStatusCode() === 200) {
                return redirect()->to('/pemesanan')->with('pesan_sukses', 'Tiket berhasil dibatalkan.');
            }

            return redirect()->back()->with('pesan_error', $hasilJson['pesan'] ?? 'Gagal membatalkan tiket.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('pesan_error', 'Koneksi ke server bermasalah. Coba lagi nanti.');
        }
    }
}
