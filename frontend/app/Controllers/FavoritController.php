<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * FavoritController — Menangani pengelolaan daftar destinasi favorit pengguna (Member)
 * pada aplikasi Frontend Client JejakSumut.
 *
 * Seluruh data favorit diambil dan disimpan melalui Backend REST API (/api/v1/favorit/*).
 *
 * Anggota 3 — Autentikasi, Sesi Pengguna & Fitur Favorit
 */
class FavoritController extends BaseController
{
    private LayananApiBackend $layananApiBackend;

    public function __construct()
    {
        $this->layananApiBackend = new LayananApiBackend();
    }

    /**
     * GET /favorit-saya
     * Menampilkan daftar destinasi favorit milik pengguna yang sedang login.
     */
    public function index(): string|RedirectResponse
    {
        $idPengguna = (int) session()->get('id_pengguna');
        if ($idPengguna <= 0) {
            return redirect()->to('/masuk')->with('error', 'Silakan masuk terlebih dahulu untuk melihat favorit.');
        }

        $hasil = $this->layananApiBackend->kirimGet('/favorit/' . $idPengguna, [], (string) session()->get('token_api'));

        $daftarFavorit = [];
        if ($hasil['terhubung'] && $hasil['berhasil'] && is_array($hasil['data'])) {
            $daftarFavorit = $hasil['data'];
        }

        $data = [
            'judulHalaman'  => 'Destinasi Favorit Saya — JejakSumut',
            'daftarFavorit' => $daftarFavorit,
        ];

        return view('pages/member/favorit', $data);
    }

    /**
     * POST /favorit/ubah
     * Mengubah (toggle tambah/hapus) status favorit suatu destinasi via REST API Backend.
     * Mendukung request form biasa (POST) maupun panggilan AJAX / Fetch JSON.
     */
    public function ubah(): ResponseInterface|RedirectResponse
    {
        $idPengguna = (int) session()->get('id_pengguna');
        if ($idPengguna <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Silakan masuk terlebih dahulu.',
                ]);
            }
            return redirect()->to('/masuk')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        // Ambil id_destinasi dari POST form atau JSON body jika request AJAX
        $idDestinasi = $this->request->getPost('id_destinasi');
        if (! $idDestinasi) {
            $dataJson = $this->request->getJSON(true);
            $idDestinasi = $dataJson['id_destinasi'] ?? null;
        }
        $idDestinasi = (int) $idDestinasi;

        if ($idDestinasi <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'ID destinasi tidak valid.',
                ]);
            }
            return redirect()->back()->with('error', 'ID destinasi tidak valid.');
        }

        // Kirim request toggle favorit ke Backend REST API dengan token autentikasi
        $tokenApi = (string) session()->get('token_api');
        $hasil = $this->layananApiBackend->kirimPost('/favorit/ubah', [
            'id_pengguna'  => $idPengguna,
            'id_destinasi' => $idDestinasi,
        ], $tokenApi);

        if (! $hasil['terhubung']) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(503)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Tidak dapat terhubung ke server Backend API.',
                ]);
            }
            return redirect()->back()->with('error', 'Tidak dapat terhubung ke server Backend API.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($hasil['status_kode'])->setJSON($hasil['isi']);
        }

        $statusBerhasil = $hasil['berhasil'] ?? false;
        if (isset($hasil['isi']['status'])) {
            $statusBerhasil = $hasil['isi']['status'] === 'berhasil';
        }

        $pesan = $hasil['pesan'] ?? ($statusBerhasil ? 'Status favorit berhasil diperbarui.' : 'Gagal memperbarui status favorit.');
        return redirect()->back()->with($statusBerhasil ? 'success' : 'error', $pesan);
    }
}
