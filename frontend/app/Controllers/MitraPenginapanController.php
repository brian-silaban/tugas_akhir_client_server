<?php

namespace App\Controllers;

use App\Libraries\LayananApiMitra;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller proxy sisi-server untuk endpoint ulasan REST API Penginapan (Singgah Medan).
 * Menjaga kebijakan CSP connect-src 'self' sekaligus memvalidasi autentikasi & kepemilikan ulasan.
 */
class MitraPenginapanController extends BaseController
{
    /**
     * GET /mitra/penginapan/ulasan?hotel_id=7
     * Mengambil daftar ulasan terbaru dari Singgah Medan untuk satu hotel.
     */
    public function daftarUlasan(): ResponseInterface
    {
        $idHotel = (int) $this->request->getGet('hotel_id');
        if ($idHotel <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Parameter hotel_id wajib berupa angka positif.',
                'data'   => [],
            ]);
        }

        $layananMitra       = new LayananApiMitra();
        $hasilUlasan        = $layananMitra->ambilUlasanPenginapan($idHotel);
        $daftarUlasanSesi   = session('ulasan_hotel_dibuat');
        $daftarIdMilikSesi  = is_array($daftarUlasanSesi) ? array_values(array_map('intval', $daftarUlasanSesi)) : [];

        if (! $hasilUlasan['berhasil']) {
            $kodeHttp = $hasilUlasan['status_kode'] >= 400 ? (int) $hasilUlasan['status_kode'] : ResponseInterface::HTTP_BAD_GATEWAY;

            return $this->response->setStatusCode($kodeHttp)->setJSON([
                'status'                => 'gagal',
                'pesan'                 => $hasilUlasan['pesan_error'] ?? 'Gagal memuat ulasan hotel dari mitra.',
                'data'                  => [],
                'daftar_id_milik_sesi'  => $daftarIdMilikSesi,
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status'               => 'berhasil',
            'waktu_respon_ms'      => $hasilUlasan['waktu_respon_ms'],
            'total'                => $hasilUlasan['total'] ?? count($hasilUlasan['data']),
            'data'                 => $hasilUlasan['data'],
            'daftar_id_milik_sesi' => $daftarIdMilikSesi,
        ]);
    }

    /**
     * POST /mitra/penginapan/ulasan
     * Mengirim ulasan hotel baru ke server Singgah Medan untuk pengguna yang sudah masuk.
     */
    public function kirimUlasan(): ResponseInterface
    {
        if (empty(session('id_pengguna'))) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'     => 'gagal',
                'pesan'      => 'Silakan masuk dengan akun JejakSumut terlebih dahulu untuk menulis ulasan.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $dataJson = $this->request->getJSON(true);
        if (! is_array($dataJson)) {
            $dataJson = [];
        }

        $idHotel = (int) ($dataJson['hotel_id'] ?? $this->request->getPost('hotel_id'));
        $rating  = (int) ($dataJson['rating'] ?? $this->request->getPost('rating'));
        $judul   = trim((string) ($dataJson['judul'] ?? $this->request->getPost('judul')));
        $isi     = trim((string) ($dataJson['isi'] ?? $this->request->getPost('isi')));

        if ($idHotel <= 0 || $rating < 1 || $rating > 5) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'     => 'gagal',
                'pesan'      => 'ID hotel dan rating bintang (1–5) wajib diisi dengan benar.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        if (mb_strlen($judul) < 3 || mb_strlen($judul) > 120) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'     => 'gagal',
                'pesan'      => 'Judul ulasan harus berisi antara 3 hingga 120 karakter.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        if (mb_strlen($isi) < 5 || mb_strlen($isi) > 1000) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'     => 'gagal',
                'pesan'      => 'Isi ulasan harus berisi antara 5 hingga 1000 karakter.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $layananMitra = new LayananApiMitra();
        $hasilKirim   = $layananMitra->kirimUlasanPenginapan($idHotel, $rating, $judul, $isi);

        if (! $hasilKirim['berhasil']) {
            $kodeHttp = $hasilKirim['status_kode'] >= 400 ? (int) $hasilKirim['status_kode'] : ResponseInterface::HTTP_BAD_GATEWAY;

            return $this->response->setStatusCode($kodeHttp)->setJSON([
                'status'     => 'gagal',
                'pesan'      => $hasilKirim['pesan_error'] ?? 'Gagal mengirim ulasan hotel ke layanan mitra.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        // Simpan ID ulasan yang dibuat di sesi agar tombol hapus hanya muncul untuk ulasan pengguna ini
        $idUlasanBaru = (int) ($hasilKirim['data']['id'] ?? 0);
        if ($idUlasanBaru > 0) {
            $daftarLama = session('ulasan_hotel_dibuat');
            $daftarBaru = is_array($daftarLama) ? array_map('intval', $daftarLama) : [];
            if (! in_array($idUlasanBaru, $daftarBaru, true)) {
                $daftarBaru[] = $idUlasanBaru;
            }
            session()->set('ulasan_hotel_dibuat', $daftarBaru);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'          => 'berhasil',
            'pesan'           => 'Ulasan berhasil dikirim ke layanan penginapan.',
            'waktu_respon_ms' => $hasilKirim['waktu_respon_ms'],
            'data'            => $hasilKirim['data'],
            'csrf_token'      => csrf_hash(),
        ]);
    }

    /**
     * POST /mitra/penginapan/ulasan/{id}/hapus
     * Menghapus ulasan hotel yang dibuat oleh pengguna pada sesi aktif.
     */
    public function hapusUlasan(int $idUlasan): ResponseInterface
    {
        if (empty(session('id_pengguna'))) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)->setJSON([
                'status'     => 'gagal',
                'pesan'      => 'Silakan masuk dengan akun JejakSumut terlebih dahulu.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $daftarUlasanSesi = session('ulasan_hotel_dibuat');
        $daftarMilikSesi  = is_array($daftarUlasanSesi) ? array_map('intval', $daftarUlasanSesi) : [];

        if ($idUlasan <= 0 || ! in_array($idUlasan, $daftarMilikSesi, true)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON([
                'status'     => 'gagal',
                'pesan'      => 'Anda hanya dapat menghapus ulasan yang baru Anda kirim pada sesi ini.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $layananMitra = new LayananApiMitra();
        $hasilHapus   = $layananMitra->hapusUlasanPenginapan($idUlasan);

        if (! $hasilHapus['berhasil']) {
            $kodeHttp = $hasilHapus['status_kode'] >= 400 ? (int) $hasilHapus['status_kode'] : ResponseInterface::HTTP_BAD_GATEWAY;

            return $this->response->setStatusCode($kodeHttp)->setJSON([
                'status'     => 'gagal',
                'pesan'      => $hasilHapus['pesan_error'] ?? 'Gagal menghapus ulasan di server mitra.',
                'csrf_token' => csrf_hash(),
            ]);
        }

        $daftarTersisa = array_values(array_filter(
            $daftarMilikSesi,
            static fn (int $idItem): bool => $idItem !== $idUlasan
        ));
        session()->set('ulasan_hotel_dibuat', $daftarTersisa);

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)->setJSON([
            'status'          => 'berhasil',
            'pesan'           => 'Ulasan berhasil dihapus.',
            'waktu_respon_ms' => $hasilHapus['waktu_respon_ms'],
            'csrf_token'      => csrf_hash(),
        ]);
    }
}
