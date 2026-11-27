<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PenggunaModel — Model untuk tabel 'pengguna'.
 * Mengelola data akun pengguna (admin & member) di PostgreSQL.
 */
class PenggunaModel extends Model
{
    protected $table            = 'pengguna';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Kolom yang boleh diisi melalui insert/update
    protected $allowedFields = [
        'nama',
        'email',
        'kata_sandi',
        'peran',
        'foto_profil',
        'dibuat_pada',
        'diperbarui_pada',
    ];

    // Nonaktifkan timestamps otomatis CI4 karena kita kelola manual
    protected $useTimestamps = false;

    // ---------------------------------------------------------------
    // Aturan Validasi Bawaan Model
    // ---------------------------------------------------------------
    protected $validationRules = [
        'nama'       => 'required|min_length[3]|max_length[120]',
        'email'      => 'required|valid_email|max_length[150]',
        'kata_sandi' => 'required|min_length[6]',
    ];

    protected $validationMessages = [
        'nama' => [
            'required'   => 'Nama lengkap wajib diisi.',
            'min_length' => 'Nama minimal 3 karakter.',
        ],
        'email' => [
            'required'    => 'Alamat email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
        ],
        'kata_sandi' => [
            'required'   => 'Kata sandi wajib diisi.',
            'min_length' => 'Kata sandi minimal 6 karakter.',
        ],
    ];

    // ---------------------------------------------------------------
    // Fungsi Buatan Sendiri (Bahasa Indonesia)
    // ---------------------------------------------------------------

    /**
     * Cari data pengguna berdasarkan alamat email secara case-insensitive.
     * Digunakan saat proses login dan pengecekan email duplikat.
     *
     * @param string $email
     * @return array|null
     */
    public function cariDenganEmail(string $email): ?array
    {
        $emailBersih = strtolower(trim($email));
        if ($emailBersih === '') {
            return null;
        }

        $baris = $this->db->query(
            'SELECT * FROM ' . $this->db->protectIdentifiers($this->table) . ' WHERE LOWER(email) = ? LIMIT 1',
            [$emailBersih]
        )->getRowArray();

        if (! is_array($baris)) {
            return null;
        }

        return $baris;
    }
}
