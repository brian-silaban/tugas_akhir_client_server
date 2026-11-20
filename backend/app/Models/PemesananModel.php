<?php

namespace App\Models;

use CodeIgniter\Model;

class PemesananModel extends Model
{
    protected $table          = 'pemesanan';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'dibuat_pada';
    protected $updatedField   = 'diperbarui_pada';
    protected $allowedFields  = [
        'kode_pemesanan',
        'id_pengguna',
        'id_destinasi',
        'tanggal_kunjungan',
        'jumlah_pengunjung',
        'total_harga',
        'status',
    ];
}
