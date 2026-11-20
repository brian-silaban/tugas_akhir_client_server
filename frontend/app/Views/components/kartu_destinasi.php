<?php
/**
 * Komponen: Kartu Destinasi Wisata Sumatera Utara
 * Dipakai di: Beranda (welcome_message.php) & Katalog (pages/destinasi/katalog.php)
 *
 * @var array<string, mixed> $destinasi
 */

$urlDetail       = base_url('destinasi/' . ($destinasi['slug'] ?? $destinasi['id'] ?? ''));
$jamOperasional  = ambilTeksJamOperasional($destinasi['jam_operasional'] ?? null);
$daftarFasilitas = is_array($destinasi['daftar_fasilitas'] ?? null) ? $destinasi['daftar_fasilitas'] : [];
$rataRating      = (float) ($destinasi['rata_rating'] ?? 0);
$jumlahUlasan    = (int) ($destinasi['jumlah_ulasan'] ?? 0);
$hargaTiket      = (int) ($destinasi['harga_tiket'] ?? 0);
?>
<article class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-surface-variant/40 flex flex-col justify-between h-full group hover:shadow-md transition-shadow">
    <div>
        <!-- Foto Destinasi & Badge Sub-Kategori -->
        <a href="<?= esc($urlDetail) ?>" class="block relative aspect-[4/3] w-full overflow-hidden bg-surface-container">
            <img
                src="<?= esc($destinasi['foto_utama'] ?? '') ?>"
                alt="<?= esc($destinasi['nama'] ?? 'Destinasi Sumatera Utara') ?>"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
            />
            <span class="absolute top-3 left-3 font-label-caps text-label-caps px-3 py-1 rounded-full bg-surface-container-lowest/95 backdrop-blur-sm text-primary font-bold shadow-sm">
                <?= esc(($destinasi['sub_kategori'] ?? '') !== '' ? $destinasi['sub_kategori'] : ($destinasi['kategori'] ?? 'Wisata Sumut')) ?>
            </span>
        </a>

        <!-- Detail Lokasi, Rating, Deskripsi & Fasilitas -->
        <div class="p-5">
            <div class="flex items-center justify-between text-outline font-body-sm text-body-sm gap-2">
                <span class="truncate flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs text-primary">pin_drop</span>
                    <?= esc($destinasi['kota'] ?? 'Sumatera Utara') ?>
                    <?php if (! empty($destinasi['kode_kabupaten'])) : ?>
                        (<?= esc($destinasi['kode_kabupaten']) ?>)
                    <?php endif; ?>
                </span>
                <span class="font-metric-rating text-metric-rating text-on-surface flex items-center gap-0.5 shrink-0 tabular-nums">
                    <span class="material-symbols-outlined text-xs text-amber-600" style="font-variation-settings: 'FILL' 1;">star</span>
                    <?= esc(number_format($rataRating, 2, '.', '')) ?>
                    <span class="text-outline font-normal text-xs">(<?= esc(number_format($jumlahUlasan, 0, ',', '.')) ?>)</span>
                </span>
            </div>

            <h3 class="font-headline-sm text-headline-sm text-on-surface mt-2 group-hover:text-primary transition-colors line-clamp-2">
                <a href="<?= esc($urlDetail) ?>" class="focus-visible:outline-none focus-visible:underline">
                    <?= esc($destinasi['nama'] ?? '') ?>
                </a>
            </h3>

            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5 flex items-center gap-1 tabular-nums">
                <span class="material-symbols-outlined text-xs text-primary">schedule</span>
                <span class="truncate"><?= esc($jamOperasional) ?></span>
            </p>

            <?php if (! empty($destinasi['deskripsi'])) : ?>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 line-clamp-2">
                    <?= esc($destinasi['deskripsi']) ?>
                </p>
            <?php endif; ?>

            <?php if (! empty($destinasi['pengelola'])) : ?>
                <p class="font-body-sm text-body-sm text-outline mt-2 truncate">
                    Pengelola: <?= esc($destinasi['pengelola']) ?>
                </p>
            <?php endif; ?>

            <?php if ($daftarFasilitas !== []) : ?>
                <div class="flex flex-wrap gap-1.5 mt-3">
                    <?php foreach (array_slice($daftarFasilitas, 0, 3) as $fasilitasItem) : ?>
                        <span class="font-body-sm text-body-sm px-2 py-0.5 rounded bg-surface-container text-on-surface-variant">
                            <?= esc($fasilitasItem) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Kartu: Transparansi Harga Tiket & Tautan Detail -->
    <div class="px-5 pb-5 pt-3 border-t border-surface-variant/30 flex items-center justify-between gap-2">
        <div class="min-w-0">
            <span class="font-label-caps text-label-caps text-outline uppercase block truncate">
                <?= esc($destinasi['label_harga_tiket'] ?? 'Tarif Masuk') ?>
            </span>
            <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                <?= esc(formatHargaRupiah($hargaTiket)) ?>
                <?php if ($hargaTiket > 0) : ?>
                    <span class="font-body-sm text-body-sm text-outline font-normal">/ orang</span>
                <?php endif; ?>
            </span>
        </div>
        <a
            href="<?= esc($urlDetail) ?>"
            aria-label="Lihat detail <?= esc($destinasi['nama'] ?? 'destinasi') ?>"
            class="w-9 h-9 rounded-lg bg-surface-container hover:bg-primary-container hover:text-on-primary text-on-surface flex items-center justify-center transition-colors shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
            <span class="material-symbols-outlined text-base">arrow_forward</span>
        </a>
    </div>
</article>
