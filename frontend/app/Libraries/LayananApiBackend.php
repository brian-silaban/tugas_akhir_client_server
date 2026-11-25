<?php

namespace App\Libraries;

use Config\Services;
use Throwable;

/**
 * HTTP Client terpusat untuk berkomunikasi dengan REST API Backend JejakSumut.
 * Frontend dilarang mengakses PostgreSQL secara langsung; seluruh pertukaran data
 * wajib melalui library ini menggunakan metode HTTP standar (GET, POST, PUT, PATCH, DELETE).
 *
 * Anggota 2 (Brian) — Integrasi Client-Server
 */
class LayananApiBackend
{
    private string $urlDasarApi;
    private int $batasWaktuDetik;
    private ?string $tokenAutentikasi = null;

    public function __construct(?string $urlDasarApi = null, ?int $batasWaktuDetik = null)
    {
        // Ambil URL dasar API dari parameter atau konfigurasi .env Frontend
        $urlKonfigurasi = $urlDasarApi;
        if ($urlKonfigurasi === null || trim($urlKonfigurasi) === '') {
            $urlKonfigurasi = (string) env('JEJAKSUMUT_BACKEND_API_URL', 'http://localhost:8080/api/v1');
        }
        $this->urlDasarApi = rtrim(trim($urlKonfigurasi), '/');

        // Tentukan batas waktu (timeout) request dalam detik, minimal 1 detik
        if ($batasWaktuDetik !== null) {
            $waktuKonfigurasi = $batasWaktuDetik;
        } else {
            $waktuKonfigurasi = (int) env('BACKEND_API_TIMEOUT', 5);
        }
        $this->batasWaktuDetik = max(1, $waktuKonfigurasi);
    }

    /**
     * Mengambil URL dasar REST API Backend yang sedang aktif.
     */
    public function ambilUrlDasarApi(): string
    {
        return $this->urlDasarApi;
    }

    /**
     * Menyimpan token Bearer untuk otorisasi endpoint Member maupun Admin.
     */
    public function aturTokenAutentikasi(?string $tokenAutentikasi): self
    {
        if ($tokenAutentikasi === null) {
            $this->tokenAutentikasi = null;
            return $this;
        }

        $tokenBersih = trim($tokenAutentikasi);
        if ($tokenBersih === '') {
            $this->tokenAutentikasi = null;
        } else {
            $this->tokenAutentikasi = $tokenBersih;
        }

        return $this;
    }

    // =========================================================================
    // METHOD HTTP GENERIK (Dipakai Bersama Oleh Seluruh Modul Anggota 1–5)
    // =========================================================================

    /**
     * Mengirim permintaan HTTP GET ke endpoint REST API Backend.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function kirimGet(string $path, array $query = [], ?string $token = null): array
    {
        return $this->kirimPermintaan('GET', $path, $query, null, $token);
    }

    /**
     * Mengirim permintaan HTTP POST dengan payload JSON ke endpoint Backend.
     *
     * @param array<string, mixed> $dataBody
     * @return array<string, mixed>
     */
    public function kirimPost(string $path, array $dataBody = [], ?string $token = null): array
    {
        return $this->kirimPermintaan('POST', $path, [], $dataBody, $token);
    }

    /**
     * Mengirim permintaan HTTP PUT dengan payload JSON ke endpoint Backend.
     *
     * @param array<string, mixed> $dataBody
     * @return array<string, mixed>
     */
    public function kirimPut(string $path, array $dataBody = [], ?string $token = null): array
    {
        return $this->kirimPermintaan('PUT', $path, [], $dataBody, $token);
    }

    /**
     * Mengirim permintaan HTTP PATCH dengan payload JSON ke endpoint Backend.
     *
     * @param array<string, mixed> $dataBody
     * @return array<string, mixed>
     */
    public function kirimPatch(string $path, array $dataBody = [], ?string $token = null): array
    {
        return $this->kirimPermintaan('PATCH', $path, [], $dataBody, $token);
    }

    /**
     * Mengirim permintaan HTTP DELETE ke endpoint REST API Backend.
     *
     * @return array<string, mixed>
     */
    public function kirimDelete(string $path, ?string $token = null): array
    {
        return $this->kirimPermintaan('DELETE', $path, [], null, $token);
    }

    // =========================================================================
    // METHOD DOMAIN KATALOG & WILAYAH (Kompatibel Penuh dengan Anggota 1)
    // =========================================================================

