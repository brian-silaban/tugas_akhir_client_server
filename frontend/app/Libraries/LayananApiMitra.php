<?php

namespace App\Libraries;

use Config\Services;
use Throwable;

/**
 * HTTP Client + Resilient Adapter untuk mengonsumsi REST API dari 4 kelompok mitra
 * di kelas (Penyedia Tur Guide, Kuliner/Rumah Makan, Penginapan, dan Transportasi).
 *
 * Jika API mitra belum aktif, timeout, atau merespons error, library ini otomatis
 * menyediakan data simulasi Sumatera Utara (Mock Fallback) agar tampilan detail
 * destinasi tidak pernah gagal dimuat.
 *
 * Anggota 2 (Brian) — Integrasi REST API 4 Kelompok Mitra
 */
class LayananApiMitra
{
    /** @var array<string, array<string, string>> */
    private array $konfigurasiMitra = [];

    private int $batasWaktuDetik;
    private bool $modeCadanganMock;

    /**
     * @param array<string, string>|null $overrideUrlMitra Opsional untuk uji coba / override URL mitra
     */
    public function __construct(
        ?array $overrideUrlMitra = null,
        ?int $batasWaktuDetik = null,
        ?bool $modeCadanganMock = null
    ) {
        // Daftar 4 kelompok mitra resmi sesuai PRD.md (prioritaskan mitra Live API di tab awal)
        $definisiMitra = [
            'penginapan' => [
                'nama_layanan'      => 'Penginapan & Hotel',
                'env_key'           => 'PARTNER_HOTEL_API_URL',
                'env_api_key'       => 'PARTNER_HOTEL_API_KEY',
                'ikon'              => 'hotel',
                'satuan_harga'      => '/ malam',
                'deskripsi_singkat' => 'Pilihan hotel, homestay, dan penginapan nyaman di Sumatera Utara.',
            ],
            'transportasi' => [
                'nama_layanan'      => 'Layanan Transportasi',
                'env_key'           => 'PARTNER_TRANSPORT_API_URL',
                'env_api_key'       => 'PARTNER_TRANSPORT_API_KEY',
                'ikon'              => 'directions_bus',
                'satuan_harga'      => '/ perjalanan',
                'deskripsi_singkat' => 'Akses shuttle, travel, kereta bandara, dan penerbangan menuju destinasi.',
            ],
            'tur_guide' => [
                'nama_layanan'      => 'Penyedia Tur Guide',
                'env_key'           => 'PARTNER_TUR_GUIDE_API_URL',
                'env_api_key'       => 'PARTNER_TUR_GUIDE_API_KEY',
                'ikon'              => 'hiking',
                'satuan_harga'      => '/ paket',
                'deskripsi_singkat' => 'Pemandu wisata lokal bersertifikat & paket jelajah rute Sumut.',
            ],
            'kuliner' => [
                'nama_layanan'      => 'Kuliner & Rumah Makan',
                'env_key'           => 'PARTNER_KULINER_API_URL',
                'env_api_key'       => 'PARTNER_KULINER_API_KEY',
                'ikon'              => 'restaurant',
                'satuan_harga'      => '/ porsi',
                'deskripsi_singkat' => 'Rekomendasi hidangan khas dan rumah makan di sekitar destinasi.',
            ],
        ];

        // Susun konfigurasi URL masing-masing mitra dari override atau file .env
        foreach ($definisiMitra as $kodeMitra => $metaMitra) {
            $urlMitra = '';
            if (is_array($overrideUrlMitra) && array_key_exists($kodeMitra, $overrideUrlMitra)) {
                $urlMitra = trim((string) $overrideUrlMitra[$kodeMitra]);
            } else {
                $urlMitra = trim((string) env($metaMitra['env_key'], ''));
            }

            $apiKeyMitra = trim((string) env($metaMitra['env_api_key'], ''));

            $this->konfigurasiMitra[$kodeMitra] = [
                'kode_mitra'        => $kodeMitra,
                'nama_layanan'      => $metaMitra['nama_layanan'],
                'env_key'           => $metaMitra['env_key'],
                'api_key'           => $apiKeyMitra,
                'ikon'              => $metaMitra['ikon'],
                'satuan_harga'      => $metaMitra['satuan_harga'],
                'deskripsi_singkat' => $metaMitra['deskripsi_singkat'],
                'url_api'           => $urlMitra,
            ];
        }

        // Batasi timeout 1–10 detik agar halaman detail wisata tetap cepat saat mitra down
        if ($batasWaktuDetik !== null) {
            $waktuMentah = $batasWaktuDetik;
        } else {
            $waktuMentah = (int) env('PARTNER_API_TIMEOUT', 3);
        }
        $this->batasWaktuDetik = max(1, min(10, $waktuMentah));

        // Aktifkan atau nonaktifkan fallback data simulasi Sumatera Utara
        if ($modeCadanganMock !== null) {
            $this->modeCadanganMock = $modeCadanganMock;
        } else {
            $this->modeCadanganMock = $this->ubahKeBoolean(env('MODE_CADANGAN_MOCK', true), true);
        }
    }

    /**
     * Mengembalikan metadata konfigurasi untuk seluruh 4 kelompok mitra.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilDaftarKonfigurasiMitra(): array
    {
        $daftarKonfigurasi = [];

        foreach ($this->konfigurasiMitra as $infoMitra) {
            $daftarKonfigurasi[] = [
                'kode_mitra'        => $infoMitra['kode_mitra'],
                'nama_layanan'      => $infoMitra['nama_layanan'],
                'ikon'              => $infoMitra['ikon'],
                'satuan_harga'      => $infoMitra['satuan_harga'],
                'url_api'           => $infoMitra['url_api'],
                'url_terkonfigurasi' => $infoMitra['url_api'] !== '',
            ];
        }

        return $daftarKonfigurasi;
    }

    /**
     * Membersihkan awalan "Kabupaten " atau "Kota " agar query parameter ?kota= cocok
     * dengan format pencarian API kelompok mitra (misal: "Kabupaten Samosir" -> "Samosir").
     */
    public function bersihkanNamaKota(string $namaKota): string
    {
        $teks = trim($namaKota);
        if ($teks === '') {
            return 'Sumatera Utara';
        }

        // Hapus prefiks administratif di awal string secara case-insensitive
        $hasil = preg_replace('/^(kabupaten|kota)\s+/i', '', $teks);
        $hasilBersih = trim((string) $hasil);

        // Rapikan spasi ganda bila ada
        $hasilBersih = (string) preg_replace('/\s+/', ' ', $hasilBersih);

        if ($hasilBersih === '') {
            return 'Sumatera Utara';
        }

        return $hasilBersih;
    }

    /**
     * Mengambil rekomendasi dari seluruh 4 kelompok mitra sekaligus untuk suatu kabupaten/kota.
     *
     * @param array<string, mixed>|null $konteksDestinasi Opsional data destinasi (nama, koordinat GPS)
     * @return array<string, mixed>
     */
    public function ambilSemuaRekomendasi(string $kota, int $batasPerMitra = 3, ?array $konteksDestinasi = null): array
    {
        $kotaAsli      = trim($kota) !== '' ? trim($kota) : 'Sumatera Utara';
        $kotaPencarian = $this->bersihkanNamaKota($kotaAsli);

        $layananTerhimpun   = [];
        $jumlahApiLangsung  = 0;
        $jumlahFallback     = 0;
        $totalItem          = 0;

        foreach (array_keys($this->konfigurasiMitra) as $kodeMitra) {
            $hasilMitra = $this->ambilRekomendasiPerMitra($kodeMitra, $kotaAsli, $batasPerMitra, $konteksDestinasi);
            $layananTerhimpun[$kodeMitra] = $hasilMitra;

            if ($hasilMitra['sumber_data'] === 'api_mitra') {
                $jumlahApiLangsung++;
            } elseif ($hasilMitra['sumber_data'] === 'simulasi_fallback') {
                $jumlahFallback++;
            }

            $totalItem += count($hasilMitra['daftar_item']);
        }

        return [
            'kota_asli'       => $kotaAsli,
            'kota_pencarian'  => $kotaPencarian,
            'mode_mock_aktif' => $this->modeCadanganMock,
            'ringkasan'       => [
                'total_mitra'         => count($this->konfigurasiMitra),
                'jumlah_api_langsung' => $jumlahApiLangsung,
                'jumlah_fallback'     => $jumlahFallback,
                'total_item'          => $totalItem,
            ],
            'layanan' => $layananTerhimpun,
        ];
    }

    /**
     * Mengambil rekomendasi layanan dari satu kelompok mitra tertentu dengan auto-fallback.
     *
     * @param array<string, mixed>|null $konteksDestinasi Opsional data destinasi (nama, koordinat GPS)
     * @return array<string, mixed>
     */
    public function ambilRekomendasiPerMitra(
        string $kodeMitra,
        string $kota,
        int $batasItem = 3,
        ?array $konteksDestinasi = null
    ): array {
        if (! array_key_exists($kodeMitra, $this->konfigurasiMitra)) {
            return [
                'kode_mitra'        => $kodeMitra,
                'nama_layanan'      => 'Mitra Tidak Dikenal',
                'ikon'              => 'info',
                'deskripsi_singkat' => '',
                'url_tujuan'        => '',
                'terhubung'         => false,
                'status_kode'       => 0,
                'sumber_data'       => 'tidak_tersedia',
                'label_sumber'      => 'Tidak Tersedia',
                'alasan_fallback'   => 'Kode layanan mitra tidak terdaftar.',
                'waktu_respon_ms'   => 0,
                'daftar_item'       => [],
            ];
        }

        $infoMitra     = $this->konfigurasiMitra[$kodeMitra];
        $kotaAsli      = trim($kota) !== '' ? trim($kota) : 'Sumatera Utara';
        $kotaPencarian = $this->bersihkanNamaKota($kotaAsli);
        $batasAman     = max(1, min(12, $batasItem));

        $urlDasar       = $infoMitra['url_api'];
        $apiKey         = $infoMitra['api_key'] ?? '';
        $urlTujuan      = '';
        $statusKode     = 0;
        $waktuResponMs  = 0;
        $alasanFallback = null;

        // Jalur khusus API kelompok Penginapan Singgah Medan (/api/hotels, /kecamatan, /reviews)
        $apakahAdapterPenginapan = $kodeMitra === 'penginapan'
            && $this->apakahAdapterPenginapanSinggahMedan($urlDasar);

        // Jalur khusus API kelompok Transportasi (/api/transport/lokasi, /tarif, /rekomendasi-wisata)
        $apakahAdapterTransportasi = $kodeMitra === 'transportasi'
            && $urlDasar !== ''
            && ($apiKey !== '' || str_ends_with(rtrim($urlDasar, '/'), '/api'));

        if ($apakahAdapterPenginapan) {
            $hasilPenginapan = $this->ambilRekomendasiPenginapanMitra(
                $infoMitra,
                $kotaPencarian,
                $batasAman,
                $konteksDestinasi
            );

            if ($hasilPenginapan['berhasil']) {
                return $hasilPenginapan['data'];
            }

            $urlTujuan      = $hasilPenginapan['url_tujuan'];
            $statusKode     = $hasilPenginapan['status_kode'];
            $waktuResponMs  = $hasilPenginapan['waktu_respon_ms'];
            $alasanFallback = $hasilPenginapan['alasan_fallback'];
        } elseif ($apakahAdapterTransportasi) {
            $hasilTransport = $this->ambilRekomendasiTransportasiMitra(
                $infoMitra,
                $kotaPencarian,
                $batasAman,
                $konteksDestinasi
            );

            if ($hasilTransport['berhasil']) {
                return $hasilTransport['data'];
            }

            $urlTujuan      = $hasilTransport['url_tujuan'];
            $statusKode     = $hasilTransport['status_kode'];
            $waktuResponMs  = $hasilTransport['waktu_respon_ms'];
            $alasanFallback = $hasilTransport['alasan_fallback'];
        } elseif ($urlDasar !== '') {
            $pemisahQuery = str_contains($urlDasar, '?') ? '&' : '?';
            $urlTujuan    = $urlDasar . $pemisahQuery . 'kota=' . urlencode($kotaPencarian);

            $hasilPanggil   = $this->panggilEndpointMitra($urlTujuan, $apiKey);
            $statusKode     = $hasilPanggil['status_kode'];
            $waktuResponMs  = $hasilPanggil['waktu_respon_ms'];
            $alasanFallback = $hasilPanggil['pesan_error'];

            if ($hasilPanggil['berhasil']) {
                $daftarItemMentah = $this->ekstrakDaftarItemApi($hasilPanggil['isi']);

                if ($daftarItemMentah !== []) {
                    $daftarTernormalisasi = [];
                    foreach (array_slice($daftarItemMentah, 0, $batasAman) as $itemMentah) {
                        if (is_array($itemMentah)) {
                            $daftarTernormalisasi[] = $this->normalisasiItemMitra(
                                $itemMentah,
                                $infoMitra,
                                $kotaPencarian
                            );
                        }
                    }

                    if ($daftarTernormalisasi !== []) {
                        return [
                            'kode_mitra'        => $kodeMitra,
                            'nama_layanan'      => $infoMitra['nama_layanan'],
                            'ikon'              => $infoMitra['ikon'],
                            'deskripsi_singkat' => $infoMitra['deskripsi_singkat'],
                            'url_tujuan'        => $urlTujuan,
                            'terhubung'         => true,
                            'status_kode'       => $statusKode,
                            'sumber_data'       => 'api_mitra',
                            'label_sumber'      => 'Live API Mitra',
                            'alasan_fallback'   => null,
                            'waktu_respon_ms'   => $waktuResponMs,
                            'daftar_item'       => $daftarTernormalisasi,
                        ];
                    }
                }

                $alasanFallback = 'API mitra merespons sukses tetapi tidak mengirimkan item data yang valid.';
            }
        } else {
            $alasanFallback = 'URL API mitra belum dikonfigurasi pada file .env.';
        }

        // Jalankan fallback data simulasi Sumatera Utara jika diizinkan
        if ($this->modeCadanganMock) {
            $daftarSimulasi = $this->ambilDataSimulasiSumut(
                $kodeMitra,
                $kotaPencarian,
                $kotaAsli,
                $batasAman
            );

            return [
                'kode_mitra'        => $kodeMitra,
                'nama_layanan'      => $infoMitra['nama_layanan'],
                'ikon'              => $infoMitra['ikon'],
                'deskripsi_singkat' => $infoMitra['deskripsi_singkat'],
                'url_tujuan'        => $urlTujuan,
                'terhubung'         => false,
                'status_kode'       => $statusKode,
                'sumber_data'       => 'simulasi_fallback',
                'label_sumber'      => 'Simulasi Fallback',
                'alasan_fallback'   => $alasanFallback,
                'waktu_respon_ms'   => $waktuResponMs,
                'daftar_item'       => $daftarSimulasi,
            ];
        }

        return [
            'kode_mitra'        => $kodeMitra,
            'nama_layanan'      => $infoMitra['nama_layanan'],
            'ikon'              => $infoMitra['ikon'],
            'deskripsi_singkat' => $infoMitra['deskripsi_singkat'],
            'url_tujuan'        => $urlTujuan,
            'terhubung'         => false,
            'status_kode'       => $statusKode,
            'sumber_data'       => 'tidak_tersedia',
            'label_sumber'      => 'Tidak Tersedia',
            'alasan_fallback'   => $alasanFallback,
            'waktu_respon_ms'   => $waktuResponMs,
            'daftar_item'       => [],
        ];
    }

