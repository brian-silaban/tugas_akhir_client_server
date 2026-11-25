<?php

namespace App\Controllers\Api;

use App\Models\UlasanModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * API Controller modul Ulasan & Rating Bintang JejakSumut.
 * Penyimpanan ulasan berjalan di dalam transaksi ACID; kolom rata_rating dan
 * jumlah_ulasan di tabel destinasi diperbarui oleh Trigger trg_sinkronkan_rating_destinasi.
 *
 * Anggota 4 — Sistem Ulasan & Rating (1–5 Bintang)
 */
class UlasanApiController extends ResourceController
{
    protected $format = 'json';

    // =========================================================================
    // GET /api/v1/ulasan?id_destinasi=X
    // Daftar ulasan untuk satu destinasi beserta nama pengulas.
    // =========================================================================
    public function index(): ResponseInterface
    {
        $idDestinasi = $this->request->getGet('id_destinasi');

        if (empty($idDestinasi) || ! is_numeric($idDestinasi)) {
            return $this->kirimGagal(
                'Parameter id_destinasi wajib disertakan dan berupa angka.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $ulasanModel  = new UlasanModel();
        $daftarUlasan = $ulasanModel->ambilDaftarUlasanDestinasi((int) $idDestinasi);

        // Hitung rata-rata rating dari ulasan yang tampil
        $jumlahUlasan = count($daftarUlasan);
        $rataRating   = 0;
        if ($jumlahUlasan > 0) {
            $totalBintang = array_sum(array_column($daftarUlasan, 'rating'));
            $rataRating   = round($totalBintang / $jumlahUlasan, 1);
        }

        return $this->respond([
            'status'        => 'berhasil',
            'pesan'         => 'Daftar ulasan berhasil diambil.',
            'jumlah_ulasan' => $jumlahUlasan,
            'rata_rating'   => $rataRating,
            'data'          => $daftarUlasan,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // POST /api/v1/ulasan
    // Menyimpan ulasan baru lewat transaksi ACID di UlasanModel.
    // =========================================================================
    public function create(): ResponseInterface
    {
        $inputJson = $this->request->getJSON(true);

        // Dukung input JSON (dari LayananApiBackend) maupun form POST biasa
        if (! empty($inputJson)) {
            $inputData = $inputJson;
        } else {
            $inputData = $this->request->getPost();
        }

        // Validasi field wajib
        $fieldWajib = ['id_pengguna', 'id_destinasi', 'rating', 'komentar'];
        foreach ($fieldWajib as $namaField) {
            if (! isset($inputData[$namaField]) || (string) $inputData[$namaField] === '') {
                return $this->kirimGagal(
                    'Field ' . $namaField . ' wajib diisi.',
                    ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $idPengguna  = (int) $inputData['id_pengguna'];
        $idDestinasi = (int) $inputData['id_destinasi'];
        $nilaiRating = (int) $inputData['rating'];
        $isiKomentar = trim((string) $inputData['komentar']);

        // Pastikan pengguna hanya menulis ulasan atas nama akunnya sendiri (kecuali admin)
        $dataToken = AutentikasiApiController::verifikasiHeaderOtorisasi(
            $this->request->getHeaderLine('Authorization')
        );
        if (
            $dataToken !== null
            && ($dataToken['peran'] ?? '') !== 'admin'
            && (int) $dataToken['id_pengguna'] !== $idPengguna
        ) {
            return $this->kirimGagal(
                'Akses ditolak. Anda hanya dapat mengirim ulasan menggunakan akun sendiri.',
                ResponseInterface::HTTP_FORBIDDEN
            );
        }

        if ($nilaiRating < 1 || $nilaiRating > 5) {
            return $this->kirimGagal(
                'Rating harus berupa angka antara 1 sampai 5.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Komentar minimal 10 karakter agar tidak berisi spam satu kata
        if (mb_strlen($isiKomentar) < 10) {
            return $this->kirimGagal(
                'Komentar ulasan minimal 10 karakter.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $ulasanModel = new UlasanModel();

        // Satu pengguna hanya boleh mengulas satu destinasi sekali
        $ulasanLama = $ulasanModel
            ->where('id_pengguna', $idPengguna)
            ->where('id_destinasi', $idDestinasi)
            ->first();

        if ($ulasanLama !== null) {
            return $this->kirimGagal(
                'Kamu sudah pernah mengulas destinasi wisata ini.',
                ResponseInterface::HTTP_CONFLICT
            );
        }

        try {
            // Transaksi ACID: insert ulasan → Trigger memperbarui rating destinasi → commit/rollback
            $hasilSimpan = $ulasanModel->simpanUlasanDenganTransaksi(
                $idPengguna,
                $idDestinasi,
                $nilaiRating,
                $isiKomentar
            );
        } catch (InvalidArgumentException $kesalahan) {
            return $this->kirimGagal($kesalahan->getMessage(), ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        } catch (RuntimeException $kesalahan) {
            if (str_contains($kesalahan->getMessage(), 'tidak ditemukan')) {
                return $this->kirimGagal($kesalahan->getMessage(), ResponseInterface::HTTP_NOT_FOUND);
            }

            return $this->kirimGagal($kesalahan->getMessage(), ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Throwable $kesalahan) {
            log_message('error', 'Gagal menyimpan ulasan: ' . $kesalahan->getMessage());

            return $this->kirimGagal(
                'Ulasan gagal disimpan oleh database. Silakan coba lagi.',
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Ulasan tersimpan. Terima kasih sudah berbagi pengalaman.',
            'data'   => $hasilSimpan,
        ], ResponseInterface::HTTP_CREATED);
    }

    /**
     * Menyusun respons JSON gagal dengan format seragam.
     */
    private function kirimGagal(string $pesan, int $kodeStatus): ResponseInterface
    {
        return $this->respond([
            'status' => 'gagal',
            'pesan'  => $pesan,
            'data'   => null,
        ], $kodeStatus);
    }
}
