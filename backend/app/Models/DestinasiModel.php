<?php

namespace App\Models;

use CodeIgniter\Model;

class DestinasiModel extends Model
{
    protected $table          = 'destinasi';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'dibuat_pada';
    protected $updatedField   = 'diperbarui_pada';
    protected $allowedFields  = [
        'id_kategori',
        'kode_kabupaten',
        'nama',
        'slug',
        'sub_kategori',
        'klasifikasi_status',
        'kota',
        'provinsi',
        'alamat',
        'garis_lintang',
        'garis_bujur',
        'harga_tiket',
        'label_harga_tiket',
        'keterangan_tiket',
        'jam_buka',
        'jam_tutup',
        'deskripsi',
        'fasilitas',
        'pengelola',
        'no_telp',
        'email',
        'website_sosial',
        'foto_utama',
        'sumber_data',
        'sumber_url',
        'rata_rating',
        'jumlah_ulasan',
    ];

    /**
     * Mengambil daftar destinasi terfilter beserta data kategori dan kabupaten/kota.
     *
     * @param array<string, mixed> $filterPencarian
     * @return list<array<string, mixed>>
     */
    public function ambilKatalogTerfilter(array $filterPencarian = [], int $batas = 12, int $offset = 0): array
    {
        $builder = $this->select(
            'destinasi.*,
             kategori.nama AS nama_kategori,
             kategori.slug AS slug_kategori,
             kabupaten_kota.nama_kabupaten_kota,
             kabupaten_kota.jenis_daerah,
             kabupaten_kota.ibu_kota'
        )
            ->join('kategori', 'kategori.id = destinasi.id_kategori')
            ->join('kabupaten_kota', 'kabupaten_kota.kode_kabupaten = destinasi.kode_kabupaten');

        $this->terapkanFilterKatalog($builder, $filterPencarian);

        $urutan = $filterPencarian['urutan'] ?? 'terbaik';
        if ($urutan === 'harga_terendah') {
            $builder->orderBy('destinasi.harga_tiket', 'ASC')->orderBy('destinasi.id', 'ASC');
        } elseif ($urutan === 'terbaru') {
            $builder->orderBy('destinasi.id', 'DESC');
        } else {
            // Prioritaskan destinasi dengan rating tertinggi, jumlah ulasan terbanyak, dan data kaya
            $builder->orderBy('destinasi.rata_rating', 'DESC')
                ->orderBy('destinasi.jumlah_ulasan', 'DESC')
                ->orderBy('destinasi.id', 'ASC');
        }

        $hasil = $builder->findAll($batas, $offset);

        return array_map(fn (array $baris): array => $this->lengkapiAtributTampilan($baris), $hasil);
    }

    /**
     * Menghitung total destinasi yang memenuhi kriteria filter untuk kebutuhan paginasi.
     *
     * @param array<string, mixed> $filterPencarian
     */
    public function hitungKatalogTerfilter(array $filterPencarian = []): int
    {
        $builder = $this->builder()
            ->join('kategori', 'kategori.id = destinasi.id_kategori')
            ->join('kabupaten_kota', 'kabupaten_kota.kode_kabupaten = destinasi.kode_kabupaten');

        $this->terapkanFilterKatalog($builder, $filterPencarian);

        return (int) $builder->countAllResults();
    }

    /**
     * Menerapkan kondisi pencarian dan filter yang sama untuk pengambilan maupun penghitungan data.
     *
     * @param object               $builder
     * @param array<string, mixed> $filterPencarian
     */
    private function terapkanFilterKatalog(object $builder, array $filterPencarian): void
    {
        if (! empty($filterPencarian['kata_kunci'])) {
            $kataKunci = trim((string) $filterPencarian['kata_kunci']);
            $builder->groupStart()
                ->like('destinasi.nama', $kataKunci, 'both', null, true)
                ->orLike('destinasi.kota', $kataKunci, 'both', null, true)
                ->orLike('destinasi.sub_kategori', $kataKunci, 'both', null, true)
                ->orLike('destinasi.alamat', $kataKunci, 'both', null, true)
                ->groupEnd();
        }

        if (! empty($filterPencarian['kategori'])) {
            $builder->where('kategori.slug', (string) $filterPencarian['kategori']);
        }

        if (! empty($filterPencarian['kode_kabupaten'])) {
            $builder->where('destinasi.kode_kabupaten', (string) $filterPencarian['kode_kabupaten']);
        }

        if (! empty($filterPencarian['kota'])) {
            $builder->like('destinasi.kota', trim((string) $filterPencarian['kota']), 'both', null, true);
        }

        if (isset($filterPencarian['harga_maksimal']) && $filterPencarian['harga_maksimal'] !== '') {
            $builder->where('destinasi.harga_tiket <=', (int) $filterPencarian['harga_maksimal']);
        }
    }

