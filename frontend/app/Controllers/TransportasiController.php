<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;
use App\Libraries\LayananApiMitra;

/**
 * Controller halaman mandiri Transportasi Sumatera Utara.
 * Menghubungkan langsung REST API mitra Transportasi untuk cek tarif, jadwal, dan rekomendasi rute.
 */
class TransportasiController extends BaseController
{
    /**
     * GET /transportasi
     * Menampilkan kalkulator tarif antar-simpul, rekomendasi rute menuju destinasi Sumut, dan direktori simpul.
     */
    public function index(): string
    {
        $layananMitra   = new LayananApiMitra();
        $layananBackend = new LayananApiBackend();

        $hasilLokasi = $layananMitra->ambilDaftarLokasiTransportasi();
        $daftarLokasi = $hasilLokasi['data'] ?? [];

        // Ambil 8 destinasi populer dari backend agar pengguna bisa langsung mengecek rute ke sana
        $hasilDestinasi      = $layananBackend->ambilDaftarDestinasi(['batas' => 8, 'halaman' => 1, 'urutan' => 'terbaik']);
        $daftarDestinasiTuju = [];

        foreach ($hasilDestinasi['data'] ?? [] as $item) {
            $koordinat = is_array($item['koordinat'] ?? null) ? $item['koordinat'] : [];
            $lat       = (float) ($koordinat['garis_lintang'] ?? ($item['garis_lintang'] ?? 0));
            $lng       = (float) ($koordinat['garis_bujur'] ?? ($item['garis_bujur'] ?? 0));
            if ($lat === 0.0 || $lng === 0.0) {
                continue;
            }
            $daftarDestinasiTuju[] = [
                'nama' => (string) ($item['nama'] ?? 'Destinasi Sumut'),
                'kota' => (string) ($item['kota'] ?? 'Sumatera Utara'),
                'lat'  => $lat,
                'lng'  => $lng,
            ];
        }

        if ($daftarDestinasiTuju === []) {
            $daftarDestinasiTuju = [
                ['nama' => 'Danau Toba', 'kota' => 'Kabupaten Samosir', 'lat' => 2.6800, 'lng' => 98.8800],
                ['nama' => 'Air Terjun Ponot', 'kota' => 'Kabupaten Asahan', 'lat' => 2.5872, 'lng' => 99.3274],
                ['nama' => 'Pulau Samosir', 'kota' => 'Kabupaten Samosir', 'lat' => 2.6167, 'lng' => 98.8333],
                ['nama' => 'Istana Maimun', 'kota' => 'Kota Medan', 'lat' => 3.5752, 'lng' => 98.6838],
                ['nama' => 'Bukit Lawang', 'kota' => 'Kabupaten Langkat', 'lat' => 3.5536, 'lng' => 98.1449],
                ['nama' => 'Air Terjun Sipiso-piso', 'kota' => 'Kabupaten Karo', 'lat' => 2.9159, 'lng' => 98.5191],
            ];
        }

        return view('pages/mitra/transportasi', [
            'judulHalaman'        => 'Transportasi Sumatera Utara — JejakSumut',
            'menuAktif'           => 'transportasi',
            'terhubungMitra'      => $hasilLokasi['terhubung'] ?? false,
            'pesanErrorMitra'     => $hasilLokasi['pesan_error'] ?? null,
            'daftarLokasi'        => $daftarLokasi,
            'daftarDestinasiTuju' => $daftarDestinasiTuju,
        ]);
    }
}
