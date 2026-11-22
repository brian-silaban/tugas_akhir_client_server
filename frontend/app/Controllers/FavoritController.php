<?php

namespace App\Controllers;

use App\Models\FavoritModel;

/**
 * FavoritController — Menangani pengelolaan daftar destinasi favorit pengguna (Member).
 */
class FavoritController extends BaseController
{
    /**
     * Menampilkan daftar destinasi favorit milik pengguna yang sedang login.
     */
    public function index()
    {
        $idPengguna = (int) session()->get('id_pengguna');

        $modelFavorit   = new FavoritModel();
        $daftarFavorit = $modelFavorit->ambilFavoritPengguna($idPengguna);

        $data = [
            'judulHalaman'  => 'Destinasi Favorit Saya — IndWalk',
            'daftarFavorit' => $daftarFavorit,
        ];

        return view('pages/member/favorit', $data);
    }

    /**
     * Mengubah (toggle tambah/hapus) status favorit suatu destinasi.
     * Mendukung request form biasa (POST) maupun panggilan AJAX / Fetch JSON.
     */
    public function ubah()
    {
        $idPengguna = (int) session()->get('id_pengguna');

        // Ambil id_destinasi dari POST form atau JSON body jika request AJAX
        $idDestinasi = $this->request->getPost('id_destinasi');

        if (! $idDestinasi) {
            $dataJson = $this->request->getJSON(true);
            $idDestinasi = $dataJson['id_destinasi'] ?? null;
        }

        $idDestinasi = (int) $idDestinasi;

        // Validasi keberadaan id_destinasi
        if ($idDestinasi <= 0) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'ID destinasi tidak valid.',
                ]);
            }

            return redirect()->back()->with('error', 'ID destinasi tidak valid.');
        }

        $modelFavorit = new FavoritModel();
        $hasil = $modelFavorit->ubahFavorit($idPengguna, $idDestinasi);

        // Jika request datang dari AJAX / Fetch
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($hasil);
        }

        // Jika request dari submit formulir biasa
        return redirect()->back()->with('success', $hasil['pesan']);
    }
}
