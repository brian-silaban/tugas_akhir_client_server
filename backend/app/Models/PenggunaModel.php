<?php

namespace App\Models;

use CodeIgniter\Model;

class PenggunaModel extends Model
{
    protected $table          = 'pengguna';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'dibuat_pada';
    protected $updatedField   = 'diperbarui_pada';
    protected $allowedFields  = [
        'nama',
        'email',
        'kata_sandi',
        'peran',
        'foto_profil',
    ];
}
