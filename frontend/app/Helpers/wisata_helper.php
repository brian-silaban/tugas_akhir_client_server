<?php

/**
 * Helper Tampilan Wisata — JejakSumut
 * Standar Clean Code Berbahasa Indonesia (.agents/rules/clean-code-indonesia.md)
 */

if (! function_exists('formatHargaRupiah')) {
    /**
     * Memformat angka harga tiket menjadi format mata uang Rupiah yang rapi.
     */
    function formatHargaRupiah(int $hargaTiket): string
    {
        if ($hargaTiket > 0) {
            return 'Rp ' . number_format($hargaTiket, 0, ',', '.');
        }

        return 'Gratis';
    }
}

if (! function_exists('formatKoordinatLokasi')) {
    /**
     * Memformat titik garis lintang dan garis bujur menjadi teks koordinat GPS secara aman.
     */
    function formatKoordinatLokasi(?float $garisLintang, ?float $garisBujur): string
    {
        if ($garisLintang === null || $garisBujur === null) {
            return 'Koordinat belum tersedia';
        }

        $arahLintang  = $garisLintang < 0 ? 'S' : 'N';
        $arahBujur    = $garisBujur < 0 ? 'W' : 'E';
        $nilaiLintang = number_format(abs($garisLintang), 4, '.', '');
        $nilaiBujur   = number_format(abs($garisBujur), 4, '.', '');

        return $nilaiLintang . '° ' . $arahLintang . ', ' . $nilaiBujur . '° ' . $arahBujur;
    }
}

if (! function_exists('ambilTeksJamOperasional')) {
    /**
     * Memastikan jam operasional tetap informatif meskipun jam_buka/jam_tutup bernilai null.
     */
    function ambilTeksJamOperasional(?string $jamOperasional): string
    {
        $teks = trim((string) $jamOperasional);
        if ($teks !== '') {
            return $teks;
        }

        return 'Informasi jam operasional belum tersedia';
    }
}
