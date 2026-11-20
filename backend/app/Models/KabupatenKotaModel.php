<?php

namespace App\Models;

use CodeIgniter\Model;

class KabupatenKotaModel extends Model
{
    protected $table            = 'kabupaten_kota';
    protected $primaryKey       = 'kode_kabupaten';
    protected $useAutoIncrement = false;
    protected $keyType          = 'string';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'kode_kabupaten',
        'nama_kabupaten_kota',
        'jenis_daerah',
        'ibu_kota',
    ];

    /**
     * Mengambil daftar 33 kabupaten/kota Sumatera Utara beserta jumlah destinasi wisatanya.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilSemuaDenganJumlahDestinasi(): array
    {
        return $this->select(
            'kabupaten_kota.kode_kabupaten,
             kabupaten_kota.nama_kabupaten_kota,
             kabupaten_kota.jenis_daerah,
             kabupaten_kota.ibu_kota,
             COUNT(destinasi.id) AS jumlah_destinasi'
        )
            ->join('destinasi', 'destinasi.kode_kabupaten = kabupaten_kota.kode_kabupaten', 'left')
            ->groupBy('kabupaten_kota.kode_kabupaten')
            ->orderBy('jumlah_destinasi', 'DESC')
            ->orderBy('kabupaten_kota.nama_kabupaten_kota', 'ASC')
            ->findAll();
    }
}
