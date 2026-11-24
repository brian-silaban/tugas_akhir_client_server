<?php

namespace App\Controllers\Api;

use App\Models\DestinasiModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;

class DestinasiAdminApiController extends BasisAdminApiController
{
    private const ATURAN = [
        'nama' => 'required|max_length[180]',
        'slug' => 'required|max_length[220]|regex_match[/^(?=.*[a-z])[a-z0-9]+(?:-[a-z0-9]+)*$/D]',
        'id_kategori' => 'required|is_natural_no_zero|less_than_equal_to[2147483647]',
        'kode_kabupaten' => 'required|max_length[10]',
        'sub_kategori' => 'permit_empty|max_length[100]',
        'harga_tiket' => 'required|is_natural|less_than_equal_to[2147483647]',
        'deskripsi' => 'required|max_length[20000]',
        'alamat' => 'permit_empty|max_length[2000]',
        'foto_utama' => 'permit_empty|max_length[2000]|valid_url_strict[http,https]',
        'jam_buka' => 'permit_empty|regex_match[/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D]',
        'jam_tutup' => 'permit_empty|regex_match[/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D]',
        'garis_lintang' => 'permit_empty|decimal|greater_than_equal_to[-90]|less_than_equal_to[90]',
        'garis_bujur' => 'permit_empty|decimal|greater_than_equal_to[-180]|less_than_equal_to[180]',
    ];

    public function show($id = null): ResponseInterface
    {
        $destinasi = (new DestinasiModel($this->koneksiAdmin()))->find((int) $id);

        if ($destinasi === null) {
            return $this->gagal(404, 'Destinasi tidak ditemukan.');
        }

        return $this->berhasil(array_intersect_key($destinasi, self::ATURAN) + ['id' => (int) $id]);
    }

    public function create(): ResponseInterface
    {
        return $this->simpan();
    }

    public function update($id = null): ResponseInterface
    {
        return $this->simpan((int) $id);
    }

    public function delete($id = null): ResponseInterface
    {
        $koneksi = $this->koneksiAdmin();

        // Kunci induk selama pemeriksaan agar pemesanan baru tidak masuk sebelum penghapusan.
        $koneksi->transBegin();
        try {
            $destinasi = $koneksi->query('SELECT id FROM destinasi WHERE id = ? FOR UPDATE', [(int) $id])->getRowArray();
            if ($destinasi === null) {
                $koneksi->transRollback();
                return $this->gagal(404, 'Destinasi tidak ditemukan.');
            }

            if ($koneksi->table('pemesanan')->where('id_destinasi', (int) $id)->countAllResults() > 0) {
                $koneksi->transRollback();
                return $this->gagal(409, 'Destinasi ini memiliki riwayat pemesanan dan tidak dapat dihapus.');
            }

            $terhapus = (new DestinasiModel($koneksi))->delete((int) $id);
            if (! $terhapus || ! $koneksi->transStatus() || ! $koneksi->transCommit()) {
                throw new DatabaseException($koneksi->error()['message'] ?? '');
            }
        } catch (DatabaseException $kesalahan) {
            $koneksi->transRollback();

            return $this->tanganiKesalahanDatabase($kesalahan);
        }

        return $this->berhasil(null, 'Destinasi berhasil dihapus.');
    }

