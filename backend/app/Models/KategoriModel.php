<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table          = 'kategori';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'dibuat_pada';
    protected $updatedField   = 'diperbarui_pada';
    protected $allowedFields  = [
        'nama',
        'slug',
        'deskripsi',
    ];

    /**
     * Mengambil seluruh kategori wisata beserta jumlah destinasi aktif pada tiap kategori.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilSemuaDenganJumlahDestinasi(): array
    {
        return $this->select(
            'kategori.id,
             kategori.nama,
             kategori.slug,
             kategori.deskripsi,
             COUNT(destinasi.id) AS jumlah_destinasi'
        )
            ->join('destinasi', 'destinasi.id_kategori = kategori.id', 'left')
            ->groupBy('kategori.id')
            ->orderBy('kategori.id', 'ASC')
            ->findAll();
    }
}
