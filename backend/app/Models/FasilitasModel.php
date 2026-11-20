<?php

namespace App\Models;

use CodeIgniter\Model;

class FasilitasModel extends Model
{
    protected $table          = 'fasilitas';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'nama',
        'slug',
    ];

    /**
     * Mengambil daftar fasilitas milik satu destinasi melalui tabel pivot destinasi_fasilitas.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilBerdasarkanDestinasi(int $idDestinasi): array
    {
        return $this->select('fasilitas.id, fasilitas.nama, fasilitas.slug')
            ->join('destinasi_fasilitas', 'destinasi_fasilitas.id_fasilitas = fasilitas.id')
            ->where('destinasi_fasilitas.id_destinasi', $idDestinasi)
            ->orderBy('fasilitas.nama', 'ASC')
            ->findAll();
    }
}
