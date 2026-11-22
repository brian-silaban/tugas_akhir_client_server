<?php
/**
 * Komponen: Rekomendasi Layanan 4 Kelompok Mitra Sumatera Utara
 * Dipanggil otomatis oleh halaman Detail Destinasi (frontend/app/Views/pages/destinasi/detail.php).
 * Mengambil data dari REST API 4 kelompok mitra (Tur Guide, Kuliner, Penginapan, Transportasi)
 * melalui LayananApiMitra dengan mekanisme timeout & auto-fallback.
 *
 * Anggota 2 (Brian) — Integrasi REST API Kelompok Mitra
 *
 * @var string|null               $kota
 * @var array<string, mixed>|null $destinasi
 * @var array<string, mixed>|null $hasilMitra
 */

use App\Libraries\LayananApiMitra;

// Pastikan kota pencarian terisi dari parameter View atau data destinasi
$kotaAktif = trim((string) ($kota ?? ''));
if ($kotaAktif === '' && is_array($destinasi ?? null)) {
    $kotaAktif = trim((string) ($destinasi['kota'] ?? $destinasi['nama_kabupaten_kota'] ?? ''));
}
if ($kotaAktif === '') {
    $kotaAktif = 'Sumatera Utara';
}

// Ambil rekomendasi dari 4 kelompok mitra jika belum dikirimkan oleh Controller
if (! isset($hasilMitra) || ! is_array($hasilMitra)) {
    try {
        $layananMitra = new LayananApiMitra();
        $hasilMitra   = $layananMitra->ambilSemuaRekomendasi($kotaAktif, 3);
    } catch (\Throwable $kesalahan) {
        $hasilMitra = [
            'kota_asli'       => $kotaAktif,
            'kota_pencarian'  => $kotaAktif,
            'mode_mock_aktif' => false,
            'ringkasan'       => [
                'total_mitra'         => 4,
                'jumlah_api_langsung' => 0,
                'jumlah_fallback'     => 0,
                'total_item'          => 0,
            ],
            'layanan' => [],
        ];
    }
}

$kotaTampil        = (string) ($hasilMitra['kota_asli'] ?? $kotaAktif);
$kotaPencarian     = (string) ($hasilMitra['kota_pencarian'] ?? $kotaAktif);
$ringkasanMitra    = is_array($hasilMitra['ringkasan'] ?? null) ? $hasilMitra['ringkasan'] : [];
$daftarLayanan     = is_array($hasilMitra['layanan'] ?? null) ? $hasilMitra['layanan'] : [];
$totalMitra        = (int) ($ringkasanMitra['total_mitra'] ?? count($daftarLayanan));
$jumlahApiLangsung = (int) ($ringkasanMitra['jumlah_api_langsung'] ?? 0);

// Tentukan kode tab yang aktif saat halaman pertama kali dibuka
$daftarKodeMitra = array_keys($daftarLayanan);
$kodeTabAwal     = $daftarKodeMitra[0] ?? 'tur_guide';
?>

<section
    id="bagian-rekomendasi-mitra"
    class="mt-12 pt-8 border-t border-surface-variant/50"
    aria-label="Rekomendasi Layanan Mitra Sumatera Utara"
