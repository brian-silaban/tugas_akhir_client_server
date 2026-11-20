<?php

namespace App\Libraries;

use Config\Services;
use Throwable;

/**
 * HTTP Client untuk mengambil data wisata Sumatera Utara dari REST API Backend.
 * Frontend dilarang mengakses PostgreSQL secara langsung; seluruh data lewat API ini.
 */
class LayananApiBackend
{
    private string $urlDasarApi;
    private int $batasWaktuDetik;

    public function __construct()
    {
        $urlKonfigurasi        = env('JEJAKSUMUT_BACKEND_API_URL') ?: env('INDWALK_BACKEND_API_URL', 'http://localhost:8080/api/v1');
        $this->urlDasarApi     = rtrim((string) $urlKonfigurasi, '/');
        $this->batasWaktuDetik = 5;
    }

    /**
     * Mengambil daftar destinasi beserta paginasi dari GET /api/v1/destinasi.
     *
     * @param array<string, mixed> $parameter
     * @return array<string, mixed>
     */
    public function ambilDaftarDestinasi(array $parameter = []): array
    {
        $hasil = $this->kirimPermintaanGet('/destinasi', $parameter);
        if (! $hasil['terhubung']) {
            return [
                'terhubung'   => false,
                'data'        => [],
                'paginasi'    => [
                    'halaman_sekarang'  => 1,
                    'batas_per_halaman' => 12,
                    'total_data'        => 0,
                    'total_halaman'     => 1,
                ],
                'pesan_error' => $hasil['pesan_error'],
            ];
        }

        $isi = $hasil['isi'];

        return [
            'terhubung'   => true,
            'data'        => is_array($isi['data'] ?? null) ? $isi['data'] : [],
            'paginasi'    => is_array($isi['paginasi'] ?? null) ? $isi['paginasi'] : [
                'halaman_sekarang'  => 1,
                'batas_per_halaman' => 12,
                'total_data'        => count($isi['data'] ?? []),
                'total_halaman'     => 1,
            ],
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
        $hasil = $this->kirimPermintaanGet('/destinasi/' . rawurlencode($slugAtauId));

        return [
            'terhubung'   => $hasil['terhubung'],
            'status_kode' => $hasil['status_kode'],
            'data'        => is_array($hasil['isi']['data'] ?? null) ? $hasil['isi']['data'] : null,
            'pesan_error' => $hasil['pesan_error'],
        ];
    }

    /**
     * Mengambil daftar 6 kategori wisata dari GET /api/v1/kategori.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilDaftarKategori(): array
    {
        $hasil = $this->kirimPermintaanGet('/kategori');
        if (! $hasil['terhubung']) {
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
        $hasil = $this->kirimPermintaanGet('/kabupaten-kota');
        if (! $hasil['terhubung']) {
            return [];
        }

        return is_array($hasil['isi']['data'] ?? null) ? $hasil['isi']['data'] : [];
    }

    /**
     * Helper terpusat untuk menjalankan HTTP GET ke REST API Backend secara aman.
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function kirimPermintaanGet(string $path, array $query = []): array
    {
        // Buang parameter query kosong agar URL bersih
        $queryBersih = array_filter(
            $query,
            static fn ($nilai): bool => $nilai !== null && $nilai !== ''
        );

        $urlTujuan = $this->urlDasarApi . $path;
        if ($queryBersih !== []) {
            $urlTujuan .= '?' . http_build_query($queryBersih);
        }

        try {
            $klienHttp = Services::curlrequest();
            $respon    = $klienHttp->get($urlTujuan, [
                'timeout'     => $this->batasWaktuDetik,
                'http_errors' => false,
                'headers'     => ['Accept' => 'application/json'],
            ]);

            $statusKode = $respon->getStatusCode();
            $isiJson    = json_decode((string) $respon->getBody(), true);

            if (! is_array($isiJson)) {
                return [
                    'terhubung'   => false,
                    'status_kode' => $statusKode,
                    'isi'         => [],
                    'pesan_error' => 'Respons REST API backend tidak valid.',
                ];
            }

            return [
                'terhubung'   => true,
                'status_kode' => $statusKode,
                'isi'         => $isiJson,
                'pesan_error' => $statusKode >= 400 ? ($isiJson['pesan'] ?? 'Permintaan gagal.') : null,
            ];
        } catch (Throwable $kesalahan) {
            return [
                'terhubung'   => false,
                'status_kode' => 0,
                'isi'         => [],
                'pesan_error' => 'Tidak dapat terhubung ke server REST API Backend.',
            ];
        }
    }
}
