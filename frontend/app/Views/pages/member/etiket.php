<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$etiket      = is_array($data_etiket ?? null) ? $data_etiket : [];
$statusTiket = (string) ($etiket['status'] ?? '');
$pesanSukses = (string) (session('pesan_sukses') ?? '');
$pesanError  = (string) (session('pesan_error') ?? '');

if ($statusTiket === 'lunas') {
    $kelasStatus = 'bg-[#e4f3ea] text-[#174d32] border-[#9fd4b5]';
    $ikonStatus  = 'verified';
    $labelStatus = 'Terverifikasi · Lunas';
    $catatanStub = 'Tiket aktif. Tunjukkan QR Code ini kepada petugas loket saat kedatangan.';
} elseif ($statusTiket === 'dibatalkan') {
    $kelasStatus = 'bg-[#f2e8e4] text-[#6b4e45] border-[#d9c5bd]';
    $ikonStatus  = 'cancel';
    $labelStatus = 'Dibatalkan';
    $catatanStub = 'Tiket ini telah dibatalkan dan tidak lagi berlaku untuk masuk destinasi.';
} else {
    $kelasStatus = 'bg-[#fff3dc] text-[#85420e] border-[#f0c784]';
    $ikonStatus  = 'schedule';
    $labelStatus = 'Menunggu Verifikasi';
    $catatanStub = 'Simpan kode tiket ini. Pengelola akan memverifikasi pembayaran sebelum status menjadi Lunas.';
}

// Format tanggal lengkap berbahasa Indonesia tanpa bergantung pada locale server.
$namaHariId  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$namaBulanId = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

$formatTanggalLengkap = static function (?string $tanggal) use ($namaHariId, $namaBulanId): string {
    $waktu = strtotime((string) $tanggal);
    if ($waktu === false) {
        return '-';
    }
    $hari  = $namaHariId[(int) date('w', $waktu)] ?? '';
    $bulan = $namaBulanId[(int) date('n', $waktu)] ?? date('F', $waktu);

    return $hari . ', ' . date('d', $waktu) . ' ' . $bulan . ' ' . date('Y', $waktu);
};

$formatTanggalRingkas = static function (?string $tanggal) use ($namaBulanId): string {
    $waktu = strtotime((string) $tanggal);
    if ($waktu === false) {
        return '-';
    }
    $bulan = substr($namaBulanId[(int) date('n', $waktu)] ?? date('M', $waktu), 0, 3);

    return date('d', $waktu) . ' ' . $bulan . ' ' . date('Y', $waktu);
};

$formatWaktuPesan = static function (?string $timestamp) use ($namaBulanId): string {
    $waktu = strtotime((string) $timestamp);
    if ($waktu === false) {
        return '-';
    }
    $bulan = substr($namaBulanId[(int) date('n', $waktu)] ?? date('M', $waktu), 0, 3);

    return date('d', $waktu) . ' ' . $bulan . ' ' . date('Y, H:i', $waktu) . ' WIB';
};

$kotaDestinasi    = trim((string) ($etiket['kota_destinasi'] ?? ''));
$kabupatenKota    = trim((string) ($etiket['nama_kabupaten_kota'] ?? ''));
$lokasiDasar      = $kotaDestinasi !== '' ? $kotaDestinasi : $kabupatenKota;
$lokasiTampil     = $lokasiDasar !== '' ? $lokasiDasar . ', Sumatera Utara' : 'Sumatera Utara';
$punyaFoto        = ! empty($etiket['foto_destinasi']);
$kodePemesanan    = (string) ($etiket['kode_pemesanan'] ?? 'JSM-TIKET');
$idPemesanan      = (int) ($etiket['id'] ?? 0);
$jumlahPengunjung = max(1, (int) ($etiket['jumlah_pengunjung'] ?? 1));
$totalHarga       = (int) ($etiket['total_harga'] ?? 0);
$hargaSatuan      = (int) ($etiket['harga_satuan'] ?? intdiv($totalHarga, $jumlahPengunjung));
$namaPemesan      = trim((string) ($etiket['nama_pemesan'] ?? ''));
$emailPemesan     = trim((string) ($etiket['email_pemesan'] ?? ''));
$jamBuka          = trim((string) ($etiket['jam_buka_destinasi'] ?? ''));
$jamTutup         = trim((string) ($etiket['jam_tutup_destinasi'] ?? ''));
$punyaJamBuka     = $jamBuka !== '' && $jamTutup !== '';
$alamatDestinasi  = trim((string) ($etiket['alamat_destinasi'] ?? ''));
$tanggalKunjungan = $formatTanggalLengkap($etiket['tanggal_kunjungan'] ?? null);
$tanggalRingkas   = $formatTanggalRingkas($etiket['tanggal_kunjungan'] ?? null);
$waktuPemesanan   = $formatWaktuPesan($etiket['dibuat_pada'] ?? null);
$waktuCetak       = $formatWaktuPesan(date('Y-m-d H:i:s'));