    /**
     * Memeriksa status koneksi real-time ke seluruh 4 API kelompok mitra.
     * Digunakan oleh Dasbor Admin (Anggota 5) dan showcase integrasi dosen.
     *
     * @return list<array<string, mixed>>
     */
    public function cekStatusSemuaMitra(string $kotaUji = 'Medan'): array
    {
        $kotaPencarian = $this->bersihkanNamaKota($kotaUji);
        $daftarStatus  = [];

        foreach ($this->konfigurasiMitra as $kodeMitra => $infoMitra) {
            $urlDasar = $infoMitra['url_api'];
            $apiKey   = $infoMitra['api_key'] ?? '';

            if ($urlDasar === '') {
                $daftarStatus[] = [
                    'kode_mitra'      => $kodeMitra,
                    'nama_layanan'    => $infoMitra['nama_layanan'],
                    'url_api'         => '',
                    'terhubung'       => false,
                    'status_kode'     => 0,
                    'sumber_aktif'    => $this->modeCadanganMock ? 'simulasi_fallback' : 'tidak_tersedia',
                    'waktu_respon_ms' => 0,
                    'pesan_status'    => 'URL API belum diatur; memakai data simulasi fallback.',
                ];
                continue;
            }

            $batasUjiDetik = null;
            if ($kodeMitra === 'penginapan' && $this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
                $urlUji        = $this->ambilBaseUrlPenginapan($urlDasar) . '/kecamatan';
                $batasUjiDetik = max($this->batasWaktuDetik, 8);
            } elseif ($kodeMitra === 'transportasi' && ($apiKey !== '' || str_ends_with(rtrim($urlDasar, '/'), '/api'))) {
                $urlUji = $this->ambilBaseUrlTransportasi($urlDasar) . '/transport/lokasi';
            } else {
                $pemisahQuery = str_contains($urlDasar, '?') ? '&' : '?';
                $urlUji       = $urlDasar . $pemisahQuery . 'kota=' . urlencode($kotaPencarian);
            }

            $hasilPanggil = $this->panggilEndpointMitra($urlUji, $apiKey, $batasUjiDetik);

            if ($hasilPanggil['berhasil']) {
                $daftarStatus[] = [
                    'kode_mitra'      => $kodeMitra,
                    'nama_layanan'    => $infoMitra['nama_layanan'],
                    'url_api'         => $urlDasar,
                    'terhubung'       => true,
                    'status_kode'     => $hasilPanggil['status_kode'],
                    'sumber_aktif'    => 'api_mitra',
                    'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                    'pesan_status'    => 'Terhubung ke server REST API kelompok mitra.',
                ];
            } else {
                $daftarStatus[] = [
                    'kode_mitra'      => $kodeMitra,
                    'nama_layanan'    => $infoMitra['nama_layanan'],
                    'url_api'         => $urlDasar,
                    'terhubung'       => false,
                    'status_kode'     => $hasilPanggil['status_kode'],
                    'sumber_aktif'    => $this->modeCadanganMock ? 'simulasi_fallback' : 'tidak_tersedia',
                    'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                    'pesan_status'    => $hasilPanggil['pesan_error'] ?? 'Server mitra tidak merespons.',
                ];
            }
        }

        return $daftarStatus;
    }

    // =========================================================================
    // ADAPTER KHUSUS REST API MITRA TRANSPORTASI (/api/transport/*)
    // =========================================================================

    /**
     * Mengambil daftar titik simpul transportasi (Bandara, Stasiun, Terminal)
     * dari endpoint GET /api/transport/lokasi.
     *
     * @return array<string, mixed>
     */
    public function ambilDaftarLokasiTransportasi(): array
    {
        $infoTransport = $this->konfigurasiMitra['transportasi'] ?? [];
        $urlDasar      = trim((string) ($infoTransport['url_api'] ?? ''));
        $apiKey        = trim((string) ($infoTransport['api_key'] ?? ''));

        if ($urlDasar === '') {
            return [
                'berhasil'        => false,
                'terhubung'       => false,
                'status_kode'     => 0,
                'waktu_respon_ms' => 0,
                'data'            => [],
                'pesan_error'     => 'URL API mitra transportasi belum dikonfigurasi.',
            ];
        }

        $urlTujuan    = $this->ambilBaseUrlTransportasi($urlDasar) . '/transport/lokasi';
        $hasilPanggil = $this->panggilEndpointMitra($urlTujuan, $apiKey);

        if (! $hasilPanggil['berhasil']) {
            return [
                'berhasil'        => false,
                'terhubung'       => false,
                'status_kode'     => $hasilPanggil['status_kode'],
                'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                'data'            => [],
                'pesan_error'     => $hasilPanggil['pesan_error'],
            ];
        }

        $daftarMentah = $this->ekstrakDaftarItemApi($hasilPanggil['isi']);
        $daftarLokasi = [];
        $namaSudahAda = [];

        foreach ($daftarMentah as $barisLokasi) {
            if (! is_array($barisLokasi)) {
                continue;
            }

            $idLokasi   = (int) ($barisLokasi['id'] ?? 0);
            $namaLokasi = trim((string) ($barisLokasi['nama_lokasi'] ?? ''));
            $lintang    = (float) ($barisLokasi['latitude'] ?? 0);
            $bujur      = (float) ($barisLokasi['longitude'] ?? 0);

            if ($idLokasi <= 0 || $namaLokasi === '') {
                continue;
            }

            // Hilangkan entri duplikat dengan nama lokasi yang sama persis
            $kunciNama = strtolower($namaLokasi);
            if (isset($namaSudahAda[$kunciNama])) {
                continue;
            }
            $namaSudahAda[$kunciNama] = true;

            // Tandai simpul yang berada di wilayah Sumatera Utara
            $dalamSumut = ($lintang >= 0.5 && $lintang <= 4.5 && $bujur >= 97.0 && $bujur <= 100.5);

            $daftarLokasi[] = [
                'id'          => $idLokasi,
                'nama_lokasi' => $namaLokasi,
                'latitude'    => $lintang,
                'longitude'   => $bujur,
                'dalam_sumut' => $dalamSumut,
            ];
        }

        // Urutkan lokasi Sumatera Utara di bagian atas, lalu urut abjad A-Z
        usort($daftarLokasi, static function (array $lokasiA, array $lokasiB): int {
            if ($lokasiA['dalam_sumut'] !== $lokasiB['dalam_sumut']) {
                return $lokasiA['dalam_sumut'] ? -1 : 1;
            }

            return strcasecmp($lokasiA['nama_lokasi'], $lokasiB['nama_lokasi']);
        });

        return [
            'berhasil'        => $daftarLokasi !== [],
            'terhubung'       => true,
            'status_kode'     => $hasilPanggil['status_kode'],
            'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
            'data'            => $daftarLokasi,
            'pesan_error'     => $daftarLokasi === [] ? 'Daftar lokasi transportasi kosong.' : null,
        ];
    }

    /**
     * Menghitung jarak, jadwal aktif, dan estimasi tarif antar dua simpul transportasi
     * melalui endpoint GET /api/transport/tarif.
     *
     * @return array<string, mixed>
     */
    public function cekTarifTransportasi(int $idAsal, int $idTujuan, string $jam = '08:30'): array
    {
        $infoTransport = $this->konfigurasiMitra['transportasi'] ?? [];
        $urlDasar      = trim((string) ($infoTransport['url_api'] ?? ''));
        $apiKey        = trim((string) ($infoTransport['api_key'] ?? ''));

        if ($urlDasar === '') {
            return [
                'berhasil'             => false,
                'terhubung'            => false,
                'status_kode'          => 0,
                'waktu_respon_ms'      => 0,
                'rute'                 => null,
                'total_jadwal'         => 0,
                'pilihan_transportasi' => [],
                'pesan_error'          => 'URL API mitra transportasi belum dikonfigurasi.',
            ];
        }

        $queryTarif   = http_build_query([
            'id_asal'   => $idAsal,
            'id_tujuan' => $idTujuan,
            'jam'       => $jam,
        ]);
        $urlTujuan    = $this->ambilBaseUrlTransportasi($urlDasar) . '/transport/tarif?' . $queryTarif;
        $hasilPanggil = $this->panggilEndpointMitra($urlTujuan, $apiKey);

        if (! $hasilPanggil['berhasil']) {
            return [
                'berhasil'             => false,
                'terhubung'            => false,
                'status_kode'          => $hasilPanggil['status_kode'],
                'waktu_respon_ms'      => $hasilPanggil['waktu_respon_ms'],
                'rute'                 => null,
                'total_jadwal'         => 0,
                'pilihan_transportasi' => [],
                'pesan_error'          => $hasilPanggil['pesan_error'],
            ];
        }

        $dataRespon  = is_array($hasilPanggil['isi']['data'] ?? null) ? $hasilPanggil['isi']['data'] : [];
        $daftarOpsi  = is_array($dataRespon['pilihan_transportasi'] ?? null) ? $dataRespon['pilihan_transportasi'] : [];
        $totalJadwal = (int) ($dataRespon['total_jadwal_tersedia'] ?? count($daftarOpsi));

        return [
            'berhasil'             => true,
            'terhubung'            => true,
            'status_kode'          => $hasilPanggil['status_kode'],
            'waktu_respon_ms'      => $hasilPanggil['waktu_respon_ms'],
            'rute'                 => $dataRespon['rute'] ?? null,
            'total_jadwal'         => $totalJadwal,
            'pilihan_transportasi' => $daftarOpsi,
            'pesan_error'          => null,
        ];
    }

    /**
     * Mengintegrasikan endpoint /transport/lokasi, /transport/tarif, dan /transport/rekomendasi-wisata
     * menjadi daftar kartu rekomendasi + data form interaktif untuk halaman detail destinasi.
     *
     * @param array<string, string>     $infoMitra
     * @param array<string, mixed>|null $konteksDestinasi
     * @return array<string, mixed>
     */
    private function ambilRekomendasiTransportasiMitra(
        array $infoMitra,
        string $kotaPencarian,
        int $batasItem,
        ?array $konteksDestinasi
    ): array {
        $hasilLokasi = $this->ambilDaftarLokasiTransportasi();
        $baseUrl     = $this->ambilBaseUrlTransportasi($infoMitra['url_api']);
        $apiKey      = $infoMitra['api_key'] ?? '';

        if (! $hasilLokasi['berhasil'] || $hasilLokasi['data'] === []) {
            return [
                'berhasil'        => false,
                'url_tujuan'      => $baseUrl . '/transport/lokasi',
                'status_kode'     => $hasilLokasi['status_kode'],
                'waktu_respon_ms' => $hasilLokasi['waktu_respon_ms'],
                'alasan_fallback' => $hasilLokasi['pesan_error'] ?? 'Gagal mengambil daftar lokasi transportasi.',
            ];
        }

        $daftarLokasi = $hasilLokasi['data'];
        $lokasiSumut  = array_values(array_filter(
            $daftarLokasi,
            static fn (array $item): bool => ! empty($item['dalam_sumut'])
        ));
        if ($lokasiSumut === []) {
            $lokasiSumut = $daftarLokasi;
        }

        // Tentukan koordinat wisata dari data destinasi, atau cocokkan nama kota bila kosong
        [$wisataLat, $wisataLng] = $this->tentukanKoordinatWisata(
            $konteksDestinasi,
            $lokasiSumut,
            $kotaPencarian
        );

        // Cari simpul transportasi terdekat dari lokasi objek wisata
        $lokasiTujuanTerdekat = $this->cariLokasiTerdekat($lokasiSumut, $wisataLat, $wisataLng);
        $idTujuanDefault      = (int) ($lokasiTujuanTerdekat['id'] ?? $lokasiSumut[0]['id']);
        $jarakKeWisataKm      = (float) ($lokasiTujuanTerdekat['jarak_km'] ?? 0);

        // Pilih simpul asal utama di Medan (berbeda dari tujuan agar rute antar-kota valid)
        $lokasiAsalDefault = $this->pilihLokasiAsalDefault($lokasiSumut, $idTujuanDefault);
        $idAsalDefault     = (int) ($lokasiAsalDefault['id'] ?? $lokasiSumut[0]['id']);

        $waktuResponMs = (int) $hasilLokasi['waktu_respon_ms'];
        $daftarKartu   = [];
        $namaWisata    = trim((string) ($konteksDestinasi['nama'] ?? ('Wisata ' . $kotaPencarian)));

        // 1. Ambil jadwal & estimasi tarif langsung dari gerbang utama menuju simpul terdekat
        if ($idAsalDefault > 0 && $idTujuanDefault > 0 && $idAsalDefault !== $idTujuanDefault) {
            $hasilTarif = $this->cekTarifTransportasi($idAsalDefault, $idTujuanDefault, '08:30');
            $waktuResponMs = max($waktuResponMs, (int) $hasilTarif['waktu_respon_ms']);

            if ($hasilTarif['berhasil'] && $hasilTarif['pilihan_transportasi'] !== []) {
                $ruteInfo   = is_array($hasilTarif['rute']) ? $hasilTarif['rute'] : [];
                $namaAsal   = (string) ($ruteInfo['asal']['nama_lokasi'] ?? ($lokasiAsalDefault['nama_lokasi'] ?? 'Medan'));
                $namaTujuan = (string) ($ruteInfo['tujuan']['nama_lokasi'] ?? ($lokasiTujuanTerdekat['nama_lokasi'] ?? $kotaPencarian));
                $jarakKm    = (float) ($ruteInfo['jarak_km'] ?? 0);

                foreach ($hasilTarif['pilihan_transportasi'] as $opsi) {
                    if (! is_array($opsi) || count($daftarKartu) >= $batasItem) {
                        continue;
                    }

                    $daftarKartu[] = [
                        'nama'          => trim((string) ($opsi['penyedia'] ?? 'Armada Mitra')) . ' (' . $namaTujuan . ')',
                        'sub_label'     => trim((string) ($opsi['jenis_transport'] ?? 'Transportasi')),
                        'kota'          => $kotaPencarian,
                        'alamat'        => $namaAsal . ' → ' . $namaTujuan . ' (' . number_format($jarakKm, 1, ',', '.') . ' km)',
                        'harga'         => (int) ($opsi['estimasi_tarif'] ?? 0),
                        'satuan_harga'  => '/ perjalanan',
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 42,
                        'kontak'        => 'Jadwal aktif ' . trim((string) ($opsi['jam_berlaku'] ?? '06:00 - 21:00')),
                        'deskripsi'     => 'Tarif dihitung langsung dari REST API Transportasi (' . $jarakKm . ' km dari titik berangkat, jadwal 08:30).',
                        'foto'          => $this->ambilFotoBawaanMitra('transportasi'),
                        'url_eksternal' => null,
                    ];
                }
            }
        }

        // 2. Ambil rekomendasi rute cerdas dari dalam Sumut (Medan) menuju koordinat GPS wisata
        $queryRekomLokal = http_build_query([
            'user_lat'    => 3.5952,
            'user_lng'    => 98.6722,
            'wisata_lat'  => $wisataLat,
            'wisata_lng'  => $wisataLng,
            'nama_wisata' => $namaWisata,
            'trip_type'   => 'oneway',
        ]);
        $urlRekomLokal   = $baseUrl . '/transport/rekomendasi-wisata?' . $queryRekomLokal;
        $hasilRekomLokal = $this->panggilEndpointMitra($urlRekomLokal, $apiKey);
        $waktuResponMs   = max($waktuResponMs, (int) $hasilRekomLokal['waktu_respon_ms']);

        if ($hasilRekomLokal['berhasil']) {
            $kartuRekomLokal = $this->ubahRekomendasiWisataKeKartu(
                $hasilRekomLokal['isi'],
                $kotaPencarian,
                'Rute Dari Pusat Kota Medan'
            );
            foreach ($kartuRekomLokal as $kartu) {
                if (count($daftarKartu) >= $batasItem) {
                    break;
                }
                $daftarKartu[] = $kartu;
            }
        }

        // 3. Lengkapi dengan rekomendasi rute multi-moda luar Sumut (Jakarta CGK -> Sumut)
        if (count($daftarKartu) < $batasItem) {
            $queryRekomNasional = http_build_query([
                'user_lat'    => -6.1275,
                'user_lng'    => 106.6537,
                'wisata_lat'  => $wisataLat,
                'wisata_lng'  => $wisataLng,
                'nama_wisata' => $namaWisata,
                'trip_type'   => 'oneway',
            ]);
            $urlRekomNasional   = $baseUrl . '/transport/rekomendasi-wisata?' . $queryRekomNasional;
            $hasilRekomNasional = $this->panggilEndpointMitra($urlRekomNasional, $apiKey);
            $waktuResponMs      = max($waktuResponMs, (int) $hasilRekomNasional['waktu_respon_ms']);

            if ($hasilRekomNasional['berhasil']) {
                $kartuNasional = $this->ubahRekomendasiWisataKeKartu(
                    $hasilRekomNasional['isi'],
                    $kotaPencarian,
                    'Rute Udara + Darat (Dari Jakarta CGK)'
                );
                foreach ($kartuNasional as $kartu) {
                    if (count($daftarKartu) >= $batasItem) {
                        break;
                    }
                    $daftarKartu[] = $kartu;
                }
            }
        }

        if ($daftarKartu === []) {
            return [
                'berhasil'        => false,
                'url_tujuan'      => $urlRekomLokal,
                'status_kode'     => $hasilRekomLokal['status_kode'] ?? 0,
                'waktu_respon_ms' => $waktuResponMs,
                'alasan_fallback' => 'API mitra transportasi belum mengembalikan rekomendasi rute untuk koordinat ini.',
            ];
        }

        return [
            'berhasil' => true,
            'data'     => [
                'kode_mitra'        => 'transportasi',
                'nama_layanan'      => $infoMitra['nama_layanan'],
                'ikon'              => $infoMitra['ikon'],
                'deskripsi_singkat' => $infoMitra['deskripsi_singkat'],
                'url_tujuan'        => $urlRekomLokal,
                'endpoint_label'    => 'GET /api/transport/lokasi, /tarif & /rekomendasi-wisata',
                'terhubung'         => true,
                'status_kode'       => 200,
                'sumber_data'       => 'api_mitra',
                'label_sumber'      => 'Live API Mitra',
                'alasan_fallback'   => null,
                'waktu_respon_ms'   => $waktuResponMs,
                'daftar_item'       => array_slice($daftarKartu, 0, $batasItem),
                'data_transportasi' => [
                    'daftar_lokasi'        => $daftarLokasi,
                    'id_asal_default'      => $idAsalDefault,
                    'id_tujuan_default'    => $idTujuanDefault,
                    'nama_tujuan_terdekat' => (string) ($lokasiTujuanTerdekat['nama_lokasi'] ?? ''),
                    'jarak_ke_wisata_km'   => $jarakKeWisataKm,
                ],
            ],
        ];
    }

