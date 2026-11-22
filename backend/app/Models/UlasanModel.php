<?php

namespace App\Models;

use CodeIgniter\Model;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class UlasanModel extends Model
{
    protected $table          = 'ulasan';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;
    protected $createdField   = 'dibuat_pada';
    protected $updatedField   = 'diperbarui_pada';
    protected $allowedFields  = [
        'id_pengguna',
        'id_destinasi',
        'rating',
        'komentar',
    ];

    /**
     * Menyimpan ulasan baru di dalam transaksi ACID PostgreSQL.
     * Kolom rata_rating dan jumlah_ulasan pada tabel destinasi akan diperbarui
     * otomatis oleh Trigger trg_sinkronkan_rating_destinasi.
     *
     * @return array<string, mixed>
     */
    public function simpanUlasanDenganTransaksi(
        int $idPengguna,
        int $idDestinasi,
        int $rating,
        string $komentar
    ): array {
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Nilai rating harus berada di antara 1 sampai 5.');
        }

        $komentarBersih = trim($komentar);
        if ($komentarBersih === '') {
            throw new InvalidArgumentException('Komentar ulasan tidak boleh kosong.');
        }

        // Mulai transaksi ACID; jika gagal di tengah jalan seluruh perubahan di-rollback
        $this->db->transBegin();

        try {
            $dataUlasanBaru = [
                'id_pengguna'  => $idPengguna,
                'id_destinasi' => $idDestinasi,
                'rating'       => $rating,
                'komentar'     => $komentarBersih,
            ];

            $berhasilSimpan = $this->insert($dataUlasanBaru, false);
            if ($berhasilSimpan === false) {
                throw new RuntimeException('Gagal menyimpan ulasan ke database.');
            }

            // Baca rating terbaru yang baru saja diperbarui otomatis oleh Trigger PostgreSQL
            $ringkasanRating = $this->db->table('destinasi')
                ->select('id, rata_rating, jumlah_ulasan')
                ->where('id', $idDestinasi)
                ->get()
                ->getRowArray();

            if ($ringkasanRating === null) {
                throw new RuntimeException('Destinasi tujuan ulasan tidak ditemukan.');
            }

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Status transaksi ulasan gagal.');
            }

            $this->db->transCommit();

            return [
                'ulasan'        => $dataUlasanBaru,
                'rata_rating'   => (float) $ringkasanRating['rata_rating'],
                'jumlah_ulasan' => (int) $ringkasanRating['jumlah_ulasan'],
            ];
        } catch (Throwable $kesalahan) {
            $this->db->transRollback();
            throw $kesalahan;
        }
    }

    /**
     * Mengambil daftar ulasan pengunjung untuk satu destinasi beserta nama pengulas.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilDaftarUlasanDestinasi(int $idDestinasi, int $batas = 20, int $offset = 0): array
    {
        return $this->select(
            'ulasan.id,
             ulasan.id_pengguna,
             ulasan.id_destinasi,
             ulasan.rating,
             ulasan.komentar,
             ulasan.dibuat_pada,
             pengguna.nama AS nama_pengulas'
        )
            ->join('pengguna', 'pengguna.id = ulasan.id_pengguna')
            ->where('ulasan.id_destinasi', $idDestinasi)
            ->orderBy('ulasan.id', 'DESC')
            ->findAll($batas, $offset);
    }
}
