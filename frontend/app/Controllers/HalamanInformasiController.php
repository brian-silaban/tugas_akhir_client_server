<?php

namespace App\Controllers;

use App\Libraries\LayananApiMitra;

/**
 * Controller halaman informasi tambahan pada dropdown "Lainnya":
 * - Daftar layanan mitra lainnya (Kuliner & Tur Guide)
 * - Halaman Tentang Kami & profil anggota tim pembuat JejakSumut (Kelas TRPL 5A)
 */
class HalamanInformasiController extends BaseController
{
    /**
     * GET /mitra-lainnya
     * Menampilkan layanan mitra Kuliner & Tur Guide berdasarkan wilayah Sumatera Utara.
     */
    public function mitraLainnya(): string
    {
        $layananFilter = trim((string) ($this->request->getGet('layanan') ?: 'semua'));
        $wilayahFilter = trim((string) ($this->request->getGet('wilayah') ?: 'semua'));

        $layananMitra = new LayananApiMitra();
        $katalog      = $layananMitra->ambilKatalogMitraLainnya($layananFilter, $wilayahFilter);

        return view('pages/informasi/mitra_lainnya', [
            'judulHalaman'  => 'Kuliner & Tur Guide — JejakSumut',
            'menuAktif'     => 'lainnya',
            'layananAktif'  => $layananFilter,
            'wilayahAktif'  => $wilayahFilter,
            'daftarKlaster' => $katalog['daftar_klaster'] ?? [],
            'daftarItem'    => $katalog['daftar_item'] ?? [],
        ]);
    }

    /**
     * GET /tentang
     * Menampilkan profil platform JejakSumut dan daftar anggota tim pengembang TRPL 5A Polmed.
     */
    public function tentang(): string
    {
        $daftarAnggotaTim = [
            [
                'inisial' => 'JM',
                'nama'    => 'Joxtian Mangiring Tua Sirait',
                'nim'     => '2405181051',
                'peran'   => 'Beranda, Katalog & Detail Destinasi',
                'fokus'   => 'Merancang tampilan utama wisatawan, filter katalog wisata, dan halaman informasi lengkap setiap destinasi Sumatera Utara.',
            ],
            [
                'inisial' => 'BD',
                'nama'    => 'Brian Decon Christoffel Silaban',
                'nim'     => '2405181061',
                'peran'   => 'Integrasi Backend, API Mitra & Deployment',
                'fokus'   => 'Menyusun arsitektur REST API client-server, integrasi layanan mitra penginapan dan transportasi, serta keamanan server produksi.',
            ],
            [
                'inisial' => 'MH',
                'nama'    => 'Mhd. Hafitz Maulana',
                'nim'     => '2405181011',
                'peran'   => 'Autentikasi, Sesi, Hak Akses & Favorit',
                'fokus'   => 'Membangun alur pendaftaran, login aman dengan token bertanda tangan, pengelolaan sesi pengguna, dan daftar destinasi favorit.',
            ],
            [
                'inisial' => 'SH',
                'nama'    => 'Samuel Halasson Lumbantoruan',
                'nim'     => '2405181056',
                'peran'   => 'Pemesanan Tiket, E-Ticket, Transaksi & Ulasan',
                'fokus'   => 'Mengembangkan alur pemesanan tiket kunjungan dengan transaksi aman, pembuatan e-ticket digital, serta fitur ulasan wisatawan.',
            ],
            [
                'inisial' => 'AN',
                'nama'    => 'Andreas Nugraha Hutabarat',
                'nim'     => '2405181036',
                'peran'   => 'Basis Data PostgreSQL, Endpoint Admin & Panel Pengelola',
                'fokus'   => 'Merancang skema database PostgreSQL beserta objek lanjutan, proteksi konflik penyuntingan, dan antarmuka Panel Pengelola.',
            ],
        ];

        return view('pages/informasi/tentang', [
            'judulHalaman'     => 'Tentang Kami — JejakSumut',
            'menuAktif'        => 'lainnya',
            'daftarAnggotaTim' => $daftarAnggotaTim,
        ]);
    }
}