    /**
     * Mengubah hasil endpoint /api/transport/rekomendasi-wisata menjadi daftar kartu seragam.
     *
     * @param array<string, mixed> $isiJson
     * @return list<array<string, mixed>>
     */
    private function ubahRekomendasiWisataKeKartu(array $isiJson, string $kotaPencarian, string $labelAsal): array
    {
        $data = is_array($isiJson['data'] ?? null) ? $isiJson['data'] : [];
        $pergi = is_array($data['rute_perjalanan']['pergi'] ?? null) ? $data['rute_perjalanan']['pergi'] : [];

        if ($pergi === []) {
            return [];
        }

        $daftarKartu = [];
        foreach ($pergi as $segmen) {
            if (! is_array($segmen)) {
                continue;
            }

            $opsiPertama   = is_array($segmen['opsi_armada'][0] ?? null) ? $segmen['opsi_armada'][0] : [];
            $titikBerangkat = (string) ($segmen['titik_berangkat']['nama_lokasi'] ?? '');
            $titikTiba      = (string) ($segmen['titik_tiba']['nama_lokasi'] ?? '');
            $jarakKm        = (float) ($segmen['jarak_km'] ?? 0);
            $jenisSegmen    = trim((string) ($segmen['jenis_segmen'] ?? 'Transportasi'));
            $penyedia       = trim((string) ($opsiPertama['penyedia'] ?? $jenisSegmen));
            $jamBerlaku     = trim((string) ($opsiPertama['jam_berlaku'] ?? '06:00 - 21:00'));

            $daftarKartu[] = [
                'nama'          => $penyedia . ' — ' . $labelAsal,
                'sub_label'     => trim((string) ($opsiPertama['jenis_transport'] ?? $jenisSegmen)),
                'kota'          => $kotaPencarian,
                'alamat'        => $titikBerangkat . ' → ' . $titikTiba . ' (' . number_format($jarakKm, 1, ',', '.') . ' km)',
                'harga'         => (int) ($segmen['tarif_termurah'] ?? ($opsiPertama['estimasi_tarif'] ?? 0)),
                'satuan_harga'  => '/ perjalanan',
                'rating'        => 4.8,
                'jumlah_ulasan' => 56,
                'kontak'        => 'Jadwal aktif ' . $jamBerlaku,
                'deskripsi'     => (string) ($segmen['deskripsi'] ?? 'Rekomendasi rute menuju objek wisata.'),
                'foto'          => $this->ambilFotoBawaanMitra('transportasi'),
                'url_eksternal' => null,
            ];
        }

        return $daftarKartu;
    }

    /**
     * Menormalkan URL dasar API Transportasi agar selalu mengarah ke prefix /api.
     */
    private function ambilBaseUrlTransportasi(string $urlMentah): string
    {
        $urlBersih   = rtrim(trim($urlMentah), '/');
        $polaAkhiran = '#/transport/(lokasi|tarif|rekomendasi-wisata|transports)$#i';
        $urlBersih   = (string) preg_replace($polaAkhiran, '', $urlBersih);

        return rtrim($urlBersih, '/');
    }

    /**
     * Menghitung jarak garis lurus dua koordinat GPS dalam satuan kilometer (Haversine).
     */
    private function hitungJarakKm(float $lintang1, float $bujur1, float $lintang2, float $bujur2): float
    {
        $radiusBumiKm  = 6371.0;
        $selisihLat    = deg2rad($lintang2 - $lintang1);
        $selisihBujur  = deg2rad($bujur2 - $bujur1);

        $komponen = sin($selisihLat / 2) * sin($selisihLat / 2)
            + cos(deg2rad($lintang1)) * cos(deg2rad($lintang2))
            * sin($selisihBujur / 2) * sin($selisihBujur / 2);

        $sudut = 2 * atan2(sqrt($komponen), sqrt(1 - $komponen));

        return round($radiusBumiKm * $sudut, 2);
    }

    /**
     * Menentukan koordinat acuan wisata dari data destinasi atau pencarian nama kota.
     *
     * @param array<string, mixed>|null       $konteksDestinasi
     * @param list<array<string, mixed>>      $lokasiSumut
     * @return array{0: float, 1: float}
     */
    private function tentukanKoordinatWisata(
        ?array $konteksDestinasi,
        array $lokasiSumut,
        string $kotaPencarian
    ): array {
        if (is_array($konteksDestinasi)) {
            $koordinatBersarang = is_array($konteksDestinasi['koordinat'] ?? null)
                ? $konteksDestinasi['koordinat']
                : [];
            $lintang = (float) ($koordinatBersarang['garis_lintang'] ?? ($konteksDestinasi['garis_lintang'] ?? 0));
            $bujur   = (float) ($koordinatBersarang['garis_bujur'] ?? ($konteksDestinasi['garis_bujur'] ?? 0));

            if ($lintang !== 0.0 && $bujur !== 0.0) {
                return [$lintang, $bujur];
            }
        }

        $kataKota = strtolower($kotaPencarian);
        foreach ($lokasiSumut as $lokasi) {
            if (str_contains(strtolower($lokasi['nama_lokasi']), $kataKota)) {
                return [(float) $lokasi['latitude'], (float) $lokasi['longitude']];
            }
        }

        // Default acuan pusat Kota Medan jika koordinat destinasi belum tersedia
        return [3.5952, 98.6722];
    }

    /**
     * Mencari simpul transportasi (bandara/stasiun/terminal) terdekat dari koordinat wisata.
     *
     * @param list<array<string, mixed>> $daftarLokasi
     * @return array<string, mixed>
     */
    private function cariLokasiTerdekat(array $daftarLokasi, float $wisataLat, float $wisataLng): array
    {
        $lokasiTerdekat  = $daftarLokasi[0] ?? [];
        $jarakTerpendek  = INF;

        foreach ($daftarLokasi as $lokasi) {
            $jarak = $this->hitungJarakKm(
                $wisataLat,
                $wisataLng,
                (float) $lokasi['latitude'],
                (float) $lokasi['longitude']
            );

            if ($jarak < $jarakTerpendek) {
                $jarakTerpendek            = $jarak;
                $lokasiTerdekat            = $lokasi;
                $lokasiTerdekat['jarak_km'] = $jarak;
            }
        }

        return $lokasiTerdekat;
    }

    /**
     * Memilih simpul asal default di gerbang Medan yang tidak sama dengan titik tujuan.
     *
     * @param list<array<string, mixed>> $lokasiSumut
     * @return array<string, mixed>
     */
    private function pilihLokasiAsalDefault(array $lokasiSumut, int $idTujuan): array
    {
        $prioritasNama = [
            'Medan - Terminal Amplas',
            'Medan - Stasiun Medan (MDN)',
            'Medan - Bandara Kualanamu (KNO)',
        ];

        foreach ($prioritasNama as $namaCari) {
            foreach ($lokasiSumut as $lokasi) {
                if ($lokasi['nama_lokasi'] === $namaCari && (int) $lokasi['id'] !== $idTujuan) {
                    return $lokasi;
                }
            }
        }

        foreach ($lokasiSumut as $lokasi) {
            if ((int) $lokasi['id'] !== $idTujuan) {
                return $lokasi;
            }
        }

        return $lokasiSumut[0] ?? [];
    }

    // =========================================================================
    // ADAPTER KHUSUS REST API MITRA PENGINAPAN SINGGAH MEDAN (/api/hotels, /reviews)
    // =========================================================================

    /**
     * Mengambil daftar kecamatan dan jumlah hotel dari endpoint GET /api/kecamatan.
     *
     * @return array<string, mixed>
     */
    public function ambilDaftarKecamatanPenginapan(): array
    {
        $infoPenginapan = $this->konfigurasiMitra['penginapan'] ?? [];
        $urlDasar       = trim((string) ($infoPenginapan['url_api'] ?? ''));

        if (! $this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
            return [
                'berhasil'        => false,
                'terhubung'       => false,
                'status_kode'     => 0,
                'waktu_respon_ms' => 0,
                'data'            => [],
                'pesan_error'     => 'URL API mitra penginapan belum mengarah ke layanan Singgah Medan.',
            ];
        }

        $kunciCache = 'jejaksumut_singgah_medan_kecamatan_v1';
        $dataCache  = $this->ambilDariCacheSingkat($kunciCache);
        if (is_array($dataCache) && $dataCache !== []) {
            return [
                'berhasil'        => true,
                'terhubung'       => true,
                'status_kode'     => 200,
                'waktu_respon_ms' => 1,
                'data'            => $dataCache,
                'pesan_error'     => null,
            ];
        }

        $baseUrl      = $this->ambilBaseUrlPenginapan($urlDasar);
        $urlTujuan    = $baseUrl . '/kecamatan';
        $batasTimeout = max($this->batasWaktuDetik, 8);
        $hasilPanggil = $this->panggilEndpointMitra($urlTujuan, $infoPenginapan['api_key'] ?? '', $batasTimeout);

        if (! $hasilPanggil['berhasil']) {
            return [
                'berhasil'        => false,
                'terhubung'       => false,
                'status_kode'     => $hasilPanggil['status_kode'],
                'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                'data'            => [],
                'pesan_error'     => $hasilPanggil['pesan_error'],
            ];
        }

        $daftarMentah    = $this->ekstrakDaftarItemApi($hasilPanggil['isi']);
        $daftarKecamatan = [];

        foreach ($daftarMentah as $baris) {
            if (! is_array($baris)) {
                continue;
            }
            $namaKecamatan = trim((string) ($baris['kecamatan'] ?? ''));
            if ($namaKecamatan === '') {
                continue;
            }
            $daftarKecamatan[] = [
                'kecamatan' => $namaKecamatan,
                'jumlah'    => (int) ($baris['jumlah'] ?? 0),
            ];
        }

        if ($daftarKecamatan !== []) {
            $this->simpanKeCacheSingkat($kunciCache, $daftarKecamatan, 300);
        }

        return [
            'berhasil'        => $daftarKecamatan !== [],
            'terhubung'       => true,
            'status_kode'     => $hasilPanggil['status_kode'],
            'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
            'data'            => $daftarKecamatan,
            'pesan_error'     => $daftarKecamatan === [] ? 'Daftar kecamatan penginapan kosong.' : null,
        ];
    }

    /**
     * Mengambil katalog hotel dari API Singgah Medan dengan filter & paginasi,
     * serta fallback ke data simulasi Medan jika server mitra tidak aktif.
     *
     * @param array<string, mixed> $filter
     * @return array<string, mixed>
     */
    public function ambilKatalogPenginapan(array $filter = []): array
    {
        $infoPenginapan = $this->konfigurasiMitra['penginapan'] ?? [];
        $urlDasar       = trim((string) ($infoPenginapan['url_api'] ?? ''));
        $halaman        = max(1, (int) ($filter['halaman'] ?? 1));
        $batas          = max(1, min(24, (int) ($filter['batas'] ?? 12)));
        $kataKunci      = trim((string) ($filter['q'] ?? ''));
        $kecamatan      = trim((string) ($filter['kecamatan'] ?? ''));
        $sarapan        = strtolower(trim((string) ($filter['sarapan'] ?? '')));
        $ratingMin      = trim((string) ($filter['rating_min'] ?? ''));

        $parameterQuery = [
            'page'  => $halaman,
            'limit' => $batas,
        ];
        if ($kataKunci !== '') {
            $parameterQuery['q'] = $kataKunci;
        }
        if ($kecamatan !== '') {
            $parameterQuery['kecamatan'] = $kecamatan;
        }
        if ($sarapan === 'ya') {
            $parameterQuery['sarapan'] = 'ya';
        }
        if ($ratingMin !== '' && is_numeric($ratingMin)) {
            $parameterQuery['rating_min'] = (float) $ratingMin;
        }

        if ($this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
            $baseUrl      = $this->ambilBaseUrlPenginapan($urlDasar);
            $urlTujuan    = $baseUrl . '/hotels?' . http_build_query($parameterQuery);
            $batasTimeout = max($this->batasWaktuDetik, 8);
            $apakahDefault = ($kataKunci === '' && $kecamatan === '' && $sarapan === '' && $ratingMin === '' && $halaman === 1 && $batas === 12);

            if ($apakahDefault) {
                $cacheKatalog = $this->ambilDariCacheSingkat('jejaksumut_singgah_medan_katalog_p1_v1');
                if (is_array($cacheKatalog) && ! empty($cacheKatalog['daftar_hotel'])) {
                    return $cacheKatalog;
                }
            }

            $hasilPanggil = $this->panggilEndpointMitra($urlTujuan, $infoPenginapan['api_key'] ?? '', $batasTimeout);

            if ($hasilPanggil['berhasil']) {
                $isiJson      = $hasilPanggil['isi'];
                $daftarMentah = $this->ekstrakDaftarItemApi($isiJson);
                $daftarHotel  = [];

                foreach ($daftarMentah as $itemHotel) {
                    if (is_array($itemHotel)) {
                        $daftarHotel[] = $this->normalisasiHotelSinggahMedan($itemHotel, $baseUrl);
                    }
                }

                $metaMentah = is_array($isiJson['meta'] ?? null) ? $isiJson['meta'] : [];
                $meta = [
                    'page'        => max(1, (int) ($metaMentah['page'] ?? $halaman)),
                    'limit'       => max(1, (int) ($metaMentah['limit'] ?? $batas)),
                    'total'       => max(count($daftarHotel), (int) ($metaMentah['total'] ?? count($daftarHotel))),
                    'total_pages' => max(1, (int) ($metaMentah['total_pages'] ?? 1)),
                ];

                $hasilResmi = [
                    'berhasil'        => true,
                    'terhubung'       => true,
                    'sumber_data'     => 'api_mitra',
                    'status_kode'     => $hasilPanggil['status_kode'],
                    'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                    'meta'            => $meta,
                    'daftar_hotel'    => $daftarHotel,
                    'pesan_error'     => null,
                ];

                if ($apakahDefault && $daftarHotel !== []) {
                    $this->simpanKeCacheSingkat('jejaksumut_singgah_medan_katalog_p1_v1', $hasilResmi, 180);
                }

                return $hasilResmi;
            }

            $pesanErrorApi = $hasilPanggil['pesan_error'] ?? 'Gagal menghubungi layanan penginapan.';
        } else {
            $pesanErrorApi = 'URL API penginapan belum dikonfigurasi ke layanan mitra.';
        }

        // Fallback data simulasi jika API Singgah Medan tidak terjangkau
        if ($this->modeCadanganMock) {
            $dataSimulasi = $this->ambilDataSimulasiSumut('penginapan', 'Medan', 'Kota Medan', 12);
            $daftarHotelFallback = [];
            foreach ($dataSimulasi as $indeks => $itemMock) {
                $daftarHotelFallback[] = [
                    'id'                => $indeks + 1,
                    'nama'              => $itemMock['nama'],
                    'sub_label'         => $itemMock['sub_label'],
                    'kota'              => $itemMock['kota'],
                    'kecamatan'         => $itemMock['kota'],
                    'alamat'            => $itemMock['alamat'],
                    'latitude'          => 3.5952,
                    'longitude'         => 98.6722,
                    'jarak_km'          => null,
                    'rating'            => $itemMock['rating'],
                    'jumlah_ulasan'     => $itemMock['jumlah_ulasan'],
                    'ada_sarapan'       => true,
                    'teks_sarapan'      => 'Termasuk Sarapan',
                    'harga'             => $itemMock['harga'],
                    'satuan_harga'      => '/ malam',
                    'label_tarif'       => 'Estimasi Tarif',
                    'teks_harga_khusus' => '',
                    'kontak'            => $itemMock['kontak'],
                    'deskripsi'         => $itemMock['deskripsi'],
                    'foto'              => $itemMock['foto'],
                    'url_eksternal'     => null,
                    'tripadvisor_url'   => null,
                    'ulasan_unggulan'   => null,
                    'hotel_transit'     => false,
                ];
            }

            return [
                'berhasil'        => true,
                'terhubung'       => false,
                'sumber_data'     => 'simulasi_fallback',
                'status_kode'     => 0,
                'waktu_respon_ms' => 0,
                'meta'            => [
                    'page'        => 1,
                    'limit'       => 12,
                    'total'       => count($daftarHotelFallback),
                    'total_pages' => 1,
                ],
                'daftar_hotel'    => $daftarHotelFallback,
                'pesan_error'     => $pesanErrorApi,
            ];
        }

        return [
            'berhasil'        => false,
            'terhubung'       => false,
            'sumber_data'     => 'tidak_tersedia',
            'status_kode'     => 0,
            'waktu_respon_ms' => 0,
            'meta'            => ['page' => 1, 'limit' => $batas, 'total' => 0, 'total_pages' => 1],
            'daftar_hotel'    => [],
            'pesan_error'     => $pesanErrorApi,
        ];
    }

