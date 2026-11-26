<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$etiket      = is_array($data_etiket ?? null) ? $data_etiket : [];
$statusTiket = (string) ($etiket['status'] ?? '');
$pesanSukses = (string) (session('pesan_sukses') ?? '');
$pesanError  = (string) (session('pesan_error') ?? '');

if ($statusTiket === 'lunas') {
    $kelasStatus = 'bg-[#e4f1e8] text-[#1b4d34] border-[#a7d7b8]';
    $ikonStatus  = 'verified';
    $labelStatus = 'Terverifikasi · Lunas';
    $catatanStub = 'Tiket aktif. Tunjukkan QR Code ini kepada petugas loket saat kedatangan.';
} elseif ($statusTiket === 'dibatalkan') {
    $kelasStatus = 'bg-[#eee7e3] text-[#665751] border-[#d9cdc7]';
    $ikonStatus  = 'cancel';
    $labelStatus = 'Dibatalkan';
    $catatanStub = 'Tiket ini telah dibatalkan dan tidak berlaku untuk masuk destinasi.';
} else {
    $kelasStatus = 'bg-[#fff2dc] text-[#85420e] border-[#f2cc8f]';
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
$lokasiTampil     = $kotaDestinasi !== '' ? $kotaDestinasi . ', Sumatera Utara' : 'Sumatera Utara';
$punyaFoto        = ! empty($etiket['foto_destinasi']);
$kodePemesanan    = (string) ($etiket['kode_pemesanan'] ?? 'JSM-TIKET');
$jumlahPengunjung = max(1, (int) ($etiket['jumlah_pengunjung'] ?? 1));
$totalHarga       = (int) ($etiket['total_harga'] ?? 0);
$hargaSatuan      = (int) ($etiket['harga_satuan'] ?? intdiv($totalHarga, $jumlahPengunjung));

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
    .kartu-etiket {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    @media (prefers-reduced-motion: reduce) {
        .transisi-etiket {
            transition: none !important;
        }
    }
    @page {
        size: A4;
        margin: 12mm;
    }
    @media print {
        header,
        footer,
        .no-print {
            display: none !important;
        }
        main {
            padding-top: 0 !important;
            min-height: auto !important;
            background: #ffffff !important;
        }
        .bungkus-etiket {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .kartu-etiket {
            box-shadow: none !important;
            border-color: #d6c4bb !important;
            grid-template-columns: minmax(0, 1.65fr) auto minmax(220px, 0.95fr) !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .garis-sobekan {
            flex-direction: column !important;
        }
        .garis-sobekan-putus {
            margin: 0.5rem 0 !important;
            border-top: 0 !important;
            border-left: 2px dashed #d6c4bb !important;
        }
        .takik-atas {
            margin-left: 0 !important;
            margin-top: -0.75rem !important;
        }
        .takik-bawah {
            margin-right: 0 !important;
            margin-bottom: -0.75rem !important;
        }
    }
</style>

<div class="bungkus-etiket max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full">

    <div class="no-print mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="<?= base_url('pemesanan') ?>"
           class="transisi-etiket cursor-pointer inline-flex items-center gap-2 min-h-[44px] self-start rounded-xl px-3 py-2 text-sm font-semibold text-primary hover:bg-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            <span>Kembali ke Tiket Saya</span>
        </a>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button"
                    data-salin-kode="<?= esc($kodePemesanan, 'attr') ?>"
                    class="transisi-etiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-xl border border-[#d6c4bb] bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-surface hover:bg-surface-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">content_copy</span>
                <span>Salin Kode</span>
            </button>

            <button type="button"
                    onclick="window.print()"
                    class="transisi-etiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary shadow-sm hover:bg-primary-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
                <span>Cetak E-Ticket</span>
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

    <!-- Desain Boarding Pass Modern JejakSumut (Muat 1 Halaman Cetak A4) -->
    <article aria-labelledby="judul-destinasi-etiket"
             class="kartu-etiket rounded-2xl border border-[#decbc2] bg-white overflow-hidden shadow-[0_14px_34px_-12px_rgba(36,29,25,0.14)] grid grid-cols-1 md:grid-cols-[minmax(0,1.65fr)_auto_minmax(240px,0.95fr)]">

        <!-- BAGIAN UTAMA PASS (KIRI) -->
        <div class="flex flex-col justify-between min-w-0">
            <!-- Header Boarding Pass -->
            <div class="bg-[#00545b] text-white px-6 py-3.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/15">
                        <span class="material-symbols-outlined text-base text-white" aria-hidden="true">confirmation_number</span>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] leading-none">JejakSumut · E-Pass</p>
                        <p class="mt-0.5 text-[11px] text-white/80 leading-none">Tiket Kunjungan Resmi Sumatera Utara</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold shrink-0 <?= $kelasStatus ?>">
                    <span class="material-symbols-outlined text-xs" aria-hidden="true"><?= $ikonStatus ?></span>
                    <span><?= esc($labelStatus) ?></span>
                </span>
            </div>

            <!-- Identitas Destinasi (Padat, tanpa ruang kosong berlebih) -->
            <div class="px-6 py-5 bg-[#faf4f0] border-b border-[#efe3dc] flex items-center gap-4">
                <?php if ($punyaFoto) : ?>
                    <img
                        src="<?= esc((string) $etiket['foto_destinasi']) ?>"
                        alt="Foto <?= esc((string) ($etiket['nama_destinasi'] ?? '')) ?>"
                        width="96"
                        height="76"
                        onerror="this.style.display='none'"
                        class="h-20 w-24 shrink-0 rounded-xl object-cover border border-[#e5d7d0] bg-[#efe3dc]"
                    />
                <?php endif; ?>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#00545b]">Destinasi Wisata</p>
                    <h1 id="judul-destinasi-etiket" class="mt-0.5 font-serif text-xl sm:text-2xl font-bold text-[#241d19] leading-snug">
                        <?= esc((string) ($etiket['nama_destinasi'] ?? 'Destinasi')) ?>
                    </h1>
                    <p class="mt-1 flex items-center gap-1 text-xs sm:text-sm text-[#665751]">
                        <span class="material-symbols-outlined text-base text-[#b53801] shrink-0" aria-hidden="true">location_on</span>
                        <span class="truncate"><?= esc($lokasiTampil) ?></span>
                    </p>
                </div>
            </div>

            <!-- Rincian Utama Tiket -->
            <dl class="px-6 py-5 grid grid-cols-2 gap-x-6 gap-y-4">
                <?php if (! empty($etiket['nama_pemesan'])) : ?>
                    <div class="col-span-2 sm:col-span-1">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Nama Pemesan</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#241d19]">
                            <?= esc((string) $etiket['nama_pemesan']) ?>
                        </dd>
                    </div>
                <?php endif; ?>

                <div class="col-span-2 sm:col-span-1">
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Tanggal Kunjungan</dt>
                    <dd class="mt-1 text-sm font-bold text-[#00545b] tabular-nums">
                        <?= esc($formatTanggalLengkap($etiket['tanggal_kunjungan'] ?? null)) ?>
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Jumlah Pengunjung</dt>
                    <dd class="mt-1 text-sm font-semibold text-[#241d19] tabular-nums">
                        <?= esc((string) $jumlahPengunjung) ?> Orang
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Total Pembayaran</dt>
                    <dd class="mt-1 text-base sm:text-lg font-bold text-[#b53801] tabular-nums leading-tight">
                        <?= esc((string) ($etiket['total_harga_terformat'] ?? '-')) ?>
                    </dd>
                    <?php if ($hargaSatuan > 0) : ?>
                        <dd class="mt-0.5 text-[11px] text-[#78716c] tabular-nums">
                            <?= esc((string) $jumlahPengunjung) ?> × Rp <?= esc(number_format($hargaSatuan, 0, ',', '.')) ?>
                        </dd>
                    <?php endif; ?>
                </div>

                <?php if (! empty($etiket['jam_buka_destinasi']) && ! empty($etiket['jam_tutup_destinasi'])) : ?>
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Jam Operasional</dt>
                        <dd class="mt-1 text-xs sm:text-sm font-medium text-[#241d19] tabular-nums">
                            <?= esc(substr((string) $etiket['jam_buka_destinasi'], 0, 5)) ?> –
                            <?= esc(substr((string) $etiket['jam_tutup_destinasi'], 0, 5)) ?> WIB
                        </dd>
                    </div>
                <?php endif; ?>

                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Waktu Pemesanan</dt>
                    <dd class="mt-1 text-xs sm:text-sm text-[#44403c] tabular-nums">
                        <?= esc($formatWaktuPesan($etiket['dibuat_pada'] ?? null)) ?>
                    </dd>
                </div>

                <?php if (! empty($etiket['alamat_destinasi'])) : ?>
                    <div class="col-span-2 pt-3 border-t border-[#efe4de]">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-[#78716c]">Alamat Lokasi</dt>
                        <dd class="mt-1 text-xs sm:text-sm text-[#44403c] leading-relaxed">
                            <?= esc((string) $etiket['alamat_destinasi']) ?>
                        </dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- GARIS SOBEKAN TIKET (HORIZONTAL DI MOBILE, VERTIKAL DI DESKTOP & CETAK) -->
        <div class="garis-sobekan relative flex md:flex-col items-center justify-between bg-[#faf4f0]" aria-hidden="true">
            <span class="takik-atas h-6 w-6 -ml-3 md:ml-0 md:-mt-3 rounded-full bg-surface border border-[#decbc2] shrink-0"></span>
            <span class="garis-sobekan-putus flex-1 mx-2 md:mx-0 md:my-2 border-t-2 md:border-t-0 md:border-l-2 border-dashed border-[#d6c4bb]"></span>
            <span class="takik-bawah h-6 w-6 -mr-3 md:mr-0 md:-mb-3 rounded-full bg-surface border border-[#decbc2] shrink-0"></span>
        </div>

        <!-- POTONGAN STUB VERIFIKASI (KANAN) -->
        <aside class="bg-[#faf4f0] p-5 sm:p-6 flex flex-col justify-between items-center text-center gap-4">
            <div class="w-full rounded-xl bg-[#00545b] px-3.5 py-3 text-white">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-white/80">
                    Kode Booking
                </p>
                <p class="mt-1 font-mono text-base font-bold tabular-nums tracking-wider break-all">
                    <?= esc($kodePemesanan) ?>
                </p>
            </div>

            <!-- QR Code Verifikasi Loket -->
            <div class="rounded-xl border border-[#e5d7d0] bg-white p-3.5 flex flex-col items-center gap-2 shadow-xs">
                <svg viewBox="0 0 21 21"
                     class="w-28 h-28 text-[#241d19]"
                     shape-rendering="crispEdges"
                     role="img"
                     aria-label="Kode QR verifikasi tiket <?= esc($kodePemesanan, 'attr') ?>">
                    <rect width="21" height="21" fill="#ffffff" />
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
                <span class="font-mono text-[10px] font-bold tracking-[0.14em] text-[#665751] uppercase">
                    Scan di Loket Masuk
                </span>
            </div>

            <div class="w-full pt-3 border-t border-[#e5d7d0] space-y-1.5">
                <p class="text-xs font-semibold text-[#241d19] tabular-nums">
                    <?= esc((string) $jumlahPengunjung) ?> Orang · <?= esc($formatTanggalRingkas($etiket['tanggal_kunjungan'] ?? null)) ?>
                </p>
                <p class="text-[11px] text-[#665751] leading-relaxed">
                    <?= esc($catatanStub) ?>
                </p>
            </div>
        </aside>
    </article>

    <?php if ($statusTiket === 'menunggu') : ?>
        <div class="no-print mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-[#e5d7d0] bg-surface-container-lowest px-5 py-4">
            <p class="text-sm text-on-surface-variant">
                Berubah rencana? Tiket berstatus menunggu pembayaran masih dapat dibatalkan.
            </p>
            <form action="<?= base_url('pemesanan/batal/' . (int) ($etiket['id'] ?? 0)) ?>"
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
                        class="transisi-etiket cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-1.5 min-h-[44px] rounded-xl border border-on-error-container bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-error-container hover:bg-error-container active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                    <span data-teks-tombol>Batalkan Tiket</span>
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

<script>
    (function () {
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