    /**
     * Mengambil daftar destinasi beserta paginasi dari GET /api/v1/destinasi.
     *
     * @param array<string, mixed> $parameter
     * @return array<string, mixed>
     */
    public function ambilDaftarDestinasi(array $parameter = []): array
    {
        $hasil = $this->kirimGet('/destinasi', $parameter);

        $paginasiDefault = [
            'halaman_sekarang'  => 1,
            'batas_per_halaman' => 12,
            'total_data'        => 0,
            'total_halaman'     => 1,
        ];

        if (! $hasil['terhubung'] || ! $hasil['berhasil']) {
            return [
                'terhubung'   => $hasil['terhubung'],
                'data'        => [],
                'paginasi'    => $paginasiDefault,
                'pesan_error' => $hasil['pesan_error'],
            ];
        }

        $isi        = $hasil['isi'];
        $daftarData = is_array($isi['data'] ?? null) ? $isi['data'] : [];

        if (is_array($isi['paginasi'] ?? null)) {
            $paginasi = $isi['paginasi'];
        } else {
            $paginasi = [
                'halaman_sekarang'  => 1,
                'batas_per_halaman' => 12,
                'total_data'        => count($daftarData),
                'total_halaman'     => 1,
            ];
        }

        return [
            'terhubung'   => true,
            'data'        => $daftarData,
            'paginasi'    => $paginasi,
            'pesan_error' => null,
        ];
    }

    /**
     * Mengambil detail satu destinasi dari GET /api/v1/destinasi/{slug_atau_id}.
     *
     * @return array<string, mixed>
     */
    public function ambilDetailDestinasi(string $slugAtauId): array
    {
        $hasil = $this->kirimGet('/destinasi/' . rawurlencode($slugAtauId));

        $dataDestinasi = is_array($hasil['isi']['data'] ?? null) ? $hasil['isi']['data'] : null;

        return [
            'terhubung'   => $hasil['terhubung'],
            'status_kode' => $hasil['status_kode'],
            'data'        => $dataDestinasi,
            'pesan_error' => $hasil['pesan_error'],
        ];
    }

    /**
     * Mengambil daftar kategori wisata dari GET /api/v1/kategori.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilDaftarKategori(): array
    {
        $hasil = $this->kirimGet('/kategori');
        if (! $hasil['terhubung'] || ! $hasil['berhasil']) {
            return [];
        }

        return is_array($hasil['isi']['data'] ?? null) ? $hasil['isi']['data'] : [];
    }

    /**
     * Mengambil daftar 33 kabupaten/kota Sumatera Utara dari GET /api/v1/kabupaten-kota.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilDaftarKabupatenKota(): array
    {
        $hasil = $this->kirimGet('/kabupaten-kota');
        if (! $hasil['terhubung'] || ! $hasil['berhasil']) {
            return [];
        }

        return is_array($hasil['isi']['data'] ?? null) ? $hasil['isi']['data'] : [];
    }

    // =========================================================================
    // METHOD DOMAIN TRANSAKSI & KESEHATAN KONEKSI (Siap Pakai Anggota 4 & 5)
    // =========================================================================

    /**
     * Mengambil daftar riwayat pemesanan tiket milik seorang pengguna.
     *
     * @return array<string, mixed>
     */
    public function ambilDaftarPemesanan(int $idPengguna, ?string $token = null): array
    {
        return $this->kirimGet('/pemesanan', ['id_pengguna' => $idPengguna], $token);
    }

    /**
     * Mengambil detail satu pemesanan tiket (E-Ticket) berdasarkan ID.
     *
     * @return array<string, mixed>
     */
    public function ambilDetailPemesanan(int $idPemesanan, ?string $token = null): array
    {
        return $this->kirimGet('/pemesanan/' . $idPemesanan, [], $token);
    }

    /**
     * Mengirim pesanan tiket baru ke POST /api/v1/pemesanan.
     *
     * @param array<string, mixed> $dataPemesanan
     * @return array<string, mixed>
     */
    public function buatPemesanan(array $dataPemesanan, ?string $token = null): array
    {
        return $this->kirimPost('/pemesanan', $dataPemesanan, $token);
    }