    /**
     * Mengambil daftar ulasan hotel langsung dari endpoint GET /api/reviews?hotel_id=...
     * (tanpa cache agar perubahan ulasan terlihat real-time).
     *
     * @return array<string, mixed>
     */
    public function ambilUlasanPenginapan(int $idHotel): array
    {
        $infoPenginapan = $this->konfigurasiMitra['penginapan'] ?? [];
        $urlDasar       = trim((string) ($infoPenginapan['url_api'] ?? ''));

        if (! $this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
            return [
                'berhasil'        => false,
                'status_kode'     => 503,
                'waktu_respon_ms' => 0,
                'data'            => [],
                'pesan_error'     => 'Layanan penginapan Singgah Medan belum dikonfigurasi.',
            ];
        }

        $urlTujuan    = $this->ambilBaseUrlPenginapan($urlDasar) . '/reviews?hotel_id=' . $idHotel;
        $hasilPanggil = $this->panggilEndpointMitra($urlTujuan, $infoPenginapan['api_key'] ?? '', 8);

        if (! $hasilPanggil['berhasil']) {
            return [
                'berhasil'        => false,
                'status_kode'     => $hasilPanggil['status_kode'],
                'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                'data'            => [],
                'pesan_error'     => $hasilPanggil['pesan_error'],
            ];
        }

        $daftarUlasan = $this->ekstrakDaftarItemApi($hasilPanggil['isi']);

        return [
            'berhasil'        => true,
            'status_kode'     => $hasilPanggil['status_kode'],
            'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
            'total'           => (int) ($hasilPanggil['isi']['total'] ?? count($daftarUlasan)),
            'data'            => $daftarUlasan,
            'pesan_error'     => null,
        ];
    }

    /**
     * Mengirim ulasan hotel baru ke endpoint POST /api/reviews milik Singgah Medan.
     *
     * @return array<string, mixed>
     */
    public function kirimUlasanPenginapan(int $idHotel, int $rating, string $judul, string $isi): array
    {
        $infoPenginapan = $this->konfigurasiMitra['penginapan'] ?? [];
        $urlDasar       = trim((string) ($infoPenginapan['url_api'] ?? ''));

        if (! $this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
            return [
                'berhasil'        => false,
                'status_kode'     => 503,
                'waktu_respon_ms' => 0,
                'data'            => null,
                'pesan_error'     => 'Layanan penginapan Singgah Medan belum dikonfigurasi.',
            ];
        }

        $urlTujuan = $this->ambilBaseUrlPenginapan($urlDasar) . '/reviews';
        $payload   = [
            'hotel_id' => $idHotel,
            'user_id'  => max(1, (int) env('PARTNER_HOTEL_DEFAULT_USER_ID', 2)),
            'rating'   => max(1, min(5, $rating)),
            'judul'    => trim($judul),
            'isi'      => trim($isi),
        ];

        $hasil = $this->kirimPermintaanJsonMitra('POST', $urlTujuan, $payload, $infoPenginapan['api_key'] ?? '', 8);

        return [
            'berhasil'        => $hasil['berhasil'],
            'status_kode'     => $hasil['status_kode'],
            'waktu_respon_ms' => $hasil['waktu_respon_ms'],
            'data'            => is_array($hasil['isi']['data'] ?? null) ? $hasil['isi']['data'] : null,
            'pesan'           => (string) ($hasil['isi']['message'] ?? $hasil['pesan_error'] ?? ''),
            'pesan_error'     => $hasil['pesan_error'],
        ];
    }

    /**
     * Menghapus ulasan hotel berdasarkan ID lewat endpoint DELETE /api/reviews/{id}.
     *
     * @return array<string, mixed>
     */
    public function hapusUlasanPenginapan(int $idUlasan): array
    {
        $infoPenginapan = $this->konfigurasiMitra['penginapan'] ?? [];
        $urlDasar       = trim((string) ($infoPenginapan['url_api'] ?? ''));

        if (! $this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
            return [
                'berhasil'        => false,
                'status_kode'     => 503,
                'waktu_respon_ms' => 0,
                'pesan_error'     => 'Layanan penginapan Singgah Medan belum dikonfigurasi.',
            ];
        }

        $urlTujuan = $this->ambilBaseUrlPenginapan($urlDasar) . '/reviews/' . $idUlasan;
        $hasil     = $this->kirimPermintaanJsonMitra('DELETE', $urlTujuan, [], $infoPenginapan['api_key'] ?? '', 8);

        return [
            'berhasil'        => $hasil['berhasil'],
            'status_kode'     => $hasil['status_kode'],
            'waktu_respon_ms' => $hasil['waktu_respon_ms'],
            'pesan'           => (string) ($hasil['isi']['message'] ?? $hasil['pesan_error'] ?? ''),
            'pesan_error'     => $hasil['pesan_error'],
        ];
    }

    /**
     * Memilih hotel terdekat (atau hotel transit Medan) dari API Singgah Medan
     * untuk ditampilkan pada tab Penginapan di halaman detail destinasi.
     *
     * @param array<string, string>     $infoMitra
     * @param array<string, mixed>|null $konteksDestinasi
     * @return array<string, mixed>
     */
    private function ambilRekomendasiPenginapanMitra(
        array $infoMitra,
        string $kotaPencarian,
        int $batasItem,
        ?array $konteksDestinasi
    ): array {
        $baseUrl      = $this->ambilBaseUrlPenginapan($infoMitra['url_api']);
        $apiKey       = $infoMitra['api_key'] ?? '';
        $batasTimeout = max($this->batasWaktuDetik, 8);
        $urlTujuan    = $baseUrl . '/hotels?limit=30&page=1';

        [$wisataLat, $wisataLng] = $this->tentukanKoordinatWisata($konteksDestinasi, [], $kotaPencarian);

        // Jika destinasi berada di kecamatan Medan yang dikenal Singgah Medan, utamakan filter kecamatan
        $daftarHotelMentah = [];
        $waktuResponMs     = 0;
        $alamatGabungan    = strtolower(
            trim((string) ($konteksDestinasi['alamat'] ?? '')) . ' ' . $kotaPencarian
        );
        $kecamatanCocok    = $this->deteksiKecamatanMedan($alamatGabungan);

        if ($kecamatanCocok !== null) {
            $urlKecamatan  = $baseUrl . '/hotels?' . http_build_query([
                'kecamatan' => $kecamatanCocok,
                'limit'     => 12,
                'page'      => 1,
            ]);
            $hasilKec      = $this->panggilEndpointMitra($urlKecamatan, $apiKey, $batasTimeout);
            $waktuResponMs = max($waktuResponMs, (int) $hasilKec['waktu_respon_ms']);

            if ($hasilKec['berhasil']) {
                $daftarHotelMentah = $this->ekstrakDaftarItemApi($hasilKec['isi']);
                $urlTujuan         = $urlKecamatan;
            }
        }

        // Ambil daftar 30 hotel dari cache singkat atau API bila filter kecamatan belum menghasilkan data
        if ($daftarHotelMentah === []) {
            $kunciCache   = 'jejaksumut_singgah_medan_hotels_30_v1';
            $cacheHotels  = $this->ambilDariCacheSingkat($kunciCache);

            if (is_array($cacheHotels) && $cacheHotels !== []) {
                $daftarHotelMentah = $cacheHotels;
                $waktuResponMs     = max($waktuResponMs, 1);
            } else {
                $hasilHotels   = $this->panggilEndpointMitra($urlTujuan, $apiKey, $batasTimeout);
                $waktuResponMs = max($waktuResponMs, (int) $hasilHotels['waktu_respon_ms']);

                if (! $hasilHotels['berhasil']) {
                    return [
                        'berhasil'        => false,
                        'url_tujuan'      => $urlTujuan,
                        'status_kode'     => $hasilHotels['status_kode'],
                        'waktu_respon_ms' => $waktuResponMs,
                        'alasan_fallback' => $hasilHotels['pesan_error'] ?? 'Gagal mengambil data hotel dari Singgah Medan.',
                    ];
                }

                $daftarHotelMentah = $this->ekstrakDaftarItemApi($hasilHotels['isi']);
                if ($daftarHotelMentah !== []) {
                    $this->simpanKeCacheSingkat($kunciCache, $daftarHotelMentah, 300);
                }
            }
        }

        if ($daftarHotelMentah === []) {
            return [
                'berhasil'        => false,
                'url_tujuan'      => $urlTujuan,
                'status_kode'     => 200,
                'waktu_respon_ms' => $waktuResponMs,
                'alasan_fallback' => 'Data penginapan dari mitra Singgah Medan kosong.',
            ];
        }

        // Hitung jarak dari koordinat destinasi ke masing-masing hotel lalu urutkan dari yang terdekat
        $daftarDenganJarak = [];
        foreach ($daftarHotelMentah as $itemHotel) {
            if (! is_array($itemHotel)) {
                continue;
            }
            $latHotel = (float) ($itemHotel['latitude'] ?? 0);
            $lngHotel = (float) ($itemHotel['longitude'] ?? 0);
            $jarakKm  = null;

            if ($latHotel !== 0.0 && $lngHotel !== 0.0) {
                $jarakKm = $this->hitungJarakKm($wisataLat, $wisataLng, $latHotel, $lngHotel);
            }

            $daftarDenganJarak[] = [
                'hotel'    => $itemHotel,
                'jarak_km' => $jarakKm ?? 9999.0,
                'jarak_asli' => $jarakKm,
            ];
        }

        usort($daftarDenganJarak, static function (array $barisA, array $barisB): int {
            return $barisA['jarak_km'] <=> $barisB['jarak_km'];
        });

        $jarakTerdekatKm = (float) ($daftarDenganJarak[0]['jarak_km'] ?? 9999.0);
        // Opsi A: Untuk wisata di luar radius 35 km dari Medan, tampilkan dengan keterangan Hotel Transit Medan
        $apakahTransit   = $jarakTerdekatKm > 35.0;

        $daftarKartu = [];
        foreach (array_slice($daftarDenganJarak, 0, $batasItem) as $entri) {
            $daftarKartu[] = $this->normalisasiHotelSinggahMedan(
                $entri['hotel'],
                $baseUrl,
                $entri['jarak_asli'],
                $apakahTransit
            );
        }

        return [
            'berhasil' => true,
            'data'     => [
                'kode_mitra'        => 'penginapan',
                'nama_layanan'      => $infoMitra['nama_layanan'],
                'ikon'              => $infoMitra['ikon'],
                'deskripsi_singkat' => $apakahTransit
                    ? 'Pilihan hotel transit di Kota Medan dari mitra Singgah Medan sebelum melanjutkan perjalanan ke ' . $kotaPencarian . '.'
                    : 'Pilihan hotel terdekat di sekitar lokasi ' . $kotaPencarian . ' dari mitra Singgah Medan.',
                'url_tujuan'        => $urlTujuan,
                'terhubung'         => true,
                'status_kode'       => 200,
                'sumber_data'       => 'api_mitra',
                'label_sumber'      => 'Live Mitra',
                'alasan_fallback'   => null,
                'waktu_respon_ms'   => $waktuResponMs,
                'daftar_item'       => $daftarKartu,
                'info_penginapan'   => [
                    'mode_transit'      => $apakahTransit,
                    'jarak_terdekat_km' => $jarakTerdekatKm < 9999.0 ? $jarakTerdekatKm : null,
                ],
            ],
        ];
    }

    /**
     * Memeriksa apakah konfigurasi URL mengarah ke API Singgah Medan.
     */
    private function apakahAdapterPenginapanSinggahMedan(string $urlDasar): bool
    {
        if ($urlDasar === '') {
            return false;
        }

        $urlRapi = rtrim($urlDasar, '/');

        return str_ends_with($urlRapi, '/api')
            || str_contains($urlRapi, '/api/hotels')
            || str_contains($urlRapi, 'ngrok');
    }

    /**
     * Menormalkan URL dasar API Singgah Medan agar berakhiran /api.
     */
    private function ambilBaseUrlPenginapan(string $urlMentah): string
    {
        $urlBersih   = rtrim(trim($urlMentah), '/');
        $polaAkhiran = '#/(hotels|kecamatan|reviews)$#i';
        $urlBersih   = (string) preg_replace($polaAkhiran, '', $urlBersih);

        return rtrim($urlBersih, '/');
    }

    /**
     * Menulis ulang URL penginapan agar tidak memakai localhost:8080 jika server diakses publik.
     */
    private function bangunUrlPublikPenginapan(string $baseUrlApi, ?string $urlMentah, ?string $slug): ?string
    {
        $komponenBase = parse_url($baseUrlApi);
        $skemaBase    = is_array($komponenBase) ? ($komponenBase['scheme'] ?? 'https') : 'https';
        $hostBase     = is_array($komponenBase) ? ($komponenBase['host'] ?? '') : '';
        $portBase     = (is_array($komponenBase) && isset($komponenBase['port'])) ? ':' . $komponenBase['port'] : '';
        $asalPublik   = $hostBase !== '' ? ($skemaBase . '://' . $hostBase . $portBase) : '';

        $urlTeks   = trim((string) $urlMentah);
        $slugTeks  = trim((string) $slug);

        if ($urlTeks !== '') {
            $komponenUrl = parse_url($urlTeks);
            $hostUrl     = is_array($komponenUrl) ? strtolower((string) ($komponenUrl['host'] ?? '')) : '';
            $apakahLokal = ($hostUrl === 'localhost' || $hostUrl === '127.0.0.1');

            if ($apakahLokal && $asalPublik !== '') {
                $jalur = is_array($komponenUrl) ? ($komponenUrl['path'] ?? '') : '';
                if ($jalur !== '') {
                    return $asalPublik . $jalur;
                }
            }

            if (! $apakahLokal && preg_match('/^https?:\/\//i', $urlTeks) === 1) {
                return $urlTeks;
            }
        }

        if ($asalPublik !== '' && $slugTeks !== '') {
            return $asalPublik . '/penginapan/' . rawurlencode($slugTeks);
        }

        return null;
    }

