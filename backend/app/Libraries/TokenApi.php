<?php

namespace App\Libraries;

class TokenApi
{
    private string $kunci;

    public function __construct()
    {
        $this->kunci = (string) env('JEJAKSUMUT_KUNCI_TOKEN', '');
    }

    public function terbitkan(int $idPengguna): ?string
    {
        if (strlen($this->kunci) < 32) {
            return null;
        }

        $muatan = $idPengguna . '.' . (time() + 7200) . '.' . bin2hex(random_bytes(16));

        return $muatan . '.' . hash_hmac('sha256', $muatan, $this->kunci);
    }

    public function bacaPengguna(string $otorisasi): ?int
    {
        // Cek token bertanda tangan dari AutentikasiApiController (Anggota 3)
        $tokenBersih = trim(preg_replace('/\ABearer\s+/i', '', $otorisasi));
        $dataToken   = \App\Controllers\Api\AutentikasiApiController::verifikasiToken($tokenBersih);
        if ($dataToken !== null) {
            return (int) $dataToken['id_pengguna'];
        }

        if (strlen($this->kunci) < 32 || ! preg_match(
            '/\ABearer (\d{1,10})\.(\d{1,12})\.([a-f0-9]{32})\.([a-f0-9]{64})\z/i',
            $otorisasi,
            $bagian
        )) {
            return null;
        }

        [, $idPengguna, $kedaluwarsa, $acak, $tandaTangan] = $bagian;
        $muatan = $idPengguna . '.' . $kedaluwarsa . '.' . $acak;
        $tandaTanganServer = hash_hmac('sha256', $muatan, $this->kunci);

        if (! hash_equals($tandaTanganServer, $tandaTangan) || (int) $kedaluwarsa <= time()) {
            return null;
        }

        return (int) $idPengguna;
    }
}
