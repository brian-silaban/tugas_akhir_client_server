<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$etiket      = is_array($data_etiket ?? null) ? $data_etiket : [];
$statusTiket = (string) ($etiket['status'] ?? '');
$pesanSukses = (string) (session('pesan_sukses') ?? '');
$pesanError  = (string) (session('pesan_error') ?? '');

if ($statusTiket === 'lunas') {
    $kelasStatus = 'bg-secondary-container text-on-secondary-fixed';
    $ikonStatus  = 'check_circle';
    $labelStatus = 'Tiket Aktif / Lunas';
} elseif ($statusTiket === 'dibatalkan') {
    $kelasStatus = 'bg-surface-container text-on-surface';
    $ikonStatus  = 'cancel';
    $labelStatus = 'Dibatalkan';
} else {
    $kelasStatus = 'bg-[#fef3c7] text-[#78350f]';
    $ikonStatus  = 'schedule';
    $labelStatus = 'Menunggu Pembayaran';
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

$formatWaktuPesan = static function (?string $timestamp) use ($namaBulanId): string {
    $waktu = strtotime((string) $timestamp);
    if ($waktu === false) {
        return '-';
    }
    $bulan = substr($namaBulanId[(int) date('n', $waktu)] ?? date('M', $waktu), 0, 3);

    return date('d', $waktu) . ' ' . $bulan . ' ' . date('Y, H:i', $waktu) . ' WIB';
};

$kotaDestinasi = trim((string) ($etiket['kota_destinasi'] ?? ''));
$lokasiTampil  = $kotaDestinasi !== '' ? $kotaDestinasi . ', Sumatera Utara' : 'Sumatera Utara';
$punyaFoto     = ! empty($etiket['foto_destinasi']);
?>

<style>
    @media (prefers-reduced-motion: reduce) {
        .transisi-etiket {
            transition: none !important;
        }
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
        .kartu-etiket {
            box-shadow: none !important;
            border-color: #d6d3d1 !important;
        }
    }
</style>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10 w-full">

    <div class="no-print mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="<?= base_url('pemesanan') ?>"
           class="transisi-etiket cursor-pointer inline-flex items-center gap-2 min-h-[44px] self-start rounded-lg px-2 py-2 text-sm font-medium text-primary hover:bg-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_back</span>
            <span>Kembali ke Tiket Saya</span>
        </a>

        <button type="button"
                onclick="window.print()"
                class="transisi-etiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">print</span>
            <span>Cetak E-Ticket</span>
        </button>
    </div>

    <?php if ($pesanSukses !== '') : ?>
        <div role="status" class="no-print mb-6 flex items-start gap-3 rounded-xl border border-secondary/40 bg-[#e8f6ea] px-4 py-3.5 text-sm text-on-secondary-fixed-variant">
            <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">check_circle</span>
            <span><?= esc($pesanSukses) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($pesanError !== '') : ?>
        <div role="alert" class="no-print mb-6 flex items-start gap-3 rounded-xl border border-error/40 bg-error-container px-4 py-3.5 text-sm text-on-error-container">
            <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">error</span>
            <span><?= esc($pesanError) ?></span>
        </div>
    <?php endif; ?>

    <article aria-labelledby="judul-destinasi-etiket" class="kartu-etiket rounded-2xl border border-surface-variant bg-surface-container-lowest overflow-hidden shadow-sm">

        <?php if ($punyaFoto) : ?>
            <div class="relative aspect-[16/8] sm:aspect-[16/7] overflow-hidden bg-surface-container">
                <img
                    src="<?= esc((string) $etiket['foto_destinasi']) ?>"
                    alt="Foto destinasi <?= esc((string) ($etiket['nama_destinasi'] ?? '')) ?>"
                    width="768"
                    height="336"
                    fetchpriority="high"
                    class="h-full w-full object-cover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/65 to-black/25" aria-hidden="true"></div>
                <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6 text-white">
                    <p class="text-xs font-medium tracking-wide uppercase text-white/90">
                        Tiket Masuk Wisata Sumatera Utara
                    </p>
                    <h1 id="judul-destinasi-etiket" class="mt-1 font-serif text-2xl sm:text-3xl font-semibold leading-tight">
                        <?= esc((string) ($etiket['nama_destinasi'] ?? 'Destinasi')) ?>
                    </h1>
                    <p class="mt-1 flex items-center gap-1 text-sm text-white/90">
                        <span class="material-symbols-outlined text-base shrink-0" aria-hidden="true">location_on</span>
                        <span><?= esc($lokasiTampil) ?></span>
                    </p>
                </div>
            </div>
        <?php else : ?>
            <div class="px-5 sm:px-6 pt-6 pb-5 bg-surface-container-low border-b border-surface-variant">
                <p class="text-xs font-medium tracking-wide uppercase text-on-surface-variant">
                    Tiket Masuk Wisata Sumatera Utara
                </p>
                <h1 id="judul-destinasi-etiket" class="mt-1 font-serif text-2xl sm:text-3xl font-semibold text-on-surface leading-tight">
                    <?= esc((string) ($etiket['nama_destinasi'] ?? 'Destinasi')) ?>
                </h1>
                <p class="mt-1 flex items-center gap-1 text-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-base shrink-0" aria-hidden="true">location_on</span>
                    <span><?= esc($lokasiTampil) ?></span>
                </p>
            </div>
        <?php endif; ?>

        <div class="px-5 sm:px-6 py-5 bg-primary text-on-primary flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wider text-on-primary-container">
                    Kode Pemesanan
                </p>
                <p class="mt-1 font-mono text-xl sm:text-2xl font-bold tabular-nums tracking-wider break-all">
                    <?= esc((string) ($etiket['kode_pemesanan'] ?? '-')) ?>
                </p>
            </div>

            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-md px-3 py-1.5 text-xs font-semibold whitespace-nowrap <?= $kelasStatus ?>">
                <span class="material-symbols-outlined text-base" aria-hidden="true"><?= $ikonStatus ?></span>
                <span><?= esc($labelStatus) ?></span>
            </span>
        </div>

        <!-- Garis sobekan pemisah kode booking dan rincian kunjungan -->
        <div class="relative flex items-center bg-surface-container-low py-2.5 border-y border-dashed border-outline/40" aria-hidden="true">
            <span class="h-5 w-5 -ml-2.5 rounded-full bg-surface border border-surface-variant shrink-0"></span>
            <span class="flex-1 mx-3 border-t border-dashed border-outline/50"></span>
            <span class="h-5 w-5 -mr-2.5 rounded-full bg-surface border border-surface-variant shrink-0"></span>
        </div>

        <dl class="px-5 sm:px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
            <?php if (! empty($etiket['nama_pemesan'])) : ?>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Nama Pemesan</dt>
                    <dd class="mt-1 text-sm font-semibold text-on-surface">
                        <?= esc((string) $etiket['nama_pemesan']) ?>
                    </dd>
                </div>
            <?php endif; ?>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Tanggal Kunjungan</dt>
                <dd class="mt-1 text-sm font-semibold text-on-surface tabular-nums">
                    <?= esc($formatTanggalLengkap($etiket['tanggal_kunjungan'] ?? null)) ?>
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Jumlah Pengunjung</dt>
                <dd class="mt-1 text-sm font-semibold text-on-surface tabular-nums">
                    <?= esc((string) ($etiket['jumlah_pengunjung'] ?? 0)) ?> Orang
                </dd>
            </div>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Total Bayar</dt>
                <dd class="mt-1 text-xl font-bold text-tertiary-container tabular-nums">
                    <?= esc((string) ($etiket['total_harga_terformat'] ?? '-')) ?>
                </dd>
            </div>

            <?php if (! empty($etiket['jam_buka_destinasi']) && ! empty($etiket['jam_tutup_destinasi'])) : ?>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Jam Operasional</dt>
                    <dd class="mt-1 text-sm font-medium text-on-surface tabular-nums">
                        <?= esc(substr((string) $etiket['jam_buka_destinasi'], 0, 5)) ?> -
                        <?= esc(substr((string) $etiket['jam_tutup_destinasi'], 0, 5)) ?> WIB
                    </dd>
                </div>
            <?php endif; ?>

            <div>
                <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Waktu Pemesanan</dt>
                <dd class="mt-1 text-sm text-on-surface tabular-nums">
                    <?= esc($formatWaktuPesan($etiket['dibuat_pada'] ?? null)) ?>
                </dd>
            </div>

            <?php if (! empty($etiket['alamat_destinasi'])) : ?>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">Alamat Lokasi</dt>
                    <dd class="mt-1 text-sm text-on-surface leading-relaxed">
                        <?= esc((string) $etiket['alamat_destinasi']) ?>
                    </dd>
                </div>
            <?php endif; ?>
        </dl>

        <div class="px-5 sm:px-6 py-4 bg-surface-container-low border-t border-surface-variant text-center">
            <p class="text-xs text-on-surface-variant">
                Tunjukkan kode pemesanan ini kepada petugas loket di pintu masuk destinasi. JejakSumut: Pariwisata Sumatera Utara.
            </p>
        </div>
    </article>

    <?php if ($statusTiket === 'menunggu') : ?>
        <div class="no-print mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-surface-variant bg-surface-container-lowest px-5 py-4">
            <p class="text-sm text-on-surface-variant">
                Berubah rencana? Tiket berstatus menunggu pembayaran masih dapat dibatalkan.
            </p>
            <form action="<?= base_url('pemesanan/batal/' . (int) ($etiket['id'] ?? 0)) ?>"
                  method="post"
                  data-form-batal-etiket
                  class="shrink-0">
                <?= csrf_field() ?>
                <button type="submit"
                        class="transisi-etiket cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-1.5 min-h-[44px] rounded-lg border border-on-error-container bg-surface-container-lowest px-4 py-2.5 text-sm font-semibold text-on-error-container hover:bg-error-container active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                    <span data-teks-tombol>Batalkan Tiket</span>
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>

<script>
    // Konfirmasi dan cegah klik ganda saat membatalkan tiket dari halaman E-Ticket.
    (function () {
        var formulir = document.querySelector('[data-form-batal-etiket]');
        if (!formulir) return;

        formulir.addEventListener('submit', function (event) {
            if (!window.confirm('Batalkan tiket ini? Tindakan ini tidak dapat dibatalkan.')) {
                event.preventDefault();
                return;
            }

            var tombol = formulir.querySelector('button[type="submit"]');
            var label  = formulir.querySelector('[data-teks-tombol]');
            if (tombol) {
                tombol.disabled = true;
                tombol.setAttribute('aria-busy', 'true');
            }
            if (label) {
                label.textContent = 'Membatalkan...';
            }
        });
    })();
</script>
<?= $this->endSection() ?>
