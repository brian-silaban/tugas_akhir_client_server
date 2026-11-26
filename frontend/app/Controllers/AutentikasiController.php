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
    // Halaman & Proses GANTI KATA SANDI (Member & Admin)
    // ---------------------------------------------------------------

    /**
     * Tampilkan formulir ganti kata sandi.
     * Dapat diakses dari halaman login maupun saat sudah login.
     */
    public function gantiSandi(): string
    {
        $sudahMasuk = (bool) session()->get('sudah_masuk');

        return view('pages/autentikasi/ganti_sandi', [
            'mode_admin'  => env('JEJAKSUMUT_MODE_CLIENT') === 'admin',
            'sudah_masuk' => $sudahMasuk,
            'email_sesi'  => $sudahMasuk ? (string) session()->get('email') : '',
            'peran_sesi'  => $sudahMasuk ? (string) session()->get('peran') : '',
        ]);
    }

    /**
     * Memproses pergantian kata sandi melalui REST API Backend.
     */
    public function prosesGantiSandi(): RedirectResponse
    {
        $sudahMasuk = (bool) session()->get('sudah_masuk');
        $email      = $sudahMasuk
            ? (string) session()->get('email')
            : trim((string) $this->request->getPost('email'));

        $aturanValidasi = [
            'kata_sandi_lama'            => 'required',
            'kata_sandi_baru'            => 'required|min_length[6]',
            'konfirmasi_kata_sandi_baru' => 'required|matches[kata_sandi_baru]',
        ];
        if (! $sudahMasuk) {
            $aturanValidasi['email'] = 'required|valid_email|max_length[150]';
        }

        $pesanValidasi = [
            'email' => [
                'required'    => 'Alamat email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
            ],
            'kata_sandi_lama' => [
                'required' => 'Kata sandi lama wajib diisi.',
            ],
            'kata_sandi_baru' => [
                'required'   => 'Kata sandi baru wajib diisi.',
                'min_length' => 'Kata sandi baru minimal 6 karakter.',
            ],
            'konfirmasi_kata_sandi_baru' => [
                'required' => 'Konfirmasi kata sandi baru wajib diisi.',
                'matches'  => 'Konfirmasi kata sandi baru tidak cocok.',
            ],
        ];

        if (! $this->validate($aturanValidasi, $pesanValidasi)) {
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $tokenApi = $sudahMasuk ? (string) session()->get('token_api') : null;
        $hasil    = $this->layananApiBackend->kirimPost('/auth/ganti-sandi', [
            'email'                      => $email,
            'kata_sandi_lama'            => (string) $this->request->getPost('kata_sandi_lama'),
            'kata_sandi_baru'            => (string) $this->request->getPost('kata_sandi_baru'),
            'konfirmasi_kata_sandi_baru' => (string) $this->request->getPost('konfirmasi_kata_sandi_baru'),
        ], $tokenApi);

        if (! $hasil['terhubung']) {
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('error', 'Tidak dapat terhubung ke server Backend API. Pastikan server backend sedang aktif.');
        }

        if (! $hasil['berhasil']) {
            $pesanError = $hasil['pesan_error'] ?? 'Gagal memperbarui kata sandi. Silakan periksa kembali data Anda.';
            return redirect()->back()->withCookies()
                ->withInput()
                ->with('error', $pesanError);
        }

        if ($sudahMasuk) {
            session()->destroy();
        }

        return redirect()->to(site_url('masuk'))
            ->withCookies()
            ->with('success', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
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
