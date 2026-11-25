<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Uji HTTP dengan PostgreSQL terpisah. Aktifkan hanya pada database pengujian.
 * Token admin dan URL API dibaca dari lingkungan, tidak disimpan di repository.
 *
 * @internal
 */
final class KonflikDestinasiTest extends CIUnitTestCase
{
    private string $urlApi;
    private string $token;
    private array $idBuatan = [];
    private array $destinasi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlApi = rtrim((string) getenv('JEJAKSUMUT_UJI_API_URL'), '/');
        $this->token = (string) getenv('JEJAKSUMUT_UJI_TOKEN_ADMIN');
        if ($this->urlApi === '' || $this->token === '') {
            $this->markTestSkipped('Isi JEJAKSUMUT_UJI_API_URL dan JEJAKSUMUT_UJI_TOKEN_ADMIN untuk database pengujian.');
        }

        $kategori = $this->kirim('GET', '/kategori', 200)['data'][0];
        $wilayah = $this->kirim('GET', '/kabupaten-kota', 200)['data'][0];
        $data = $this->kirim('POST', '/destinasi', 201, [
            'nama' => 'Destinasi Uji Konkurensi',
            'slug' => 'uji-konkurensi-' . bin2hex(random_bytes(8)),
            'id_kategori' => $kategori['id'],
            'kode_kabupaten' => $wilayah['kode_kabupaten'],
            'harga_tiket' => 10000,
            'deskripsi' => 'Data sementara untuk pengujian konflik edit Admin.',
        ])['data'];
        $this->idBuatan[] = (int) $data['id'];
        $this->destinasi = $this->kirim('GET', '/admin/destinasi/' . $data['id'], 200)['data'];
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->idBuatan as $id) {
                $this->kirim('DELETE', '/destinasi/' . $id, 200);
            }
        } finally {
            parent::tearDown();
        }
    }

    public function testFormulirLamaDitolakDanDapatDieditSetelahMuatUlang(): void
    {
        $id = $this->destinasi['id'];
        $versi = $this->destinasi['versi_data'];
        $hasil = $this->kirim('PUT', '/destinasi/' . $id, 200, ['harga_tiket' => 20000, 'versi_data' => $versi]);
        $this->assertNotSame($versi, $hasil['data']['versi_data']);
        $konflik = $this->kirim('PUT', '/destinasi/' . $id, 409, ['harga_tiket' => 30000, 'versi_data' => $versi]);
        $this->assertSame('versi_data', $konflik['data']['konflik']);
        $terbaru = $this->kirim('GET', '/admin/destinasi/' . $id, 200)['data'];
        $this->assertSame(20000, (int) $terbaru['harga_tiket']);
        $this->kirim('PUT', '/destinasi/' . $id, 200, ['harga_tiket' => 30000, 'versi_data' => $terbaru['versi_data']]);
        $katalog = $this->kirim('GET', '/destinasi/' . $this->destinasi['slug'], 200)['data'];
        $this->assertSame(30000, (int) $katalog['harga_tiket']);
    }

    public function testVersiWajibValidDanValidasiTidakMengubahData(): void
    {
        $id = $this->destinasi['id'];
        foreach ([null, '', 123, [], str_repeat('x', 64)] as $versi) {
            $this->kirim('PUT', '/destinasi/' . $id, 422, ['harga_tiket' => 20000, 'versi_data' => $versi]);
        }
        $this->kirim('PUT', '/destinasi/' . $id, 422, [
            'harga_tiket' => -1, 'versi_data' => $this->destinasi['versi_data'],
        ]);
        $terbaru = $this->kirim('GET', '/admin/destinasi/' . $id, 200)['data'];
        $this->assertSame($this->destinasi['versi_data'], $terbaru['versi_data']);
        $this->assertSame(10000, (int) $terbaru['harga_tiket']);
    }

    public function testDestinasiTerhapusMenghasilkan404(): void
    {
        $id = array_pop($this->idBuatan);
        $this->kirim('DELETE', '/destinasi/' . $id, 200);
        $this->kirim('PUT', '/destinasi/' . $id, 404, [
            'harga_tiket' => 20000, 'versi_data' => $this->destinasi['versi_data'],
        ]);
    }

    public function testDuaPermintaanDenganVersiSamaHanyaSatuBerhasil(): void
    {
        $jalur = '/destinasi/' . $this->destinasi['id'];
        $permintaan = [];
        $gabungan = curl_multi_init();
        foreach ([20000, 30000] as $harga) {
            $koneksi = $this->siapkanPermintaan('PUT', $jalur, [
                'harga_tiket' => $harga, 'versi_data' => $this->destinasi['versi_data'],
            ]);
            $permintaan[] = $koneksi;
            curl_multi_add_handle($gabungan, $koneksi);
        }

        try {
            do {
                $status = curl_multi_exec($gabungan, $aktif);
                $this->assertSame(CURLM_OK, $status);
                if ($aktif) {
                    curl_multi_select($gabungan, 0.1);
                }
            } while ($aktif);

            $kode = [];
            $hargaTersimpan = null;
            foreach ($permintaan as $urutan => $koneksi) {
                $kodeRespons = curl_getinfo($koneksi, CURLINFO_RESPONSE_CODE);
                $kode[] = $kodeRespons;
                if ($kodeRespons === 200) {
                    $hargaTersimpan = [20000, 30000][$urutan];
                }
            }
            sort($kode);
            $this->assertSame([200, 409], $kode);
            $terbaru = $this->kirim('GET', '/admin/destinasi/' . $this->destinasi['id'], 200)['data'];
            $this->assertSame($hargaTersimpan, (int) $terbaru['harga_tiket']);
        } finally {
            foreach ($permintaan as $koneksi) {
                curl_multi_remove_handle($gabungan, $koneksi);
                curl_close($koneksi);
            }
            curl_multi_close($gabungan);
        }
    }

    private function siapkanPermintaan(string $metode, string $jalur, ?array $data = null): CurlHandle
    {
        $koneksi = curl_init($this->urlApi . $jalur);
        curl_setopt_array($koneksi, [
            CURLOPT_CUSTOMREQUEST => $metode,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json', 'Authorization: Bearer ' . $this->token],
        ]);
        if ($data !== null) {
            curl_setopt($koneksi, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
        }

        return $koneksi;
    }

    private function kirim(string $metode, string $jalur, int $kode, ?array $data = null): array
    {
        $koneksi = $this->siapkanPermintaan($metode, $jalur, $data);
        try {
            $isi = curl_exec($koneksi);
            $this->assertSame($kode, curl_getinfo($koneksi, CURLINFO_RESPONSE_CODE), $metode . ' ' . $jalur);
            $this->assertIsString($isi);

            return json_decode($isi, true, 512, JSON_THROW_ON_ERROR);
        } finally {
            curl_close($koneksi);
        }
    }
}