    private function simpan(?int $id = null): ResponseInterface
    {
        $koneksi = $this->koneksiAdmin();
        $modelDestinasi = new DestinasiModel($koneksi);
        $destinasiLama = $id === null ? null : $modelDestinasi->find($id);
        if ($id !== null && $destinasiLama === null) {
            return $this->gagal(404, 'Destinasi tidak ditemukan.');
        }

        $masukan = array_intersect_key($this->bacaMasukan(), self::ATURAN);
        if ($masukan === []) {
            return $this->gagal(422, 'Isi data destinasi yang akan disimpan.');
        }

        foreach ($masukan as $kolom => $nilai) {
            if (is_string($nilai) && ! str_contains($nilai, "\0")) {
                $masukan[$kolom] = trim($nilai);
            } elseif (! is_int($nilai) && ! is_float($nilai) && $nilai !== null) {
                return $this->gagal(422, 'Nilai untuk ' . $kolom . ' tidak valid.');
            }
        }

        if ($id === null && ($masukan['slug'] ?? '') === '' && is_string($masukan['nama'] ?? null)) {
            helper('text');
            $masukan['slug'] = url_title(convert_accented_characters($masukan['nama']), '-', true);
        }

        $aturan = $id === null ? self::ATURAN : array_intersect_key(self::ATURAN, $masukan);
        if (! $this->validateData($masukan, $aturan)) {
            $validasi = $this->validator->getErrors();

            return $this->gagal(422, 'Periksa kembali isian destinasi.', $validasi);
        }

        if (array_key_exists('id_kategori', $masukan)) {
            $kategori = $koneksi->table('kategori')->where('id', (int) $masukan['id_kategori'])->get()->getRowArray();
            if ($kategori === null) {
                return $this->gagal(422, 'Kategori tidak ditemukan.');
            }
            if (($masukan['sub_kategori'] ?? $destinasiLama['sub_kategori'] ?? '') === '') {
                $masukan['sub_kategori'] = $kategori['nama'];
            }
        }

        if (array_key_exists('kode_kabupaten', $masukan)) {
            $wilayah = $koneksi->table('kabupaten_kota')->where('kode_kabupaten', $masukan['kode_kabupaten'])
                ->get()->getRowArray();
            if ($wilayah === null) {
                return $this->gagal(422, 'Kabupaten atau kota tidak ditemukan.');
            }
            if ($masukan['kode_kabupaten'] !== ($destinasiLama['kode_kabupaten'] ?? null)) {
                $masukan['kota'] = preg_replace('/^(Kabupaten|Kota)\s+/i', '', $wilayah['nama_kabupaten_kota']);
            }
        }

        if (isset($masukan['harga_tiket']) && (int) $masukan['harga_tiket'] !== (int) ($destinasiLama['harga_tiket'] ?? -1)) {
            $harga = (int) $masukan['harga_tiket'];
            $masukan['label_harga_tiket'] = $harga === 0 ? 'Gratis (Rp 0)' : 'Rp ' . number_format($harga, 0, ',', '.');
            $masukan['keterangan_tiket'] = '';
        }

        foreach (['alamat', 'jam_buka', 'jam_tutup', 'garis_lintang', 'garis_bujur'] as $kolom) {
            if (($masukan[$kolom] ?? null) === '') {
                $masukan[$kolom] = null;
            }
        }
        foreach (['foto_utama', 'sub_kategori'] as $kolom) {
            if (array_key_exists($kolom, $masukan)) {
                $masukan[$kolom] = $masukan[$kolom] ?? '';
            }
        }
        if ($id === null) {
            $masukan += ['foto_utama' => '', 'sub_kategori' => ''];
        }

        try {
            if ($id === null) {
                $idBaru = $modelDestinasi->insert($masukan);
                if ($idBaru === false) {
                    throw new DatabaseException($koneksi->error()['message'] ?? '');
                }

                return $this->berhasil(['id' => (int) $idBaru], 'Destinasi berhasil ditambahkan.', 201);
            }

            if (! $modelDestinasi->update($id, $masukan)) {
                throw new DatabaseException($koneksi->error()['message'] ?? '');
            }
            if ($koneksi->affectedRows() === 0) {
                return $this->gagal(404, 'Destinasi tidak ditemukan.');
            }
        } catch (DatabaseException $kesalahan) {
            return $this->tanganiKesalahanDatabase($kesalahan);
        }

        return $this->berhasil(['id' => $id], 'Destinasi berhasil diperbarui.');
    }

    private function tanganiKesalahanDatabase(DatabaseException $kesalahan): ResponseInterface
    {
        $pesan = htmlspecialchars_decode($kesalahan->getMessage(), ENT_QUOTES);
        // Driver PostgreSQL CI4 tidak mengembalikan SQLSTATE. Cocokkan nama constraint kedua skema.
        if (preg_match('/"destinasi_slug(?:_unique|_key)?"/', $pesan)) {
            return $this->gagal(409, 'Slug destinasi sudah digunakan.');
        }
        if (preg_match('/"pemesanan_id_destinasi_(?:foreign|fkey)"/', $pesan)) {
            return $this->gagal(409, 'Destinasi ini memiliki riwayat pemesanan dan tidak dapat dihapus.');
        }
        if (preg_match('/"destinasi_(?:id_kategori|kode_kabupaten)_(?:foreign|fkey)"/', $pesan)) {
            return $this->gagal(422, 'Kategori atau kabupaten/kota tidak tersedia.');
        }

        log_message('error', 'Operasi database destinasi admin gagal: {pesan}', ['pesan' => $pesan]);

        return $this->gagal(500, 'Perubahan destinasi gagal disimpan.');
    }
}
