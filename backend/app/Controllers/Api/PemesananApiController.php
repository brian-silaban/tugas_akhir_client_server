<?php

namespace App\Controllers\Api;

use App\Models\DestinasiModel;
use App\Models\PemesananModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * API Controller modul Pemesanan Tiket Wisata JejakSumut.
 * Seluruh operasi baca memakai SQL View v_ringkasan_pemesanan, operasi tulis memakai
 * transaksi ACID + Stored Function fn_buat_kode_pemesanan, dan pembatalan memakai
 * Stored Procedure sp_verifikasi_pemesanan (row lock FOR UPDATE).
 *
 * Anggota 4 — Transaksi Pemesanan Tiket & E-Ticket
 */
class PemesananApiController extends ResourceController
{
    protected $format = 'json';

    // =========================================================================
    // GET /api/v1/pemesanan?id_pengguna=X
    // Daftar riwayat tiket milik satu pengguna (dibaca dari SQL View).
    // =========================================================================
    public function index(): ResponseInterface
    {
        $idPengguna = $this->request->getGet('id_pengguna');

        if (empty($idPengguna) || ! is_numeric($idPengguna)) {
            return $this->kirimGagal(
                'Parameter id_pengguna wajib disertakan dan berupa angka.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $tolakAkses = $this->cekKepemilikanPengguna(
            (int) $idPengguna,
            'Akses ditolak. Anda hanya dapat melihat riwayat tiket milik akun sendiri.'
        );
        if ($tolakAkses !== null) {
            return $tolakAkses;
        }

        $pemesananModel = new PemesananModel();

        // Satu query ke SQL View v_ringkasan_pemesanan (tanpa loop query N+1)
        $daftarPemesanan = $pemesananModel->ambilRingkasanPemesanan((int) $idPengguna);

        // Ambil foto semua destinasi terkait dalam satu query WHERE IN
        $daftarIdDestinasi = array_unique(array_column($daftarPemesanan, 'id_destinasi'));
        $petaFoto          = $this->ambilPetaFotoDestinasi($daftarIdDestinasi);

        foreach ($daftarPemesanan as $indeks => $tiket) {
            $idDestinasi = (int) $tiket['id_destinasi'];

            $daftarPemesanan[$indeks]['foto_destinasi']        = $petaFoto[$idDestinasi] ?? '';
            $daftarPemesanan[$indeks]['total_harga_terformat'] = $this->formatRupiah((int) $tiket['total_harga']);
        }

        return $this->respond([
            'status'      => 'berhasil',
            'pesan'       => 'Riwayat pemesanan berhasil diambil.',
            'jumlah_data' => count($daftarPemesanan),
            'data'        => $daftarPemesanan,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // GET /api/v1/pemesanan/{id}
    // Detail satu tiket untuk halaman E-Ticket (dibaca dari SQL View).
    // =========================================================================
    public function show($id = null): ResponseInterface
    {
        if (empty($id) || ! is_numeric($id)) {
            return $this->kirimGagal('ID pemesanan tidak valid.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $pemesananModel = new PemesananModel();
        $tiket          = $pemesananModel->ambilSatuRingkasan((int) $id);

        if ($tiket === null) {
            return $this->kirimGagal(
                'Tiket dengan ID ' . $id . ' tidak ditemukan.',
                ResponseInterface::HTTP_NOT_FOUND
            );
        }

        $tolakAkses = $this->cekKepemilikanPengguna(
            (int) $tiket['id_pengguna'],
            'Akses ditolak. Tiket ini bukan milik akun Anda.'
        );
        if ($tolakAkses !== null) {
            return $tolakAkses;
        }

        // Lengkapi data E-Ticket dengan alamat, foto, dan jam operasional destinasi
        $destinasiModel = new DestinasiModel();
        $dataDestinasi  = $destinasiModel
            ->select('foto_utama, alamat, jam_buka, jam_tutup')
            ->find((int) $tiket['id_destinasi']);

        $tiket['foto_destinasi']        = $dataDestinasi['foto_utama'] ?? '';
        $tiket['alamat_destinasi']      = $dataDestinasi['alamat'] ?? '';
        $tiket['jam_buka_destinasi']    = $dataDestinasi['jam_buka'] ?? null;
        $tiket['jam_tutup_destinasi']   = $dataDestinasi['jam_tutup'] ?? null;
        $tiket['total_harga_terformat'] = $this->formatRupiah((int) $tiket['total_harga']);

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Detail tiket berhasil diambil.',
            'data'   => $tiket,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // POST /api/v1/pemesanan
    // Membuat pesanan baru lewat transaksi ACID di PemesananModel.
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
        $fieldWajib = ['id_pengguna', 'id_destinasi', 'tanggal_kunjungan', 'jumlah_pengunjung'];
        foreach ($fieldWajib as $namaField) {
            if (empty($inputData[$namaField])) {
                return $this->kirimGagal(
                    'Field ' . $namaField . ' wajib diisi.',
                    ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $idPengguna       = (int) $inputData['id_pengguna'];
        $idDestinasi      = (int) $inputData['id_destinasi'];
        $tanggalKunjungan = (string) $inputData['tanggal_kunjungan'];
        $jumlahPengunjung = (int) $inputData['jumlah_pengunjung'];

        $tolakAkses = $this->cekKepemilikanPengguna(
            $idPengguna,
            'Akses ditolak. Anda hanya dapat memesan tiket untuk akun sendiri.'
        );
        if ($tolakAkses !== null) {
            return $tolakAkses;
        }

        // Tanggal harus berformat YYYY-MM-DD dan tidak boleh di masa lampau
        $tanggalValid = \DateTime::createFromFormat('Y-m-d', $tanggalKunjungan);
        if ($tanggalValid === false || $tanggalValid->format('Y-m-d') !== $tanggalKunjungan) {
            return $this->kirimGagal(
                'Format tanggal kunjungan harus YYYY-MM-DD.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (strtotime($tanggalKunjungan) < strtotime('today')) {
            return $this->kirimGagal(
                'Tanggal kunjungan tidak boleh di masa lampau.',
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $pemesananModel = new PemesananModel();

        try {
            // Transaksi ACID: FOR SHARE lock harga + fn_buat_kode_pemesanan + insert + commit/rollback
            $tiketBaru = $pemesananModel->buatPemesananDenganTransaksi(
                $idPengguna,
                $idDestinasi,
                $tanggalKunjungan,
                $jumlahPengunjung
            );
        } catch (InvalidArgumentException $kesalahan) {
            return $this->kirimGagal($kesalahan->getMessage(), ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        } catch (RuntimeException $kesalahan) {
            // Destinasi tidak ada → 404, kegagalan lain di dalam transaksi → 500
            if (str_contains($kesalahan->getMessage(), 'tidak ditemukan')) {
                return $this->kirimGagal($kesalahan->getMessage(), ResponseInterface::HTTP_NOT_FOUND);
            }

            return $this->kirimGagal($kesalahan->getMessage(), ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Throwable $kesalahan) {
            log_message('error', 'Gagal membuat pemesanan: ' . $kesalahan->getMessage());

            return $this->kirimGagal(
                'Pemesanan gagal diproses oleh database. Silakan coba lagi.',
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $tiketBaru['total_harga_terformat'] = $this->formatRupiah((int) $tiketBaru['total_harga']);

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Tiket berhasil dipesan dengan kode ' . $tiketBaru['kode_pemesanan'] . '.',
            'data'   => $tiketBaru,
        ], ResponseInterface::HTTP_CREATED);
    }

    // =========================================================================
    // PATCH /api/v1/pemesanan/{id}/batal
    // Membatalkan tiket lewat Stored Procedure sp_verifikasi_pemesanan.
    // =========================================================================
    public function batalkan($id = null): ResponseInterface
    {
        if (empty($id) || ! is_numeric($id)) {
            return $this->kirimGagal('ID pemesanan tidak valid.', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $pemesananModel = new PemesananModel();
        $tiket          = $pemesananModel->ambilSatuRingkasan((int) $id);

        if ($tiket === null) {
            return $this->kirimGagal('Tiket tidak ditemukan.', ResponseInterface::HTTP_NOT_FOUND);
        }

        $tolakAkses = $this->cekKepemilikanPengguna(
            (int) $tiket['id_pengguna'],
            'Kamu tidak berhak membatalkan tiket milik pengguna lain.'
        );
        if ($tolakAkses !== null) {
            return $tolakAkses;
        }

        // Jika klien mengirim id_pengguna, pastikan tiket memang milik pengguna tersebut
        $inputJson = $this->request->getJSON(true);
        if (! empty($inputJson)) {
            $inputData = $inputJson;
        } else {
            $inputData = $this->request->getRawInput();
        }

        $idPengirim = (int) ($inputData['id_pengguna'] ?? 0);
        if ($idPengirim > 0 && $idPengirim !== (int) $tiket['id_pengguna']) {
            return $this->kirimGagal(
                'Kamu tidak berhak membatalkan tiket milik pengguna lain.',
                ResponseInterface::HTTP_FORBIDDEN
            );
        }

        // Tiket hanya bisa dibatalkan selama statusnya masih 'menunggu'
        if ($tiket['status'] !== 'menunggu') {
            return $this->kirimGagal(
                'Tiket hanya bisa dibatalkan saat statusnya masih "menunggu".',
                ResponseInterface::HTTP_CONFLICT
            );
        }

        try {
            // Stored Procedure mengunci baris (FOR UPDATE) sebelum mengubah status
            $tiketTerbaru = $pemesananModel->ubahStatusDenganProsedur((int) $id, 'dibatalkan');
        } catch (Throwable $kesalahan) {
            log_message('error', 'Gagal membatalkan pemesanan: ' . $kesalahan->getMessage());

            return $this->kirimGagal(
                'Status tiket berubah di saat yang sama. Muat ulang halaman lalu coba lagi.',
                ResponseInterface::HTTP_CONFLICT
            );
        }

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Tiket dengan kode ' . $tiket['kode_pemesanan'] . ' berhasil dibatalkan.',
            'data'   => $tiketTerbaru,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // HELPER INTERNAL
    // =========================================================================

    /**
     * Mengambil foto utama beberapa destinasi sekaligus dalam satu query.
     *
     * @param list<int|string> $daftarIdDestinasi
     * @return array<int, string>
     */
    private function ambilPetaFotoDestinasi(array $daftarIdDestinasi): array
    {
        if ($daftarIdDestinasi === []) {
            return [];
        }

        $destinasiModel = new DestinasiModel();
        $barisFoto      = $destinasiModel
            ->select('id, foto_utama')
            ->whereIn('id', $daftarIdDestinasi)
            ->findAll();

        $petaFoto = [];
        foreach ($barisFoto as $baris) {
            $petaFoto[(int) $baris['id']] = (string) ($baris['foto_utama'] ?? '');
        }

        return $petaFoto;
    }

    /**
     * Memformat angka menjadi teks rupiah, contoh: Rp 25.000.
     */
    private function formatRupiah(int $nominal): string
    {
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }

    /**
     * Memastikan pemesanan hanya diakses atau diubah oleh pemilik akunnya (kecuali admin).
     */
    private function cekKepemilikanPengguna(int $idPenggunaTarget, string $pesanTolak): ?ResponseInterface
    {
        $dataToken = AutentikasiApiController::verifikasiHeaderOtorisasi(
            $this->request->getHeaderLine('Authorization')
        );

        if (
            $dataToken !== null
            && ($dataToken['peran'] ?? '') !== 'admin'
            && (int) $dataToken['id_pengguna'] !== $idPenggunaTarget
        ) {
            return $this->kirimGagal($pesanTolak, ResponseInterface::HTTP_FORBIDDEN);
        }

        return null;
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