>
    <!-- Header Seksi Integrasi 4 Kelompok Mitra -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <span class="font-label-caps text-label-caps text-primary uppercase inline-flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">hub</span>
                Ekosistem Client-Server Sumatera Utara
            </span>
            <h2 class="font-headline-md text-headline-md text-on-surface mt-1.5">
                Layanan Mitra Sekitar <?= esc($kotaTampil) ?>
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Rekomendasi terintegrasi melalui REST API 4 kelompok mitra (Tur Guide, Kuliner, Penginapan, dan Transportasi) untuk wilayah <?= esc($kotaPencarian) ?>.
            </p>
        </div>

        <!-- Ringkasan Status Integrasi -->
        <?php if ($jumlahApiLangsung > 0) : ?>
            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3.5 py-1.5 rounded-full bg-secondary-container text-on-secondary-fixed-variant font-label-md text-label-md shrink-0 tabular-nums">
                <span class="material-symbols-outlined text-base">cloud_done</span>
                <?= esc((string) $jumlahApiLangsung) ?>/<?= esc((string) $totalMitra) ?> Live API Mitra
            </span>
        <?php else : ?>
            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3.5 py-1.5 rounded-full bg-surface-container text-on-surface-variant border border-surface-variant/70 font-label-md text-label-md shrink-0">
                <span class="material-symbols-outlined text-base text-amber-600">cloud_sync</span>
                Mode Simulasi Fallback
            </span>
        <?php endif; ?>
    </div>

    <?php if ($daftarLayanan === []) : ?>
        <div class="mt-6 rounded-2xl bg-surface-container-low border border-surface-variant/50 p-6 text-center">
            <span class="material-symbols-outlined text-3xl text-outline">cloud_off</span>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                Rekomendasi layanan mitra untuk wilayah <?= esc($kotaTampil) ?> belum dapat dimuat saat ini.
            </p>
        </div>
    <?php else : ?>
        <!-- Navigasi 4 Tab Layanan Mitra -->
        <div role="tablist" aria-label="Pilih kategori layanan mitra" class="flex flex-wrap gap-2 mt-6">
            <?php foreach ($daftarLayanan as $kodeMitra => $layanan) : ?>
                <?php
                $apakahTabAktif  = ($kodeMitra === $kodeTabAwal);
                $daftarItemTab   = is_array($layanan['daftar_item'] ?? null) ? $layanan['daftar_item'] : [];
                $sumberTab       = (string) ($layanan['sumber_data'] ?? 'simulasi_fallback');
                $apakahLiveApi   = ($sumberTab === 'api_mitra');

                if ($apakahTabAktif) {
                    $kelasTombol = 'bg-primary text-on-primary shadow-sm';
                } else {
                    $kelasTombol = 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface';
                }

                if ($apakahLiveApi) {
                    $kelasTitikStatus = $apakahTabAktif ? 'bg-emerald-300' : 'bg-secondary';
                } else {
                    $kelasTitikStatus = 'bg-amber-500';
                }
                ?>
                <button
                    type="button"
                    role="tab"
                    id="tab-mitra-<?= esc((string) $kodeMitra) ?>"
                    aria-controls="panel-mitra-<?= esc((string) $kodeMitra) ?>"
                    aria-selected="<?= $apakahTabAktif ? 'true' : 'false' ?>"
                    data-tab-mitra="<?= esc((string) $kodeMitra) ?>"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary <?= esc($kelasTombol) ?>"
                >
                    <span class="material-symbols-outlined text-base"><?= esc((string) ($layanan['ikon'] ?? 'hub')) ?></span>
                    <span><?= esc((string) ($layanan['nama_layanan'] ?? $kodeMitra)) ?></span>
                    <span class="tabular-nums text-xs opacity-80">(<?= esc((string) count($daftarItemTab)) ?>)</span>
                    <span
                        class="w-2 h-2 rounded-full <?= esc($kelasTitikStatus) ?>"
                        data-titik-mitra="<?= esc((string) $kodeMitra) ?>"
                        data-live-api="<?= $apakahLiveApi ? 'true' : 'false' ?>"
                        aria-hidden="true"
                    ></span>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Konten Panel Setiap Kategori Mitra -->
        <?php foreach ($daftarLayanan as $kodeMitra => $layanan) : ?>
            <?php
            $apakahPanelAktif = ($kodeMitra === $kodeTabAwal);
            $daftarItemPanel  = is_array($layanan['daftar_item'] ?? null) ? $layanan['daftar_item'] : [];
            $sumberPanel      = (string) ($layanan['sumber_data'] ?? 'simulasi_fallback');
            $apakahLivePanel  = ($sumberPanel === 'api_mitra');
            $waktuResponPanel = (int) ($layanan['waktu_respon_ms'] ?? 0);
            $alasanFallback   = trim((string) ($layanan['alasan_fallback'] ?? ''));
            $kelasHiddenPanel = $apakahPanelAktif ? '' : 'hidden';
            ?>
            <div
                role="tabpanel"
                id="panel-mitra-<?= esc((string) $kodeMitra) ?>"
                aria-labelledby="tab-mitra-<?= esc((string) $kodeMitra) ?>"
                data-panel-mitra="<?= esc((string) $kodeMitra) ?>"
                class="mt-5 <?= esc($kelasHiddenPanel) ?>"
            >
                <!-- Baris Informasi Sumber Data & Query Kota -->
                <div class="pb-3 mb-5 border-b border-surface-variant/40">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p class="font-body-sm text-body-sm text-on-surface-variant">
                            <?= esc((string) ($layanan['deskripsi_singkat'] ?? '')) ?>
                            <span class="text-outline">• Query: <code>GET ?kota=<?= esc($kotaPencarian) ?></code></span>
                        </p>

                        <?php if ($apakahLivePanel) : ?>
                            <span class="inline-flex items-center gap-1 self-start sm:self-auto px-2.5 py-1 rounded-lg bg-secondary-container text-on-secondary-fixed-variant font-label-caps text-label-caps shrink-0 tabular-nums">
                                <span class="material-symbols-outlined text-xs">cloud_done</span>
                                Live API Mitra • <?= esc((string) $waktuResponPanel) ?> ms
                            </span>
                        <?php else : ?>
                            <span class="inline-flex items-center gap-1 self-start sm:self-auto px-2.5 py-1 rounded-lg bg-surface-container text-on-surface-variant font-label-caps text-label-caps shrink-0">
                                <span class="material-symbols-outlined text-xs text-amber-600">cloud_sync</span>
                                <?= esc((string) ($layanan['label_sumber'] ?? 'Simulasi Fallback')) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (! $apakahLivePanel && $alasanFallback !== '') : ?>
                        <p class="font-body-sm text-body-sm text-outline mt-1">
                            Catatan koneksi: <?= esc($alasanFallback) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($daftarItemPanel === []) : ?>
                    <div class="rounded-2xl bg-surface-container-low border border-surface-variant/50 p-6 text-center">
                        <span class="material-symbols-outlined text-3xl text-outline">cloud_off</span>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                            Belum ada data layanan <?= esc((string) ($layanan['nama_layanan'] ?? '')) ?> untuk wilayah <?= esc($kotaPencarian) ?>.
                        </p>
                    </div>
                <?php else : ?>
                    <!-- Layout Asimetris: Item ke-1 Sorotan Horizontal + Item Berikutnya Grid 2 Kolom -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <?php foreach ($daftarItemPanel as $indeks => $itemMitra) : ?>
                            <?php
                            $hargaItem      = (int) ($itemMitra['harga'] ?? 0);
                            $satuanItem     = (string) ($itemMitra['satuan_harga'] ?? '');
                            $ratingItem     = (float) ($itemMitra['rating'] ?? 0);
                            $ulasanItem     = (int) ($itemMitra['jumlah_ulasan'] ?? 0);
                            $urlEksternal   = trim((string) ($itemMitra['url_eksternal'] ?? ''));
                            $urlValidAman   = (preg_match('/^https?:\/\//i', $urlEksternal) === 1);
                            ?>

                            <?php if ($indeks === 0) : ?>
                                <!-- Kartu Sorotan Utama Mitra (Span 2 Kolom pada Tablet/Desktop) -->
                                <article class="sm:col-span-2 bg-surface-container-lowest rounded-2xl overflow-hidden border border-surface-variant/50 shadow-sm hover:shadow-md transition-shadow group grid grid-cols-1 sm:grid-cols-12">
                                    <div class="sm:col-span-5 relative aspect-[16/10] sm:aspect-auto sm:min-h-[220px] bg-surface-container overflow-hidden">
                                        <img
                                            src="<?= esc((string) ($itemMitra['foto'] ?? '')) ?>"
                                            alt="<?= esc((string) ($itemMitra['nama'] ?? 'Layanan Mitra Sumatera Utara')) ?>"
                                            loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                        />
                                        <span class="absolute top-3 left-3 font-label-caps text-label-caps px-3 py-1 rounded-full bg-surface-container-lowest/95 backdrop-blur-sm text-primary font-bold shadow-sm">
                                            <?= esc((string) ($itemMitra['sub_label'] ?? $layanan['nama_layanan'])) ?>
                                        </span>
                                    </div>

                                    <div class="sm:col-span-7 p-5 flex flex-col justify-between gap-4">
                                        <div>
                                            <div class="flex items-center justify-between gap-2 text-outline font-body-sm text-body-sm">
                                                <span class="inline-flex items-center gap-1 truncate">
                                                    <span class="material-symbols-outlined text-xs text-primary">pin_drop</span>
                                                    <?= esc((string) ($itemMitra['kota'] ?? $kotaPencarian)) ?>
                                                </span>
                                                <span class="font-metric-rating text-metric-rating text-on-surface inline-flex items-center gap-0.5 shrink-0 tabular-nums">
                                                    <span class="material-symbols-outlined text-xs text-amber-600" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    <?= esc(number_format($ratingItem, 1, '.', '')) ?>
                                                    <span class="text-outline font-normal text-xs">(<?= esc(number_format($ulasanItem, 0, ',', '.')) ?>)</span>
                                                </span>
                                            </div>

                                            <h3 class="font-headline-sm text-headline-sm text-on-surface mt-1.5">
                                                <?= esc((string) ($itemMitra['nama'] ?? '')) ?>
                                            </h3>

                                            <?php if (! empty($itemMitra['deskripsi'])) : ?>
                                                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                                                    <?= esc((string) $itemMitra['deskripsi']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="mt-3 space-y-1 text-outline font-body-sm text-body-sm">
                                                <?php if (! empty($itemMitra['alamat'])) : ?>
                                                    <p class="flex items-start gap-1.5">
                                                        <span class="material-symbols-outlined text-sm text-primary shrink-0 mt-0.5">location_on</span>
                                                        <span><?= esc((string) $itemMitra['alamat']) ?></span>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (! empty($itemMitra['kontak'])) : ?>
                                                    <p class="flex items-center gap-1.5 tabular-nums">
                                                        <span class="material-symbols-outlined text-sm text-secondary shrink-0">call</span>
                                                        <span><?= esc((string) $itemMitra['kontak']) ?></span>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="pt-3 border-t border-surface-variant/40 flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <span class="font-label-caps text-label-caps text-outline uppercase block">Estimasi Tarif</span>
                                                <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                                    <?= esc(formatHargaRupiah($hargaItem)) ?>
                                                    <?php if ($hargaItem > 0 && $satuanItem !== '') : ?>
                                                        <span class="font-body-sm text-body-sm text-outline font-normal"><?= esc($satuanItem) ?></span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>

                                            <?php if ($urlValidAman) : ?>
                                                <a
                                                    href="<?= esc($urlEksternal) ?>"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-primary text-primary hover:bg-primary hover:text-on-primary font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                                >
                                                    <span>Lihat Layanan</span>
                                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php else : ?>
                                <!-- Kartu Pendukung 2 Kolom -->
                                <article class="bg-surface-container-lowest rounded-2xl overflow-hidden border border-surface-variant/50 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between h-full">
                                    <div>
                                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-surface-container">
                                            <img
                                                src="<?= esc((string) ($itemMitra['foto'] ?? '')) ?>"
                                                alt="<?= esc((string) ($itemMitra['nama'] ?? 'Layanan Mitra Sumatera Utara')) ?>"
                                                loading="lazy"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                            />
                                            <span class="absolute top-3 left-3 font-label-caps text-label-caps px-3 py-1 rounded-full bg-surface-container-lowest/95 backdrop-blur-sm text-primary font-bold shadow-sm">
                                                <?= esc((string) ($itemMitra['sub_label'] ?? $layanan['nama_layanan'])) ?>
                                            </span>
                                        </div>

                                        <div class="p-5">
                                            <div class="flex items-center justify-between gap-2 text-outline font-body-sm text-body-sm">
                                                <span class="inline-flex items-center gap-1 truncate">
                                                    <span class="material-symbols-outlined text-xs text-primary">pin_drop</span>
                                                    <?= esc((string) ($itemMitra['kota'] ?? $kotaPencarian)) ?>
                                                </span>
                                                <span class="font-metric-rating text-metric-rating text-on-surface inline-flex items-center gap-0.5 shrink-0 tabular-nums">
                                                    <span class="material-symbols-outlined text-xs text-amber-600" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    <?= esc(number_format($ratingItem, 1, '.', '')) ?>
                                                    <span class="text-outline font-normal text-xs">(<?= esc(number_format($ulasanItem, 0, ',', '.')) ?>)</span>
                                                </span>
                                            </div>

                                            <h3 class="font-headline-sm text-headline-sm text-on-surface mt-2 line-clamp-2">
                                                <?= esc((string) ($itemMitra['nama'] ?? '')) ?>
                                            </h3>

                                            <?php if (! empty($itemMitra['deskripsi'])) : ?>
                                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 line-clamp-2">
                                                    <?= esc((string) $itemMitra['deskripsi']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="mt-3 space-y-1 text-outline font-body-sm text-body-sm">
                                                <?php if (! empty($itemMitra['alamat'])) : ?>
                                                    <p class="truncate">
                                                        <?= esc((string) $itemMitra['alamat']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (! empty($itemMitra['kontak'])) : ?>
                                                    <p class="inline-flex items-center gap-1 tabular-nums">
                                                        <span class="material-symbols-outlined text-xs text-secondary">call</span>
                                                        <span><?= esc((string) $itemMitra['kontak']) ?></span>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-5 pb-5 pt-3 border-t border-surface-variant/40 flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <span class="font-label-caps text-label-caps text-outline uppercase block truncate">Estimasi Tarif</span>
                                            <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                                <?= esc(formatHargaRupiah($hargaItem)) ?>
                                                <?php if ($hargaItem > 0 && $satuanItem !== '') : ?>
                                                    <span class="font-body-sm text-body-sm text-outline font-normal"><?= esc($satuanItem) ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </div>

                                        <?php if ($urlValidAman) : ?>
                                            <a
                                                href="<?= esc($urlEksternal) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label="Lihat layanan <?= esc((string) ($itemMitra['nama'] ?? 'mitra')) ?>"
                                                class="w-9 h-9 rounded-lg border border-primary text-primary hover:bg-primary hover:text-on-primary flex items-center justify-center transition-colors shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                            >
                                                <span class="material-symbols-outlined text-base">open_in_new</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<!-- Script Interaktif Perpindahan Tab Layanan Mitra -->
<script>
    (function () {
        const wadahMitra = document.getElementById('bagian-rekomendasi-mitra');
        if (!wadahMitra) {
            return;
        }

        const daftarTombolTab = wadahMitra.querySelectorAll('[data-tab-mitra]');
        const daftarPanelTab  = wadahMitra.querySelectorAll('[data-panel-mitra]');
        if (daftarTombolTab.length === 0) {
            return;
        }

        const kelasAktif    = ['bg-primary', 'text-on-primary', 'shadow-sm'];
        const kelasNonAktif = ['bg-surface-container', 'text-on-surface-variant', 'hover:bg-surface-container-high', 'hover:text-on-surface'];

        function aktifkanTabMitra(kodeDipilih) {
            // Perbarui tampilan seluruh tombol tab dan atribut aksesibilitas aria-selected
            daftarTombolTab.forEach(function (tombol) {
                const kodeTombol    = tombol.getAttribute('data-tab-mitra');
                const apakahDipilih = (kodeTombol === kodeDipilih);

                tombol.setAttribute('aria-selected', apakahDipilih ? 'true' : 'false');

                if (apakahDipilih) {
                    tombol.classList.remove(...kelasNonAktif);
                    tombol.classList.add(...kelasAktif);
                } else {
                    tombol.classList.remove(...kelasAktif);
                    tombol.classList.add(...kelasNonAktif);
                }

                // Sesuaikan warna titik status saat tab aktif agar tetap kontras
                const titikStatus = tombol.querySelector('[data-titik-mitra]');
                if (titikStatus && titikStatus.getAttribute('data-live-api') === 'true') {
                    titikStatus.classList.toggle('bg-emerald-300', apakahDipilih);
                    titikStatus.classList.toggle('bg-secondary', !apakahDipilih);
                }
            });

            // Tampilkan hanya panel yang sesuai dengan tab terpilih
            daftarPanelTab.forEach(function (panel) {
                const kodePanel = panel.getAttribute('data-panel-mitra');
                panel.classList.toggle('hidden', kodePanel !== kodeDipilih);
            });
        }

        daftarTombolTab.forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                const kodeDipilih = tombol.getAttribute('data-tab-mitra');
                if (kodeDipilih) {
                    aktifkanTabMitra(kodeDipilih);
                }
            });
        });
    })();
</script>