    /**
     * Menyeragamkan item hotel dari JSON Singgah Medan agar siap dipakai di Kartu & Katalog.
     *
     * @param array<string, mixed> $hotel
     * @return array<string, mixed>
     */
    private function normalisasiHotelSinggahMedan(
        array $hotel,
        string $baseUrlApi,
        ?float $jarakKm = null,
        bool $hotelTransit = false
    ): array {
        $idHotel     = (int) ($hotel['id'] ?? 0);
        $namaHotel   = trim((string) ($hotel['nama'] ?? 'Hotel Mitra Medan'));
        $slugHotel   = trim((string) ($hotel['slug'] ?? ''));
        $kota        = trim((string) ($hotel['kota'] ?? 'Medan'));
        $kecamatan   = trim((string) ($hotel['kecamatan'] ?? ''));
        $alamat      = trim((string) ($hotel['alamat'] ?? ($kecamatan !== '' ? $kecamatan . ', ' . $kota : $kota)));
        $rating      = round(max(0.0, min(5.0, (float) ($hotel['rating'] ?? 4.5))), 1);
        $ulasan      = max(0, (int) ($hotel['jumlah_ulasan'] ?? 0));
        $sarapanRaw  = strtolower(trim((string) ($hotel['sarapan'] ?? '')));
        $adaSarapan  = ($sarapanRaw === 'ya');
        $fotoMentah  = trim((string) ($hotel['foto'] ?? ''));
        $fotoAman    = (preg_match('/^https?:\/\//i', $fotoMentah) === 1) ? $fotoMentah : $this->ambilFotoBawaanMitra('penginapan');
        $urlPublik   = $this->bangunUrlPublikPenginapan($baseUrlApi, $hotel['url'] ?? null, $slugHotel);

        $tripadvisorRaw  = trim((string) ($hotel['tripadvisor_url'] ?? ''));
        $tripadvisorUrl  = (preg_match('/^https?:\/\//i', $tripadvisorRaw) === 1) ? $tripadvisorRaw : null;
        $ulasanUnggulan  = is_array($hotel['ulasan_unggulan'] ?? null) ? $hotel['ulasan_unggulan'] : null;

        if ($hotelTransit) {
            $subLabel = 'Hotel Transit Medan';
        } elseif ($kecamatan !== '') {
            $subLabel = $kecamatan;
        } else {
            $subLabel = 'Penginapan Medan';
        }

        $labelJarak = '';
        if ($jarakKm !== null) {
            $angkaJarak = number_format($jarakKm, 1, ',', '.');
            $labelJarak = $hotelTransit
                ? 'Sekitar ' . $angkaJarak . ' km (Transit Medan)'
                : $angkaJarak . ' km dari lokasi wisata';
        }

        $teksSarapan = $adaSarapan ? 'Sarapan tersedia' : 'Tanpa info sarapan';
        $kontakTeks  = $labelJarak !== '' ? ($labelJarak . ' • ' . $teksSarapan) : $teksSarapan;

        $deskripsi = $kecamatan !== ''
            ? 'Akomodasi di kawasan ' . $kecamatan . ', ' . $kota . ($adaSarapan ? ' dengan pilihan layanan sarapan.' : '.')
            : 'Pilihan penginapan nyaman di ' . $kota . ' dari mitra Singgah Medan.';

        if ($ulasanUnggulan !== null && ! empty($ulasanUnggulan['judul'])) {
            $deskripsi .= ' Ulasan pilihan: "' . trim((string) $ulasanUnggulan['judul']) . '".';
        }

        return [
            'id'                => $idHotel,
            'nama'              => $namaHotel,
            'slug'              => $slugHotel,
            'sub_label'         => $subLabel,
            'kota'              => $kecamatan !== '' ? $kecamatan . ', ' . $kota : $kota,
            'kecamatan'         => $kecamatan,
            'alamat'            => $alamat,
            'latitude'          => (float) ($hotel['latitude'] ?? 0),
            'longitude'         => (float) ($hotel['longitude'] ?? 0),
            'jarak_km'          => $jarakKm,
            'harga'             => 0,
            'satuan_harga'      => '',
            'label_tarif'       => 'Fasilitas & Tarif',
            'teks_harga_khusus' => $adaSarapan ? 'Termasuk Sarapan' : 'Cek Tarif di Mitra',
            'ada_sarapan'       => $adaSarapan,
            'teks_sarapan'      => $teksSarapan,
            'rating'            => $rating,
            'jumlah_ulasan'     => $ulasan,
            'kontak'            => $kontakTeks,
            'deskripsi'         => $deskripsi,
            'foto'              => $fotoAman,
            'url_eksternal'     => $urlPublik,
            'tripadvisor_url'   => $tripadvisorUrl,
            'ulasan_unggulan'   => $ulasanUnggulan,
            'hotel_transit'     => $hotelTransit,
        ];
    }

    /**
     * Mendeteksi nama kecamatan Medan dari alamat atau kota destinasi.
     */
    private function deteksiKecamatanMedan(string $teksLokasi): ?string
    {
        $daftarKecamatan = [
            'Medan Petisah', 'Medan Barat', 'Medan Kota', 'Medan Maimun',
            'Medan Baru', 'Medan Timur', 'Medan Selayang', 'Medan Sunggal',
            'Medan Helvetia', 'Medan Polonia', 'Medan Area', 'Medan Amplas',
            'Percut Sei Tuan', 'Beringin', 'Deli Tua', 'Namo Rambe', 'Batang Serangan',
        ];

        foreach ($daftarKecamatan as $namaKecamatan) {
            if (str_contains($teksLokasi, strtolower($namaKecamatan))) {
                return $namaKecamatan;
            }
        }

        return null;
    }

    // =========================================================================
    // HELPER TAMBAHAN UNTUK HALAMAN MANDIRI TRANSPORTASI & MITRA LAINNYA
    // =========================================================================

    /**
     * Mengambil rekomendasi rute multi-moda menuju koordinat wisata tertentu
     * dari endpoint GET /api/transport/rekomendasi-wisata.
     *
     * @return array<string, mixed>
     */
    public function ambilRekomendasiRuteTransportasi(
        float $userLat,
        float $userLng,
        float $wisataLat,
        float $wisataLng,
        string $namaWisata,
        string $tripType = 'oneway'
    ): array {
        $infoTransport = $this->konfigurasiMitra['transportasi'] ?? [];
        $urlDasar      = trim((string) ($infoTransport['url_api'] ?? ''));
        $apiKey        = trim((string) ($infoTransport['api_key'] ?? ''));

        if ($urlDasar === '') {
            return [
                'berhasil'        => false,
                'status_kode'     => 0,
                'waktu_respon_ms' => 0,
                'data'            => null,
                'pesan_error'     => 'URL API mitra transportasi belum dikonfigurasi.',
            ];
        }

        $query = http_build_query([
            'user_lat'    => $userLat,
            'user_lng'    => $userLng,
            'wisata_lat'  => $wisataLat,
            'wisata_lng'  => $wisataLng,
            'nama_wisata' => $namaWisata,
            'trip_type'   => $tripType === 'roundtrip' ? 'roundtrip' : 'oneway',
        ]);
        $urlTujuan    = $this->ambilBaseUrlTransportasi($urlDasar) . '/transport/rekomendasi-wisata?' . $query;
        $hasilPanggil = $this->panggilEndpointMitra($urlTujuan, $apiKey, max($this->batasWaktuDetik, 6));

        if (! $hasilPanggil['berhasil']) {
            return [
                'berhasil'        => false,
                'status_kode'     => $hasilPanggil['status_kode'],
                'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                'data'            => null,
                'pesan_error'     => $hasilPanggil['pesan_error'],
            ];
        }

        return [
            'berhasil'        => true,
            'status_kode'     => $hasilPanggil['status_kode'],
            'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
            'data'            => is_array($hasilPanggil['isi']['data'] ?? null) ? $hasilPanggil['isi']['data'] : [],
            'pesan_error'     => null,
        ];
    }

    /**
     * Mengambil katalog layanan Tur Guide & Kuliner di 6 klaster wilayah Sumut
     * untuk ditampilkan pada halaman /mitra-lainnya.
     *
     * @return array<string, mixed>
     */
    public function ambilKatalogMitraLainnya(?string $jenisLayanan = null, ?string $kodeKlaster = null): array
    {
        $labelKlaster = [
            'toba_samosir'    => 'Danau Toba & Samosir',
            'karo'            => 'Karo & Berastagi',
            'langkat'         => 'Langkat & Bukit Lawang',
            'medan_sekitar'   => 'Medan & Sekitar',
            'nias'            => 'Kepulauan Nias',
            'pesisir_selatan' => 'Pesisir Selatan & Tapanuli',
        ];

        $jenisAktif = trim((string) $jenisLayanan);
        if (! in_array($jenisAktif, ['tur_guide', 'kuliner'], true)) {
            $jenisAktif = 'semua';
        }

        $klasterAktif = trim((string) $kodeKlaster);
        if (! array_key_exists($klasterAktif, $labelKlaster)) {
            $klasterAktif = 'semua';
        }

        $semuaKlaster = $this->daftarDataSimulasiPerKlaster();
        $daftarItem   = [];

        foreach ($semuaKlaster as $kodeWilayah => $dataWilayah) {
            if ($klasterAktif !== 'semua' && $kodeWilayah !== $klasterAktif) {
                continue;
            }

            $daftarKodeTarget = $jenisAktif === 'semua' ? ['tur_guide', 'kuliner'] : [$jenisAktif];
            foreach ($daftarKodeTarget as $kodeMitra) {
                $infoMitra = $this->konfigurasiMitra[$kodeMitra] ?? null;
                $items     = $dataWilayah[$kodeMitra] ?? [];
                if ($infoMitra === null || ! is_array($items)) {
                    continue;
                }

                foreach ($items as $item) {
                    $daftarItem[] = [
                        'kode_mitra'    => $kodeMitra,
                        'nama_layanan'  => $infoMitra['nama_layanan'],
                        'kode_klaster'  => $kodeWilayah,
                        'nama_klaster'  => $labelKlaster[$kodeWilayah] ?? 'Sumatera Utara',
                        'nama'          => $item['nama'],
                        'sub_label'     => $item['sub_label'],
                        'kota'          => $item['kota'],
                        'alamat'        => $item['alamat'],
                        'harga'         => (int) $item['harga'],
                        'satuan_harga'  => $infoMitra['satuan_harga'],
                        'rating'        => (float) $item['rating'],
                        'jumlah_ulasan' => (int) $item['jumlah_ulasan'],
                        'kontak'        => $item['kontak'],
                        'deskripsi'     => $item['deskripsi'],
                        'foto'          => $item['foto'],
                    ];
                }
            }
        }

        return [
            'daftar_klaster' => $labelKlaster,
            'daftar_item'    => $daftarItem,
        ];
    }

    // =========================================================================
    // HELPER HTTP ADAPTER & NORMALISASI JSON MITRA
    // =========================================================================

    /**
     * Mengirim permintaan HTTP GET dengan timeout ketat ke endpoint kelompok mitra.
     *
     * @return array<string, mixed>
     */
    private function panggilEndpointMitra(string $urlTujuan, string $apiKey = '', ?int $batasWaktuKhusus = null): array
    {
        $waktuMulai       = microtime(true);
        $timeoutAktif     = $batasWaktuKhusus !== null ? max(1, min(15, $batasWaktuKhusus)) : $this->batasWaktuDetik;
        $headerPermintaan = ['Accept' => 'application/json'];

        if ($apiKey !== '') {
            $headerPermintaan['X-API-KEY'] = $apiKey;
        }
        if (str_contains($urlTujuan, 'ngrok')) {
            $headerPermintaan['ngrok-skip-browser-warning'] = 'true';
        }

        try {
            // Gunakan getShared = false agar instance cURL tiap mitra berdiri sendiri
            $klienHttp     = Services::curlrequest([], null, null, false);
            $respon        = $klienHttp->get($urlTujuan, [
                'timeout'         => $timeoutAktif,
                'connect_timeout' => $timeoutAktif,
                'http_errors'     => false,
                'headers'         => $headerPermintaan,
            ]);
            $waktuResponMs = (int) round((microtime(true) - $waktuMulai) * 1000);
            $statusKode    = $respon->getStatusCode();
            $isiJson       = json_decode((string) $respon->getBody(), true);

            if (! is_array($isiJson)) {
                return [
                    'berhasil'        => false,
                    'status_kode'     => $statusKode,
                    'isi'             => [],
                    'pesan_error'     => 'Server mitra tidak mengembalikan format JSON yang valid.',
                    'waktu_respon_ms' => $waktuResponMs,
                ];
            }

            if ($statusKode < 200 || $statusKode >= 300) {
                return [
                    'berhasil'        => false,
                    'status_kode'     => $statusKode,
                    'isi'             => $isiJson,
                    'pesan_error'     => 'Server mitra merespons dengan kode HTTP ' . $statusKode . '.',
                    'waktu_respon_ms' => $waktuResponMs,
                ];
            }

            return [
                'berhasil'        => true,
                'status_kode'     => $statusKode,
                'isi'             => $isiJson,
                'pesan_error'     => null,
                'waktu_respon_ms' => $waktuResponMs,
            ];
        } catch (Throwable $kesalahan) {
            $waktuResponMs = (int) round((microtime(true) - $waktuMulai) * 1000);

            return [
                'berhasil'        => false,
                'status_kode'     => 0,
                'isi'             => [],
                'pesan_error'     => 'Server mitra tidak aktif atau melewati batas waktu ' . $timeoutAktif . ' detik.',
                'waktu_respon_ms' => $waktuResponMs,
            ];
        }
    }

    /**
     * Mengirim permintaan HTTP POST atau DELETE berformat JSON ke endpoint kelompok mitra.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function kirimPermintaanJsonMitra(
        string $metode,
        string $urlTujuan,
        array $payload = [],
        string $apiKey = '',
        int $batasWaktuKhusus = 8
    ): array {
        $waktuMulai       = microtime(true);
        $metodeBersih     = strtoupper(trim($metode));
        $timeoutAktif     = max(1, min(15, $batasWaktuKhusus));
        $headerPermintaan = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($apiKey !== '') {
            $headerPermintaan['X-API-KEY'] = $apiKey;
        }
        if (str_contains($urlTujuan, 'ngrok')) {
            $headerPermintaan['ngrok-skip-browser-warning'] = 'true';
        }

        $opsiRequest = [
            'timeout'         => $timeoutAktif,
            'connect_timeout' => $timeoutAktif,
            'http_errors'     => false,
            'headers'         => $headerPermintaan,
        ];
        if ($payload !== []) {
            $opsiRequest['json'] = $payload;
        }

        try {
            $klienHttp     = Services::curlrequest([], null, null, false);
            $respon        = $klienHttp->request($metodeBersih, $urlTujuan, $opsiRequest);
            $waktuResponMs = (int) round((microtime(true) - $waktuMulai) * 1000);
            $statusKode    = $respon->getStatusCode();
            $isiJson       = json_decode((string) $respon->getBody(), true);

            if (! is_array($isiJson)) {
                return [
                    'berhasil'        => false,
                    'status_kode'     => $statusKode,
                    'isi'             => [],
                    'pesan_error'     => 'Server mitra tidak mengembalikan format JSON yang valid.',
                    'waktu_respon_ms' => $waktuResponMs,
                ];
            }

            if ($statusKode < 200 || $statusKode >= 300) {
                $pesanMitra = trim((string) ($isiJson['message'] ?? $isiJson['pesan'] ?? ''));

                return [
                    'berhasil'        => false,
                    'status_kode'     => $statusKode,
                    'isi'             => $isiJson,
                    'pesan_error'     => $pesanMitra !== '' ? $pesanMitra : ('Server mitra merespons dengan kode HTTP ' . $statusKode . '.'),
                    'waktu_respon_ms' => $waktuResponMs,
                ];
            }

            return [
                'berhasil'        => true,
                'status_kode'     => $statusKode,
                'isi'             => $isiJson,
                'pesan_error'     => null,
                'waktu_respon_ms' => $waktuResponMs,
            ];
        } catch (Throwable $kesalahan) {
            $waktuResponMs = (int) round((microtime(true) - $waktuMulai) * 1000);

            return [
                'berhasil'        => false,
                'status_kode'     => 0,
                'isi'             => [],
                'pesan_error'     => 'Koneksi ke server mitra terputus atau melewati batas waktu.',
                'waktu_respon_ms' => $waktuResponMs,
            ];
        }
    }

    /**
     * Mengambil data cache berumur singkat secara aman tanpa mengganggu alur halaman jika cache error.
     */
    private function ambilDariCacheSingkat(string $kunci): mixed
    {
        try {
            return cache($kunci);
        } catch (Throwable $kesalahan) {
            return null;
        }
    }

    /**
     * Menyimpan data ke cache berumur singkat (default 5 menit) secara aman.
     */
    private function simpanKeCacheSingkat(string $kunci, mixed $nilai, int $ttlDetik = 300): void
    {
        try {
            cache()->save($kunci, $nilai, $ttlDetik);
        } catch (Throwable $kesalahan) {
            // Abaikan kegagalan penulisan cache agar permintaan tetap berjalan normal
        }
    }

