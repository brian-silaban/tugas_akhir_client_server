<?php

namespace App\Controllers\Api;

use App\Models\PemesananModel;
use App\Models\UlasanModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

class AdminApiController extends BasisAdminApiController
{
    public function statistik(): ResponseInterface
    {
        $koneksi = $this->koneksiAdmin();
        $ringkasan = $koneksi->table('v_ringkasan_pemesanan')
            ->select('status, COUNT(*) AS jumlah, SUM(total_harga) AS total', false)
            ->groupBy('status')->get()->getResultArray();

        $jumlahPemesanan = ['menunggu' => 0, 'lunas' => 0, 'dibatalkan' => 0];
        $pendapatan = 0;
        foreach ($ringkasan as $baris) {
            $jumlahPemesanan[$baris['status']] = (int) $baris['jumlah'];
            if ($baris['status'] === 'lunas') {
                $pendapatan = (int) $baris['total'];
            }
        }

        return $this->berhasil([
            'jumlah_destinasi' => $koneksi->table('v_wisata_lengkap')->countAllResults(),
            'jumlah_pengguna' => $koneksi->table('pengguna')->countAllResults(),
            'jumlah_pemesanan' => $jumlahPemesanan,
            'total_pendapatan' => $pendapatan,
            'ulasan_terbaru' => $this->kueriUlasan()->limit(5)->get()->getResultArray(),
        ]);
    }

    public function daftarPemesanan(): ResponseInterface
    {
        $koneksi = $this->koneksiAdmin();
        $modelPemesanan = new PemesananModel($koneksi);
        $jumlah = $koneksi->table('v_ringkasan_pemesanan')->countAllResults();
        $paginasi = $this->buatPaginasi($jumlah);
        $daftar = $modelPemesanan->ambilRingkasanPemesanan(null, 25, ($paginasi['halaman'] - 1) * 25);

        return $this->berhasil(['daftar' => $daftar, 'paginasi' => $paginasi]);
    }

    public function daftarUlasan(): ResponseInterface
    {
        $jumlah = $this->koneksiAdmin()->table('ulasan')->countAllResults();
        $paginasi = $this->buatPaginasi($jumlah);
        $daftar = $this->kueriUlasan()->limit(25, ($paginasi['halaman'] - 1) * 25)->get()->getResultArray();

        return $this->berhasil(['daftar' => $daftar, 'paginasi' => $paginasi]);
    }

    public function ubahStatusPemesanan($id): ResponseInterface
    {
        $statusBaru = $this->bacaMasukan()['status'] ?? null;
        if (! in_array($statusBaru, ['lunas', 'dibatalkan'], true)) {
            return $this->gagal(422, 'Status harus lunas atau dibatalkan.');
        }

        $modelPemesanan = new PemesananModel($this->koneksiAdmin());
        try {
            $pemesanan = $modelPemesanan->ubahStatusDenganProsedur((int) $id, $statusBaru);
        } catch (DomainException $kesalahan) {
            return $this->gagal(409, $kesalahan->getMessage());
        }

        if ($pemesanan === null) {
            return $this->gagal(404, 'Pemesanan tidak ditemukan.');
        }

        return $this->berhasil($pemesanan, 'Status pemesanan berhasil diperbarui.');
    }

    public function hapusUlasan($id): ResponseInterface
    {
        $koneksi = $this->koneksiAdmin();
        $modelUlasan = new UlasanModel($koneksi);

        // Trigger PostgreSQL menyinkronkan rating pada transaksi penghapusan yang sama.
        if (! $modelUlasan->delete((int) $id)) {
            return $this->gagal(500, 'Ulasan gagal dihapus.');
        }
        if ($koneksi->affectedRows() === 0) {
            return $this->gagal(404, 'Ulasan tidak ditemukan.');
        }

        return $this->berhasil(null, 'Ulasan berhasil dihapus.');
    }

    private function kueriUlasan(): BaseBuilder
    {
        return $this->koneksiAdmin()->table('ulasan')
            ->select('ulasan.id, ulasan.rating, ulasan.komentar, ulasan.dibuat_pada, pengguna.nama AS nama_pengguna, destinasi.nama AS nama_destinasi')
            ->join('pengguna', 'pengguna.id = ulasan.id_pengguna')
            ->join('destinasi', 'destinasi.id = ulasan.id_destinasi')
            ->orderBy('ulasan.id', 'DESC');
    }

    private function buatPaginasi(int $jumlah): array
    {
        $totalHalaman = max(1, (int) ceil($jumlah / 25));
        $halaman = filter_var($this->request->getGet('halaman'), FILTER_VALIDATE_INT);

        return [
            'halaman' => min($totalHalaman, max(1, (int) $halaman)),
            'total_halaman' => $totalHalaman,
            'total_data' => $jumlah,
        ];
    }
}