    /**
     * Membatalkan pesanan tiket melalui PATCH /api/v1/pemesanan/{id}/batal.
     *
     * @return array<string, mixed>
     */
    public function batalkanPemesanan(int $idPemesanan, ?string $token = null): array
    {
        return $this->kirimPatch('/pemesanan/' . $idPemesanan . '/batal', [], $token);
    }

    /**
     * Mengambil daftar ulasan dan rating untuk satu destinasi wisata.
     *
     * @return array<string, mixed>
     */
    public function ambilDaftarUlasan(int $idDestinasi): array
    {
        return $this->kirimGet('/ulasan', ['id_destinasi' => $idDestinasi]);
    }

    /**
     * Mengirim ulasan dan rating bintang baru ke POST /api/v1/ulasan.
     *
     * @param array<string, mixed> $dataUlasan
     * @return array<string, mixed>
     */
    public function kirimUlasan(array $dataUlasan, ?string $token = null): array
    {
        return $this->kirimPost('/ulasan', $dataUlasan, $token);
    }

    /**
     * Memeriksa status koneksi dan latensi server REST API Backend.
     *
     * @return array<string, mixed>
     */
    public function cekKoneksiBackend(): array
    {
        $hasil = $this->kirimGet('/kategori');

        if ($hasil['terhubung'] && $hasil['berhasil']) {
            $pesanStatus = 'Terhubung ke REST API Backend JejakSumut.';
        } elseif ($hasil['terhubung']) {
            $pesanStatus = $hasil['pesan_error'] ?? 'Server Backend merespons dengan status error.';
        } else {
            $pesanStatus = $hasil['pesan_error'] ?? 'Tidak dapat terhubung ke server REST API Backend.';
        }

        return [
            'terhubung'       => $hasil['terhubung'],
            'status_kode'     => $hasil['status_kode'],
            'url_dasar'       => $this->urlDasarApi,
            'waktu_respon_ms' => $hasil['waktu_respon_ms'],
            'pesan'           => $pesanStatus,
        ];
    }

    // =========================================================================
    // HELPER INTERNAL HTTP CLIENT
    // =========================================================================

    /**
     * Menjalankan permintaan HTTP ke REST API Backend dengan format respons seragam.
     *
     * @param array<string, mixed>      $query
     * @param array<string, mixed>|null $dataBody
     * @return array<string, mixed>
     */
    private function kirimPermintaan(
        string $metodeHttp,
        string $path,
        array $query = [],
        ?array $dataBody = null,
        ?string $token = null
    ): array {
        $metodeBersih = strtoupper(trim($metodeHttp));
        $pathBersih   = '/' . ltrim($path, '/');

        // Buang parameter query kosong agar URL tetap rapi
        $queryBersih = array_filter(
            $query,
            static fn ($nilai): bool => $nilai !== null && $nilai !== ''
        );

        $urlTujuan = $this->urlDasarApi . $pathBersih;
        if ($queryBersih !== []) {
            $urlTujuan .= '?' . http_build_query($queryBersih);
        }

        // Susun header dan opsi request untuk dikirim via cURL
        $headerPermintaan = $this->siapkanHeader($token);
        $opsiPermintaan   = [
            'timeout'     => $this->batasWaktuDetik,
            'http_errors' => false,
            'headers'     => $headerPermintaan,
        ];

        // Sertakan body JSON hanya jika ada data payload yang dikirim
        if ($dataBody !== null && $dataBody !== []) {
            $opsiPermintaan['json'] = $dataBody;
        }

        $waktuMulai = microtime(true);

        try {
            // Pakai getShared = false agar header Authorization dan body tidak bocor antar request
            $klienHttp     = Services::curlrequest([], null, null, false);
            $respon        = $klienHttp->request($metodeBersih, $urlTujuan, $opsiPermintaan);
            $waktuResponMs = (int) round((microtime(true) - $waktuMulai) * 1000);

            $statusKode = $respon->getStatusCode();
            $isiJson    = json_decode((string) $respon->getBody(), true);

            if (! is_array($isiJson)) {
                return [
                    'berhasil'        => false,
                    'terhubung'       => false,
                    'status_kode'     => $statusKode,
                    'isi'             => [],
                    'data'            => null,
                    'pesan'           => null,
                    'pesan_error'     => 'Respons REST API backend tidak valid atau bukan JSON.',
                    'daftar_validasi' => [],
                    'waktu_respon_ms' => $waktuResponMs,
                ];
            }

            $berhasil       = $statusKode >= 200 && $statusKode < 300;
            $pesanServer    = $this->ambilPesanServer($isiJson);
            $daftarValidasi = $this->ambilDaftarValidasi($isiJson);

            if ($berhasil) {
                $pesanError = null;
            } else {
                $pesanError = $this->susunPesanError($statusKode, $isiJson);
            }

            return [
                'berhasil'        => $berhasil,
                'terhubung'       => true,
                'status_kode'     => $statusKode,
                'isi'             => $isiJson,
                'data'            => $isiJson['data'] ?? null,
                'pesan'           => $pesanServer,
                'pesan_error'     => $pesanError,
                'daftar_validasi' => $daftarValidasi,
                'waktu_respon_ms' => $waktuResponMs,
            ];
        } catch (Throwable $kesalahan) {
            $waktuResponMs = (int) round((microtime(true) - $waktuMulai) * 1000);

            return [
                'berhasil'        => false,
                'terhubung'       => false,
                'status_kode'     => 0,
                'isi'             => [],
                'data'            => null,
                'pesan'           => null,
                'pesan_error'     => 'Tidak dapat terhubung ke server REST API Backend.',
                'daftar_validasi' => [],
                'waktu_respon_ms' => $waktuResponMs,
            ];
        }
    }

