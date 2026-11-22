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
        // Daftar 4 kelompok mitra resmi sesuai PRD.md
        $definisiMitra = [
            'tur_guide' => [
                'nama_layanan'      => 'Penyedia Tur Guide',
                'env_key'           => 'PARTNER_TUR_GUIDE_API_URL',
                'ikon'              => 'hiking',
                'satuan_harga'      => '/ paket',
                'deskripsi_singkat' => 'Pemandu wisata lokal bersertifikat & paket jelajah rute Sumut.',
            ],
            'kuliner' => [
                'nama_layanan'      => 'Kuliner & Rumah Makan',
                'env_key'           => 'PARTNER_KULINER_API_URL',
                'ikon'              => 'restaurant',
                'satuan_harga'      => '/ porsi',
                'deskripsi_singkat' => 'Rekomendasi hidangan khas dan rumah makan di sekitar destinasi.',
            ],
            'penginapan' => [
                'nama_layanan'      => 'Penginapan & Homestay',
                'env_key'           => 'PARTNER_HOTEL_API_URL',
                'ikon'              => 'hotel',
                'satuan_harga'      => '/ malam',
                'deskripsi_singkat' => 'Pilihan homestay, cottage, dan hotel nyaman dekat lokasi wisata.',
            ],
            'transportasi' => [
                'nama_layanan'      => 'Layanan Transportasi',
                'env_key'           => 'PARTNER_TRANSPORT_API_URL',
                'ikon'              => 'directions_bus',
                'satuan_harga'      => '/ perjalanan',
                'deskripsi_singkat' => 'Akses shuttle, travel, sewa kendaraan, dan kapal penyeberangan.',
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

            $this->konfigurasiMitra[$kodeMitra] = [
                'kode_mitra'        => $kodeMitra,
                'nama_layanan'      => $metaMitra['nama_layanan'],
                'env_key'           => $metaMitra['env_key'],
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
     * @return array<string, mixed>
     */
    public function ambilSemuaRekomendasi(string $kota, int $batasPerMitra = 3): array
    {
        $kotaAsli      = trim($kota) !== '' ? trim($kota) : 'Sumatera Utara';
        $kotaPencarian = $this->bersihkanNamaKota($kotaAsli);

        $layananTerhimpun   = [];
        $jumlahApiLangsung  = 0;
        $jumlahFallback     = 0;
        $totalItem          = 0;

        foreach (array_keys($this->konfigurasiMitra) as $kodeMitra) {
            $hasilMitra = $this->ambilRekomendasiPerMitra($kodeMitra, $kotaAsli, $batasPerMitra);
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
     * @return array<string, mixed>
     */
    public function ambilRekomendasiPerMitra(string $kodeMitra, string $kota, int $batasItem = 3): array
    {
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
        $urlTujuan      = '';
        $statusKode     = 0;
        $waktuResponMs  = 0;
        $alasanFallback = null;

        if ($urlDasar !== '') {
            $pemisahQuery = str_contains($urlDasar, '?') ? '&' : '?';
            $urlTujuan    = $urlDasar . $pemisahQuery . 'kota=' . urlencode($kotaPencarian);

            $hasilPanggil = $this->panggilEndpointMitra($urlTujuan);
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

            $pemisahQuery = str_contains($urlDasar, '?') ? '&' : '?';
            $urlUji       = $urlDasar . $pemisahQuery . 'kota=' . urlencode($kotaPencarian);
            $hasilPanggil = $this->panggilEndpointMitra($urlUji);

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
    // HELPER HTTP ADAPTER & NORMALISASI JSON MITRA
    // =========================================================================

    /**
     * Mengirim permintaan HTTP GET dengan timeout ketat ke endpoint kelompok mitra.
     *
     * @return array<string, mixed>
     */
    private function panggilEndpointMitra(string $urlTujuan): array
    {
        $waktuMulai = microtime(true);

        try {
            // Gunakan getShared = false agar instance cURL tiap mitra berdiri sendiri
            $klienHttp     = Services::curlrequest([], null, null, false);
            $respon        = $klienHttp->get($urlTujuan, [
                'timeout'         => $this->batasWaktuDetik,
                'connect_timeout' => $this->batasWaktuDetik,
                'http_errors'     => false,
                'headers'         => ['Accept' => 'application/json'],
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
                'pesan_error'     => 'Server mitra tidak aktif atau melewati batas waktu ' . $this->batasWaktuDetik . ' detik.',
                'waktu_respon_ms' => $waktuResponMs,
            ];
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
