<?php

namespace App\Controllers;

use App\Libraries\LayananApiBackend;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * AutentikasiController — Menangani antarmuka Masuk (Login), Daftar (Register),
 * dan Keluar (Logout) pengguna JejakSumut pada Frontend Client.
 *
 * Sesuai arsitektur Client-Server, controller ini TIDAK mengakses PostgreSQL secara langsung,
 * melainkan berkomunikasi via REST API Backend (/api/v1/autentikasi/*).
 *
 * Anggota 3 — Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class AutentikasiController extends BaseController
{
    private LayananApiBackend $layananApiBackend;

    public function __construct()
    {
        $this->layananApiBackend = new LayananApiBackend();
    }

    // ---------------------------------------------------------------
    // Halaman & Proses MASUK (Login)
    // ---------------------------------------------------------------

    /**
     * Tampilkan formulir masuk.
     */
    public function masuk(): string|RedirectResponse
    {
        // Jika pengguna sudah login, arahkan langsung sesuai peran
        if (session()->get('sudah_masuk')) {
            return $this->arahkanSesuaiPeran((string) session()->get('peran'));
        }

        return view('pages/autentikasi/masuk', ['mode_admin' => env('JEJAKSUMUT_MODE_CLIENT') === 'admin']);
    }

    /**
     * Memproses formulir masuk dengan memanggil REST API Backend.
     */
    public function prosesMasuk(): RedirectResponse
    {
        $email     = trim((string) $this->request->getPost('email'));
        $kataSandi = (string) $this->request->getPost('kata_sandi');

        $aturanValidasi = [
            'email'      => 'required|valid_email',
            'kata_sandi' => 'required',
        ];

        if (! $this->validate($aturanValidasi)) {
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Kirim request login ke Backend REST API
        $hasil = $this->layananApiBackend->kirimPost('/auth/masuk', [
            'email'      => $email,
            'kata_sandi' => $kataSandi,
        ]);

        if (! $hasil['terhubung']) {
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('error', 'Tidak dapat terhubung ke server Backend API. Pastikan server backend sedang aktif.');
        }

        if (! $hasil['berhasil'] || empty($hasil['data']['pengguna'])) {
            $pesanError = $hasil['pesan_error'] ?? 'Email atau kata sandi salah.';
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('error', $pesanError);
        }

        $pengguna = $hasil['data']['pengguna'];
        $token    = $hasil['data']['token'] ?? null;
        if (env('JEJAKSUMUT_MODE_CLIENT') === 'admin' && $pengguna['peran'] !== 'admin') {
            return redirect()->to(site_url('masuk'))->withCookies()->with('error', 'Panel Pengelola hanya untuk administrator.');
        }

        // Simpan sesi pengguna
        $dataSesi = [
            'id_pengguna'   => (int) $pengguna['id'],
            'nama'          => $pengguna['nama'],
            'nama_pengguna' => $pengguna['nama'], // Kompatibel dengan layout navbar
            'email'         => $pengguna['email'],
            'peran'         => $pengguna['peran'],
            'token_api'     => $token,
            'sudah_masuk'   => true,
        ];
        session()->regenerate(true);
        session()->set($dataSesi);

        return $this->arahkanSesuaiPeran((string) $pengguna['peran']);
    }

    // ---------------------------------------------------------------
    // Halaman & Proses DAFTAR (Register)
    // ---------------------------------------------------------------

    /**
     * Tampilkan formulir pendaftaran akun baru.
     */
    public function daftar(): string|RedirectResponse
    {
        if (session()->get('sudah_masuk')) {
            return $this->arahkanSesuaiPeran((string) session()->get('peran'));
        }

        return view('pages/autentikasi/daftar');
    }

    /**
     * Memproses formulir pendaftaran member baru dengan memanggil REST API Backend.
     */
    public function prosesDaftar(): RedirectResponse
    {
        $aturanValidasi = [
            'nama'                  => 'required|min_length[3]|max_length[120]',
            'email'                 => 'required|valid_email|max_length[150]',
            'kata_sandi'            => 'required|min_length[6]',
            'konfirmasi_kata_sandi' => 'required|matches[kata_sandi]',
        ];

        $pesanValidasi = [
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
            'konfirmasi_kata_sandi' => [
                'required' => 'Konfirmasi kata sandi wajib diisi.',
                'matches'  => 'Konfirmasi kata sandi tidak cocok.',
            ],
        ];

        if (! $this->validate($aturanValidasi, $pesanValidasi)) {
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Kirim request pendaftaran ke Backend REST API
        $hasil = $this->layananApiBackend->kirimPost('/auth/daftar', [
            'nama'                  => trim((string) $this->request->getPost('nama')),
            'email'                 => trim((string) $this->request->getPost('email')),
            'kata_sandi'            => (string) $this->request->getPost('kata_sandi'),
            'konfirmasi_kata_sandi' => (string) $this->request->getPost('konfirmasi_kata_sandi'),
        ]);

        if (! $hasil['terhubung']) {
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('error', 'Tidak dapat terhubung ke server Backend API. Pastikan server backend sedang aktif.');
        }

        if (! $hasil['berhasil']) {
            $pesanError = $hasil['pesan_error'] ?? 'Pendaftaran akun gagal. Silakan coba lagi.';
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('error', $pesanError);
        }

        return redirect()->to('/masuk')
            ->with('success', 'Pendaftaran berhasil! Silakan masuk dengan akun baru Anda.');
    }

    // ---------------------------------------------------------------
    // KELUAR (Logout)
    // ---------------------------------------------------------------

    /**
     * Hapus seluruh data sesi dan arahkan kembali ke beranda.
     */
    public function keluar(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to(site_url('/'))->with('success', 'Anda telah berhasil keluar.');
    }

    public function keluarAdmin(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to(site_url('masuk'))->withCookies()->with('success', 'Anda telah berhasil keluar.');
    }

    // ---------------------------------------------------------------
    // Helper Internal
    // ---------------------------------------------------------------

    private function arahkanSesuaiPeran(string $peran): RedirectResponse
    {
        if ($peran === 'admin') {
            if (env('JEJAKSUMUT_MODE_CLIENT') !== 'wisatawan') {
                return redirect()->to(site_url('admin'))->withCookies();
            }

            $urlAdmin = (string) env('JEJAKSUMUT_PANEL_ADMIN_URL', '');
            if ($urlAdmin !== '') {
                return redirect()->to($urlAdmin);
            }
        }

        return redirect()->to('/');
    }
}