    /**
     * Mengambil satu destinasi lengkap berdasarkan slug atau ID numerik.
     *
     * @return array<string, mixed>|null
     */
    public function ambilDetailBerdasarkanSlugAtauId(string|int $slugAtauId): ?array
    {
        $builder = $this->select(
            'destinasi.*,
             kategori.nama AS nama_kategori,
             kategori.slug AS slug_kategori,
             kabupaten_kota.nama_kabupaten_kota,
             kabupaten_kota.jenis_daerah,
             kabupaten_kota.ibu_kota'
        )
            ->join('kategori', 'kategori.id = destinasi.id_kategori')
            ->join('kabupaten_kota', 'kabupaten_kota.kode_kabupaten = destinasi.kode_kabupaten');

        if (is_numeric($slugAtauId)) {
            $builder->where('destinasi.id', (int) $slugAtauId);
        } else {
            $builder->where('destinasi.slug', (string) $slugAtauId);
        }

        $baris = $builder->first();
        if ($baris === null) {
            return null;
        }

        return $this->lengkapiAtributTampilan($baris);
    }

    /**
     * Mengambil data dari SQL View v_wisata_lengkap.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilDariViewLengkap(int $batas = 12, int $offset = 0): array
    {
        return $this->db->table('v_wisata_lengkap')
            ->orderBy('id', 'ASC')
            ->limit($batas, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Memformat jam buka dan jam tutup secara aman ketika bernilai null di database.
     */
    public function formatJamOperasional(?string $jamBuka, ?string $jamTutup): string
    {
        if (! empty($jamBuka) && ! empty($jamTutup)) {
            return substr($jamBuka, 0, 5) . ' – ' . substr($jamTutup, 0, 5) . ' WIB';
        }

        if (! empty($jamBuka)) {
            return 'Buka mulai ' . substr($jamBuka, 0, 5) . ' WIB';
        }

        return 'Informasi jam operasional belum tersedia';
    }

    /**
     * Menguraikan string JSON fasilitas dan melengkapi field siap tampil pada View/API.
     *
     * @param array<string, mixed> $baris
     * @return array<string, mixed>
     */
    private function lengkapiAtributTampilan(array $baris): array
    {
        $daftarFasilitas = [];
        if (! empty($baris['fasilitas'])) {
            $hasilDecode = json_decode((string) $baris['fasilitas'], true);
            if (is_array($hasilDecode)) {
                $daftarFasilitas = array_values(array_filter(
                    $hasilDecode,
                    static fn ($item): bool => is_string($item)
                        && trim($item) !== ''
                        && strcasecmp(trim($item), 'Belum ada fasilitas untuk ditampilkan') !== 0
                ));
            }
        }

        $baris['daftar_fasilitas'] = $daftarFasilitas;
        $baris['jam_operasional']  = $this->formatJamOperasional(
            $baris['jam_buka'] ?? null,
            $baris['jam_tutup'] ?? null
        );
        $baris['foto_tampil'] = $this->resolveFotoUtama(
            $baris['foto_utama'] ?? null,
            (string) ($baris['nama_kategori'] ?? 'Wisata Alam')
        );

        return $baris;
    }

    /**
     * Memastikan URL foto aman ditampilkan; bila path lokal belum tersedia atau masih memakai fallback lama luar Sumut, gunakan foto cadangan kategori.
     */
    private function resolveFotoUtama(?string $fotoUtama, string $namaKategori): string
    {
        $foto = trim((string) $fotoUtama);

        $daftarFallbackLama = [
            'photo-1544644181-1484b3fdfc62',
            'photo-1588668214407-6ea9a6d8c272',
            'photo-1516690561799-46d8f74f9abf',
            'photo-1518548419970-58e3b4079ab2',
            'photo-1537996194471-e657df975ab4',
        ];

        $apakahFallbackLama = false;
        foreach ($daftarFallbackLama as $idFotoLama) {
            if (str_contains($foto, $idFotoLama)) {
                $apakahFallbackLama = true;
                break;
            }
        }

        if (
            $foto !== ''
            && preg_match('#^https?://#i', $foto) === 1
            && ! $apakahFallbackLama
        ) {
            return $foto;
        }

        if ($foto !== '' && ! $apakahFallbackLama && defined('FCPATH')) {
            $jalurLokal = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $foto), '/\\');
            if (is_file($jalurLokal)) {
                return base_url(ltrim($foto, '/'));
            }
        }

        return $this->tentukanFotoFallbackKategori($namaKategori);
    }

    /**
     * Menentukan URL foto cadangan bernuansa Sumatera Utara berdasarkan kategori wisata.
     */
    public function tentukanFotoFallbackKategori(string $namaKategori): string
    {
        $petaFoto = [
            'Wisata Alam'             => 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Lake_Toba_and_the_surrounding_hills.jpg',
            'Wisata Bahari & Pantai'  => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/e/e1/Ndulu_pantai_sorake.jpg/1280px-Ndulu_pantai_sorake.jpg',
            'Wisata Budaya & Sejarah' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/6/6c/Maimun_Palace_Medan_Indonesia.jpg/1280px-Maimun_Palace_Medan_Indonesia.jpg',
            'Wisata Religi'           => 'https://upload.wikimedia.org/wikipedia/commons/7/71/TamanLumbini20110607.jpg',
            'Wisata Buatan & Edukasi' => 'https://upload.wikimedia.org/wikipedia/commons/6/6f/Taman_Budaya_Medan.jpg',
            'Desa Wisata'             => 'https://upload.wikimedia.org/wikipedia/commons/a/a3/Bakkara_Village%2C_North_Sumatra%2C_Indonesia.jpg',
        ];

        return $petaFoto[$namaKategori] ?? $petaFoto['Wisata Alam'];
    }
}
