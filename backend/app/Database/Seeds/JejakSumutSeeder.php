<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class JejakSumutSeeder extends Seeder
{
    /**
     * Menjalankan pengisian data real JejakSumut (Sumatera Utara) secara idempotent.
     */
    public function run()
    {
        $jalurDataset = APPPATH . 'Database/Seeds/data/wisata_sumut_lengkap.json';
        if (! is_file($jalurDataset)) {
            throw new RuntimeException('File dataset wisata Sumatera Utara tidak ditemukan: ' . $jalurDataset);
        }

        $isiJson = file_get_contents($jalurDataset);
        $dataset = json_decode((string) $isiJson, true);
        if (! is_array($dataset) || empty($dataset['objek_wisata']) || empty($dataset['kabupaten_kota'])) {
            throw new RuntimeException('Format file dataset wisata_sumut_lengkap.json tidak valid.');
        }

        $waktuSekarang = date('Y-m-d H:i:s');

        $this->isiPenggunaAwal($waktuSekarang);
        $this->isiKabupatenKota($dataset['kabupaten_kota']);
        $petaKategori = $this->isiKategoriWisata($waktuSekarang);
        $petaFasilitas = $this->isiMasterFasilitas($dataset['objek_wisata']);
        $this->isiDestinasiDanRelasi($dataset['objek_wisata'], $petaKategori, $petaFasilitas, $waktuSekarang);
        $this->sinkronkanSequencePostgres();
    }

    /**
     * Mengisi akun awal secara idempotent.
     */
    private function isiPenggunaAwal(string $waktuSekarang): void
    {
        $daftarPengguna = [
            [
                'nama'            => 'Raka Pratama',
                'email'           => 'raka@jejaksumut.id',
                'kata_sandi'      => password_hash('member123', PASSWORD_DEFAULT),
                'peran'           => 'member',
                'foto_profil'     => null,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'nama'            => 'Nadia Kusuma',
                'email'           => 'nadia@jejaksumut.id',
                'kata_sandi'      => password_hash('member123', PASSWORD_DEFAULT),
                'peran'           => 'member',
                'foto_profil'     => null,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
        ];

        // Akun admin baru hanya dibuat bila operator memasok kata sandi sendiri.
        $kataSandiAdmin = (string) env('JEJAKSUMUT_KATA_SANDI_ADMIN_AWAL', '');
        if ($kataSandiAdmin !== '') {
            if (strlen($kataSandiAdmin) < 16) {
                throw new \InvalidArgumentException('Kata sandi admin awal minimal 16 karakter.');
            }

            $daftarPengguna[] = [
                'nama'            => 'Admin JejakSumut',
                'email'           => 'admin@jejaksumut.id',
                'kata_sandi'      => password_hash($kataSandiAdmin, PASSWORD_DEFAULT),
                'peran'           => 'admin',
                'foto_profil'     => null,
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ];
        }

        $barisExisting = $this->db->table('pengguna')->select('email')->get()->getResultArray();
        $emailTerdaftar = array_flip(array_column($barisExisting, 'email'));

        $penggunaBaru = [];
        foreach ($daftarPengguna as $pengguna) {
            if (! isset($emailTerdaftar[$pengguna['email']])) {
                $penggunaBaru[] = $pengguna;
            }
        }

        if ($penggunaBaru !== []) {
            $this->db->table('pengguna')->insertBatch($penggunaBaru);
        }
    }

    /**
     * Mengisi 33 kabupaten/kota di Provinsi Sumatera Utara secara idempotent.
     */
    private function isiKabupatenKota(array $dataKabupaten): void
    {
        $barisExisting = $this->db->table('kabupaten_kota')->select('kode_kabupaten')->get()->getResultArray();
        $kodeTerdaftar = array_flip(array_column($barisExisting, 'kode_kabupaten'));

        $kabupatenBaru = [];
        foreach ($dataKabupaten as $wilayah) {
            $kode = (string) $wilayah['kode_kabupaten'];
            if (! isset($kodeTerdaftar[$kode])) {
                $kabupatenBaru[] = [
                    'kode_kabupaten'      => $kode,
                    'nama_kabupaten_kota' => (string) $wilayah['nama_kabupaten_kota'],
                    'jenis_daerah'        => (string) $wilayah['jenis_daerah'],
                    'ibu_kota'            => (string) $wilayah['ibu_kota'],
                ];
            }
        }

        if ($kabupatenBaru !== []) {
            $this->db->table('kabupaten_kota')->insertBatch($kabupatenBaru);
        }
    }

    /**
     * Mengisi 6 kategori wisata Sumatera Utara dan mengembalikan peta nama => id.
     *
     * @return array<string, int>
     */
    private function isiKategoriWisata(string $waktuSekarang): array
    {
        $daftarKategori = [
            [
                'id'              => 1,
                'nama'            => 'Wisata Alam',
                'slug'            => 'wisata-alam',
                'deskripsi'       => 'Air terjun, danau, gunung, bukit, gua, pemandian alam, hutan wisata, dan sungai.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id'              => 2,
                'nama'            => 'Wisata Bahari & Pantai',
                'slug'            => 'wisata-bahari-pantai',
                'deskripsi'       => 'Pantai, pulau wisata, terumbu karang, dan spot selancar/snorkeling.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id'              => 3,
                'nama'            => 'Wisata Budaya & Sejarah',
                'slug'            => 'wisata-budaya-sejarah',
                'deskripsi'       => 'Candi, situs arkeologi, istana, museum, rumah adat, monumen, dan cagar budaya.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id'              => 4,
                'nama'            => 'Wisata Religi',
                'slug'            => 'wisata-religi',
                'deskripsi'       => 'Masjid bersejarah, gereja bersejarah/bukit doa, vihara, pagoda, dan kuil.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id'              => 5,
                'nama'            => 'Wisata Buatan & Edukasi',
                'slug'            => 'wisata-buatan-edukasi',
                'deskripsi'       => 'Taman kota, taman rekreasi, kebun binatang, agrowisata, waterpark, dan spot edukasi.',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
            [
                'id'              => 6,
                'nama'            => 'Desa Wisata',
                'slug'            => 'desa-wisata',
                'deskripsi'       => 'Desa wisata resmi terdaftar Kemenpar/Disbudparekraf (Rintisan, Berkembang, Maju, Mandiri).',
                'dibuat_pada'     => $waktuSekarang,
                'diperbarui_pada' => $waktuSekarang,
            ],
        ];

        $barisExisting = $this->db->table('kategori')->select('id, slug')->get()->getResultArray();
        $slugTerdaftar = array_flip(array_column($barisExisting, 'slug'));

        $kategoriBaru = [];
        foreach ($daftarKategori as $kategori) {
            if (! isset($slugTerdaftar[$kategori['slug']])) {
                $kategoriBaru[] = $kategori;
            }
        }

        if ($kategoriBaru !== []) {
            $this->db->table('kategori')->insertBatch($kategoriBaru);
        }

        $semuaKategori = $this->db->table('kategori')->select('id, nama')->get()->getResultArray();
        $petaKategori  = [];
        foreach ($semuaKategori as $baris) {
            $petaKategori[$baris['nama']] = (int) $baris['id'];
        }

        return $petaKategori;
    }

    /**
     * Mengisi 17 master fasilitas aktif dan mengembalikan peta nama => id.
     *
     * @return array<string, int>
     */
    private function isiMasterFasilitas(array $daftarObjek): array
    {
        $fasilitasUnik = [];
        foreach ($daftarObjek as $objek) {
            foreach ($this->saringFasilitasAktif($objek['fasilitas'] ?? []) as $namaFasilitas) {
                $fasilitasUnik[$namaFasilitas] = $this->buatSlug($namaFasilitas);
            }
        }

        $barisExisting = $this->db->table('fasilitas')->select('nama')->get()->getResultArray();
        $namaTerdaftar = array_flip(array_column($barisExisting, 'nama'));

        $fasilitasBaru = [];
        foreach ($fasilitasUnik as $nama => $slug) {
            if (! isset($namaTerdaftar[$nama])) {
                $fasilitasBaru[] = [
                    'nama' => $nama,
                    'slug' => $slug,
                ];
            }
        }

        if ($fasilitasBaru !== []) {
            $this->db->table('fasilitas')->insertBatch($fasilitasBaru);
        }

        $semuaFasilitas = $this->db->table('fasilitas')->select('id, nama')->get()->getResultArray();
        $petaFasilitas  = [];
        foreach ($semuaFasilitas as $baris) {
            $petaFasilitas[$baris['nama']] = (int) $baris['id'];
        }

        return $petaFasilitas;
    }

    /**
     * Mengisi 621 objek wisata Sumatera Utara beserta tabel pivot destinasi_fasilitas.
     *
     * @param array<string, int> $petaKategori
     * @param array<string, int> $petaFasilitas
     */
    private function isiDestinasiDanRelasi(
        array $daftarObjek,
        array $petaKategori,
        array $petaFasilitas,
        string $waktuSekarang
    ): void {
        $barisExisting = $this->db->table('destinasi')->select('id, slug')->get()->getResultArray();
        $idTerdaftar   = [];
        $slugTerpakai  = [];
        foreach ($barisExisting as $baris) {
            $idTerdaftar[(int) $baris['id']] = true;
            $slugTerpakai[$baris['slug']]    = true;
        }

        $relasiExisting = $this->db->table('destinasi_fasilitas')
            ->select('id_destinasi, id_fasilitas')
            ->get()
            ->getResultArray();
        $relasiTerdaftar = [];
        foreach ($relasiExisting as $rel) {
            $relasiTerdaftar[$rel['id_destinasi'] . ':' . $rel['id_fasilitas']] = true;
        }

        $destinasiBaru = [];
        $relasiBaru    = [];

        foreach ($daftarObjek as $objek) {
            $idDestinasi       = (int) $objek['id_wisata'];
            $nama              = trim((string) $objek['nama']);
            $kota              = trim((string) $objek['kabupaten_kota']);
            $kategoriUtama     = trim((string) $objek['kategori_utama']);
            $subKategori       = trim((string) $objek['sub_kategori']);
            $klasifikasiStatus = $this->nilaiAtauNull($objek['klasifikasi_status'] ?? null);
            $fasilitasAktif    = $this->saringFasilitasAktif($objek['fasilitas'] ?? []);

            if (! isset($idTerdaftar[$idDestinasi])) {
                // Pastikan slug unik; tambahkan nama kabupaten/kota atau ID bila ada duplikat nama lintas kabupaten
                $slugDasar = $this->buatSlug($nama);
                $slug      = $slugDasar;
                if (isset($slugTerpakai[$slug])) {
                    $slug = $slugDasar . '-' . $this->buatSlug($kota);
                }
                if (isset($slugTerpakai[$slug])) {
                    $slug .= '-' . $idDestinasi;
                }
                $slugTerpakai[$slug] = true;

                // Deskripsi fallback otomatis bila kosong di dataset
                $deskripsi = trim((string) ($objek['deskripsi'] ?? ''));
                if ($deskripsi === '') {
                    $bagianStatus = $klasifikasiStatus !== null ? ' (' . $klasifikasiStatus . ')' : '';
                    $deskripsi    = $nama . ' merupakan objek wisata ' . $subKategori . $bagianStatus
                        . ' yang berlokasi di ' . $kota . ', Provinsi Sumatera Utara.';
                }

                // URL foto fallback per kategori bila gambar_url kosong
                $fotoUtama = trim((string) ($objek['gambar_url'] ?? ''));
                if ($fotoUtama === '') {
                    $fotoUtama = $this->ambilFotoCadanganKategori($kategoriUtama);
                }

                $fasilitasJson = $fasilitasAktif !== []
                    ? json_encode($fasilitasAktif, JSON_UNESCAPED_UNICODE)
                    : null;

                $destinasiBaru[] = [
                    'id'                 => $idDestinasi,
                    'id_kategori'        => $petaKategori[$kategoriUtama] ?? 1,
                    'kode_kabupaten'     => (string) $objek['kode_kabupaten'],
                    'nama'               => $nama,
                    'slug'               => $slug,
                    'sub_kategori'       => $subKategori,
                    'klasifikasi_status' => $klasifikasiStatus,
                    'kota'               => $kota,
                    'provinsi'           => 'Sumatera Utara',
                    'alamat'             => $this->nilaiAtauNull($objek['alamat'] ?? null),
                    'garis_lintang'      => $objek['latitude'] !== null && $objek['latitude'] !== '' ? $objek['latitude'] : null,
                    'garis_bujur'        => $objek['longitude'] !== null && $objek['longitude'] !== '' ? $objek['longitude'] : null,
                    'harga_tiket'        => (int) ($objek['harga_tiket_rp'] ?? 0),
                    'label_harga_tiket'  => (string) ($objek['harga_tiket'] ?? 'Gratis (Rp 0)'),
                    'keterangan_tiket'   => (string) ($objek['keterangan_tiket'] ?? 'Belum ada tarif resmi terpublikasi'),
                    'jam_buka'           => null,
                    'jam_tutup'          => null,
                    'deskripsi'          => $deskripsi,
                    'fasilitas'          => $fasilitasJson,
                    'pengelola'          => $this->nilaiAtauNull($objek['pengelola'] ?? null),
                    'no_telp'            => $this->nilaiAtauNull($objek['no_telp'] ?? null),
                    'email'              => $this->nilaiAtauNull($objek['email'] ?? null),
                    'website_sosial'     => $this->nilaiAtauNull($objek['website_sosial'] ?? null),
                    'foto_utama'         => $fotoUtama,
                    'sumber_data'        => $this->nilaiAtauNull($objek['sumber_data'] ?? null),
                    'sumber_url'         => $this->nilaiAtauNull($objek['sumber_url'] ?? null),
                    'rata_rating'        => 0.00,
                    'jumlah_ulasan'      => 0,
                    'dibuat_pada'        => $waktuSekarang,
                    'diperbarui_pada'    => $waktuSekarang,
                ];
            }

            foreach ($fasilitasAktif as $namaFasilitas) {
                if (! isset($petaFasilitas[$namaFasilitas])) {
                    continue;
                }
                $idFasilitas = $petaFasilitas[$namaFasilitas];
                $kunciRelasi = $idDestinasi . ':' . $idFasilitas;
                if (! isset($relasiTerdaftar[$kunciRelasi])) {
                    $relasiTerdaftar[$kunciRelasi] = true;
                    $relasiBaru[]                  = [
                        'id_destinasi' => $idDestinasi,
                        'id_fasilitas' => $idFasilitas,
                    ];
                }
            }
        }

        if ($destinasiBaru !== []) {
            foreach (array_chunk($destinasiBaru, 100) as $potongan) {
                $this->db->table('destinasi')->insertBatch($potongan);
            }
        }

        if ($relasiBaru !== []) {
            foreach (array_chunk($relasiBaru, 250) as $potongan) {
                $this->db->table('destinasi_fasilitas')->insertBatch($potongan);
            }
        }
    }

    /**
     * Menyelaraskan sequence auto-increment PostgreSQL setelah seeding eksplisit dengan ID.
     */
    private function sinkronkanSequencePostgres(): void
    {
        $daftarTabel = [
            'kategori',
            'destinasi',
            'fasilitas',
            'pengguna',
            'galeri_destinasi',
            'pemesanan',
            'ulasan',
            'favorit',
        ];

        foreach ($daftarTabel as $namaTabel) {
            $this->db->query(
                "SELECT setval(
                    pg_get_serial_sequence('{$namaTabel}', 'id'),
                    COALESCE((SELECT MAX(id) FROM {$namaTabel}), 1),
                    (SELECT COUNT(*) > 0 FROM {$namaTabel})
                )"
            );
        }
    }

    /**
     * Menyaring daftar fasilitas agar mengabaikan teks placeholder dataset.
     *
     * @return list<string>
     */
    private function saringFasilitasAktif(array $daftarFasilitas): array
    {
        $hasil = [];
        foreach ($daftarFasilitas as $item) {
            $namaBersih = trim((string) $item);
            if ($namaBersih === '' || strcasecmp($namaBersih, 'Belum ada fasilitas untuk ditampilkan') === 0) {
                continue;
            }
            $hasil[] = $namaBersih;
        }

        return array_values(array_unique($hasil));
    }

    /**
     * Menghasilkan slug URL lowercase bersih dari string teks.
     */
    private function buatSlug(string $teks): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $teks), '-'));

        return $slug !== '' ? $slug : 'wisata';
    }

    /**
     * Mengubah string kosong menjadi null agar bersih di PostgreSQL.
     */
    private function nilaiAtauNull(mixed $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $teks = trim((string) $nilai);

        return $teks !== '' ? $teks : null;
    }

    /**
     * Mengembalikan URL foto cadangan sesuai kategori wisata Sumatera Utara.
     */
    private function ambilFotoCadanganKategori(string $namaKategori): string
    {
        $petaFoto = [
            'Wisata Alam'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Lake_Toba_and_the_surrounding_hills.jpg/1280px-Lake_Toba_and_the_surrounding_hills.jpg',
            'Wisata Bahari & Pantai'  => 'https://images.unsplash.com/photo-1516690561799-46d8f74f9abf?auto=format&fit=crop&w=1200&q=80',
            'Wisata Budaya & Sejarah' => 'https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80',
            'Wisata Religi'           => 'https://upload.wikimedia.org/wikipedia/commons/7/71/TamanLumbini20110607.jpg',
            'Wisata Buatan & Edukasi' => 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=1200&q=80',
            'Desa Wisata'             => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80',
        ];

        return $petaFoto[$namaKategori] ?? $petaFoto['Wisata Alam'];
    }
}
