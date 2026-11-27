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
     * Menghasilkan sidik pendek HMAC dari hash kata sandi agar token otomatis hangus saat sandi berubah.
     */
    private static function buatSidikHashSandi(string $hashKataSandi, string $kunciRahasia): string
    {
        return substr(hash_hmac('sha256', $hashKataSandi, $kunciRahasia), 0, 16);
    }

    /**
     * Membuat token HMAC-SHA256 yang bertanda tangan.
     * Payload memuat id, email, peran, exp (waktu kedaluwarsa), dan sidik hash sandi.
     *
     * @param int    $idPengguna
     * @param string $email
     * @param string $peran
     * @param string $hashKataSandi
     * @return string|null
     */
    public static function buatToken(int $idPengguna, string $email, string $peran, string $hashKataSandi = ''): ?string
    {
        $kunciRahasia = self::ambilKunciRahasia();
        if ($kunciRahasia === null) {
            return null;
        }

        // Masa berlaku token: 24 jam ke depan
        $waktuKedaluwarsa = time() + (60 * 60 * 24);
        $sidikSandi       = self::buatSidikHashSandi($hashKataSandi, $kunciRahasia);

        // Susun payload sebagai string terformat
        $payload = $idPengguna . '|' . $email . '|' . $peran . '|' . $waktuKedaluwarsa . '|' . $sidikSandi;

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
        if (count($bagian) !== 5) {
            return null;
        }

        [$idPengguna, $email, $peran, $exp, $sidikSandi] = $bagian;

        // Cek masa berlaku
        if ((int) $exp < time()) {
            return null; // Token sudah kedaluwarsa
        }

        return [
            'id_pengguna' => (int) $idPengguna,
            'email'       => $email,
            'peran'       => $peran,
            'exp'         => (int) $exp,
            'sidik_sandi' => $sidikSandi,
        ];
    }

    /**
     * Memeriksa apakah sidik hash sandi pada token masih cocok dengan kata sandi aktif di database.
     *
     * @param array<string, mixed> $dataToken
     */
    public static function cocokkanSidikSandiToken(array $dataToken, string $hashKataSandiDatabase): bool
    {
        $kunciRahasia = self::ambilKunciRahasia();
        if ($kunciRahasia === null) {
            return false;
        }

        $sidikToken = (string) ($dataToken['sidik_sandi'] ?? '');
        if ($sidikToken === '') {
            return false;
        }

        $sidikSaatIni = self::buatSidikHashSandi($hashKataSandiDatabase, $kunciRahasia);

        return hash_equals($sidikSaatIni, $sidikToken);
    }

    /**
     * Membuat token reset kata sandi berdurasi 15 menit yang terikat pada sidik hash sandi saat ini
     * sehingga token otomatis tidak berlaku lagi setelah sandi berhasil diubah (single-use).
     *
     * @param array<string, mixed> $pengguna
     */
    public static function buatTokenResetSandi(array $pengguna): ?string
    {
        $kunciRahasia = self::ambilKunciRahasia();
        if ($kunciRahasia === null) {
            return null;
        }

        $waktuKedaluwarsa = time() + (60 * 15);
        $sidikHashSandi   = substr(hash_hmac('sha256', (string) ($pengguna['kata_sandi'] ?? ''), $kunciRahasia), 0, 16);
        $payload          = 'reset-sandi|' . (int) $pengguna['id'] . '|' . $waktuKedaluwarsa . '|' . $sidikHashSandi;
        $tandaTangan      = hash_hmac('sha256', $payload, $kunciRahasia);
        $tokenMentah      = $payload . '.' . $tandaTangan;

        return rtrim(strtr(base64_encode($tokenMentah), '+/', '-_'), '=');
    }

    /**
     * Memverifikasi token reset kata sandi HMAC-SHA256.
     * Mengembalikan array ['id_pengguna', 'sidik_sandi', 'exp'] bila sah, atau null jika tidak valid.
     *
     * @return array<string, mixed>|null
     */
    public static function verifikasiTokenResetSandi(string $token): ?array
    {
        $kunciRahasia = self::ambilKunciRahasia();
        if ($kunciRahasia === null || trim($token) === '') {
            return null;
        }

        $tokenBersih = trim($token);
        $tokenMentah = base64_decode(strtr($tokenBersih, '-_', '+/') . str_repeat('=', (4 - strlen($tokenBersih) % 4) % 4));
        if ($tokenMentah === false) {
            return null;
        }

        $posisiTitik = strrpos($tokenMentah, '.');
        if ($posisiTitik === false) {
            return null;
        }

        $payload               = substr($tokenMentah, 0, $posisiTitik);
        $tandaTangan           = substr($tokenMentah, $posisiTitik + 1);
        $tandaTanganDiharapkan = hash_hmac('sha256', $payload, $kunciRahasia);

        if (! hash_equals($tandaTanganDiharapkan, $tandaTangan)) {
            return null;
        }

        $bagian = explode('|', $payload);
        if (count($bagian) !== 4 || $bagian[0] !== 'reset-sandi') {
            return null;
        }

        [$tujuan, $idPengguna, $exp, $sidikSandi] = $bagian;
        if ((int) $exp < time()) {
            return null;
        }

        return [
            'id_pengguna' => (int) $idPengguna,
            'sidik_sandi' => $sidikSandi,
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
        // Batasi percobaan login per IP untuk mencegah brute-force kata sandi
        $throttler = \Config\Services::throttler();
        $kunciIp   = md5($this->request->getIPAddress() . '_masuk');
        if (! $throttler->check($kunciIp, 15, MINUTE)) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Terlalu banyak percobaan masuk. Silakan coba lagi dalam 1 menit.',
                null,
                ResponseInterface::HTTP_TOO_MANY_REQUESTS
            );
        }

        $inputJson = $this->request->getJSON(true);
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        $email     = strtolower(trim((string) ($inputData['email'] ?? '')));
        $kataSandi = (string) ($inputData['kata_sandi'] ?? '');

        if ($email === '' || $kataSandi === '') {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email dan kata sandi wajib diisi.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
        }

        $modelPengguna = new PenggunaModel();
        $pengguna      = $modelPengguna->cariDenganEmail($email);

        if ($pengguna === null || ! password_verify($kataSandi, $pengguna['kata_sandi'])) {
            return $this->respond([
                'status' => 'gagal',
                'pesan'  => 'Email atau kata sandi salah.',
                'data'   => null,
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Buat token HMAC bertanda tangan yang terikat pada sidik hash kata sandi saat ini
        $tokenBaru = self::buatToken(
            (int) $pengguna['id'],
            $pengguna['email'],
            $pengguna['peran'],
            (string) $pengguna['kata_sandi']
        );
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
        // Batasi percobaan pendaftaran per IP untuk mencegah spam akun
        $throttler = \Config\Services::throttler();
        $kunciIp   = md5($this->request->getIPAddress() . '_daftar');
        if (! $throttler->check($kunciIp, 10, MINUTE)) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Terlalu banyak permintaan pendaftaran. Silakan coba lagi dalam 1 menit.',
                null,
                ResponseInterface::HTTP_TOO_MANY_REQUESTS
            );
        }

        $inputJson = $this->request->getJSON(true);
        $inputData = ! empty($inputJson) ? $inputJson : $this->request->getPost();

        $nama                = trim((string) ($inputData['nama'] ?? ''));
        $email               = strtolower(trim((string) ($inputData['email'] ?? '')));
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

        // Cek email duplikat secara case-insensitive
        $sudahAda = $modelPengguna->cariDenganEmail($email);
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

    /**
     * POST /api/v1/auth/ganti-sandi dan POST /api/v1/autentikasi/ganti-sandi
     * Mengubah kata sandi akun (member atau admin) setelah memverifikasi kata sandi lama.
     */
    public function gantiSandi(): ResponseInterface
    {
        $inputData           = $this->ambilInputPermintaan();
        $emailInput          = trim((string) ($inputData['email'] ?? ''));
        $kataSandiLama       = (string) ($inputData['kata_sandi_lama'] ?? '');
        $kataSandiBaru       = (string) ($inputData['kata_sandi_baru'] ?? '');
        $konfirmasiKataSandi = (string) ($inputData['konfirmasi_kata_sandi'] ?? '');

        if ($kataSandiLama === '' || $kataSandiBaru === '' || $konfirmasiKataSandi === '') {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Kata sandi lama, kata sandi baru, dan konfirmasi kata sandi wajib diisi.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (strlen($kataSandiBaru) < 6) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Kata sandi baru minimal 6 karakter.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($kataSandiBaru !== $konfirmasiKataSandi) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Konfirmasi kata sandi baru tidak cocok.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($kataSandiBaru === $kataSandiLama) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Kata sandi baru harus berbeda dari kata sandi saat ini.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $modelPengguna = new PenggunaModel();
        $dataToken     = self::verifikasiHeaderOtorisasi($this->request->getHeaderLine('Authorization'));

        if ($dataToken !== null) {
            $pengguna = $modelPengguna->find((int) $dataToken['id_pengguna']);
            if ($pengguna === null || ! self::cocokkanSidikSandiToken($dataToken, (string) ($pengguna['kata_sandi'] ?? ''))) {
                return $this->kirimResponsAutentikasi(
                    'gagal',
                    'Sesi autentikasi telah berakhir. Silakan masuk kembali.',
                    null,
                    ResponseInterface::HTTP_UNAUTHORIZED
                );
            }
        } else {
            // Batasi percobaan ganti sandi tanpa sesi agar terhindar dari brute-force
            $throttler = \Config\Services::throttler();
            $kunciIp   = md5($this->request->getIPAddress() . '_ganti_sandi');
            if (! $throttler->check($kunciIp, 10, MINUTE)) {
                return $this->kirimResponsAutentikasi(
                    'gagal',
                    'Terlalu banyak percobaan. Silakan coba lagi dalam 1 menit.',
                    null,
                    ResponseInterface::HTTP_TOO_MANY_REQUESTS
                );
            }

            if ($emailInput === '' || ! filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
                return $this->kirimResponsAutentikasi(
                    'gagal',
                    'Alamat email akun wajib diisi dengan format yang valid.',
                    null,
                    ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $pengguna = $modelPengguna->cariDenganEmail($emailInput);
        }

        if ($pengguna === null || ! password_verify($kataSandiLama, (string) $pengguna['kata_sandi'])) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Email atau kata sandi lama tidak sesuai.',
                null,
                ResponseInterface::HTTP_UNAUTHORIZED
            );
        }

        $hashSandiBaru  = password_hash($kataSandiBaru, PASSWORD_DEFAULT);
        $berhasilSimpan = $modelPengguna->update((int) $pengguna['id'], [
            'kata_sandi'      => $hashSandiBaru,
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        ]);

        if (! $berhasilSimpan) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Gagal memperbarui kata sandi. Silakan coba lagi.',
                null,
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Terbitkan token baru bagi sesi aktif agar pengguna tidak ikut terkeluar saat token lama hangus
        $dataHasil = ['peran' => $pengguna['peran']];
        if ($dataToken !== null) {
            $tokenBaru = self::buatToken(
                (int) $pengguna['id'],
                (string) $pengguna['email'],
                (string) $pengguna['peran'],
                $hashSandiBaru
            );
            if ($tokenBaru !== null) {
                $dataHasil['token_baru'] = $tokenBaru;
            }
        }

        return $this->kirimResponsAutentikasi(
            'berhasil',
            'Kata sandi berhasil diperbarui. Gunakan kata sandi baru saat masuk.',
            $dataHasil,
            ResponseInterface::HTTP_OK
        );
    }

    /**
     * POST /api/v1/auth/lupa-sandi dan POST /api/v1/autentikasi/lupa-sandi
     * Membuat token reset sandi HMAC berdurasi 15 menit setelah memverifikasi email & nama akun.
     */
    public function lupaSandi(): ResponseInterface
    {
        $throttler = \Config\Services::throttler();
        $kunciIp   = md5($this->request->getIPAddress() . '_lupa_sandi');
        if (! $throttler->check($kunciIp, 5, MINUTE)) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Terlalu banyak permintaan reset sandi. Silakan tunggu 1 menit.',
                null,
                ResponseInterface::HTTP_TOO_MANY_REQUESTS
            );
        }

        $inputData = $this->ambilInputPermintaan();
        $email     = strtolower(trim((string) ($inputData['email'] ?? '')));
        $nama      = trim((string) ($inputData['nama'] ?? ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $nama === '') {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Alamat email yang valid dan nama lengkap akun wajib diisi.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $modelPengguna = new PenggunaModel();
        $pengguna      = $modelPengguna->cariDenganEmail($email);

        $namaCocok = $pengguna !== null
            && mb_strtolower(trim((string) $pengguna['nama'])) === mb_strtolower($nama);

        if (! $namaCocok) {
            return $this->kirimResponsAutentikasi(
                'berhasil',
                'Jika email dan nama lengkap sesuai dengan akun terdaftar, instruksi reset kata sandi telah diproses.',
                ['mode_simulasi' => false, 'token_reset' => null],
                ResponseInterface::HTTP_OK
            );
        }

        $tokenReset = self::buatTokenResetSandi($pengguna);
        if ($tokenReset === null) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Layanan reset sandi belum dikonfigurasi pada server.',
                null,
                ResponseInterface::HTTP_SERVICE_UNAVAILABLE
            );
        }

        $konfigEmail   = config('Email');
        $smtpTersedia  = trim((string) ($konfigEmail->SMTPHost ?? '')) !== ''
            && trim((string) ($konfigEmail->fromEmail ?? '')) !== '';
        // Cegah kebocoran token reset pada akun admin bila mode simulasi aktif
        $modeSimulasi  = ! $smtpTersedia
            && ($pengguna['peran'] ?? '') !== 'admin'
            && (
                ENVIRONMENT !== 'production'
                || filter_var(env('JEJAKSUMUT_SIMULASI_RESET_SANDI', false), FILTER_VALIDATE_BOOLEAN)
            );

        if ($smtpTersedia) {
            $urlFrontend  = rtrim((string) env('JEJAKSUMUT_FRONTEND_URL', 'http://localhost:8081'), '/');
            $tautanReset  = $urlFrontend . '/reset-sandi?token=' . urlencode($tokenReset);
            $emailService = \Config\Services::email();
            $emailService->setTo($email);
            $emailService->setSubject('Atur Ulang Kata Sandi JejakSumut');
            $emailService->setMessage(
                "Halo {$pengguna['nama']},\n\n"
                . "Klik tautan berikut (berlaku 15 menit) untuk mengatur ulang kata sandi akun JejakSumut Anda:\n"
                . "{$tautanReset}\n\n"
                . "Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini.\n"
            );

            if (! $emailService->send()) {
                return $this->kirimResponsAutentikasi(
                    'gagal',
                    'Gagal mengirim email reset kata sandi. Periksa konfigurasi SMTP server.',
                    null,
                    ResponseInterface::HTTP_SERVICE_UNAVAILABLE
                );
            }
        }

        return $this->kirimResponsAutentikasi(
            'berhasil',
            $modeSimulasi
                ? 'Verifikasi akun berhasil. Gunakan tautan atur ulang sandi di bawah (berlaku 15 menit).'
                : 'Jika email dan nama lengkap sesuai dengan akun terdaftar, instruksi reset kata sandi telah dikirim.',
            [
                'mode_simulasi' => $modeSimulasi,
                'token_reset'   => $modeSimulasi ? $tokenReset : null,
                'peran'         => $modeSimulasi ? $pengguna['peran'] : null,
            ],
            ResponseInterface::HTTP_OK
        );
    }

    /**
     * POST /api/v1/auth/reset-sandi dan POST /api/v1/autentikasi/reset-sandi
     * Mengatur ulang kata sandi menggunakan token reset HMAC-SHA256 yang valid.
     */
    public function resetSandi(): ResponseInterface
    {
        $throttler = \Config\Services::throttler();
        $kunciIp   = md5($this->request->getIPAddress() . '_reset_sandi');
        if (! $throttler->check($kunciIp, 10, MINUTE)) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Terlalu banyak percobaan reset sandi. Silakan tunggu 1 menit.',
                null,
                ResponseInterface::HTTP_TOO_MANY_REQUESTS
            );
        }

        $inputData           = $this->ambilInputPermintaan();
        $token               = trim((string) ($inputData['token'] ?? ''));
        $kataSandiBaru       = (string) ($inputData['kata_sandi_baru'] ?? '');
        $konfirmasiKataSandi = (string) ($inputData['konfirmasi_kata_sandi'] ?? '');

        if ($token === '' || $kataSandiBaru === '' || $konfirmasiKataSandi === '') {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Token reset, kata sandi baru, dan konfirmasi kata sandi wajib diisi.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (strlen($kataSandiBaru) < 6) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Kata sandi baru minimal 6 karakter.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($kataSandiBaru !== $konfirmasiKataSandi) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Konfirmasi kata sandi baru tidak cocok.',
                null,
                ResponseInterface::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $dataToken = self::verifikasiTokenResetSandi($token);
        if ($dataToken === null) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Token reset sandi tidak valid atau sudah kedaluwarsa. Silakan ajukan ulang.',
                null,
                ResponseInterface::HTTP_UNAUTHORIZED
            );
        }

        $modelPengguna = new PenggunaModel();
        $pengguna      = $modelPengguna->find((int) $dataToken['id_pengguna']);
        $kunciRahasia  = self::ambilKunciRahasia();

        if ($pengguna === null || $kunciRahasia === null) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Akun pengguna tidak ditemukan.',
                null,
                ResponseInterface::HTTP_NOT_FOUND
            );
        }

        // Cocokkan sidik hash sandi saat ini agar token hanya bisa dipakai 1 kali
        $sidikSaatIni = substr(hash_hmac('sha256', (string) $pengguna['kata_sandi'], $kunciRahasia), 0, 16);
        if (! hash_equals($sidikSaatIni, (string) $dataToken['sidik_sandi'])) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Token reset sandi ini sudah pernah digunakan. Silakan ajukan ulang bila masih diperlukan.',
                null,
                ResponseInterface::HTTP_UNAUTHORIZED
            );
        }

        $berhasilSimpan = $modelPengguna->update((int) $pengguna['id'], [
            'kata_sandi'      => password_hash($kataSandiBaru, PASSWORD_DEFAULT),
            'diperbarui_pada' => date('Y-m-d H:i:s'),
        ]);

        if (! $berhasilSimpan) {
            return $this->kirimResponsAutentikasi(
                'gagal',
                'Gagal menyimpan kata sandi baru. Silakan coba lagi.',
                null,
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->kirimResponsAutentikasi(
            'berhasil',
            'Kata sandi berhasil diatur ulang. Silakan masuk dengan kata sandi baru Anda.',
            ['peran' => $pengguna['peran']],
            ResponseInterface::HTTP_OK
        );
    }

    /**
     * Mengambil payload masukan dari JSON body atau form POST.
     *
     * @return array<string, mixed>
     */
    private function ambilInputPermintaan(): array
    {
        $inputJson = $this->request->getJSON(true);
        if (! empty($inputJson) && is_array($inputJson)) {
            return $inputJson;
        }

        $inputPost = $this->request->getPost();

        return is_array($inputPost) ? $inputPost : [];
    }

    /**
     * Menyusun respons JSON autentikasi seragam tanpa cache.
     */
    private function kirimResponsAutentikasi(string $status, string $pesan, mixed $data, int $kodeStatus): ResponseInterface
    {
        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->respond([
            'status' => $status,
            'pesan'  => $pesan,
            'data'   => $data,
        ], $kodeStatus);
    }
}