// Tampilkan label harga yang jelas dan ramah wisatawan, termasuk tiket gratis.
if ($totalHarga <= 0) {
    $tampilanTotalHarga = 'Gratis';
    $rincianHarga       = 'Tiket masuk tidak berbayar';
} else {
    $tampilanTotalHarga = (string) ($etiket['total_harga_terformat'] ?? ('Rp ' . number_format($totalHarga, 0, ',', '.')));
    if ($hargaSatuan > 0) {
        $rincianHarga = $jumlahPengunjung . ' × Rp ' . number_format($hargaSatuan, 0, ',', '.');
    } else {
        $rincianHarga = 'Sudah termasuk ' . $jumlahPengunjung . ' pengunjung';
    }
}

// Bangun matriks QR Code 21x21 deterministik dari kode pemesanan
$ukuranQr = 21;
$hashQr   = hash('sha256', $kodePemesanan . '|jejaksumut-pass');
$bitQr    = '';
for ($i = 0; $i < strlen($hashQr); $i++) {
    $bitQr .= str_pad(base_convert($hashQr[$i], 16, 2), 4, '0', STR_PAD_LEFT);
}

$polaFinderAktif = static function (int $barisLokal, int $kolomLokal): bool {
    if ($barisLokal === 0 || $barisLokal === 6 || $kolomLokal === 0 || $kolomLokal === 6) {
        return true;
    }
    if ($barisLokal >= 2 && $barisLokal <= 4 && $kolomLokal >= 2 && $kolomLokal <= 4) {
        return true;
    }
    return false;
};
?>

