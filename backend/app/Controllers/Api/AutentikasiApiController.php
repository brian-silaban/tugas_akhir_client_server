<?php

namespace App\Controllers\Api;

use App\Models\PenggunaModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * AutentikasiApiController – Endpoint REST API Autentikasi JejakSumut (Backend).
 * Melayani login dan registrasi akun untuk Frontend Client.
 * Token menggunakan HMAC-SHA256 yang bertanda tangan dengan masa berlaku.
 *
 * Anggota 3 – Autentikasi, Sesi Pengguna & Filter Hak Akses
 */
class AutentikasiApiController extends ResourceController
{
    protected $format = 'json';

    // -------------------------------------------------------------------------
    // Helper: Pembuatan & Verifikasi Token HMAC-SHA256
    // -------------------------------------------------------------------------

    /**
     * Mengambil kunci rahasia penandatanganan token dari variabel lingkungan.
     * Minimal 32 karakter agar keamanan HMAC-SHA256 tetap terjaga.
     */
    private static function ambilKunciRahasia(): ?string
    {
        $kunci = trim((string) env('JEJAKSUMUT_KUNCI_TOKEN', ''));
        if ($kunci === '') {
            $kunci = trim((string) env('API_TOKEN_SECRET', ''));
        }

        if (strlen($kunci) < 32) {
            return null;
        }

        return $kunci;
    }

    /**
     * Membuat token HMAC-SHA256 yang bertanda tangan.
     * Payload memuat id, email, peran, dan exp (waktu kedaluwarsa).
     *
     * @param int    $idPengguna
     * @param string $email
     * @param string $peran
     * @return string|null
     */
    public static function buatToken(int $idPengguna, string $email, string $peran): ?string
    {
        $kunciRahasia = self::ambilKunciRahasia();
        if ($kunciRahasia === null) {
            return null;
        }

        // Masa berlaku token: 24 jam ke depan
        $waktuKedaluwarsa = time() + (60 * 60 * 24);

        // Susun payload sebagai string terformat
        $payload = $idPengguna . '|' . $email . '|' . $peran . '|' . $waktuKedaluwarsa;

        // Buat tanda tangan HMAC-SHA256
        $tandaTangan = hash_hmac('sha256', $payload, $kunciRahasia);

        // Gabungkan payload dan tanda tangan, lalu encode Base64Url
        $tokenMentah = $payload . '.' . $tandaTangan;

        return rtrim(strtr(base64_encode($tokenMentah), '+/', '-_'), '=');
    }

    /**
     * Memverifikasi token HMAC-SHA256 yang dikirim klien.
     * Mengembalikan array data payload jika valid, atau null jika tidak valid/kedaluwarsa.
     *
     * @param string $token
     * @return array<string, mixed>|null
     */
    public static function verifikasiToken(string $token): ?array
    {
        $kunciRahasia = self::ambilKunciRahasia();
        if ($kunciRahasia === null) {
            return null;
        }

        // Decode Base64Url kembali ke string mentah
        $tokenMentah = base64_decode(strtr($token, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($token)) % 4));
        if ($tokenMentah === false) {
            return null;
        }

        // Pisahkan bagian payload dan tanda tangan
        $posisiTitik = strrpos($tokenMentah, '.');
        if ($posisiTitik === false) {
            return null;
        }

        $payload     = substr($tokenMentah, 0, $posisiTitik);
        $tandaTangan = substr($tokenMentah, $posisiTitik + 1);

        // Hitung ulang tanda tangan yang diharapkan
        $tandaTanganDiharapkan = hash_hmac('sha256', $payload, $kunciRahasia);

        // Bandingkan tanda tangan dengan aman (mencegah timing attack)
        if (! hash_equals($tandaTanganDiharapkan, $tandaTangan)) {
            return null;
        }

        // Pecah payload menjadi komponen
        $bagian = explode('|', $payload);
        if (count($bagian) !== 4) {
            return null;
        }

        [$idPengguna, $email, $peran, $exp] = $bagian;

        // Cek masa berlaku
        if ((int) $exp < time()) {
            return null; // Token sudah kedaluwarsa
        }

        return [
            'id_pengguna' => (int) $idPengguna,
            'email'       => $email,
            'peran'       => $peran,
            'exp'         => (int) $exp,
        ];
    }

    /**
     * Memverifikasi header Authorization bertipe Bearer dan mengembalikan payload token.
     *
     * @return array<string, mixed>|null
     */
    public static function verifikasiHeaderOtorisasi(string $headerOtorisasi): ?array
    {
        if (preg_match('/\ABearer\s+(.+)\z/i', trim($headerOtorisasi), $cocok) !== 1) {
            return null;
        }

        $token = trim($cocok[1]);
        if ($token === '') {
            return null;
        }

        return self::verifikasiToken($token);
    }

    // -------------------------------------------------------------------------
    // Endpoint REST API
    // -------------------------------------------------------------------------

    /**
     * POST /api/v1/auth/masuk dan POST /api/v1/autentikasi/masuk
     * Memverifikasi email dan kata sandi pengguna, mengembalikan token HMAC.
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

        if ($pengguna === null || ! password_verify($kataSandi, $pengguna['kata_sandi'])) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email atau kata sandi salah.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Buat token HMAC bertanda tangan
        $tokenBaru = self::buatToken((int) $pengguna['id'], $pengguna['email'], $pengguna['peran']);
        if ($tokenBaru === null) {
            log_message('error', 'JEJAKSUMUT_KUNCI_TOKEN atau API_TOKEN_SECRET belum dikonfigurasi (minimal 32 karakter).');

            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Layanan autentikasi belum dikonfigurasi.',
                'data'   => null,
            ], ResponseInterface::HTTP_SERVICE_UNAVAILABLE);
        }

        // Hapus hash kata sandi sebelum dikirim ke klien
        unset($pengguna['kata_sandi']);

        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->respond([
            'status' => 'berhasil',
            'pesan'  => 'Login berhasil.',
            'data'   => [
                'pengguna' => $pengguna,
                'token'    => $tokenBaru,
            ],
        ], ResponseInterface::HTTP_OK);
    }

    /**
     * POST /api/v1/auth/daftar dan POST /api/v1/autentikasi/daftar
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
        $idBaru        = $modelPengguna->insert([
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