    /**
     * Mengekstrak array daftar item dari variasi pembungkus JSON kelompok mitra.
     *
     * @param array<string, mixed> $isiJson
     * @return list<mixed>
     */
    private function ekstrakDaftarItemApi(array $isiJson): array
    {
        // Kasus 1: Seluruh body JSON langsung berupa list item
        if (array_is_list($isiJson)) {
            return $isiJson;
        }

        // Kasus 2: Format standar { "data": [ ... ] } atau { "data": { "items": [ ... ] } }
        if (isset($isiJson['data']) && is_array($isiJson['data'])) {
            if (array_is_list($isiJson['data'])) {
                return $isiJson['data'];
            }
            if (isset($isiJson['data']['items']) && is_array($isiJson['data']['items'])) {
                return array_values($isiJson['data']['items']);
            }
        }

        // Kasus 3: Variasi key pembungkus lain dari kelompok mitra
        foreach (['items', 'results', 'hasil', 'daftar'] as $kunciDaftar) {
            if (isset($isiJson[$kunciDaftar]) && is_array($isiJson[$kunciDaftar])) {
                return array_values($isiJson[$kunciDaftar]);
            }
        }

        return [];
    }

    /**
     * Menyeragamkan field item JSON dari berbagai kelompok mitra agar selalu punya key konsisten.
     *
     * @param array<string, mixed> $itemMentah
     * @param array<string, string> $infoMitra
     * @return array<string, mixed>
     */
    private function normalisasiItemMitra(array $itemMentah, array $infoMitra, string $kotaDefault): array
    {
        $nama = (string) $this->ambilNilaiPertama(
            $itemMentah,
            ['nama', 'nama_layanan', 'nama_paket', 'nama_tempat', 'nama_hotel', 'nama_rute', 'judul', 'title', 'name'],
            $infoMitra['nama_layanan']
        );

        $subLabel = (string) $this->ambilNilaiPertama(
            $itemMentah,
            ['sub_label', 'kategori', 'sub_kategori', 'jenis', 'tipe', 'masakan', 'armada', 'spesialisasi'],
            $infoMitra['nama_layanan']
        );

        $kota = (string) $this->ambilNilaiPertama(
            $itemMentah,
            ['kota', 'kabupaten', 'nama_kabupaten_kota', 'lokasi', 'daerah', 'city'],
            $kotaDefault
        );

        $alamat = (string) $this->ambilNilaiPertama(
            $itemMentah,
            ['alamat', 'lokasi_lengkap', 'address', 'rute', 'jalur'],
            $kota . ', Sumatera Utara'
        );

        $hargaMentah = $this->ambilNilaiPertama(
            $itemMentah,
            ['harga', 'tarif', 'harga_mulai', 'harga_paket', 'harga_kamar', 'harga_tiket', 'rata_harga', 'price'],
            0
        );
        $harga = $this->parsingHargaKeInteger($hargaMentah);

        $satuanHarga = (string) $this->ambilNilaiPertama(
            $itemMentah,
            ['satuan_harga', 'satuan', 'unit'],
            $infoMitra['satuan_harga']
        );

        $ratingMentah  = $this->ambilNilaiPertama($itemMentah, ['rating', 'rata_rating', 'nilai_rating', 'skor'], 4.7);
        $rating        = round(max(0.0, min(5.0, (float) $ratingMentah)), 1);
        $jumlahUlasan  = max(0, (int) $this->ambilNilaiPertama($itemMentah, ['jumlah_ulasan', 'total_ulasan', 'review_count'], 24));
        $kontak        = (string) $this->ambilNilaiPertama($itemMentah, ['kontak', 'no_telp', 'telepon', 'whatsapp', 'no_hp', 'phone'], 'Hubungi pengelola lokal');
        $deskripsi     = (string) $this->ambilNilaiPertama($itemMentah, ['deskripsi', 'keterangan', 'catatan', 'description'], $infoMitra['deskripsi_singkat']);

        $fotoMentah = (string) $this->ambilNilaiPertama(
            $itemMentah,
            ['foto', 'foto_utama', 'url_foto', 'gambar', 'image', 'thumbnail'],
            ''
        );
        $foto = trim($fotoMentah) !== '' ? trim($fotoMentah) : $this->ambilFotoBawaanMitra($infoMitra['kode_mitra']);

        $urlEksternal = $this->ambilNilaiPertama($itemMentah, ['url_detail', 'url', 'tautan', 'link'], null);
        if (is_string($urlEksternal) && trim($urlEksternal) !== '') {
            $urlEksternalBersih = trim($urlEksternal);
        } else {
            $urlEksternalBersih = null;
        }

        return [
            'nama'          => trim($nama),
            'sub_label'     => trim($subLabel),
            'kota'          => trim($kota),
            'alamat'        => trim($alamat),
            'harga'         => $harga,
            'satuan_harga'  => trim($satuanHarga),
            'rating'        => $rating,
            'jumlah_ulasan' => $jumlahUlasan,
            'kontak'        => trim($kontak),
            'deskripsi'     => trim($deskripsi),
            'foto'          => $foto,
            'url_eksternal' => $urlEksternalBersih,
        ];
    }

    /**
     * Mengambil nilai pertama yang tersedia dan tidak kosong dari daftar kemungkinan key.
     *
     * @param array<string, mixed> $data
     * @param list<string>         $daftarKunci
     */
    private function ambilNilaiPertama(array $data, array $daftarKunci, mixed $nilaiBawaan): mixed
    {
        foreach ($daftarKunci as $kunci) {
            if (! array_key_exists($kunci, $data)) {
                continue;
            }

            $nilai = $data[$kunci];
            if ($nilai === null) {
                continue;
            }
            if (is_string($nilai) && trim($nilai) === '') {
                continue;
            }

            return $nilai;
        }

        return $nilaiBawaan;
    }

    /**
     * Mengubah nilai harga berformat string (contoh: "Rp 150.000") ataupun angka menjadi integer.
     */
    private function parsingHargaKeInteger(mixed $nilaiHarga): int
    {
        if (is_int($nilaiHarga) || is_float($nilaiHarga)) {
            return max(0, (int) round((float) $nilaiHarga));
        }

        if (is_string($nilaiHarga)) {
            $hanyaAngka = preg_replace('/[^0-9]/', '', $nilaiHarga);
            if ($hanyaAngka !== null && $hanyaAngka !== '') {
                return max(0, (int) $hanyaAngka);
            }
        }

        return 0;
    }

    /**
     * Mengubah nilai dari .env menjadi tipe boolean secara aman.
     */
    private function ubahKeBoolean(mixed $nilai, bool $nilaiBawaan): bool
    {
        if (is_bool($nilai)) {
            return $nilai;
        }

        if (is_string($nilai)) {
            $teks = strtolower(trim($nilai));
            if (in_array($teks, ['true', '1', 'yes', 'on', 'ya'], true)) {
                return true;
            }
            if (in_array($teks, ['false', '0', 'no', 'off', 'tidak'], true)) {
                return false;
            }
        }

        if (is_int($nilai)) {
            return $nilai !== 0;
        }

        return $nilaiBawaan;
    }

    /**
     * Mengembalikan URL foto nyata default apabila item API mitra tidak menyertakan foto.
     */
    private function ambilFotoBawaanMitra(string $kodeMitra): string
    {
        $petaFoto = [
            'tur_guide'    => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
            'kuliner'      => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
            'penginapan'   => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
            'transportasi' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
        ];

        return $petaFoto[$kodeMitra] ?? $petaFoto['tur_guide'];
    }

    // =========================================================================
    // DATA SIMULASI FALLBACK KHAS SUMATERA UTARA (33 KABUPATEN / KOTA)
    // =========================================================================

    /**
     * Memetakan 33 kabupaten/kota di Sumatera Utara ke 6 klaster geografis wisata.
     */
    private function tentukanKlasterWilayah(string $kotaPencarian): string
    {
        $teks = strtolower($kotaPencarian);

        $daftarKlaster = [
            'toba_samosir' => [
                'samosir', 'toba', 'simalungun', 'tapanuli utara',
                'humbang hasundutan', 'dairi', 'pakpak bharat', 'parapat', 'balige',
            ],
            'karo' => [
                'karo', 'berastagi', 'kabanjahe',
            ],
            'langkat' => [
                'langkat', 'bukit lawang', 'tangkahan', 'stabat',
            ],
            'nias' => [
                'nias', 'nias selatan', 'nias utara', 'nias barat', 'gunungsitoli', 'sorake',
            ],
            'pesisir_selatan' => [
                'tapanuli tengah', 'sibolga', 'tapanuli selatan', 'padangsidimpuan',
                'mandailing natal', 'padang lawas', 'padang lawas utara',
                'asahan', 'tanjungbalai', 'labuhanbatu', 'labuhanbatu utara', 'labuhanbatu selatan',
            ],
        ];

        foreach ($daftarKlaster as $namaKlaster => $daftarKataKunci) {
            foreach ($daftarKataKunci as $kataKunci) {
                if (str_contains($teks, $kataKunci)) {
                    return $namaKlaster;
                }
            }
        }

        return 'medan_sekitar';
    }

    /**
     * Menyediakan data simulasi Sumatera Utara yang realistis saat API mitra belum aktif.
     *
     * @return list<array<string, mixed>>
     */
    private function ambilDataSimulasiSumut(
        string $kodeMitra,
        string $kotaPencarian,
        string $kotaAsli,
        int $batasItem
    ): array {
        $klaster      = $this->tentukanKlasterWilayah($kotaPencarian);
        $semuaData    = $this->daftarDataSimulasiPerKlaster();
        $dataKlaster  = $semuaData[$klaster] ?? $semuaData['medan_sekitar'];
        $daftarItem   = $dataKlaster[$kodeMitra] ?? [];
        $infoMitra    = $this->konfigurasiMitra[$kodeMitra];

        $hasilTerformat = [];
        foreach (array_slice($daftarItem, 0, $batasItem) as $item) {
            $hasilTerformat[] = [
                'nama'          => $item['nama'],
                'sub_label'     => $item['sub_label'],
                'kota'          => $item['kota'] ?? $kotaPencarian,
                'alamat'        => $item['alamat'] . ' (Area layanan sekitar ' . $kotaAsli . ')',
                'harga'         => (int) $item['harga'],
                'satuan_harga'  => $infoMitra['satuan_harga'],
                'rating'        => (float) $item['rating'],
                'jumlah_ulasan' => (int) $item['jumlah_ulasan'],
                'kontak'        => $item['kontak'],
                'deskripsi'     => $item['deskripsi'],
                'foto'          => $item['foto'],
                'url_eksternal' => null,
            ];
        }

        return $hasilTerformat;
    }

