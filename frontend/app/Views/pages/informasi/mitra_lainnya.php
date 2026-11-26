<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$layananTerpilih = (string) ($layananAktif ?? 'semua');
$wilayahTerpilih = (string) ($wilayahAktif ?? 'semua');
$daftarWilayah   = is_array($daftarKlaster ?? null) ? $daftarKlaster : [];
$daftarLayanan   = is_array($daftarItem ?? null) ? $daftarItem : [];

$bangunUrlFilter = static function (string $layananBaru, string $wilayahBaru): string {
    $query = array_filter([
        'layanan' => $layananBaru !== 'semua' ? $layananBaru : '',
        'wilayah' => $wilayahBaru !== 'semua' ? $wilayahBaru : '',
    ], static fn ($nilai): bool => $nilai !== '');

    $akhiran = $query !== [] ? '?' . http_build_query($query) : '';

    return base_url('mitra-lainnya' . $akhiran);
};
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <!-- Header Halaman Mitra Lainnya -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-surface-variant/50">
        <div>
            <span class="font-label-caps text-label-caps uppercase text-primary tracking-wider">
                LAYANAN PENDAMPING WISATA
            </span>
            <h1 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-1">
                Kuliner &amp; Tur Guide Sumatera Utara
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-2xl">
                Referensi pemandu wisata lokal serta kuliner khas di berbagai kawasan Sumatera Utara untuk melengkapi agenda perjalanan Anda.
            </p>
        </div>

        <span class="inline-flex items-center gap-1.5 self-start md:self-auto px-3.5 py-1.5 rounded-full bg-surface-container text-on-surface-variant border border-surface-variant/70 font-label-md text-label-md shrink-0">
            <span class="material-symbols-outlined text-base text-amber-600" aria-hidden="true">cloud_sync</span>
            <span>Data Simulasi Wilayah</span>
        </span>
    </div>

    <!-- Filter Jenis Layanan & Klaster Wilayah -->
    <div class="mt-6 bg-surface-container-lowest rounded-2xl p-4 sm:p-5 border border-surface-variant/50 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="font-label-caps text-label-caps text-outline uppercase mr-1">Kategori:</span>
            <?php foreach ([
                'semua'     => 'Semua Layanan',
                'tur_guide' => 'Penyedia Tur Guide',
                'kuliner'   => 'Kuliner & Rumah Makan',
            ] as $kodeJenis => $labelJenis) : ?>
                <a
                    href="<?= esc($bangunUrlFilter($kodeJenis, $wilayahTerpilih)) ?>"
                    class="<?= $layananTerpilih === $kodeJenis
                        ? 'px-3.5 py-2 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md transition-colors'
                        : 'px-3.5 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-md text-label-md transition-colors' ?>"
                >
                    <?= esc($labelJenis) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-surface-variant/40">
            <span class="font-label-caps text-label-caps text-outline uppercase mr-1">Kawasan:</span>
            <a
                href="<?= esc($bangunUrlFilter($layananTerpilih, 'semua')) ?>"
                class="<?= $wilayahTerpilih === 'semua'
                    ? 'px-3 py-1.5 rounded-full bg-secondary text-on-secondary font-label-md text-label-md transition-colors'
                    : 'px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-md text-label-md transition-colors' ?>"
            >
                Semua Wilayah
            </a>
            <?php foreach ($daftarWilayah as $kodeKlaster => $namaKlaster) : ?>
                <a
                    href="<?= esc($bangunUrlFilter($layananTerpilih, $kodeKlaster)) ?>"
                    class="<?= $wilayahTerpilih === $kodeKlaster
                        ? 'px-3 py-1.5 rounded-full bg-secondary text-on-secondary font-label-md text-label-md transition-colors'
                        : 'px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-md text-label-md transition-colors' ?>"
                >
                    <?= esc($namaKlaster) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid Kartu Layanan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        <?php foreach ($daftarLayanan as $item) : ?>
            <article class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-surface-variant/40 flex flex-col justify-between h-full group hover:shadow-md transition-shadow">
                <div>
                    <div class="relative aspect-[16/10] w-full overflow-hidden bg-surface-container">
                        <img
                            src="<?= esc((string) ($item['foto'] ?? '')) ?>"
                            alt="<?= esc((string) ($item['nama'] ?? 'Layanan Mitra')) ?>"
                            loading="lazy"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                        />
                        <span class="absolute top-3 left-3 font-label-caps text-label-caps px-3 py-1 rounded-full bg-surface-container-lowest/95 backdrop-blur-sm text-primary font-bold shadow-sm">
                            <?= esc((string) ($item['sub_label'] ?? $item['nama_layanan'])) ?>
                        </span>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between gap-2 text-outline font-body-sm text-body-sm">
                            <span class="inline-flex items-center gap-1 truncate">
                                <span class="material-symbols-outlined text-xs text-primary" aria-hidden="true">pin_drop</span>
                                <?= esc((string) ($item['kota'] ?? '')) ?>
                            </span>
                            <span class="font-metric-rating text-metric-rating text-on-surface inline-flex items-center gap-0.5 shrink-0 tabular-nums">
                                <span class="material-symbols-outlined text-xs text-amber-600" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                                <?= esc(number_format((float) ($item['rating'] ?? 0), 1, '.', '')) ?>
                                <span class="text-outline font-normal text-xs">(<?= esc(number_format((int) ($item['jumlah_ulasan'] ?? 0), 0, ',', '.')) ?>)</span>
                            </span>
                        </div>

                        <h2 class="font-headline-sm text-headline-sm text-on-surface mt-2 line-clamp-2">
                            <?= esc((string) ($item['nama'] ?? '')) ?>
                        </h2>

                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 line-clamp-2">
                            <?= esc((string) ($item['deskripsi'] ?? '')) ?>
                        </p>

                        <div class="mt-3 space-y-1 text-outline font-body-sm text-body-sm">
                            <p class="truncate"><?= esc((string) ($item['alamat'] ?? '')) ?></p>
                            <p class="inline-flex items-center gap-1 tabular-nums">
                                <span class="material-symbols-outlined text-xs text-secondary" aria-hidden="true">call</span>
                                <span><?= esc((string) ($item['kontak'] ?? '')) ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-5 pt-3 border-t border-surface-variant/30 flex items-center justify-between gap-2">
                    <div>
                        <span class="font-label-caps text-label-caps text-outline uppercase block">Estimasi Tarif</span>
                        <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                            <?= esc(formatHargaRupiah((int) ($item['harga'] ?? 0))) ?>
                            <?php if (! empty($item['satuan_harga']) && (int) ($item['harga'] ?? 0) > 0) : ?>
                                <span class="font-body-sm text-body-sm text-outline font-normal"><?= esc((string) $item['satuan_harga']) ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <span class="px-3 py-1 rounded-lg bg-surface-container font-label-caps text-label-caps text-on-surface-variant">
                        <?= esc((string) ($item['nama_klaster'] ?? 'Sumatera Utara')) ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?= $this->endSection() ?>
