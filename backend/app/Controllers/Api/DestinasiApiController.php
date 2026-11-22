<?php

namespace App\Controllers\Api;

use App\Models\DestinasiModel;
use App\Models\FasilitasModel;
use App\Models\GaleriDestinasiModel;
use App\Models\UlasanModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * REST API Controller untuk katalog dan detail destinasi wisata Sumatera Utara.
 * Melayani Aplikasi Client Frontend (port 8081) serta 4 kelompok mitra.
 *
 * Anggota 1 (joxtian) — Katalog Eksplorasi & Detail Destinasi
 */
class DestinasiApiController extends ResourceController
{
    protected $modelName = 'App\Models\DestinasiModel';
    protected $format    = 'json';

    /**
     * GET /api/v1/destinasi
     * Mengambil daftar destinasi Sumatera Utara dengan filter dan paginasi.
     */
    public function index(): ResponseInterface
    {
        $destinasiModel = new DestinasiModel();

        // Kumpulkan parameter filter dari query string
        $filterPencarian = [
            'kata_kunci'     => trim((string) $this->request->getGet('kata_kunci')),
            'kategori'       => trim((string) $this->request->getGet('kategori')),
            'kode_kabupaten' => trim((string) $this->request->getGet('kode_kabupaten')),
            'kota'           => trim((string) $this->request->getGet('kota')),
            'harga_maksimal' => $this->request->getGet('harga_maksimal'),
            'urutan'         => trim((string) $this->request->getGet('urutan')),
        ];

        $halaman = max(1, (int) ($this->request->getGet('halaman') ?? 1));
        $batas   = (int) ($this->request->getGet('batas') ?? 12);
        if ($batas < 1) {
            $batas = 12;
        }
        if ($batas > 50) {
            $batas = 50;
        }

        $offset      = ($halaman - 1) * $batas;
        $totalData   = $destinasiModel->hitungKatalogTerfilter($filterPencarian);
        $totalHalaman = $totalData > 0 ? (int) ceil($totalData / $batas) : 1;
        $daftarBaris = $destinasiModel->ambilKatalogTerfilter($filterPencarian, $batas, $offset);

        $daftarTerformat = array_map(
            fn (array $baris): array => $this->formatItemDestinasi($baris),
            $daftarBaris
        );

        return $this->respond([
            'status'      => 'berhasil',
            'penyedia'    => 'JejakSumut Tourism API',
            'jumlah_data' => count($daftarTerformat),
            'paginasi'    => [
                'halaman_sekarang'  => $halaman,
                'batas_per_halaman' => $batas,
                'total_data'        => $totalData,
                'total_halaman'     => $totalHalaman,
            ],
            'data' => $daftarTerformat,
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * GET /api/v1/destinasi/{slug_atau_id}
     * Mengambil detail lengkap 1 destinasi beserta fasilitas, galeri, dan ulasan terbaru.
     */
    public function show($slugAtauId = null): ResponseInterface
    {
        $kunci = trim((string) $slugAtauId);
        if ($kunci === '') {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Parameter slug atau ID destinasi wajib diisi.',
                'data'   => null,
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $destinasiModel = new DestinasiModel();
        $destinasi      = $destinasiModel->ambilDetailBerdasarkanSlugAtauId($kunci);

        if ($destinasi === null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Destinasi wisata tidak ditemukan.',
                'data'   => null,
            ], ResponseInterface::HTTP_NOT_FOUND);
        }

        $idDestinasi    = (int) $destinasi['id'];
        $fasilitasModel = new FasilitasModel();
        $galeriModel    = new GaleriDestinasiModel();
        $ulasanModel    = new UlasanModel();

        $fasilitasRelasi = $fasilitasModel->ambilBerdasarkanDestinasi($idDestinasi);
        $galeri          = $galeriModel->ambilBerdasarkanDestinasi($idDestinasi);
        $ulasanTerbaru   = $ulasanModel->ambilDaftarUlasanDestinasi($idDestinasi, 10, 0);

        $dataDetail = $this->formatItemDestinasi($destinasi);
        $dataDetail['klasifikasi_status'] = $destinasi['klasifikasi_status'] ?? null;
        $dataDetail['deskripsi']          = $destinasi['deskripsi'];
        $dataDetail['jam_buka']           = $destinasi['jam_buka'] ?? null;
        $dataDetail['jam_tutup']          = $destinasi['jam_tutup'] ?? null;
        $dataDetail['pengelola']          = $destinasi['pengelola'] ?? null;
        $dataDetail['no_telp']            = $destinasi['no_telp'] ?? null;
        $dataDetail['email']              = $destinasi['email'] ?? null;
        $dataDetail['website_sosial']     = $destinasi['website_sosial'] ?? null;
        $dataDetail['sumber_data']        = $destinasi['sumber_data'] ?? null;
        $dataDetail['sumber_url']         = $destinasi['sumber_url'] ?? null;
        $dataDetail['fasilitas_relasi']   = $fasilitasRelasi;
        $dataDetail['galeri']             = $galeri;
        $dataDetail['ulasan_terbaru']     = $ulasanTerbaru;

        return $this->respond([
            'status'   => 'berhasil',
            'penyedia' => 'JejakSumut Tourism API',
            'data'     => $dataDetail,
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * Memformat satu baris destinasi menjadi payload JSON konsisten sesuai kontrak PRD.md.
     *
     * @param array<string, mixed> $baris
     * @return array<string, mixed>
     */
    private function formatItemDestinasi(array $baris): array
    {
        $urlFrontend = rtrim((string) env('JEJAKSUMUT_FRONTEND_URL', 'http://localhost:8081'), '/');

        return [
            'id'                  => (int) $baris['id'],
            'nama'                => $baris['nama'],
            'slug'                => $baris['slug'],
            'kategori'            => $baris['nama_kategori'] ?? '',
            'slug_kategori'       => $baris['slug_kategori'] ?? '',
            'sub_kategori'        => $baris['sub_kategori'] ?? '',
            'klasifikasi_status'  => $baris['klasifikasi_status'] ?? null,
            'kode_kabupaten'      => $baris['kode_kabupaten'] ?? '',
            'nama_kabupaten_kota' => $baris['nama_kabupaten_kota'] ?? $baris['kota'],
            'kota'                => $baris['kota'],
            'provinsi'            => $baris['provinsi'] ?? 'Sumatera Utara',
            'alamat'              => $baris['alamat'] ?? null,
            'koordinat'           => [
                'garis_lintang' => $baris['garis_lintang'] !== null ? (float) $baris['garis_lintang'] : null,
                'garis_bujur'   => $baris['garis_bujur'] !== null ? (float) $baris['garis_bujur'] : null,
            ],
            'harga_tiket'       => (int) ($baris['harga_tiket'] ?? 0),
            'label_harga_tiket' => $baris['label_harga_tiket'] ?? 'Gratis (Rp 0)',
            'keterangan_tiket'  => $baris['keterangan_tiket'] ?? '',
            'jam_operasional'   => $baris['jam_operasional'] ?? 'Informasi jam operasional belum tersedia',
            'deskripsi'         => $baris['deskripsi'] ?? '',
            'pengelola'         => $baris['pengelola'] ?? null,
            'daftar_fasilitas'  => $baris['daftar_fasilitas'] ?? [],
            'rata_rating'       => (float) ($baris['rata_rating'] ?? 0),
            'jumlah_ulasan'     => (int) ($baris['jumlah_ulasan'] ?? 0),
            'foto_utama'        => $baris['foto_tampil'] ?? $baris['foto_utama'],
            'url_detail'        => $urlFrontend . '/destinasi/' . $baris['slug'],
        ];
    }
}
