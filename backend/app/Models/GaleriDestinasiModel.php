<?php

namespace App\Models;

use CodeIgniter\Model;

class GaleriDestinasiModel extends Model
{
    protected $table          = 'galeri_destinasi';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'id_destinasi',
        'url_foto',
        'keterangan',
        'dibuat_pada',
    ];

    /**
     * Mengambil daftar foto galeri pendukung untuk satu destinasi wisata.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilBerdasarkanDestinasi(int $idDestinasi): array
    {
        return $this->select('id, id_destinasi, url_foto, keterangan, dibuat_pada')
            ->where('id_destinasi', $idDestinasi)
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