<style>
    .dokumen-etiket {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .print-only {
        display: none;
    }
    @media (prefers-reduced-motion: reduce) {
        .transisi-etiket {
            transition: none !important;
        }
    }
    @page {
        size: A4 portrait;
        margin: 0;
    }
    @media print {
        header,
        footer,
        .no-print,
        .swal2-container,
        .swal2-toast {
            display: none !important;
        }
        .print-only {
            display: flex !important;
        }
        html,
        body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        main {
            padding-top: 0 !important;
            min-height: auto !important;
            background: #ffffff !important;
        }
        .bungkus-etiket {
            max-width: 100% !important;
            width: 100% !important;
            padding: 12mm 14mm !important;
            margin: 0 !important;
        }
        .kartu-etiket {
            box-shadow: none !important;
            border-color: #d6c4bb !important;
            grid-template-columns: minmax(0, 1.7fr) 24px minmax(225px, 0.92fr) !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .garis-sobekan {
            flex-direction: column !important;
            height: auto !important;
            width: 24px !important;
        }
        .garis-sobekan-putus {
            width: 0 !important;
            height: auto !important;
            margin: 0.45rem 0 !important;
            border-top: 0 !important;
            border-left: 2px dashed #d2bfb5 !important;
        }
        .takik-atas {
            left: 50% !important;
            top: -12px !important;
            bottom: auto !important;
            transform: translateX(-50%) !important;
            background: #ffffff !important;
        }
        .takik-bawah {
            left: 50% !important;
            right: auto !important;
            top: auto !important;
            bottom: -12px !important;
            transform: translateX(-50%) !important;
            background: #ffffff !important;
        }
        .panduan-etiket {
            box-shadow: none !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>

<div class="bungkus-etiket max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full">

    <!-- Bilah Aksi Layar (Tersembunyi Saat Cetak / PDF) -->
    <div class="no-print mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="<?= base_url('pemesanan') ?>"
           class="transisi-etiket cursor-pointer inline-flex items-center gap-2 min-h-[44px] self-start rounded-lg px-3 py-2 text-sm font-semibold text-primary hover:bg-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            <span>Kembali ke Tiket Saya</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button"
                    data-salin-kode="<?= esc($kodePemesanan, 'attr') ?>"
                    class="transisi-etiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-lg border border-[#d6c4bb] bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-surface hover:bg-surface-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">content_copy</span>
                <span>Salin Kode</span>
            </button>

            <button type="button"
                    id="tombol-cetak-etiket"
                    class="transisi-etiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <noscript>
        <?php if ($pesanSukses !== '') : ?>
            <div role="status" class="no-print mb-5 flex items-start gap-3 rounded-xl border border-secondary/40 bg-[#e8f6ea] px-4 py-3.5 text-sm text-on-secondary-fixed-variant">
                <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">check_circle</span>
                <span><?= esc($pesanSukses) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($pesanError !== '') : ?>
            <div role="alert" class="no-print mb-5 flex items-start gap-3 rounded-xl border border-error/40 bg-error-container px-4 py-3.5 text-sm text-on-error-container">
                <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">error</span>
                <span><?= esc($pesanError) ?></span>
            </div>
        <?php endif; ?>
    </noscript>

    <div class="dokumen-etiket space-y-5">

        <!-- Kop Dokumen Resmi (Khusus Tampilan Cetak / PDF A4) -->
        <div class="print-only items-center justify-between gap-4 pb-3.5 border-b border-[#dfd0c8]">
            <div class="flex items-center gap-3">
                <img src="<?= esc(base_url('assets/logo-jejaksumut.png'), 'attr') ?>"
                     alt="Logo JejakSumut"
                     class="h-10 w-10 object-contain" />
                <div>
                    <p class="font-serif text-lg font-bold text-[#1e1b19] leading-none">JejakSumut</p>
                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#00545b]">
                        Dokumen E-Ticket Resmi Pariwisata Sumatera Utara
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">No. Referensi</p>
                <p class="mt-0.5 font-mono text-xs font-bold text-[#1e1b19] tabular-nums"><?= esc($kodePemesanan) ?></p>
                <p class="mt-0.5 text-[10px] text-[#78716c] tabular-nums">Dicetak <?= esc($waktuCetak) ?></p>
            </div>
        </div>

        <!-- Kartu Boarding Pass Editorial JejakSumut -->
        <article aria-labelledby="judul-destinasi-etiket"
                 class="kartu-etiket rounded-2xl border border-[#dcc9bf] bg-white overflow-hidden shadow-[0_18px_42px_-16px_rgba(30,27,25,0.14)] grid grid-cols-1 md:grid-cols-[minmax(0,1.72fr)_24px_minmax(244px,0.92fr)]">

            <!-- BAGIAN UTAMA PASS (KIRI) -->
            <div class="flex flex-col justify-between min-w-0 bg-white">
                <!-- Header Pass Resmi -->
                <div class="bg-[#00545b] text-white px-5 sm:px-6 py-3.5 border-b-[3px] border-[#b53801] flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/95 p-1 shadow-xs">
                            <img src="<?= esc(base_url('assets/logo-jejaksumut.png'), 'attr') ?>"
                                 alt=""
                                 aria-hidden="true"
                                 class="h-full w-full object-contain" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-[0.15em] leading-none text-white">
                                JejakSumut · E-Pass
                            </p>
                            <p class="mt-1 text-[11px] text-[#cce9ec] leading-none">
                                Tiket Masuk Elektronik Wisata Sumatera Utara
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-bold shrink-0 <?= $kelasStatus ?>">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true"><?= $ikonStatus ?></span>
                        <span><?= esc($labelStatus) ?></span>
                    </span>
                </div>

                <!-- Sorotan Destinasi Wisata -->
                <div class="px-5 sm:px-6 py-5 bg-[#fbf7f4] border-b border-[#ece0d9] flex flex-col sm:flex-row sm:items-center gap-4">
                    <?php if ($punyaFoto) : ?>
                        <div class="relative h-36 sm:h-24 w-full sm:w-32 shrink-0 overflow-hidden rounded-xl border border-[#e5d7d0] bg-[#efe3dc]">
                            <img
                                src="<?= esc((string) $etiket['foto_destinasi']) ?>"
                                alt="Foto <?= esc((string) ($etiket['nama_destinasi'] ?? '')) ?>"
                                onerror="this.parentElement.style.display='none'"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    <?php endif; ?>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-[#00545b]">
                                Destinasi Wisata
                            </span>
                            <?php if ($idPemesanan > 0) : ?>
                                <span class="text-[10px] font-mono font-semibold text-[#78716c] tabular-nums">
                                    · Pass #<?= esc(str_pad((string) $idPemesanan, 4, '0', STR_PAD_LEFT)) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h1 id="judul-destinasi-etiket" class="mt-1 font-serif text-2xl sm:text-[26px] font-bold text-[#1e1b19] leading-tight tracking-tight">
                            <?= esc((string) ($etiket['nama_destinasi'] ?? 'Destinasi')) ?>
                        </h1>

                        <p class="mt-1.5 flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#574c47]">
                            <span class="material-symbols-outlined text-[17px] text-[#b53801] shrink-0" aria-hidden="true">location_on</span>
                            <span class="truncate"><?= esc($lokasiTampil) ?></span>
                        </p>
                    </div>
                </div>

                <!-- Rincian Pemegang Tiket & Jadwal -->
                <dl class="px-5 sm:px-6 py-5 grid grid-cols-2 sm:grid-cols-6 gap-x-5 gap-y-4">
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">Nama Pemesan</dt>
                        <dd class="mt-1 text-sm font-bold text-[#1e1b19] leading-snug break-words">
                            <?= esc($namaPemesan !== '' ? $namaPemesan : 'Wisatawan JejakSumut') ?>
                        </dd>
                        <?php if ($emailPemesan !== '') : ?>
                            <dd class="mt-0.5 text-xs text-[#665751] truncate">
                                <?= esc($emailPemesan) ?>
                            </dd>
                        <?php endif; ?>
                    </div>

                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">Tanggal Kunjungan</dt>
                        <dd class="mt-1 text-sm sm:text-[15px] font-bold text-[#00545b] tabular-nums leading-snug">
                            <?= esc($tanggalKunjungan) ?>
                        </dd>
                        <?php if ($punyaJamBuka) : ?>
                            <dd class="mt-0.5 text-xs text-[#665751] tabular-nums">
                                Jam Operasional: <?= esc(substr($jamBuka, 0, 5)) ?> – <?= esc(substr($jamTutup, 0, 5)) ?> WIB
                            </dd>
                        <?php endif; ?>
                    </div>

                    <div class="col-span-2 sm:col-span-6 border-t border-[#f0e5df]"></div>

                    <div class="col-span-1 sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">Pengunjung</dt>
                        <dd class="mt-1 text-sm font-bold text-[#1e1b19] tabular-nums">
                            <?= esc((string) $jumlahPengunjung) ?> Orang
                        </dd>
                    </div>

                    <div class="col-span-1 sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">Total Pembayaran</dt>
                        <dd class="mt-1 text-base font-bold text-[#b53801] tabular-nums leading-tight">
                            <?= esc($tampilanTotalHarga) ?>
                        </dd>
                        <dd class="mt-0.5 text-[11px] text-[#78716c] tabular-nums">
                            <?= esc($rincianHarga) ?>
                        </dd>
                    </div>

                    <div class="col-span-2 sm:col-span-2">
                        <dt class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">Waktu Pemesanan</dt>
                        <dd class="mt-1 text-xs sm:text-[13px] font-medium text-[#3f3733] tabular-nums">
                            <?= esc($waktuPemesanan) ?>
                        </dd>
                    </div>

                    <?php if ($alamatDestinasi !== '') : ?>
                        <div class="col-span-2 sm:col-span-6 pt-3.5 border-t border-[#f0e5df]">
                            <dt class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#78716c]">Alamat Lokasi</dt>
                            <dd class="mt-1 text-xs sm:text-[13px] text-[#44403c] leading-relaxed">
                                <?= esc($alamatDestinasi) ?>
                            </dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- GARIS SOBEKAN TIKET DENGAN TAKIK PRESISI -->
            <div class="garis-sobekan relative h-6 md:h-auto md:w-6 flex md:flex-col items-center justify-center bg-[#faf4f0]" aria-hidden="true">
                <span class="takik-atas absolute -left-3 md:left-1/2 md:-top-3 md:-translate-x-1/2 h-6 w-6 rounded-full bg-surface border border-[#dcc9bf]"></span>
                <span class="garis-sobekan-putus w-full mx-4 md:w-0 md:h-full md:mx-0 md:my-4 border-t-2 md:border-t-0 md:border-l-2 border-dashed border-[#d2bfb5]"></span>
                <span class="takik-bawah absolute -right-3 md:right-auto md:left-1/2 md:-bottom-3 md:-translate-x-1/2 h-6 w-6 rounded-full bg-surface border border-[#dcc9bf]"></span>
            </div>

            <!-- POTONGAN STUB VERIFIKASI (KANAN) -->
            <aside class="bg-[#faf4f0] px-5 py-5 sm:px-6 sm:py-6 flex flex-col justify-between items-center text-center gap-4">
                <div class="w-full">
                    <div class="rounded-xl bg-[#00545b] px-3.5 py-3 text-white shadow-xs">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#cce9ec]">
                            Kode Booking
                        </p>
                        <p class="mt-1 font-mono text-[15px] font-bold tabular-nums tracking-[0.08em] break-all">
                            <?= esc($kodePemesanan) ?>
                        </p>
                    </div>
                </div>

                <!-- QR Code Verifikasi Loket dengan Quiet Zone Standar -->
                <div class="relative rounded-xl border border-[#e2d3cb] bg-white px-4 py-3.5 flex flex-col items-center gap-2 shadow-xs">
                    <svg viewBox="-1 -1 23 23"
                         class="w-28 h-28 text-[#1e1b19] <?= $statusTiket === 'dibatalkan' ? 'opacity-30' : '' ?>"
                         shape-rendering="crispEdges"
                         role="img"
                         aria-label="Kode QR verifikasi tiket <?= esc($kodePemesanan, 'attr') ?>">
                        <rect x="-1" y="-1" width="23" height="23" fill="#ffffff" />
                        <?php
                        for ($baris = 0; $baris < $ukuranQr; $baris++) {
                            for ($kolom = 0; $kolom < $ukuranQr; $kolom++) {
                                $aktif = false;
                                if ($baris < 7 && $kolom < 7) {
                                    $aktif = $polaFinderAktif($baris, $kolom);
                                } elseif ($baris < 7 && $kolom >= $ukuranQr - 7) {
                                    $aktif = $polaFinderAktif($baris, $kolom - ($ukuranQr - 7));
                                } elseif ($baris >= $ukuranQr - 7 && $kolom < 7) {
                                    $aktif = $polaFinderAktif($baris - ($ukuranQr - 7), $kolom);
                                } elseif ($baris === 7 || $kolom === 7) {
                                    $aktif = false;
                                } else {
                                    $indeksBit = (($baris * $ukuranQr) + $kolom) % strlen($bitQr);
                                    $aktif     = $bitQr[$indeksBit] === '1';
                                }

                                if ($aktif) {
                                    echo '<rect x="' . $kolom . '" y="' . $baris . '" width="1" height="1" fill="currentColor" />';
                                }
                            }
                        }
                        ?>
                    </svg>

                    <?php if ($statusTiket === 'dibatalkan') : ?>
                        <span class="rounded-md border border-[#b53801]/40 bg-[#fff2ee] px-2 py-0.5 font-mono text-[10px] font-bold tracking-[0.14em] text-[#b53801] uppercase">
                            Tidak Berlaku
                        </span>
                    <?php else : ?>
                        <span class="font-mono text-[10px] font-bold tracking-[0.14em] text-[#574c47] uppercase">
                            Scan di Loket Masuk
                        </span>
                    <?php endif; ?>
                </div>

                <div class="w-full pt-3 border-t border-[#e2d3cb] space-y-1.5">
                    <p class="text-xs font-bold text-[#1e1b19] tabular-nums">
                        <?= esc((string) $jumlahPengunjung) ?> Orang · <?= esc($tanggalRingkas) ?>
                    </p>
                    <p class="text-[11px] text-[#574c47] leading-relaxed">
                        <?= esc($catatanStub) ?>
                    </p>
                </div>
            </aside>
        </article>

        <!-- Panduan & Ketentuan Penggunaan E-Ticket (Tampil Rapi di Layar & Cetak A4) -->
        <section aria-labelledby="judul-panduan-etiket"
                 class="panduan-etiket rounded-2xl border border-[#e5d7d0] bg-white px-5 sm:px-6 py-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3.5 border-b border-[#f0e5df]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-[#00545b]" aria-hidden="true">info</span>
                    <h2 id="judul-panduan-etiket" class="font-serif text-base font-bold text-[#1e1b19]">
                        Panduan Penggunaan &amp; Ketentuan Masuk
                    </h2>
                </div>
                <span class="text-[11px] font-medium text-[#78716c]">
                    Berlaku khusus untuk destinasi tertera
                </span>
            </div>

            <ol class="mt-3.5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5 text-xs text-[#44403c] leading-relaxed">
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#eef5f6] text-[11px] font-bold text-[#00545b] tabular-nums">1</span>
                    <span>Tunjukkan E-Ticket ini kepada petugas loket destinasi dalam bentuk cetakan PDF maupun langsung dari layar ponsel.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#eef5f6] text-[11px] font-bold text-[#00545b] tabular-nums">2</span>
                    <span>Tiket hanya sah digunakan untuk masuk apabila status pada E-Pass telah berubah menjadi <strong>Terverifikasi · Lunas</strong>.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#eef5f6] text-[11px] font-bold text-[#00545b] tabular-nums">3</span>
                    <span>E-Ticket berlaku pada tanggal kunjungan <strong><?= esc($tanggalRingkas) ?></strong> sesuai jam operasional destinasi wisata.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#eef5f6] text-[11px] font-bold text-[#00545b] tabular-nums">4</span>
                    <span>Mohon menjaga kelestarian alam, kebersihan area wisata, serta mematuhi arahan petugas pengelola setempat.</span>
                </li>
            </ol>
        </section>

        <!-- Catatan Kaki Resmi Dokumen Cetak -->
        <div class="print-only items-center justify-between gap-4 pt-2 border-t border-[#e5d7d0] text-[10px] text-[#78716c]">
            <span>JejakSumut — Portal Pariwisata Provinsi Sumatera Utara</span>
            <span class="font-mono tabular-nums">Verifikasi Elektronik · <?= esc($kodePemesanan) ?></span>
        </div>

    </div>

    <?php if ($statusTiket === 'menunggu') : ?>
        <div class="no-print mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-[#e5d7d0] bg-surface-container-lowest px-5 py-4">
            <p class="text-sm text-on-surface-variant">
                Berubah rencana? Tiket berstatus menunggu verifikasi masih dapat dibatalkan.
            </p>
            <form action="<?= base_url('pemesanan/batal/' . $idPemesanan) ?>"
                  method="post"
                  data-form-batal-etiket
                  data-swal-konfirmasi
                  data-swal-ikon="warning"
                  data-swal-judul="Batalkan Tiket Ini?"
                  data-swal-teks="Tiket <?= esc($kodePemesanan, 'attr') ?> yang dibatalkan tidak dapat diaktifkan kembali."
                  data-swal-tombol-ya="Ya, Batalkan Tiket"
                  data-swal-bahaya="true"
                  data-teks-proses="Membatalkan..."
                  class="shrink-0">
                <?= csrf_field() ?>
                <button type="submit"
                        class="transisi-etiket cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-1.5 min-h-[44px] rounded-lg border border-on-error-container bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-error-container hover:bg-error-container active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                    <span data-teks-tombol>Batalkan Tiket</span>
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

<script>
    (function () {
        var tombolCetak = document.getElementById('tombol-cetak-etiket');
        if (tombolCetak) {
            tombolCetak.addEventListener('click', function () {
                // Tutup notifikasi toast yang sedang tampil agar tidak ikut tercetak
                if (typeof window.Swal !== 'undefined' && typeof window.Swal.close === 'function') {
                    window.Swal.close();
                }
                window.print();
            });
        }

        var tombolSalin = document.querySelector('[data-salin-kode]');
        if (!tombolSalin) {
            return;
        }

        tombolSalin.addEventListener('click', function () {
            var kode = tombolSalin.getAttribute('data-salin-kode') || '';
            if (!kode) {
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(kode).then(function () {
                    if (typeof window.tampilkanToastJejakSumut === 'function') {
                        window.tampilkanToastJejakSumut('success', 'Kode tiket disalin: ' + kode, 3500);
                    }
                });
                return;
            }

            var inputSementara = document.createElement('input');
            inputSementara.value = kode;
            document.body.appendChild(inputSementara);
            inputSementara.select();
            document.execCommand('copy');
            document.body.removeChild(inputSementara);

            if (typeof window.tampilkanToastJejakSumut === 'function') {
                window.tampilkanToastJejakSumut('success', 'Kode tiket disalin: ' + kode, 3500);
            }
        });
    })();
</script>
<?= $this->endSection() ?>