    /**
     * Menyiapkan header standar JSON dan token Bearer bila tersedia.
     *
     * @return array<string, string>
     */
    private function siapkanHeader(?string $token = null): array
    {
        $header = [
            'Accept' => 'application/json',
        ];

        // Prioritaskan token parameter; jika tidak dikirim, pakai token yang tersimpan di instance
        $tokenAktif = $token !== null ? trim($token) : ($this->tokenAutentikasi ?? '');
        if ($tokenAktif !== '') {
            $header['Authorization'] = 'Bearer ' . $tokenAktif;
        }

        // Teruskan IP asli pengunjung ke Backend agar Rate Limiting dihitung per pengguna, bukan per server
        if (! is_cli()) {
            $ipPengunjung = Services::request()->getIPAddress();
            if ($ipPengunjung !== '' && $ipPengunjung !== '0.0.0.0') {
                $header['X-Forwarded-For'] = $ipPengunjung;
            }
        }

        return $header;
    }

    /**
     * Mengambil teks pesan dari payload JSON backend (mendukung key 'pesan' atau 'message').
     *
     * @param array<string, mixed> $isiJson
     */
    private function ambilPesanServer(array $isiJson): ?string
    {
        if (isset($isiJson['pesan']) && is_string($isiJson['pesan']) && trim($isiJson['pesan']) !== '') {
            return trim($isiJson['pesan']);
        }

        if (isset($isiJson['message']) && is_string($isiJson['message']) && trim($isiJson['message']) !== '') {
            return trim($isiJson['message']);
        }

        return null;
    }

    /**
     * Mengambil daftar error validasi field (khususnya saat HTTP 422).
     *
     * @param array<string, mixed> $isiJson
     * @return array<string, mixed>
     */
    private function ambilDaftarValidasi(array $isiJson): array
    {
        if (is_array($isiJson['errors'] ?? null)) {
            return $isiJson['errors'];
        }

        if (is_array($isiJson['kesalahan'] ?? null)) {
            return $isiJson['kesalahan'];
        }

        return [];
    }

    /**
     * Memetakan kode status HTTP ke pesan error berbahasa Indonesia yang jelas.
     *
     * @param array<string, mixed> $isiJson
     */
    private function susunPesanError(int $statusKode, array $isiJson): string
    {
        $pesanServer = $this->ambilPesanServer($isiJson);
        if ($pesanServer !== null) {
            return $pesanServer;
        }

        if ($statusKode === 401) {
            return 'Akses ditolak: token autentikasi tidak valid atau belum dikirim.';
        }
        if ($statusKode === 403) {
            return 'Akses ditolak: peran akun Anda tidak memiliki izin untuk aksi ini.';
        }
        if ($statusKode === 404) {
            return 'Data yang diminta tidak ditemukan di server REST API.';
        }
        if ($statusKode === 409) {
            return 'Terjadi konflik data pada server. Silakan muat ulang dan coba lagi.';
        }
        if ($statusKode === 422) {
            return 'Validasi data gagal. Periksa kembali isian formulir Anda.';
        }

        return 'Permintaan ke server REST API Backend gagal diproses.';
    }
}
