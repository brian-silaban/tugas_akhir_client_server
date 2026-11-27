<?php

namespace App\Libraries;

use Config\Services;
use Throwable;

/**
 * HTTP Client + Resilient Adapter untuk mengonsumsi REST API dari 4 kelompok mitra
 * di kelas (Transportasi, Kuliner/Rumah Makan, Penginapan, dan Penyedia Tur Guide).
 *
 * Mitra Transportasi, Kuliner, dan Penginapan sudah murni menggunakan REST API resmi
 * mitra tanpa data dummy. Data simulasi cadangan hanya dipertahankan untuk mitra yang
 * dokumentasi/API-nya belum tersedia (Penyedia Tur Guide).
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
            'transportasi' => [
                'nama_layanan'      => 'Layanan Transportasi',
                'env_key'           => 'PARTNER_TRANSPORT_API_URL',
                'env_api_key'       => 'PARTNER_TRANSPORT_API_KEY',
                'ikon'              => 'directions_bus',
                'satuan_harga'      => '/ perjalanan',
                'deskripsi_singkat' => 'Akses shuttle, travel, kereta bandara, dan penerbangan menuju destinasi.',
            ],
            'kuliner' => [
                'nama_layanan'      => 'Kuliner & Rumah Makan',
                'env_key'           => 'PARTNER_KULINER_API_URL',
                'env_api_key'       => 'PARTNER_KULINER_API_KEY',
                'ikon'              => 'restaurant',
                'satuan_harga'      => '/ porsi',
                'deskripsi_singkat' => 'Rekomendasi hidangan khas dan rumah makan di sekitar destinasi.',
            ],
            'penginapan' => [
                'nama_layanan'      => 'Penginapan & Hotel',
                'env_key'           => 'PARTNER_HOTEL_API_URL',
                'env_api_key'       => 'PARTNER_HOTEL_API_KEY',
                'ikon'              => 'hotel',
                'satuan_harga'      => '/ malam',
                'deskripsi_singkat' => 'Pilihan hotel, homestay, dan penginapan nyaman di Sumatera Utara.',
            ],
            'tur_guide' => [
                'nama_layanan'      => 'Penyedia Tur Guide',
                'env_key'           => 'PARTNER_TUR_GUIDE_API_URL',
                'env_api_key'       => 'PARTNER_TUR_GUIDE_API_KEY',
                'ikon'              => 'hiking',
                'satuan_harga'      => '/ paket',
                'deskripsi_singkat' => 'Pemandu wisata lokal bersertifikat & paket jelajah rute Sumut.',
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

        // Batasi timeout 1–4 detik agar halaman detail wisata tetap cepat saat mitra lambat/offline
        if ($batasWaktuDetik !== null) {
            $waktuMentah = $batasWaktuDetik;
        } else {
            $waktuMentah = (int) env('PARTNER_API_TIMEOUT', 3);
        }
        $this->batasWaktuDetik = max(1, min(4, $waktuMentah));

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

        // Bangun kunci cache unik per mitra, kota, batas item, dan koordinat (2 desimal)
        $kunciKoordinat = '';
        if (is_array($konteksDestinasi)) {
            $koordinatObj   = is_array($konteksDestinasi['koordinat'] ?? null) ? $konteksDestinasi['koordinat'] : [];
            $latMentah      = (float) ($koordinatObj['garis_lintang'] ?? ($konteksDestinasi['garis_lintang'] ?? 0));
            $lngMentah      = (float) ($koordinatObj['garis_bujur'] ?? ($konteksDestinasi['garis_bujur'] ?? 0));
            $kunciKoordinat = number_format($latMentah, 2, '.', '') . ',' . number_format($lngMentah, 2, '.', '');
        }
        $kunciCacheMitra = 'jejaksumut_mitra_rek_' . md5($kodeMitra . '|' . strtolower($kotaPencarian) . '|' . $batasAman . '|' . $kunciKoordinat);
        $hasilDariCache  = $this->ambilDariCacheSingkat($kunciCacheMitra);
        if (is_array($hasilDariCache) && isset($hasilDariCache['kode_mitra'])) {
            return $hasilDariCache;
        }

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

        // Jalur khusus API kelompok Kuliner Khas Medan (/api/v1/restoran/terdekat & /restoran)
        $apakahAdapterKuliner = $kodeMitra === 'kuliner'
            && $this->apakahAdapterKulinerKhasMedan($urlDasar);

        if ($apakahAdapterPenginapan) {
            $hasilPenginapan = $this->ambilRekomendasiPenginapanMitra(
                $infoMitra,
                $kotaPencarian,
                $batasAman,
                $konteksDestinasi
            );

            if ($hasilPenginapan['berhasil']) {
                $this->simpanKeCacheSingkat($kunciCacheMitra, $hasilPenginapan['data'], 300);
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
                $this->simpanKeCacheSingkat($kunciCacheMitra, $hasilTransport['data'], 300);
                return $hasilTransport['data'];
            }

            $urlTujuan      = $hasilTransport['url_tujuan'];
            $statusKode     = $hasilTransport['status_kode'];
            $waktuResponMs  = $hasilTransport['waktu_respon_ms'];
            $alasanFallback = $hasilTransport['alasan_fallback'];
        } elseif ($apakahAdapterKuliner) {
            $hasilKuliner = $this->ambilRekomendasiKulinerMitra(
                $infoMitra,
                $kotaPencarian,
                $batasAman,
                $konteksDestinasi
            );

            if ($hasilKuliner['berhasil']) {
                $this->simpanKeCacheSingkat($kunciCacheMitra, $hasilKuliner['data'], 300);
                return $hasilKuliner['data'];
            }

            $urlTujuan      = $hasilKuliner['url_tujuan'];
            $statusKode     = $hasilKuliner['status_kode'];
            $waktuResponMs  = $hasilKuliner['waktu_respon_ms'];
            $alasanFallback = $hasilKuliner['alasan_fallback'];
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
                        $hasilLive = [
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
                        $this->simpanKeCacheSingkat($kunciCacheMitra, $hasilLive, 300);

                        return $hasilLive;
                    }
                }

                $alasanFallback = 'API mitra merespons sukses tetapi tidak mengirimkan item data yang valid.';
            }
        } else {
            $alasanFallback = 'URL API mitra belum dikonfigurasi pada file .env.';
        }

        // Jalankan fallback data simulasi hanya untuk mitra yang belum terintegrasi Live API
        if ($this->modeCadanganMock) {
            $daftarSimulasi = $this->ambilDataSimulasiSumut(
                $kodeMitra,
                $kotaPencarian,
                $kotaAsli,
                $batasAman
            );

            if ($daftarSimulasi !== []) {
                $hasilFallback = [
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
                // Negative cache 90 detik agar mitra yang sedang mati tidak menahan halaman berulang kali
                $this->simpanKeCacheSingkat($kunciCacheMitra, $hasilFallback, 90);

                return $hasilFallback;
            }
        }

        $hasilKosong = [
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
        $this->simpanKeCacheSingkat($kunciCacheMitra, $hasilKosong, 90);

        return $hasilKosong;
    }

    /**
     * Memeriksa status koneksi real-time ke seluruh 4 API kelompok mitra.
     * Digunakan oleh Dasbor Admin (Anggota 5) dan showcase integrasi dosen.
     *
     * @return list<array<string, mixed>>
     */
    public function cekStatusSemuaMitra(string $kotaUji = 'Medan', bool $paksaSegarkan = false): array
    {
        $kunciCacheStatus = 'jejaksumut_status_semua_mitra_v1';
        if (! $paksaSegarkan) {
            $statusCache = $this->ambilDariCacheSingkat($kunciCacheStatus);
            if (is_array($statusCache) && $statusCache !== []) {
                return $statusCache;
            }
        }

        $kotaPencarian = $this->bersihkanNamaKota($kotaUji);
        $daftarStatus  = [];
        $batasUjiCepat = min(3, $this->batasWaktuDetik);

        foreach ($this->konfigurasiMitra as $kodeMitra => $infoMitra) {
            $urlDasar      = $infoMitra['url_api'];
            $apiKey        = $infoMitra['api_key'] ?? '';
            $pakaiSimulasi = $this->modeCadanganMock && $this->punyaDataSimulasi($kodeMitra);

            if ($urlDasar === '') {
                $pesanBelumDiatur = $pakaiSimulasi
                    ? 'URL API belum diatur; memakai data simulasi fallback.'
                    : 'URL API belum diatur; tidak ada data cadangan.';

                $daftarStatus[] = [
                    'kode_mitra'      => $kodeMitra,
                    'nama_layanan'    => $infoMitra['nama_layanan'],
                    'url_api'         => '',
                    'terhubung'       => false,
                    'status_kode'     => 0,
                    'sumber_aktif'    => $pakaiSimulasi ? 'simulasi_fallback' : 'tidak_tersedia',
                    'waktu_respon_ms' => 0,
                    'pesan_status'    => $pesanBelumDiatur,
                ];
                continue;
            }

            if ($kodeMitra === 'penginapan' && $this->apakahAdapterPenginapanSinggahMedan($urlDasar)) {
                $urlUji = $this->ambilBaseUrlPenginapan($urlDasar) . '/kecamatan';
            } elseif ($kodeMitra === 'transportasi' && ($apiKey !== '' || str_ends_with(rtrim($urlDasar, '/'), '/api'))) {
                $urlUji = $this->ambilBaseUrlTransportasi($urlDasar) . '/transport/lokasi';
            } elseif ($kodeMitra === 'kuliner' && $this->apakahAdapterKulinerKhasMedan($urlDasar)) {
                $urlUji = $this->ambilBaseUrlKuliner($urlDasar) . '/kategori';
            } else {
                $pemisahQuery = str_contains($urlDasar, '?') ? '&' : '?';
                $urlUji       = $urlDasar . $pemisahQuery . 'kota=' . urlencode($kotaPencarian);
            }

            $hasilPanggil = $this->panggilEndpointMitra($urlUji, $apiKey, $batasUjiCepat);

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
                    'sumber_aktif'    => $pakaiSimulasi ? 'simulasi_fallback' : 'tidak_tersedia',
                    'waktu_respon_ms' => $hasilPanggil['waktu_respon_ms'],
                    'pesan_status'    => $hasilPanggil['pesan_error'] ?? 'Server mitra tidak merespons.',
                ];
            }
        }

        $this->simpanKeCacheSingkat($kunciCacheStatus, $daftarStatus, 180);

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

        $kunciCacheLokasi = 'jejaksumut_transport_lokasi_v1';
        $lokasiDariCache  = $this->ambilDariCacheSingkat($kunciCacheLokasi);
        if (is_array($lokasiDariCache) && $lokasiDariCache !== []) {
            return [
                'berhasil'        => true,
                'terhubung'       => true,
                'status_kode'     => 200,
                'waktu_respon_ms' => 1,
                'data'            => $lokasiDariCache,
                'pesan_error'     => null,
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

        if ($daftarLokasi !== []) {
            $this->simpanKeCacheSingkat($kunciCacheLokasi, $daftarLokasi, 300);
        }

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
    // ADAPTER KHUSUS REST API MITRA PENGINAPAN SINGGAH MEDAN (/api/hotels)
    // =========================================================================

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
        $batasTimeout = $this->batasWaktuDetik;
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
    // ADAPTER KHUSUS REST API MITRA KULINER KHAS MEDAN (/api/v1/restoran/*)
    // =========================================================================

    /**
     * Mengambil rekomendasi restoran dari API Kuliner Khas Medan berdasarkan koordinat
     * wisata (terdekat untuk area Medan, atau daftar restoran Medan untuk tujuan luar kota).
     *
     * @param array<string, string>     $infoMitra
     * @param array<string, mixed>|null $konteksDestinasi
     * @return array<string, mixed>
     */
    private function ambilRekomendasiKulinerMitra(
        array $infoMitra,
        string $kotaPencarian,
        int $batasItem,
        ?array $konteksDestinasi
    ): array {
        $baseUrl      = $this->ambilBaseUrlKuliner($infoMitra['url_api']);
        $apiKey       = $infoMitra['api_key'] ?? '';
        $batasTimeout = $this->batasWaktuDetik;

        [$wisataLat, $wisataLng] = $this->tentukanKoordinatWisata($konteksDestinasi, [], $kotaPencarian);
        // Hitung jarak dari pusat Kota Medan untuk membedakan kuliner terdekat vs persinggahan Medan
        $jarakDariMedanKm  = $this->hitungJarakKm($wisataLat, $wisataLng, 3.5952, 98.6722);
        $apakahAreaMedan   = ($jarakDariMedanKm <= 25.0);

        $daftarRestoranMentah = [];
        $urlTujuan            = $baseUrl . '/restoran?per_page=10&page=1';
        $statusKode           = 0;
        $waktuResponMs        = 0;
        $pesanErrorTerakhir   = null;
        $modeTransit          = false;

        // 1. Untuk destinasi di wilayah Medan & sekitarnya, cari kuliner terdekat dari titik GPS
        if ($apakahAreaMedan) {
            $kunciLat   = str_replace(['-', '.'], ['m', 'd'], number_format($wisataLat, 3, '.', ''));
            $kunciLng   = str_replace(['-', '.'], ['m', 'd'], number_format($wisataLng, 3, '.', ''));
            $kunciCache = 'jejaksumut_kuliner_terdekat_' . $kunciLat . '_' . $kunciLng . '_' . $batasItem;
            $dataCache  = $this->ambilDariCacheSingkat($kunciCache);

            if (is_array($dataCache) && $dataCache !== []) {
                $daftarRestoranMentah = $dataCache;
                $statusKode           = 200;
                $waktuResponMs        = 1;
            } else {
                foreach ([5, 20] as $radiusKm) {
                    $urlTerdekat   = $baseUrl . '/restoran/terdekat?' . http_build_query([
                        'lat'    => $wisataLat,
                        'lng'    => $wisataLng,
                        'radius' => $radiusKm,
                        'limit'  => $batasItem,
                    ]);
                    $urlTujuan     = $urlTerdekat;
                    $hasilTerdekat = $this->panggilEndpointMitra($urlTerdekat, $apiKey, $batasTimeout);
                    $statusKode    = (int) $hasilTerdekat['status_kode'];
                    $waktuResponMs = max($waktuResponMs, (int) $hasilTerdekat['waktu_respon_ms']);

                    if (! $hasilTerdekat['berhasil']) {
                        $pesanErrorTerakhir = $hasilTerdekat['pesan_error'];
                        break;
                    }

                    $daftarCoba = $this->ekstrakDaftarItemApi($hasilTerdekat['isi']);
                    if ($daftarCoba !== []) {
                        $daftarRestoranMentah = $daftarCoba;
                        $this->simpanKeCacheSingkat($kunciCache, $daftarRestoranMentah, 300);
                        break;
                    }
                }
            }
        }

        // 2. Untuk destinasi luar Medan atau bila /terdekat kosong, tampilkan kuliner pilihan di Medan
        if ($daftarRestoranMentah === [] && $pesanErrorTerakhir === null) {
            $modeTransit      = true;
            $kunciCacheDaftar = 'jejaksumut_kuliner_medan_daftar_10_v1';
            $cacheDaftar      = $this->ambilDariCacheSingkat($kunciCacheDaftar);

            if (is_array($cacheDaftar) && $cacheDaftar !== []) {
                $daftarRestoranMentah = $cacheDaftar;
                $statusKode           = 200;
                $waktuResponMs        = max($waktuResponMs, 1);
            } else {
                $urlDaftar   = $baseUrl . '/restoran?per_page=10&page=1';
                $urlTujuan   = $urlDaftar;
                $hasilDaftar = $this->panggilEndpointMitra($urlDaftar, $apiKey, $batasTimeout);
                $statusKode  = (int) $hasilDaftar['status_kode'];
                $waktuResponMs = max($waktuResponMs, (int) $hasilDaftar['waktu_respon_ms']);

                if (! $hasilDaftar['berhasil']) {
                    $pesanErrorTerakhir = $hasilDaftar['pesan_error'];
                } else {
                    $daftarRestoranMentah = $this->ekstrakDaftarItemApi($hasilDaftar['isi']);
                    if ($daftarRestoranMentah !== []) {
                        $this->simpanKeCacheSingkat($kunciCacheDaftar, $daftarRestoranMentah, 300);
                    }
                }
            }
        }

        if ($daftarRestoranMentah === []) {
            return [
                'berhasil'        => false,
                'url_tujuan'      => $urlTujuan,
                'status_kode'     => $statusKode,
                'waktu_respon_ms' => $waktuResponMs,
                'alasan_fallback' => $pesanErrorTerakhir ?? 'Data restoran dari mitra Kuliner Khas Medan kosong.',
            ];
        }

        $daftarKartu = [];
        foreach (array_slice($daftarRestoranMentah, 0, $batasItem) as $restoran) {
            if (is_array($restoran)) {
                $daftarKartu[] = $this->normalisasiKulinerMedan($restoran, $baseUrl, $modeTransit);
            }
        }

        if ($daftarKartu === []) {
            return [
                'berhasil'        => false,
                'url_tujuan'      => $urlTujuan,
                'status_kode'     => $statusKode,
                'waktu_respon_ms' => $waktuResponMs,
                'alasan_fallback' => 'Data restoran dari mitra Kuliner Khas Medan tidak memiliki format valid.',
            ];
        }

        $deskripsiLayanan = $modeTransit
            ? 'Pilihan kuliner khas Kota Medan dari mitra Kuliner Khas Medan untuk persinggahan sebelum atau sesudah perjalanan ke ' . $kotaPencarian . '.'
            : 'Pilihan rumah makan dan kuliner terdekat di sekitar ' . $kotaPencarian . ' dari mitra Kuliner Khas Medan.';

        return [
            'berhasil' => true,
            'data'     => [
                'kode_mitra'        => 'kuliner',
                'nama_layanan'      => $infoMitra['nama_layanan'],
                'ikon'              => $infoMitra['ikon'],
                'deskripsi_singkat' => $deskripsiLayanan,
                'url_tujuan'        => $urlTujuan,
                'terhubung'         => true,
                'status_kode'       => 200,
                'sumber_data'       => 'api_mitra',
                'label_sumber'      => 'Live Mitra',
                'alasan_fallback'   => null,
                'waktu_respon_ms'   => $waktuResponMs,
                'daftar_item'       => $daftarKartu,
            ],
        ];
    }

    /**
     * Memeriksa apakah konfigurasi URL mengarah ke API Kuliner Khas Medan.
     */
    private function apakahAdapterKulinerKhasMedan(string $urlDasar): bool
    {
        if ($urlDasar === '') {
            return false;
        }

        $urlRapi = rtrim($urlDasar, '/');

        return str_ends_with($urlRapi, '/api/v1')
            || str_contains($urlRapi, '/restoran')
            || str_contains($urlRapi, 'ngrok');
    }

    /**
     * Menormalkan URL dasar API Kuliner Khas Medan agar berakhiran /api/v1.
     */
    private function ambilBaseUrlKuliner(string $urlMentah): string
    {
        $urlBersih   = rtrim(trim($urlMentah), '/');
        $polaAkhiran = '#/restoran(/terdekat)?$#i';
        $urlBersih   = (string) preg_replace($polaAkhiran, '', $urlBersih);

        return rtrim($urlBersih, '/');
    }

    /**
     * Membangun URL foto absolut restoran dari path relatif API Kuliner Khas Medan.
     */
    private function bangunUrlFotoKuliner(string $baseUrlApi, ?string $fotoMentah): string
    {
        $fotoTeks = trim((string) $fotoMentah);
        if ($fotoTeks === '') {
            return $this->ambilFotoBawaanMitra('kuliner');
        }

        if (preg_match('/^https?:\/\//i', $fotoTeks) === 1) {
            return $fotoTeks;
        }

        $komponenBase = parse_url($baseUrlApi);
        $skemaBase    = is_array($komponenBase) ? ($komponenBase['scheme'] ?? 'https') : 'https';
        $hostBase     = is_array($komponenBase) ? ($komponenBase['host'] ?? '') : '';
        $portBase     = (is_array($komponenBase) && isset($komponenBase['port'])) ? ':' . $komponenBase['port'] : '';

        if ($hostBase === '') {
            return $this->ambilFotoBawaanMitra('kuliner');
        }

        return $skemaBase . '://' . $hostBase . $portBase . '/' . ltrim($fotoTeks, '/');
    }

    /**
     * Menyeragamkan item restoran dari JSON Kuliner Khas Medan agar siap dipakai komponen View.
     *
     * @param array<string, mixed> $restoran
     * @return array<string, mixed>
     */
    private function normalisasiKulinerMedan(
        array $restoran,
        string $baseUrlApi,
        bool $modeTransit = false
    ): array {
        $namaRestoran = trim((string) ($restoran['nama'] ?? 'Kuliner Khas Medan'));
        $kategori     = trim((string) ($restoran['kategori'] ?? ''));
        $jenisMasakan = trim((string) ($restoran['jenis_masakan'] ?? ''));
        $kecamatan    = trim((string) ($restoran['kecamatan'] ?? ''));
        $alamat       = trim((string) ($restoran['alamat'] ?? ($kecamatan !== '' ? $kecamatan . ', Medan' : 'Kota Medan')));

        $ratingWeb    = $restoran['rating_web'] ?? null;
        $ulasanWeb    = (int) ($restoran['jumlah_ulasan_web'] ?? 0);
        $ratingMentah = ($ratingWeb !== null && $ulasanWeb > 0)
            ? (float) $ratingWeb
            : (float) ($restoran['rating'] ?? 4.6);
        $rating       = round(max(0.0, min(5.0, $ratingMentah)), 1);
        $jumlahUlasan = max(0, (int) ($restoran['jumlah_ulasan'] ?? 0));

        $infoHarga = is_array($restoran['harga'] ?? null) ? $restoran['harga'] : [];
        $hargaMin  = max(0, (int) ($infoHarga['min'] ?? 0));
        $hargaMax  = max(0, (int) ($infoHarga['max'] ?? 0));

        if ($hargaMin > 0 && $hargaMax > $hargaMin) {
            $teksHarga = 'Rp ' . number_format($hargaMin, 0, ',', '.')
                . ' – Rp ' . number_format($hargaMax, 0, ',', '.');
        } elseif ($hargaMin > 0) {
            $teksHarga = 'Mulai Rp ' . number_format($hargaMin, 0, ',', '.');
        } elseif ($hargaMax > 0) {
            $teksHarga = 'Hingga Rp ' . number_format($hargaMax, 0, ',', '.');
        } else {
            $teksHarga = 'Cek Harga di Lokasi';
        }

        if ($modeTransit) {
            $subLabel = $kategori !== '' ? $kategori . ' • Medan' : 'Kuliner Medan';
        } else {
            $subLabel = $kategori !== '' ? $kategori : 'Kuliner Khas Medan';
        }

        $jarakRaw   = $restoran['jarak_km'] ?? null;
        $teksJarak  = ($jarakRaw !== null && is_numeric($jarakRaw))
            ? number_format((float) $jarakRaw, 1, ',', '.') . ' km dari lokasi wisata'
            : '';

        $infoHalalRaw = trim((string) ($restoran['info_halal'] ?? ''));
        $teksHalal    = '';
        if ($infoHalalRaw !== '' && ! str_contains(strtolower($infoHalalRaw), 'belum dicek')) {
            $teksHalal = $infoHalalRaw;
        }

        $bagianInfo = array_values(array_filter([$teksJarak, $teksHalal], static fn (string $v): bool => $v !== ''));
        if ($bagianInfo !== []) {
            $kontakTeks = implode(' • ', $bagianInfo);
        } elseif ($jenisMasakan !== '') {
            $kontakTeks = $jenisMasakan;
        } else {
            $kontakTeks = 'Kuliner Khas Medan';
        }

        $bagianDeskripsi = [];
        if ($jenisMasakan !== '') {
            $bagianDeskripsi[] = 'Sajian ' . $jenisMasakan;
        }
        if ($kategori !== '' && strtolower($kategori) !== strtolower($jenisMasakan)) {
            $bagianDeskripsi[] = 'kategori ' . $kategori;
        }
        if ($kecamatan !== '') {
            $bagianDeskripsi[] = 'di kawasan ' . $kecamatan . ', Medan';
        }

        $deskripsi = $bagianDeskripsi !== []
            ? implode(' ', $bagianDeskripsi) . '.'
            : 'Pilihan kuliner khas Medan dari mitra Kuliner Khas Medan.';

        return [
            'nama'              => $namaRestoran,
            'sub_label'         => $subLabel,
            'kota'              => $kecamatan !== '' ? $kecamatan . ', Medan' : 'Medan',
            'alamat'            => $alamat,
            'harga'             => $hargaMin,
            'satuan_harga'      => '/ porsi',
            'label_tarif'       => 'Kisaran Harga',
            'teks_harga_khusus' => $teksHarga,
            'rating'            => $rating,
            'jumlah_ulasan'     => $jumlahUlasan,
            'kontak'            => $kontakTeks,
            'deskripsi'         => $deskripsi,
            'foto'              => $this->bangunUrlFotoKuliner($baseUrlApi, $restoran['foto'] ?? null),
            'url_eksternal'     => null,
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
     * Memeriksa apakah suatu kode mitra masih memiliki data simulasi cadangan.
     */
    private function punyaDataSimulasi(string $kodeMitra): bool
    {
        foreach ($this->daftarDataSimulasiPerKlaster() as $dataKlaster) {
            if (! empty($dataKlaster[$kodeMitra])) {
                return true;
            }
        }

        return false;
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
     * Kumpulan katalog simulasi untuk mitra yang belum terhubung Live API (tur_guide)
     * pada 6 klaster wilayah Sumatera Utara. Mitra transportasi, kuliner, dan penginapan
     * sudah murni menggunakan data Live REST API resmi masing-masing kelompok.
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
            ],
        ];
    }
}
