<?php

namespace App\Controllers\Api;

use App\Models\UlasanModel;
use App\Models\DestinasiModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * API Controller untuk modul Ulasan & Rating Bintang JejakSumut.
 * Menyediakan endpoint pengiriman dan pengambilan ulasan wisatawan.
 *
 * Anggota 4 — Sistem Ulasan & Rating (1–5 Bintang)
 */
class UlasanApiController extends ResourceController
{
    protected $modelName = 'App\Models\UlasanModel';
    protected $format    = 'json';

    // =========================================================================
    // GET /api/v1/ulasan?id_destinasi=X
    // Mengambil daftar semua ulasan untuk satu destinasi wisata.
    // =========================================================================
    public function index(): ResponseInterface
    {
        $idDestinasi = $this->request->getGet('id_destinasi');

        if (empty($idDestinasi) || ! is_numeric($idDestinasi)) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Parameter id_destinasi wajib disertakan dan berupa angka.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ulasanModel = new UlasanModel();
        $db          = \Config\Database::connect();

        // Join tabel ulasan dengan tabel pengguna untuk mengambil nama pengulas
        $daftarUlasan = $db->table('ulasan')
            ->select('ulasan.id, ulasan.rating, ulasan.komentar, ulasan.dibuat_pada, pengguna.nama AS nama_pengulas')
            ->join('pengguna', 'pengguna.id = ulasan.id_pengguna', 'left')
            ->where('ulasan.id_destinasi', (int) $idDestinasi)
            ->orderBy('ulasan.dibuat_pada', 'DESC')
            ->get()
            ->getResultArray();

        // Hitung rata-rata rating untuk destinasi ini
        $rataRating    = 0;
        $jumlahUlasan  = count($daftarUlasan);
        if ($jumlahUlasan > 0) {
            $totalBintang = array_sum(array_column($daftarUlasan, 'rating'));
            $rataRating   = round($totalBintang / $jumlahUlasan, 1);
        }

        return $this->respond([
            'status'        => 'berhasil',
            'jumlah_ulasan' => $jumlahUlasan,
            'rata_rating'   => $rataRating,
            'data'          => $daftarUlasan,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // POST /api/v1/ulasan
    // Menyimpan ulasan dan rating bintang baru dari pengguna yang sudah login.
    // =========================================================================
    public function create(): ResponseInterface
    {
        $inputJson = $this->request->getJSON(true);

        // Dukung input JSON (fetch API) maupun form POST biasa
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        // --- Validasi field wajib ---
        $fieldWajib = ['id_pengguna', 'id_destinasi', 'rating', 'komentar'];
        foreach ($fieldWajib as $namaField) {
            if (! isset($inputData[$namaField]) || (string) $inputData[$namaField] === '') {
                return $this->respond([
                    'status' => 'gagal',
                    'pesan'  => 'Field ' . $namaField . ' wajib diisi.',
                    'data'   => null,
                ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $idPengguna  = (int) $inputData['id_pengguna'];
        $idDestinasi = (int) $inputData['id_destinasi'];
        $nilaiRating = (int) $inputData['rating'];
        $isiKomentar = trim((string) $inputData['komentar']);

        // Validasi nilai bintang harus antara 1 dan 5
        if ($nilaiRating < 1 || $nilaiRating > 5) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Rating harus berupa angka antara 1 sampai 5.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validasi komentar minimal 10 karakter agar tidak spam
        if (mb_strlen($isiKomentar) < 10) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Komentar ulasan minimal 10 karakter.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Cek: satu pengguna hanya boleh mengulas satu destinasi sekali
        $ulasanModel      = new UlasanModel();
        $ulasanYangAdaSudah = $ulasanModel
            ->where('id_pengguna', $idPengguna)
            ->where('id_destinasi', $idDestinasi)
            ->first();

        if ($ulasanYangAdaSudah !== null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Kamu sudah pernah mengulas destinasi wisata ini sebelumnya.',
                'data'   => null,
            ], ResponseInterface::HTTP_CONFLICT);
        }

        // Simpan ulasan baru ke database
        $idUlasanBaru = $ulasanModel->insert([
            'id_pengguna'  => $idPengguna,
            'id_destinasi' => $idDestinasi,
            'rating'       => $nilaiRating,
            'komentar'     => $isiKomentar,
        ]);

        if (! $idUlasanBaru) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Gagal menyimpan ulasan. Silakan coba lagi.',
                'data'   => null,
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Perbarui rata-rata rating dan jumlah ulasan di tabel destinasi (nilai plus rubrik!)
        $this->perbaruiRataRatingDestinasi($idDestinasi, $ulasanModel);

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Ulasan berhasil dikirimkan. Terima kasih sudah berbagi pengalaman!',
            'data'   => ['id_ulasan_baru' => $idUlasanBaru],
        ], ResponseInterface::HTTP_CREATED);
    }

    // =========================================================================
    // Helper: Memperbarui kolom rata_rating dan jumlah_ulasan di tabel destinasi
    // setiap kali ada ulasan baru masuk (dipanggil setelah insert berhasil).
    // =========================================================================
    private function perbaruiRataRatingDestinasi(int $idDestinasi, UlasanModel $ulasanModel): void
    {
        // Hitung ulang rata-rata dari seluruh ulasan destinasi ini
        $semuaUlasan   = $ulasanModel->where('id_destinasi', $idDestinasi)->findAll();
        $jumlahUlasan  = count($semuaUlasan);

        if ($jumlahUlasan === 0) {
            return;
        }

        $totalBintang = array_sum(array_column($semuaUlasan, 'rating'));
        $rataRatingBaru = round($totalBintang / $jumlahUlasan, 2);

        // Update tabel destinasi dengan nilai terbaru
        $destinasiModel = new DestinasiModel();
        $destinasiModel->update($idDestinasi, [
            'rata_rating'   => $rataRatingBaru,
            'jumlah_ulasan' => $jumlahUlasan,
        ]);
    }
}
