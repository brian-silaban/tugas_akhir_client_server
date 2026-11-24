<?php

namespace App\Controllers\Api;

use App\Libraries\TokenApi;
use App\Models\PenggunaModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * AutentikasiApiController — Endpoint REST API Autentikasi JejakSumut (Backend).
 * Melayani login dan registrasi akun untuk Frontend Client.
 *
 * Anggota 3 — Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class AutentikasiApiController extends ResourceController
{
    protected $format = 'json';

    /**
     * POST /api/v1/autentikasi/masuk
     * Memverifikasi email dan kata sandi pengguna.
     */
    public function masuk(): ResponseInterface
    {
        $inputJson = $this->request->getJSON(true);
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        $email     = trim((string) ($inputData['email'] ?? ''));
        $kataSandi = (string) ($inputData['kata_sandi'] ?? '');

        if ($email === '' || $kataSandi === '') {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email dan kata sandi wajib diisi.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $modelPengguna = new PenggunaModel();
        $pengguna      = $modelPengguna->where('email', $email)->first();

        if ($pengguna === null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email atau kata sandi salah.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (! password_verify($kataSandi, $pengguna['kata_sandi'])) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email atau kata sandi salah.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = (new TokenApi())->terbitkan((int) $pengguna['id']);
        if ($token === null) {
            log_message('error', 'JEJAKSUMUT_KUNCI_TOKEN belum dikonfigurasi untuk autentikasi API.');

            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Layanan autentikasi belum dikonfigurasi.',
                'data'   => null,
            ], ResponseInterface::HTTP_SERVICE_UNAVAILABLE);
        }

        $this->response->setHeader('Cache-Control', 'no-store');

        // Hapus hash kata sandi sebelum dikirim ke client
        unset($pengguna['kata_sandi']);

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Login berhasil.',
            'data'   => [
                'pengguna' => $pengguna,
                'token'    => $token,
            ],
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * POST /api/v1/autentikasi/daftar
     * Mendaftarkan member baru ke database.
     */
    public function daftar(): ResponseInterface
    {
        $inputJson = $this->request->getJSON(true);
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        $nama                = trim((string) ($inputData['nama'] ?? ''));
        $email               = trim((string) ($inputData['email'] ?? ''));
        $kataSandi           = (string) ($inputData['kata_sandi'] ?? '');
        $konfirmasiKataSandi = (string) ($inputData['konfirmasi_kata_sandi'] ?? '');

        if ($nama === '' || $email === '' || $kataSandi === '') {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Nama, email, dan kata sandi wajib diisi.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (strlen($nama) < 3) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Nama lengkap minimal 3 karakter.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Format email tidak valid.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (strlen($kataSandi) < 6) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Kata sandi minimal 6 karakter.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($konfirmasiKataSandi !== '' && $kataSandi !== $konfirmasiKataSandi) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Konfirmasi kata sandi tidak cocok.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $modelPengguna = new PenggunaModel();

        // Cek email duplikat
        $sudahAda = $modelPengguna->where('email', $email)->first();
        if ($sudahAda !== null) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
                'data'   => null,
            ], ResponseInterface::HTTP_CONFLICT);
        }

        $waktuSekarang = date('Y-m-d H:i:s');
        $idBaru = $modelPengguna->insert([
            'nama'            => $nama,
            'email'           => $email,
            'kata_sandi'      => password_hash($kataSandi, PASSWORD_DEFAULT),
            'peran'           => 'member',
            'foto_profil'     => null,
            'dibuat_pada'     => $waktuSekarang,
            'diperbarui_pada' => $waktuSekarang,
        ]);

        if (! $idBaru) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Gagal mendaftarkan akun. Silakan coba lagi.',
                'data'   => null,
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $penggunaBaru = $modelPengguna->find($idBaru);
        unset($penggunaBaru['kata_sandi']);

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Pendaftaran berhasil. Silakan masuk dengan akun baru Anda.',
            'data'   => $penggunaBaru,
        ], ResponseInterface::HTTP_CREATED);
    }
}
