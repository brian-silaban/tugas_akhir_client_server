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
}
