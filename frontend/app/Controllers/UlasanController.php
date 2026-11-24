<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller halaman web modul Ulasan & Rating JejakSumut.
 * Ulasan dikirim lewat LayananApiBackend ke POST /api/v1/ulasan beserta token sesi.
 *
 * Anggota 4 — Frontend: Komponen Ulasan & Rating Bintang
 */
class UlasanController extends BaseController
{
    // =========================================================================
    // POST /ulasan/kirim
    // Meneruskan form ulasan dari halaman detail destinasi ke REST API backend.
    // =========================================================================
    public function kirim(): RedirectResponse
    {
        $idPengguna = (int) session('id_pengguna');
        if ($idPengguna <= 0) {
            return redirect()->back()->with('pesan_error', 'Kamu harus masuk terlebih dahulu untuk menulis ulasan.');
        }

        $dataUlasan = [
            'id_pengguna'  => $idPengguna,
            'id_destinasi' => (int) $this->request->getPost('id_destinasi'),
            'rating'       => (int) $this->request->getPost('rating'),
            'komentar'     => trim((string) $this->request->getPost('komentar')),
        ];

        $layananApi = new LayananApiBackend();
        $hasilApi   = $layananApi->kirimUlasan($dataUlasan, (string) session('token_api'));

        if ($hasilApi['berhasil']) {
            $slugDestinasi = (string) $this->request->getPost('slug_destinasi');

            return redirect()
                ->to('/destinasi/' . rawurlencode($slugDestinasi) . '#ulasan')
                ->with('pesan_sukses', 'Ulasan tersimpan. Terima kasih sudah berbagi pengalaman.');
        }

        $pesanError = $hasilApi['pesan_error'] ?? 'Gagal mengirim ulasan. Silakan coba lagi.';

        return redirect()->back()->withInput()->with('pesan_error', $pesanError);
    }
}
