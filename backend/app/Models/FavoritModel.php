<?php

namespace App\Models;

use CodeIgniter\Model;

class FavoritModel extends Model
{
    protected $table          = 'favorit';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'id_pengguna',
        'id_destinasi',
        'dibuat_pada',
    ];
}
