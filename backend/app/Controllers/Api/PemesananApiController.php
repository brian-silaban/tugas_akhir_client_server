<?php

namespace App\Controllers\Api;

use App\Models\PemesananModel;
use App\Models\DestinasiModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * API Controller untuk modul Pemesanan Tiket Wisata JejakSumut.
 * Menyediakan endpoint CRUD pemesanan tiket yang dapat diakses
 * oleh aplikasi Client Frontend (port 8081).
 *
 * Anggota 4 — Transaksi Pemesanan Tiket & E-Ticket
 */
class PemesananApiController extends ResourceController
{
    protected $modelName = 'App\Models\PemesananModel';
    protected $format    = 'json';

    // =========================================================================
    // GET /api/v1/pemesanan?id_pengguna=X
    // Mengambil semua riwayat tiket milik pengguna tertentu.
    // =========================================================================
    public function index(): ResponseInterface
    {
        $idPengguna = $this->request->getGet('id_pengguna');

        // Validasi: id_pengguna wajib disertakan
        if (empty($idPengguna) || ! is_numeric($idPengguna)) {
            return $this->respond([
                'status'  => 'gagal',
                'pesan'   => 'Parameter id_pengguna wajib disertakan dan berupa angka.',
                'data'    => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pemesananModel = new PemesananModel();
        $destinasiModel = new DestinasiModel();

        // Ambil semua pemesanan milik pengguna ini, diurutkan paling baru
        $daftarPemesanan = $pemesananModel
            ->where('id_pengguna', (int) $idPengguna)
            ->orderBy('dibuat_pada', 'DESC')
            ->findAll();

        // Lengkapi setiap tiket dengan nama dan foto destinasi
        foreach ($daftarPemesanan as &$tiket) {
            $dataDestinasi = $destinasiModel->find((int) $tiket['id_destinasi']);
            $tiket['nama_destinasi'] = $dataDestinasi['nama']       ?? 'Destinasi tidak ditemukan';
            $tiket['foto_destinasi'] = $dataDestinasi['foto_utama'] ?? '';
            $tiket['kota_destinasi'] = $dataDestinasi['kota']       ?? '';

            // Format total harga ke rupiah agar siap tampil di frontend
            $tiket['total_harga_terformat'] = 'Rp ' . number_format((int) $tiket['total_harga'], 0, ',', '.');
        }
        unset($tiket);

        return $this->respond([
            'status'       => 'berhasil',
            'jumlah_data'  => count($daftarPemesanan),
            'data'         => $daftarPemesanan,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // GET /api/v1/pemesanan/{id}
    // Mengambil detail satu tiket spesifik untuk halaman E-Ticket.
    // =========================================================================
    public function show($id = null): ResponseInterface
    {
        if (empty($id) || ! is_numeric($id)) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'ID pemesanan tidak valid.',
                'data'   => null,
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $pemesananModel = new PemesananModel();
        $destinasiModel = new DestinasiModel();

        $tiket = $pemesananModel->find((int) $id);

        // Jika tiket tidak ditemukan di database
        if ($tiket === null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Tiket dengan ID ' . $id . ' tidak ditemukan.',
                'data'   => null,
            ], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Ambil data destinasi untuk ditampilkan di E-Ticket
        $dataDestinasi = $destinasiModel->find((int) $tiket['id_destinasi']);
        $tiket['nama_destinasi']      = $dataDestinasi['nama']          ?? 'Destinasi tidak ditemukan';
        $tiket['foto_destinasi']      = $dataDestinasi['foto_utama']    ?? '';
        $tiket['kota_destinasi']      = $dataDestinasi['kota']          ?? '';
        $tiket['alamat_destinasi']    = $dataDestinasi['alamat']        ?? '';
        $tiket['jam_buka_destinasi']  = $dataDestinasi['jam_buka']      ?? null;
        $tiket['jam_tutup_destinasi'] = $dataDestinasi['jam_tutup']     ?? null;

        // Format tampilan harga
        $tiket['total_harga_terformat'] = 'Rp ' . number_format((int) $tiket['total_harga'], 0, ',', '.');

        return $this->respond([
            'status' => 'berhasil',
            'data'   => $tiket,
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // POST /api/v1/pemesanan
    // Membuat pesanan tiket baru dari form frontend.
    // =========================================================================
    public function create(): ResponseInterface
    {
        $inputJson = $this->request->getJSON(true);

        // Dukung input dari JSON (fetch API) maupun form POST biasa
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        // --- Validasi field wajib ---
        $fieldWajib = ['id_pengguna', 'id_destinasi', 'tanggal_kunjungan', 'jumlah_pengunjung'];
        foreach ($fieldWajib as $namaField) {
            if (empty($inputData[$namaField])) {
                return $this->respond([
                    'status' => 'gagal',
                    'pesan'  => 'Field ' . $namaField . ' wajib diisi.',
                    'data'   => null,
                ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $idPengguna       = (int) $inputData['id_pengguna'];
        $idDestinasi      = (int) $inputData['id_destinasi'];
        $tanggalKunjungan = (string) $inputData['tanggal_kunjungan'];
        $jumlahPengunjung = (int) $inputData['jumlah_pengunjung'];

        // Validasi jumlah pengunjung harus minimal 1
        if ($jumlahPengunjung < 1) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Jumlah pengunjung minimal 1 orang.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Validasi tanggal kunjungan tidak boleh di masa lampau
        if (strtotime($tanggalKunjungan) < strtotime('today')) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Tanggal kunjungan tidak boleh di masa lampau.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Ambil data destinasi untuk mendapatkan harga tiket resmi dari server
        $destinasiModel = new DestinasiModel();
        $dataDestinasi  = $destinasiModel->find($idDestinasi);

        if ($dataDestinasi === null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Destinasi wisata tidak ditemukan.',
                'data'   => null,
            ], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Hitung total harga di sisi SERVER (bukan di frontend) untuk keamanan data
        $hargaTiketPerOrang = (int) $dataDestinasi['harga_tiket'];
        $totalHarga         = $hargaTiketPerOrang * $jumlahPengunjung;

        // Generate kode tiket unik format JSM-YYYYMMDD-XXX
        $kodePemesanan = $this->buatKodePemesananUnik();

        // Simpan data pemesanan baru ke tabel pemesanan
        $pemesananModel = new PemesananModel();
        $idBaru = $pemesananModel->insert([
            'kode_pemesanan'   => $kodePemesanan,
            'id_pengguna'      => $idPengguna,
            'id_destinasi'     => $idDestinasi,
            'tanggal_kunjungan' => $tanggalKunjungan,
            'jumlah_pengunjung' => $jumlahPengunjung,
            'total_harga'      => $totalHarga,
            'status'           => 'menunggu',
        ]);

        if (! $idBaru) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Gagal menyimpan pemesanan. Silakan coba lagi.',
                'data'   => null,
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Ambil data tiket yang baru saja tersimpan untuk dikirim balik ke frontend
        $tiketBaru = $pemesananModel->find($idBaru);
        $tiketBaru['nama_destinasi']      = $dataDestinasi['nama'];
        $tiketBaru['kota_destinasi']      = $dataDestinasi['kota'];
        $tiketBaru['total_harga_terformat'] = 'Rp ' . number_format($totalHarga, 0, ',', '.');

        return $this->respond([
            'status'  => 'berhasil',
            'pesan'   => 'Tiket berhasil dipesan! Kode tiket kamu: ' . $kodePemesanan,
            'data'    => $tiketBaru,
        ], ResponseInterface::HTTP_CREATED);
    }

    // =========================================================================
    // PATCH /api/v1/pemesanan/{id}/batal
    // Membatalkan tiket (hanya jika statusnya masih 'menunggu').
    // =========================================================================
    public function batalkan($id = null): ResponseInterface
    {
        if (empty($id) || ! is_numeric($id)) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'ID pemesanan tidak valid.',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $pemesananModel = new PemesananModel();
        $tiket          = $pemesananModel->find((int) $id);

        if ($tiket === null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Tiket tidak ditemukan.',
            ], ResponseInterface::HTTP_NOT_FOUND);
        }

        // Tiket hanya bisa dibatalkan kalau masih berstatus 'menunggu'
        if ($tiket['status'] !== 'menunggu') {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Tiket hanya bisa dibatalkan saat statusnya masih "menunggu".',
            ], ResponseInterface::HTTP_CONFLICT);
        }

        // Update status menjadi 'dibatalkan'
        $pemesananModel->update((int) $id, ['status' => 'dibatalkan']);

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Tiket dengan kode ' . $tiket['kode_pemesanan'] . ' berhasil dibatalkan.',
        ], ResponseInterface::HTTP_OK);
    }

    // =========================================================================
    // Helper: Membuat kode pemesanan unik format JSM-YYYYMMDD-XXX
    // =========================================================================
    private function buatKodePemesananUnik(): string
    {
        $pemesananModel = new PemesananModel();
        $tanggalHariIni = date('Ymd');
        $prefiks        = 'JSM-' . $tanggalHariIni . '-';

        // Hitung berapa tiket yang sudah dibuat hari ini
        $jumlahHariIni = $pemesananModel
            ->like('kode_pemesanan', $prefiks, 'after')
            ->countAllResults();

        // Format nomor urut 3 digit: 001, 002, 003, dst.
        $nomorUrut = str_pad((string) ($jumlahHariIni + 1), 3, '0', STR_PAD_LEFT);

        return $prefiks . $nomorUrut;
    }
}
