<?php

namespace App\Models;

use CodeIgniter\Model;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

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

    /**
     * Membuat pemesanan tiket baru di dalam transaksi ACID PostgreSQL.
     * Memakai row lock (FOR SHARE) saat membaca harga destinasi dan memanggil
     * Stored Function fn_buat_kode_pemesanan() agar tidak terjadi kode ganda.
     *
     * @return array<string, mixed>
     */
    public function buatPemesananDenganTransaksi(
        int $idPengguna,
        int $idDestinasi,
        string $tanggalKunjungan,
        int $jumlahPengunjung
    ): array {
        if ($jumlahPengunjung < 1) {
            throw new InvalidArgumentException('Jumlah pengunjung minimal 1 orang.');
        }

        // Mulai transaksi ACID; apabila ada langkah gagal di tengah proses akan di-rollback
        $this->db->transBegin();

        try {
            // Kunci baca baris destinasi (FOR SHARE) agar harga tiket tetap konsisten selama transaksi
            $queryDestinasi = $this->db->query(
                'SELECT id, nama, harga_tiket FROM destinasi WHERE id = ? FOR SHARE',
                [$idDestinasi]
            );
            $dataDestinasi = $queryDestinasi->getRowArray();

            if ($dataDestinasi === null) {
                throw new RuntimeException('Destinasi wisata tidak ditemukan.');
            }

            // Panggil Stored Function PostgreSQL untuk menghasilkan kode pemesanan unik (JSM-YYYYMMDD-XXX)
            $queryKode = $this->db->query(
                'SELECT fn_buat_kode_pemesanan(CURRENT_DATE) AS kode_pemesanan'
            );
            $hasilKode     = $queryKode->getRowArray();
            $kodePemesanan = (string) ($hasilKode['kode_pemesanan'] ?? '');

            if ($kodePemesanan === '') {
                throw new RuntimeException('Gagal menghasilkan kode pemesanan dari fungsi database.');
            }

            // Hitung total harga tiket berdasarkan harga resmi di database
            $hargaSatuan = (int) $dataDestinasi['harga_tiket'];
            $totalHarga  = $hargaSatuan * $jumlahPengunjung;

            $dataPemesananBaru = [
                'kode_pemesanan'    => $kodePemesanan,
                'id_pengguna'       => $idPengguna,
                'id_destinasi'      => $idDestinasi,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'jumlah_pengunjung' => $jumlahPengunjung,
                'total_harga'       => $totalHarga,
                'status'            => 'menunggu',
            ];

            $berhasilSimpan = $this->insert($dataPemesananBaru, false);
            if ($berhasilSimpan === false) {
                throw new RuntimeException('Gagal menyimpan data pemesanan ke database.');
            }

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Status transaksi pemesanan gagal.');
            }

            $this->db->transCommit();

            return $this->ambilBerdasarkanKode($kodePemesanan) ?? $dataPemesananBaru;
        } catch (Throwable $kesalahan) {
            // Rollback otomatis sehingga tidak ada data setengah tersimpan
            $this->db->transRollback();
            throw $kesalahan;
        }
    }

    /**
     * Mengubah status pemesanan melalui Stored Procedure sp_verifikasi_pemesanan()
     * di dalam transaksi ACID PostgreSQL.
     *
     * @return array<string, mixed>|null
     */
    public function ubahStatusDenganProsedur(int $idPemesanan, string $statusBaru): ?array
    {
        $this->db->transBegin();

        try {
            $this->db->query(
                'CALL sp_verifikasi_pemesanan(?, ?)',
                [$idPemesanan, $statusBaru]
            );

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Gagal menjalankan prosedur verifikasi pemesanan.');
            }

            $this->db->transCommit();

            return $this->ambilSatuRingkasan($idPemesanan);
        } catch (Throwable $kesalahan) {
            $this->db->transRollback();
            throw $kesalahan;
        }
    }

    /**
     * Mengambil daftar ringkasan pemesanan dari SQL View v_ringkasan_pemesanan.
     *
     * @return list<array<string, mixed>>
     */
    public function ambilRingkasanPemesanan(?int $idPengguna = null, int $batas = 20, int $offset = 0): array
    {
        $builder = $this->db->table('v_ringkasan_pemesanan')
            ->orderBy('id', 'DESC')
            ->limit($batas, $offset);

        if ($idPengguna !== null) {
            $builder->where('id_pengguna', $idPengguna);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Mengambil satu ringkasan pemesanan berdasarkan ID dari SQL View v_ringkasan_pemesanan.
     *
     * @return array<string, mixed>|null
     */
    public function ambilSatuRingkasan(int $idPemesanan): ?array
    {
        $baris = $this->db->table('v_ringkasan_pemesanan')
            ->where('id', $idPemesanan)
            ->get()
            ->getRowArray();

        if ($baris === null) {
            return null;
        }

        return $baris;
    }

    /**
     * Mengambil satu ringkasan pemesanan berdasarkan kode pemesanan unik dari SQL View.
     *
     * @return array<string, mixed>|null
     */
    public function ambilBerdasarkanKode(string $kodePemesanan): ?array
    {
        $baris = $this->db->table('v_ringkasan_pemesanan')
            ->where('kode_pemesanan', $kodePemesanan)
            ->get()
            ->getRowArray();

        if ($baris === null) {
            return null;
        }

        return $baris;
    }
}
