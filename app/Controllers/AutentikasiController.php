<?php

namespace App\Controllers;

use App\Models\PenggunaModel;

/**
 * AutentikasiController — Menangani alur Masuk (Login), Daftar (Register),
 * dan Keluar (Logout) pengguna IndWalk.
 */
class AutentikasiController extends BaseController
{
    // ---------------------------------------------------------------
    // Halaman & Proses MASUK (Login)
    // ---------------------------------------------------------------

    /**
     * Tampilkan halaman formulir masuk.
     */
    public function masuk()
    {
        // Jika pengguna sudah masuk, arahkan langsung sesuai peran
        if (session()->get('sudah_masuk')) {
            return $this->arahkanSesuaiPeran(session()->get('peran'));
        }

        return view('pages/autentikasi/masuk');
    }

    /**
     * Proses validasi login: cocokkan email & kata sandi, lalu simpan sesi.
     */
    public function prosesMasuk()
    {
        // Ambil input dari formulir
        $email     = $this->request->getPost('email');
        $kataSandi = $this->request->getPost('kata_sandi');

        // Validasi input dasar
        $aturanValidasi = [
            'email'      => 'required|valid_email',
            'kata_sandi' => 'required',
        ];

        if (! $this->validate($aturanValidasi)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Cari pengguna berdasarkan email di database
        $modelPengguna = new PenggunaModel();
        $pengguna      = $modelPengguna->cariDenganEmail($email);

        // Periksa apakah pengguna ditemukan
        if ($pengguna === null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau kata sandi salah.');
        }

        // Verifikasi kata sandi dengan hash yang tersimpan
        $kataSandiCocok = password_verify($kataSandi, $pengguna['kata_sandi']);

        if (! $kataSandiCocok) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau kata sandi salah.');
        }

        // Simpan data sesi pengguna yang berhasil masuk
        $dataSesi = [
            'id_pengguna' => $pengguna['id'],
            'nama'        => $pengguna['nama'],
            'email'       => $pengguna['email'],
            'peran'       => $pengguna['peran'],
            'sudah_masuk' => true,
        ];
        session()->set($dataSesi);

        // Arahkan ke halaman sesuai peran pengguna
        return $this->arahkanSesuaiPeran($pengguna['peran']);
    }

    // ---------------------------------------------------------------
    // Halaman & Proses DAFTAR (Register)
    // ---------------------------------------------------------------

    /**
     * Tampilkan halaman formulir pendaftaran akun baru.
     */
    public function daftar()
    {
        // Jika pengguna sudah masuk, arahkan langsung sesuai peran
        if (session()->get('sudah_masuk')) {
            return $this->arahkanSesuaiPeran(session()->get('peran'));
        }

        return view('pages/autentikasi/daftar');
    }

    /**
     * Proses validasi & penyimpanan akun baru ke database.
     */
    public function prosesDaftar()
    {
        // Aturan validasi untuk pendaftaran
        $aturanValidasi = [
            'nama'                    => 'required|min_length[3]|max_length[120]',
            'email'                   => 'required|valid_email|max_length[150]|is_unique[pengguna.email]',
            'kata_sandi'              => 'required|min_length[6]',
            'konfirmasi_kata_sandi'   => 'required|matches[kata_sandi]',
        ];

        $pesanValidasi = [
            'nama' => [
                'required'   => 'Nama lengkap wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
            ],
            'email' => [
                'required'    => 'Alamat email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
                'is_unique'   => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
            ],
            'kata_sandi' => [
                'required'   => 'Kata sandi wajib diisi.',
                'min_length' => 'Kata sandi minimal 6 karakter.',
            ],
            'konfirmasi_kata_sandi' => [
                'required' => 'Konfirmasi kata sandi wajib diisi.',
                'matches'  => 'Konfirmasi kata sandi tidak cocok.',
            ],
        ];

        if (! $this->validate($aturanValidasi, $pesanValidasi)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Hash kata sandi sebelum disimpan ke database
        $kataSandiTerenkripsi = password_hash(
            $this->request->getPost('kata_sandi'),
            PASSWORD_DEFAULT
        );

        // Siapkan data pengguna baru
        $dataPenggunaBaru = [
            'nama'            => $this->request->getPost('nama'),
            'email'           => $this->request->getPost('email'),
            'kata_sandi'      => $kataSandiTerenkripsi,
            'peran'           => 'member',
            'dibuat_pada'     => date('Y-m-d H:i:s'),
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        ];

        // Simpan ke tabel pengguna
        $modelPengguna = new PenggunaModel();
        $modelPengguna->insert($dataPenggunaBaru);

        // Arahkan ke halaman masuk dengan pesan sukses
        return redirect()->to('/masuk')
            ->with('success', 'Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.');
    }

    // ---------------------------------------------------------------
    // KELUAR (Logout)
    // ---------------------------------------------------------------

    /**
     * Hapus seluruh data sesi dan arahkan kembali ke beranda.
     */
    public function keluar()
    {
        session()->destroy();

        return redirect()->to('/')
            ->with('success', 'Anda telah berhasil keluar.');
    }

    // ---------------------------------------------------------------
    // Fungsi Pembantu Internal
    // ---------------------------------------------------------------

    /**
     * Arahkan pengguna ke halaman yang sesuai berdasarkan peran.
     *
     * @param string $peran  Peran pengguna ('admin' atau 'member')
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function arahkanSesuaiPeran(string $peran)
    {
        if ($peran === 'admin') {
            return redirect()->to('/admin');
        }

        return redirect()->to('/');
    }
}