    /**
     * Kumpulan katalog simulasi realistis untuk 6 klaster wilayah Sumatera Utara.
     *
     * @return array<string, array<string, list<array<string, mixed>>>>
     */
    private function daftarDataSimulasiPerKlaster(): array
    {
        return [
            // 1. Klaster Kawasan Kaldera Danau Toba & Dataran Tinggi Barat
            'toba_samosir' => [
                'tur_guide' => [
                    [
                        'nama'          => 'Pemandu Kaldera Samosir & Bukit Holbung',
                        'sub_label'     => 'Tur Alam & Budaya Batak',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Jl. Lingkar Tuk-tuk Siadong, Simanindo, Samosir',
                        'harga'         => 275000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 148,
                        'kontak'        => '0812-6500-4120',
                        'deskripsi'     => 'Pendampingan rute Bukit Holbung, Batu Kursi Siallagan, hingga Desa Adat Tomok (durasi 6 jam).',
                        'foto'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Jelajah Warisan Pusuk Buhit & Air Terjun Efrata',
                        'sub_label'     => 'Trekking Geopark',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Kec. Pangururan, Kabupaten Samosir',
                        'harga'         => 320000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 94,
                        'kontak'        => '0813-7712-9034',
                        'deskripsi'     => 'Dipandu anggota HPI Sumatera Utara dengan pengenalan sejarah geologi kaldera Toba.',
                        'foto'          => 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Tur Budaya Lumban Binanga & Museum TB Silalahi',
                        'sub_label'     => 'Tur Edukasi Sejarah',
                        'kota'          => 'Toba',
                        'alamat'        => 'Jl. Pagar Batu No. 88, Balige, Toba',
                        'harga'         => 225000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 76,
                        'kontak'        => '0852-6109-3318',
                        'deskripsi'     => 'Paket setengah hari mengunjungi tenun Ulos tradisional dan museum sejarah Batak di Balige.',
                        'foto'          => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'kuliner' => [
                    [
                        'nama'          => 'RM Khas Batak Tuktuk Siadong (Naniura & Arsik)',
                        'sub_label'     => 'Masakan Khas Toba',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Semenanjung Tuk-tuk No. 14, Simanindo',
                        'harga'         => 48000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 312,
                        'kontak'        => '0812-6388-1904',
                        'deskripsi'     => 'Menyajikan ikan mas arsik andaliman, naniura segar, dan sambal tuk-tuk rumahan.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kedai Kopi & Bika Toba Dermaga Tiga Raja',
                        'sub_label'     => 'Kopi Lintong & Camilan',
                        'kota'          => 'Simalungun',
                        'alamat'        => 'Jl. Sisingamangaraja, Parapat, Simalungun',
                        'harga'         => 28000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 185,
                        'kontak'        => '0821-6014-8822',
                        'deskripsi'     => 'Seduhan manual kopi arabika Lintong & Samosir lengkap dengan pemandangan tepi danau.',
                        'foto'          => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Pondok Bakar Ikan Mujaer & Sambal Andaliman Halal',
                        'sub_label'     => 'Hidangan Keluarga Halal',
                        'kota'          => 'Toba',
                        'alamat'        => 'Pantai Lumban Bul-bul, Balige',
                        'harga'         => 42000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 240,
                        'kontak'        => '0813-9655-2210',
                        'deskripsi'     => 'Ikan nila dan mujair tangkapan Danau Toba dibakar dengan bumbu kemiri dan perasan jeruk.',
                        'foto'          => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'penginapan' => [
                    [
                        'nama'          => 'Rumah Bolon View Homestay Tuk-tuk',
                        'sub_label'     => 'Homestay Tepi Danau',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Jl. Lingkar Tuk-tuk, Simanindo, Samosir',
                        'harga'         => 340000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 205,
                        'kontak'        => '0811-6340-771',
                        'deskripsi'     => 'Kamar beranda menghadap langsung perairan Danau Toba lengkap dengan sarapan pagi.',
                        'foto'          => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Samosir Cottage & Dermaga Kayu',
                        'sub_label'     => 'Cottage Keluarga',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Desa Unok, Tuk-tuk Siadong, Samosir',
                        'harga'         => 475000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 176,
                        'kontak'        => '0812-6911-5502',
                        'deskripsi'     => 'Penginapan arsitektur Batak modern dengan akses dermaga langsung untuk berenang.',
                        'foto'          => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Parapat Hills Boutique Inn',
                        'sub_label'     => 'Hotel Panorama Bukit',
                        'kota'          => 'Simalungun',
                        'alamat'        => 'Jl. Kol. TPR Sinaga, Parapat',
                        'harga'         => 410000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 132,
                        'kontak'        => '(0625) 41289',
                        'deskripsi'     => 'Lokasi 5 menit dari Pelabuhan Ajibata dan Tiga Raja dengan area parkir luas.',
                        'foto'          => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'transportasi' => [
                    [
                        'nama'          => 'Feri & Kapal Penyeberangan Ajibata – Ambarita',
                        'sub_label'     => 'Penyeberangan Danau',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Pelabuhan Ajibata (Toba) ↔ Pelabuhan Ambarita (Samosir)',
                        'harga'         => 25000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 410,
                        'kontak'        => '0812-6077-8811',
                        'deskripsi'     => 'Jadwal penyeberangan reguler setiap 1 jam mulai 07.00 – 19.00 WIB.',
                        'foto'          => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Travel Eksekutif Medan / Kualanamu – Parapat',
                        'sub_label'     => 'Shuttle Tol Sinaksak',
                        'kota'          => 'Simalungun',
                        'alamat'        => 'Koridor Tol Medan – Tebing Tinggi – Sinaksak – Parapat',
                        'harga'         => 140000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 268,
                        'kontak'        => '0811-6180-990',
                        'deskripsi'     => 'Armada HiAce ber-AC dengan estimasi tempuh 3 jam via Tol Sinaksak.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Rental Skuter & Mobil Keliling Pulau Samosir',
                        'sub_label'     => 'Sewa Kendaraan Harian',
                        'kota'          => 'Samosir',
                        'alamat'        => 'Pelabuhan Tomok & Tuk-tuk, Samosir',
                        'harga'         => 110000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 159,
                        'kontak'        => '0853-7022-4490',
                        'deskripsi'     => 'Tersedia motor matik terawat dan city car lepas kunci atau dengan pengemudi lokal.',
                        'foto'          => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
            ],

            // 2. Klaster Dataran Tinggi Karo (Berastagi, Kabanjahe, Sipiso-piso)
            'karo' => [
                'tur_guide' => [
                    [
                        'nama'          => 'Ranger Pendakian Sunrise Gunung Sibayak',
                        'sub_label'     => 'Pemandu Gunung Berapi',
                        'kota'          => 'Karo',
                        'alamat'        => 'Pos Pendakian Raja Berneh, Semangat Gunung, Karo',
                        'harga'         => 250000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 214,
                        'kontak'        => '0813-6211-4470',
                        'deskripsi'     => 'Pendakian fajar aman melewati kawah belerang hingga puncak Sibayak plus kunjungan pemandian air panas.',
                        'foto'          => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Tur Budaya Desa Adat Dokan & Tongging',
                        'sub_label'     => 'Eksplorasi Budaya Karo',
                        'kota'          => 'Karo',
                        'alamat'        => 'Kec. Merek, Kabupaten Karo',
                        'harga'         => 230000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 88,
                        'kontak'        => '0812-6994-1122',
                        'deskripsi'     => 'Mengunjungi rumah adat Siwaluh Jabu berusia ratusan tahun dan Air Terjun Sipiso-piso.',
                        'foto'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Agrowisata Kebun Jeruk & Stroberi Gundaling',
                        'sub_label'     => 'Wisata Edukasi Keluarga',
                        'kota'          => 'Karo',
                        'alamat'        => 'Bukit Gundaling, Berastagi, Karo',
                        'harga'         => 150000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 119,
                        'kontak'        => '0852-7533-8012',
                        'deskripsi'     => 'Paket petik buah langsung bersama petani dataran tinggi Karo dan tur Taman Alam Lumbini.',
                        'foto'          => 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'kuliner' => [
                    [
                        'nama'          => 'Kuliner Malam Pasar Buah Berastagi (Jagung & Bandrek)',
                        'sub_label'     => 'Jajanan Hangat Dataran Tinggi',
                        'kota'          => 'Karo',
                        'alamat'        => 'Jl. Veteran, Berastagi, Kabupaten Karo',
                        'harga'         => 25000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 380,
                        'kontak'        => '0812-6445-7701',
                        'deskripsi'     => 'Susu jahe hangat, bandrek Karo, dan jagung bakar manis di tengah udara sejuk 17°C.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Rumah Makan Tasak Telu & Cipera Khas Karo',
                        'sub_label'     => 'Kuliner Tradisional Karo',
                        'kota'          => 'Karo',
                        'alamat'        => 'Jl. Jamin Ginting Km 65, Berastagi',
                        'harga'         => 55000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 195,
                        'kontak'        => '0813-7088-5120',
                        'deskripsi'     => 'Ayam kampung cipera kuah jagung tumbuk khas Karo yang cocok menghangatkan tubuh.',
                        'foto'          => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kafe Kebun Kopi Sinabung & Teh Markisa',
                        'sub_label'     => 'Kedai Kopi Panorama',
                        'kota'          => 'Karo',
                        'alamat'        => 'Jalan menuju Bukit Kubu, Berastagi',
                        'harga'         => 32000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 142,
                        'kontak'        => '0822-7744-9088',
                        'deskripsi'     => 'Arabika single origin lereng Gunung Sinabung serta sirup markisa asli Berastagi.',
                        'foto'          => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'penginapan' => [
                    [
                        'nama'          => 'Gundaling Pine Cabin & Villa Berastagi',
                        'sub_label'     => 'Kabin Hutan Pinus',
                        'kota'          => 'Karo',
                        'alamat'        => 'Jl. Letjen Jamin Ginting, Bukit Gundaling, Berastagi',
                        'harga'         => 485000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 190,
                        'kontak'        => '0811-6290-334',
                        'deskripsi'     => 'Penginapan bernuansa kayu pinus dengan pemandangan Gunung Sibayak dan perapian.',
                        'foto'          => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Mikie Highland Resort Area Homestay',
                        'sub_label'     => 'Penginapan Keluarga',
                        'kota'          => 'Karo',
                        'alamat'        => 'Jl. Jamin Ginting Km 60, Sempajaya, Berastagi',
                        'harga'         => 390000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 144,
                        'kontak'        => '(0628) 91550',
                        'deskripsi'     => 'Dekat wahana wisata keluarga Berastagi dan memiliki halaman taman yang luas.',
                        'foto'          => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Tongging Kaldera Eco Lodge',
                        'sub_label'     => 'Lodge Tepi Tebing Sipiso-piso',
                        'kota'          => 'Karo',
                        'alamat'        => 'Desa Tongging, Kec. Merek, Karo',
                        'harga'         => 350000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 112,
                        'kontak'        => '0813-7599-4412',
                        'deskripsi'     => 'Akses langsung ke lembah Tongging dan jalur trekking menuju dasar Air Terjun Sipiso-piso.',
                        'foto'          => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'transportasi' => [
                    [
                        'nama'          => 'Angkutan & Travel Medan (Padang Bulan) – Berastagi',
                        'sub_label'     => 'Rute Pegunungan Reguler',
                        'kota'          => 'Karo',
                        'alamat'        => 'Simpang Pos Padang Bulan ↔ Terminal Berastagi',
                        'harga'         => 35000,
                        'rating'        => 4.6,
                        'jumlah_ulasan' => 320,
                        'kontak'        => '0812-6554-1109',
                        'deskripsi'     => 'Keberangkatan setiap 20 menit melewati jalan berkelok Sibolangit menuju Berastagi.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Sewa Jeep & HiAce Wisata Berastagi – Tongging',
                        'sub_label'     => 'Carter Rute Sipiso-piso',
                        'kota'          => 'Karo',
                        'alamat'        => 'Tugu Kol Berastagi, Kabupaten Karo',
                        'harga'         => 450000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 87,
                        'kontak'        => '0852-6211-8890',
                        'deskripsi'     => 'Paket perjalanan pulang pergi Berastagi – Taman Lumbini – Air Terjun Sipiso-piso.',
                        'foto'          => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Shuttle Bandara Kualanamu – Berastagi',
                        'sub_label'     => 'Airport Transfer Langsung',
                        'kota'          => 'Karo',
                        'alamat'        => 'Bandara Kualanamu – Simpang Amplas – Berastagi',
                        'harga'         => 130000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 118,
                        'kontak'        => '0811-6092-773',
                        'deskripsi'     => 'Penjemputan langsung dari pintu kedatangan Kualanamu dengan bagasi luas.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
            ],

            // 3. Klaster Ekowisata Langkat (Bukit Lawang, Tangkahan, Taman Nasional Gunung Leuser)
            'langkat' => [
                'tur_guide' => [
                    [
                        'nama'          => 'Leuser Wildlife Tracker (Orangutan Trekking)',
                        'sub_label'     => 'Pemandu Taman Nasional',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Pos Informasi Wisata Bukit Lawang, Bahorok, Langkat',
                        'harga'         => 450000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 278,
                        'kontak'        => '0812-6033-5590',
                        'deskripsi'     => 'Trekking resmi 5 jam menjelajahi hutan hujan Gunung Leuser bersama pemandu berlisensi HPI.',
                        'foto'          => 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Tangkahan Eco-Adventure & River Tubing Guide',
                        'sub_label'     => 'Ekowisata Sungai Buluh',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Desa Namo Sialang, Batang Serangan, Langkat',
                        'harga'         => 300000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 162,
                        'kontak'        => '0813-7044-8210',
                        'deskripsi'     => 'Pendampingan susur sungai Buluh dengan ban karet dan tur edukasi konservasi satwa Tangkahan.',
                        'foto'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Eksplorasi Gua Kampret & Kebun Karet Bahorok',
                        'sub_label'     => 'Tur Alam Setengah Hari',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Desa Perkebunan Bukit Lawang, Langkat',
                        'harga'         => 185000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 79,
                        'kontak'        => '0852-7761-3302',
                        'deskripsi'     => 'Kunjungan sore melihat ekosistem kelelawar di Gua Kampret dan pemandangan lembah Bahorok.',
                        'foto'          => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'kuliner' => [
                    [
                        'nama'          => 'Warung Tepi Sungai Bohorok (Ikan Jurung & Gado-gado)',
                        'sub_label'     => 'Masakan Tepi Sungai',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Jembatan Gantung Bukit Lawang, Langkat',
                        'harga'         => 45000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 230,
                        'kontak'        => '0812-6709-2145',
                        'deskripsi'     => 'Hidangan ikan sungai segar, nasi goreng rempah, dan jus buah tropis di teras bambu tepi sungai.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kedai Anyang Pakis & Gulai Masam Khas Melayu Langkat',
                        'sub_label'     => 'Kuliner Melayu Deli-Langkat',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Jl. Proklamasi, Stabat, Kabupaten Langkat',
                        'harga'         => 35000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 164,
                        'kontak'        => '0813-6120-9940',
                        'deskripsi'     => 'Olah pucuk pakis tumbuk kelapa sangrai serta gulai asam manis khas Melayu pesisir Langkat.',
                        'foto'          => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Pondok Kopi Rimba Tangkahan',
                        'sub_label'     => 'Kafe Hutan Hujan',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Kawasan Jembatan Gantung Tangkahan, Langkat',
                        'harga'         => 26000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 98,
                        'kontak'        => '0821-6790-3321',
                        'deskripsi'     => 'Kopi tubruk organik petani Langkat dan pisang goreng gula aren setelah aktivitas river tubing.',
                        'foto'          => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'penginapan' => [
                    [
                        'nama'          => 'Bohorok Bamboo Ecolodge',
                        'sub_label'     => 'Ecolodge Ramah Alam',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Tepi Sungai Bohorok, Bukit Lawang, Langkat',
                        'harga'         => 375000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 255,
                        'kontak'        => '0811-6520-118',
                        'deskripsi'     => 'Kamar bambu ramah lingkungan dengan suara aliran sungai langsung di depan teras kamar.',
                        'foto'          => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Tangkahan Jungle Riverside Inn',
                        'sub_label'     => 'Penginapan Tepi Hutan',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Desa Namo Sialang, Tangkahan, Langkat',
                        'harga'         => 340000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 137,
                        'kontak'        => '0812-6301-8840',
                        'deskripsi'     => 'Berjarak 100 meter dari gerbang ekowisata Tangkahan dengan area bersantai semi-terbuka.',
                        'foto'          => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Bahorok Canopy Guest House',
                        'sub_label'     => 'Guest House Wisatawan',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Jl. Besar Bukit Lawang No. 27, Langkat',
                        'harga'         => 265000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 104,
                        'kontak'        => '0853-6011-7790',
                        'deskripsi'     => 'Akomodasi bersih dengan Wi-Fi stabil dan layanan penitipan barang selama trekking.',
                        'foto'          => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'transportasi' => [
                    [
                        'nama'          => 'Shuttle Wisata Medan (Pinang Baris) – Bukit Lawang',
                        'sub_label'     => 'Travel Langkat',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Terminal Pinang Baris – Binjai – Bukit Lawang',
                        'harga'         => 110000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 192,
                        'kontak'        => '0812-6091-2240',
                        'deskripsi'     => 'Perjalanan 2,5 jam dari Kota Medan dengan armada AC berkapasitas 7 penumpang.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Jeep Lintas Perkebunan Bukit Lawang – Tangkahan',
                        'sub_label'     => 'Transportasi 4x4',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Koridor Bahorok – Perkebunan Sawit – Tangkahan',
                        'harga'         => 550000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 84,
                        'kontak'        => '0813-9701-5521',
                        'deskripsi'     => 'Penghubung jalur off-road antar dua kawasan utama Taman Nasional Gunung Leuser.',
                        'foto'          => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Ojek & Angkutan Lokal Stabat – Tangkahan',
                        'sub_label'     => 'Transportasi Lokal',
                        'kota'          => 'Langkat',
                        'alamat'        => 'Simpang Tanjung Beringin – Batang Serangan',
                        'harga'         => 60000,
                        'rating'        => 4.6,
                        'jumlah_ulasan' => 65,
                        'kontak'        => '0852-9600-1123',
                        'deskripsi'     => 'Akses cepat dari jalan lintas Sumatera Stabat langsung menuju lokasi wisata Tangkahan.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
            ],

            // 4. Klaster Kepulauan Nias (Nias Selatan, Gunungsitoli, Sorake, Bawomataluo)
            'nias' => [
                'tur_guide' => [
                    [
                        'nama'          => 'Pemandu Adat Desa Bawomataluo & Lompat Batu',
                        'sub_label'     => 'Tur Budaya Megalitik',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Desa Bawomataluo, Kec. Fanayama, Nias Selatan',
                        'harga'         => 280000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 136,
                        'kontak'        => '0812-6588-9021',
                        'deskripsi'     => 'Tur sejarah Omo Sebua (rumah adat raja Nias) dan penjelasan tradisi Fahombo batu setinggi 2 meter.',
                        'foto'          => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Sorake Surf & Island Hopping Pulau Asu Guide',
                        'sub_label'     => 'Wisata Bahari & Surfing',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Pantai Sorake, Teluk Dalam, Nias Selatan',
                        'harga'         => 380000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 112,
                        'kontak'        => '0813-7611-4430',
                        'deskripsi'     => 'Pemandu ombak Pantai Sorake untuk peselancar pemula hingga mahir serta tur perahu Pulau Telo.',
                        'foto'          => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Eksplorasi Situs Megalitik Tetegewo & Museum Pusaka Nias',
                        'sub_label'     => 'Tur Sejarah Nias',
                        'kota'          => 'Gunungsitoli',
                        'alamat'        => 'Jl. Yos Sudarso No. 134, Kota Gunungsitoli',
                        'harga'         => 210000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 74,
                        'kontak'        => '0852-6044-2281',
                        'deskripsi'     => 'Menelusuri koleksi batu megalitik Ono Niha dan kawasan pesisir Pantai Tureloto yang jernih.',
                        'foto'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'kuliner' => [
                    [
                        'nama'          => 'Pondok Seafood Teluk Dalam & Lobster Bakar Sorake',
                        'sub_label'     => 'Hidangan Laut Segar',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Jl. Pantai Lagundri – Sorake, Nias Selatan',
                        'harga'         => 65000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 173,
                        'kontak'        => '0812-6377-1180',
                        'deskripsi'     => 'Tangkapan langsung nelayan Teluk Dalam: ikan kerapu bakar, cumi saus tiram, dan es kelapa muda.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Rumah Makan Khas Ono Niha (Harinake & Gowi Nifufu)',
                        'sub_label'     => 'Kuliner Tradisional Nias',
                        'kota'          => 'Gunungsitoli',
                        'alamat'        => 'Jl. Diponegoro, Gunungsitoli, Kepulauan Nias',
                        'harga'         => 40000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 92,
                        'kontak'        => '0813-6209-4418',
                        'deskripsi'     => 'Cicipi hidangan otentik masyarakat Nias beserta varian menu ikan laut sambal kecombrang.',
                        'foto'          => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kafe Senja Pantai Tureloto',
                        'sub_label'     => 'Kafe Pesisir & Minuman',
                        'kota'          => 'Nias Utara',
                        'alamat'        => 'Desa Balefadorotuho, Lahewa, Nias Utara',
                        'harga'         => 25000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 86,
                        'kontak'        => '0822-7631-5509',
                        'deskripsi'     => 'Tempat singgah santai menikmati matahari terbenam di perairan Laut Mati versi Kepulauan Nias.',
                        'foto'          => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'penginapan' => [
                    [
                        'nama'          => 'Sorake Point Surf Camp & Losmen',
                        'sub_label'     => 'Penginapan Tepi Pantai',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Kawasan The Point Pantai Sorake, Nias Selatan',
                        'harga'         => 360000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 198,
                        'kontak'        => '0811-6204-882',
                        'deskripsi'     => 'Menghadap langsung ke ombak ikonik Pantai Sorake lengkap dengan rak papan selancar.',
                        'foto'          => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Lagundri Bay Ocean Cottages',
                        'sub_label'     => 'Cottage Keluarga',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Teluk Lagundri, Luahagundre Maniamolo',
                        'harga'         => 420000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 115,
                        'kontak'        => '0812-6900-3142',
                        'deskripsi'     => 'Suasana tenang di teluk berpasir putih, aman untuk anak-anak berenang di pantai.',
                        'foto'          => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kaliki Boutique Hotel Gunungsitoli',
                        'sub_label'     => 'Hotel Kota & Bandara',
                        'kota'          => 'Gunungsitoli',
                        'alamat'        => 'Jl. Sirao No. 18, Pasar Gunungsitoli',
                        'harga'         => 385000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 128,
                        'kontak'        => '(0639) 21988',
                        'deskripsi'     => 'Akses mudah 20 menit menuju Bandara Binaka dan dermaga penyeberangan Gunungsitoli.',
                        'foto'          => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'transportasi' => [
                    [
                        'nama'          => 'Travel Antar Pulau Bandara Binaka – Teluk Dalam (Sorake)',
                        'sub_label'     => 'Shuttle Lintas Nias',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Bandara Binaka Gunungsitoli ↔ Teluk Dalam',
                        'harga'         => 150000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 164,
                        'kontak'        => '0812-6422-9100',
                        'deskripsi'     => 'Menghubungkan Bandara Binaka dengan kawasan wisata Sorake & Bawomataluo (2,5 jam).',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Feri Penyeberangan Pelabuhan Sibolga – Gunungsitoli',
                        'sub_label'     => 'KMP Penyeberangan Laut',
                        'kota'          => 'Gunungsitoli',
                        'alamat'        => 'Pelabuhan Sambas Sibolga ↔ Pelabuhan Angin Gunungsitoli',
                        'harga'         => 125000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 290,
                        'kontak'        => '0813-7520-6631',
                        'deskripsi'     => 'Layanan penyeberangan malam reguler jalur laut utama menuju Kepulauan Nias.',
                        'foto'          => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Rental Motor Pesisir Sorake & Teluk Dalam',
                        'sub_label'     => 'Sewa Motor Harian',
                        'kota'          => 'Nias Selatan',
                        'alamat'        => 'Jl. Raya Sorake No. 5, Nias Selatan',
                        'harga'         => 95000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 88,
                        'kontak'        => '0852-7708-3349',
                        'deskripsi'     => 'Motor siap pakai lengkap dengan penyangga samping papan selancar atau helm ganda.',
                        'foto'          => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
            ],

            // 5. Klaster Pantai Barat & Selatan Sumut (Tapteng, Sibolga, Madina, Asahan, Labuhanbatu)
            'pesisir_selatan' => [
                'tur_guide' => [
                    [
                        'nama'          => 'Pemandu Island Hopping Pulau Mursala & Putri',
                        'sub_label'     => 'Tur Pulau & Snorkeling',
                        'kota'          => 'Tapanuli Tengah',
                        'alamat'        => 'Dermaga Wisata Pantai Pandan, Tapanuli Tengah',
                        'harga'         => 350000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 182,
                        'kontak'        => '0812-6531-9044',
                        'deskripsi'     => 'Menjelajahi Air Terjun Pulau Mursala yang langsung jatuh ke laut Samudera Hindia dan spot terumbu karang.',
                        'foto'          => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Pemandu Trekking Lembah Ponot & Hutan Asahan',
                        'sub_label'     => 'Tur Air Terjun & Ngarai',
                        'kota'          => 'Asahan',
                        'alamat'        => 'Desa Tangga, Aek Songsongan, Asahan',
                        'harga'         => 210000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 119,
                        'kontak'        => '0813-6640-7712',
                        'deskripsi'     => 'Pendampingan aman menuju titik pandang Air Terjun Ponot dan jelajah lubuk sungai Asahan.',
                        'foto'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Ekspedisi Sejarah Islam Barus & Makam Papan Tinggi',
                        'sub_label'     => 'Wisata Sejarah & Religi',
                        'kota'          => 'Tapanuli Tengah',
                        'alamat'        => 'Kec. Barus, Kabupaten Tapanuli Tengah',
                        'harga'         => 180000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 91,
                        'kontak'        => '0852-6077-1923',
                        'deskripsi'     => 'Napak tilas pelabuhan rempah kuno Barus dan kompleks makam bersejarah abad ke-7.',
                        'foto'          => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'kuliner' => [
                    [
                        'nama'          => 'Pusat Kuliner Panggang Geleng & Ikan Bakar Sibolga',
                        'sub_label'     => 'Khas Pesisir Barat Sumut',
                        'kota'          => 'Sibolga',
                        'alamat'        => 'Kawasan Pelabuhan Lama, Kota Sibolga',
                        'harga'         => 45000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 274,
                        'kontak'        => '0812-6309-8844',
                        'deskripsi'     => 'Ikan kakap segar bakar rempah pesisir, ikan panggang geleng khas Sibolga, dan sambal kecap.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Warung Kopi Mandailing & Gulai Ikan Sale Asahan',
                        'sub_label'     => 'Kuliner Daerah Selatan',
                        'kota'          => 'Mandailing Natal',
                        'alamat'        => 'Jl. Willem Iskandar, Panyabungan / Lintas Kisaran',
                        'harga'         => 38000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 145,
                        'kontak'        => '0813-7780-1129',
                        'deskripsi'     => 'Gulai daun ubi tumbuk dipadu ikan salai asap serta seduhan kopi Arabika Mandailing.',
                        'foto'          => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kedai Mie Sop & Kerang Rebus Tanjungbalai',
                        'sub_label'     => 'Kuliner Pesisir Timur',
                        'kota'          => 'Tanjungbalai',
                        'alamat'        => 'Jl. Sudirman, Kota Tanjungbalai / Kisaran',
                        'harga'         => 28000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 160,
                        'kontak'        => '0821-6544-2201',
                        'deskripsi'     => 'Kerang batu rebus hangat dicocol kuah kacang nanas pedas manis khas pantai timur.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'penginapan' => [
                    [
                        'nama'          => 'Pandan Beach Resort & Ocean Terrace',
                        'sub_label'     => 'Resor Pesisir Pandan',
                        'kota'          => 'Tapanuli Tengah',
                        'alamat'        => 'Jl. Padangsidimpuan Km 10, Pantai Pandan',
                        'harga'         => 460000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 210,
                        'kontak'        => '(0631) 371880',
                        'deskripsi'     => 'Penginapan tepi pantai pasir putih dengan dermaga titik kumpul kapal ke Pulau Mursala.',
                        'foto'          => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Aek Songsongan Valley Homestay',
                        'sub_label'     => 'Homestay Dekat Air Terjun',
                        'kota'          => 'Asahan',
                        'alamat'        => 'Kec. Aek Songsongan, Kabupaten Asahan',
                        'harga'         => 250000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 82,
                        'kontak'        => '0852-6310-7745',
                        'deskripsi'     => 'Tempat istirahat nyaman bagi wisatawan menuju Air Terjun Ponot maupun Sigura-gura.',
                        'foto'          => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Hotel Prima Kisaran & Padangsidimpuan Inn',
                        'sub_label'     => 'Hotel Pusat Kota',
                        'kota'          => 'Asahan',
                        'alamat'        => 'Jl. Jend. Ahmad Yani, Kisaran / Padangsidimpuan',
                        'harga'         => 340000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 139,
                        'kontak'        => '0811-6302-991',
                        'deskripsi'     => 'Kamar AC berfasilitas lengkap dekat stasiun kereta api dan jalur lintas utama.',
                        'foto'          => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'transportasi' => [
                    [
                        'nama'          => 'Speedboat Wisata Pantai Pandan – Pulau Mursala',
                        'sub_label'     => 'Charter Kapal Wisata',
                        'kota'          => 'Tapanuli Tengah',
                        'alamat'        => 'Pantai Bosur & Pantai Pandan, Tapanuli Tengah',
                        'harga'         => 180000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 205,
                        'kontak'        => '0812-6509-7721',
                        'deskripsi'     => 'Perjalanan laut 45 menit lengkap dengan pelampung keselamatan dan peralatan snorkeling.',
                        'foto'          => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kereta Api Putri Deli (Medan – Kisaran – Tanjungbalai)',
                        'sub_label'     => 'Kereta Api Reguler',
                        'kota'          => 'Asahan',
                        'alamat'        => 'Stasiun Medan ↔ Stasiun Kisaran ↔ Tanjungbalai',
                        'harga'         => 27000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 540,
                        'kontak'        => 'KAI Access 121',
                        'deskripsi'     => 'Pilihan transportasi rel paling ekonomis dan tepat waktu menjangkau wilayah pantai timur Sumut.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Travel Eksekutif Medan – Sibolga – Panyabungan',
                        'sub_label'     => 'Shuttle Lintas Barat',
                        'kota'          => 'Sibolga',
                        'alamat'        => 'Pool Medan – Tarutung – Sibolga – Padangsidimpuan',
                        'harga'         => 190000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 176,
                        'kontak'        => '0813-6102-8833',
                        'deskripsi'     => 'Keberangkatan pagi dan malam dengan kursi reclining dan fasilitas pengisian daya.',
                        'foto'          => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
            ],

            // 6. Klaster Medan, Deli Serdang, Binjai, Serdang Bedagai & Wilayah Timur (Default)
            'medan_sekitar' => [
                'tur_guide' => [
                    [
                        'nama'          => 'Medan Heritage Walking Tour (Kesawan & Istana Maimun)',
                        'sub_label'     => 'Tur Sejarah Kota Tua',
                        'kota'          => 'Medan',
                        'alamat'        => 'Titik Kumpul Kantor Pos Lama / Titik Nol Kota Medan',
                        'harga'         => 165000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 318,
                        'kontak'        => '0812-6011-7788',
                        'deskripsi'     => 'Berjalan kaki 3 jam menelusuri arsitektur kolonial Kesawan, Tjong A Fie Mansion, hingga Istana Maimun.',
                        'foto'          => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Pemandu Wisata Alam Sibolangit & Air Terjun Dua Warna',
                        'sub_label'     => 'Trekking Deli Serdang',
                        'kota'          => 'Deli Serdang',
                        'alamat'        => 'Bumi Perkemahan Sibolangit, Deli Serdang',
                        'harga'         => 200000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 195,
                        'kontak'        => '0813-7022-5190',
                        'deskripsi'     => 'Jalur hutan lindung Sibolangit menuju telaga air terjun biru dan bening bersama ranger resmi.',
                        'foto'          => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Tur Edukasi Mangrove & Pantai Cermin Serdang Bedagai',
                        'sub_label'     => 'Ekowisata Pesisir',
                        'kota'          => 'Serdang Bedagai',
                        'alamat'        => 'Kawasan Kampung Nipah, Sei Nagalawan, Serdang Bedagai',
                        'harga'         => 140000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 89,
                        'kontak'        => '0852-6180-4402',
                        'deskripsi'     => 'Susur jembatan kayu hutan bakau, pengamatan burung pesisir, dan penanaman bibit mangrove.',
                        'foto'          => 'https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'kuliner' => [
                    [
                        'nama'          => 'Soto Medan Kesawan & Lontong Malam Legendaris',
                        'sub_label'     => 'Kuliner Ikonik Medan',
                        'kota'          => 'Medan',
                        'alamat'        => 'Jl. Ahmad Yani (Kesawan), Medan Barat',
                        'harga'         => 38000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 520,
                        'kontak'        => '(061) 4518-290',
                        'deskripsi'     => 'Soto bersantan rempah kapulaga dengan daging sapi empuk atau udang galah sejak 1970-an.',
                        'foto'          => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Kedai Bika Ambon, Bolu Meranti & Durian Medan',
                        'sub_label'     => 'Pusat Oleh-Oleh Khas',
                        'kota'          => 'Medan',
                        'alamat'        => 'Jl. Majapahit & Jl. K.H. Wahid Hasyim, Medan',
                        'harga'         => 65000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 465,
                        'kontak'        => '0811-6120-445',
                        'deskripsi'     => 'Pusat kudapan sarang lebah panggang daun jeruk, pancake durian, dan kopi sidikalang.',
                        'foto'          => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Warung Tahu Sumedang & Rambutan Binjai Jalan Soekarno-Hatta',
                        'sub_label'     => 'Kuliner Lintas Binjai',
                        'kota'          => 'Binjai',
                        'alamat'        => 'Jl. Soekarno-Hatta, Kota Binjai',
                        'harga'         => 25000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 182,
                        'kontak'        => '0812-6988-3310',
                        'deskripsi'     => 'Persinggahan favorit pelancong antara Medan dan Langkat dengan menu mie balap dan sate kerang.',
                        'foto'          => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'penginapan' => [
                    [
                        'nama'          => 'Kesawan Heritage Boutique Stay Medan',
                        'sub_label'     => 'Hotel Pusat Sejarah',
                        'kota'          => 'Medan',
                        'alamat'        => 'Jl. Balai Kota, Medan Barat, Kota Medan',
                        'harga'         => 450000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 340,
                        'kontak'        => '(061) 4530-910',
                        'deskripsi'     => 'Berjarak jalan kaki dari Stasiun Medan (Railink Bandara) dan Lapangan Merdeka.',
                        'foto'          => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Maimun Courtyard Guest House',
                        'sub_label'     => 'Guest House Keluarga',
                        'kota'          => 'Medan',
                        'alamat'        => 'Jl. Brigjen Katamso No. 45, Medan Maimun',
                        'harga'         => 310000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 176,
                        'kontak'        => '0812-6211-8890',
                        'deskripsi'     => 'Akomodasi nyaman 5 menit dari Istana Maimun dan Masjid Raya Al-Mashun.',
                        'foto'          => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Pantai Cermin Coastal Homestay',
                        'sub_label'     => 'Penginapan Pesisir Timur',
                        'kota'          => 'Serdang Bedagai',
                        'alamat'        => 'Jl. Pantai Cermin, Kab. Serdang Bedagai',
                        'harga'         => 360000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 118,
                        'kontak'        => '0813-7055-6620',
                        'deskripsi'     => 'Cocok untuk akhir pekan keluarga dekat kawasan bahari Pantai Cermin & Bali Lestari.',
                        'foto'          => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
                'transportasi' => [
                    [
                        'nama'          => 'Kereta Api Bandara Railink (Kualanamu ↔ Stasiun Medan)',
                        'sub_label'     => 'Airport Railink Express',
                        'kota'          => 'Medan',
                        'alamat'        => 'Stasiun Bandara Kualanamu ↔ Stasiun Besar Medan',
                        'harga'         => 70000,
                        'rating'        => 4.9,
                        'jumlah_ulasan' => 680,
                        'kontak'        => 'Railink Call 1500-xxx',
                        'deskripsi'     => 'Waktu tempuh pasti 30 menit bebas macet dari Bandara Kualanamu tepat ke pusat Kota Medan.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Bus Listrik & Trans Metro Deli Kota Medan',
                        'sub_label'     => 'Angkutan Publik Terpadu',
                        'kota'          => 'Medan',
                        'alamat'        => 'Koridor Lapangan Merdeka – Pinang Baris – Amplas',
                        'harga'         => 5000,
                        'rating'        => 4.7,
                        'jumlah_ulasan' => 412,
                        'kontak'        => 'Dishub Kota Medan',
                        'deskripsi'     => 'Transportasi ramah lingkungan menjangkau kawasan wisata sejarah dan kuliner Kota Medan.',
                        'foto'          => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'nama'          => 'Sewa Mobil & Pengemudi Wisata Harian Sumut',
                        'sub_label'     => 'Rental Mobil Lintas Kota',
                        'kota'          => 'Medan',
                        'alamat'        => 'Jl. Ring Road Gagak Hitam, Medan Sunggal',
                        'harga'         => 420000,
                        'rating'        => 4.8,
                        'jumlah_ulasan' => 235,
                        'kontak'        => '0811-6420-551',
                        'deskripsi'     => 'Armada MPV 7-penumpang dengan sopir berpengalaman rute Medan, Karo, Langkat, dan Toba.',
                        'foto'          => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=900&q=80',
                    ],
                ],
            ],
        ];
    }
}
