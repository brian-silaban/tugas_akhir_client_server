<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * FavoritModel — Model untuk tabel 'favorit'.
 * Mengelola daftar destinasi wisata yang disimpan/difavoritkan oleh pengguna.
 */
class FavoritModel extends Model
{
    protected $table            = 'favorit';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Kolom yang dapat diisi
    protected $allowedFields = [
        'id_pengguna',
        'id_destinasi',
        'dibuat_pada',
    ];

    protected $useTimestamps = false;

    // ---------------------------------------------------------------
    // Fungsi Buatan Sendiri Berbahasa Indonesia
    // ---------------------------------------------------------------

    /**
     * Mengecek apakah suatu destinasi sudah ada di daftar favorit pengguna.
     *
     * @param int $idPengguna
     * @param int $idDestinasi
     * @return bool
     */
    public function cekFavorit(int $idPengguna, int $idDestinasi): bool
    {
        $ada = $this->where('id_pengguna', $idPengguna)
                    ->where('id_destinasi', $idDestinasi)
                    ->first();

        return $ada !== null;
    }

    /**
     * Mengubah status favorit (toggle):
     * Jika sudah ada di tabel favorit maka dihapus, jika belum ada maka ditambahkan.
     *
     * @param int $idPengguna
     * @param int $idDestinasi
     * @return array Status aksi ('tambah' atau 'hapus') dan status keberhasilan
     */
    public function ubahFavorit(int $idPengguna, int $idDestinasi): array
    {
        $favoritAda = $this->where('id_pengguna', $idPengguna)
                           ->where('id_destinasi', $idDestinasi)
                           ->first();

        if ($favoritAda !== null) {
            // Hapus dari favorit
            $this->delete($favoritAda['id']);

            return [
                'status'    => 'berhasil',
                'aksi'      => 'hapus',
                'difavorit' => false,
                'pesan'     => 'Destinasi berhasil dihapus dari daftar favorit.',
                'data'      => null,
            ];
        }

        // Tambahkan ke favorit
        $dataBaru = [
            'id_pengguna'  => $idPengguna,
            'id_destinasi' => $idDestinasi,
            'dibuat_pada'  => date('Y-m-d H:i:s'),
        ];

        $this->insert($dataBaru);

        return [
            'status'    => 'berhasil',
            'aksi'      => 'tambah',
            'difavorit' => true,
            'pesan'     => 'Destinasi berhasil ditambahkan ke daftar favorit.',
            'data'      => null,
        ];
    }

    /**
     * Mengambil seluruh daftar destinasi favorit milik pengguna tertentu,
     * lengkap dengan data destinasi dan nama kategori.
     *
     * @param int $idPengguna
     * @return array
     */
    public function ambilFavoritPengguna(int $idPengguna): array
    {
        return $this->select('favorit.id AS id_favorit, favorit.dibuat_pada AS tanggal_favorit, destinasi.*, kategori.nama AS nama_kategori, kategori.slug AS slug_kategori')
                    ->join('destinasi', 'destinasi.id = favorit.id_destinasi')
                    ->join('kategori', 'kategori.id = destinasi.id_kategori', 'left')
                    ->where('favorit.id_pengguna', $idPengguna)
                    ->orderBy('favorit.dibuat_pada', 'DESC')
                    ->findAll();
    }
}
