<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller halaman web untuk modul Ulasan & Rating JejakSumut.
 * Semua data dikirim ke Backend REST API (port 8080) — TANPA query SQL langsung.
 *
 * Anggota 4 — Frontend: Komponen Ulasan & Rating Bintang
 */
class UlasanController extends BaseController
{
    private string $urlApiBackend;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->urlApiBackend = env('INDWALK_BACKEND_API_URL', 'http://localhost:8080/api/v1');
    }

    // =========================================================================
    // POST /ulasan/kirim
    // Memproses pengiriman ulasan baru dari form di halaman detail destinasi.
    // =========================================================================
    public function kirim(): RedirectResponse
    {
        $idPengguna = session('id_pengguna');

        // Pastikan pengguna sudah login sebelum bisa menulis ulasan
        if (empty($idPengguna)) {
            return redirect()->back()->with('pesan_error', 'Kamu harus login terlebih dahulu untuk menulis ulasan.');
        }

        // Kumpulkan data ulasan dari form
        $dataKirim = [
            'id_pengguna'  => $idPengguna,
            'id_destinasi' => (int) $this->request->getPost('id_destinasi'),
            'rating'       => (int) $this->request->getPost('rating'),
            'komentar'     => trim((string) $this->request->getPost('komentar')),
        ];

        $klienHttp   = \Config\Services::curlrequest();
        $slugKembali = $this->request->getPost('slug_destinasi') ?? '';

        try {
            // Kirim ulasan ke Backend REST API
            $responApi = $klienHttp->post(
                $this->urlApiBackend . '/ulasan',
                [
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    'body'    => json_encode($dataKirim),
                    'timeout' => 5,
                ]
            );

            $hasilJson = json_decode($responApi->getBody(), true);

            if ($responApi->getStatusCode() === 201) {
                // Ulasan berhasil dikirim — kembali ke halaman detail destinasi
                return redirect()
                    ->to('/destinasi/' . $slugKembali)
                    ->with('pesan_sukses', 'Ulasan berhasil dikirimkan! Terima kasih sudah berbagi pengalaman wisatamu.');
            }

            return redirect()->back()->with('pesan_error', $hasilJson['pesan'] ?? 'Gagal mengirim ulasan. Silakan coba lagi.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('pesan_error', 'Koneksi ke server bermasalah. Coba lagi nanti.');
        }
    }
}
