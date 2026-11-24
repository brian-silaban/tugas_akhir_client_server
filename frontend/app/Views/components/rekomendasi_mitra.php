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
        $layananMitra     = new LayananApiMitra();
        $konteksDestinasi = is_array($destinasi ?? null) ? $destinasi : null;
        $hasilMitra       = $layananMitra->ambilSemuaRekomendasi($kotaAktif, 3, $konteksDestinasi);
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
                            <?php $labelEndpoint = trim((string) ($layanan['endpoint_label'] ?? '')); ?>
                            <span class="text-outline">• Query: <code><?= esc($labelEndpoint !== '' ? $labelEndpoint : ('GET ?kota=' . $kotaPencarian)) ?></code></span>
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

                <?php if ($kodeMitra === 'transportasi' && $apakahLivePanel && ! empty($layanan['data_transportasi']['daftar_lokasi'])) : ?>
                    <?php
                    $infoTransport        = $layanan['data_transportasi'];
                    $daftarLokasiTrans    = is_array($infoTransport['daftar_lokasi'] ?? null) ? $infoTransport['daftar_lokasi'] : [];
                    $idAsalDefault        = (int) ($infoTransport['id_asal_default'] ?? 0);
                    $idTujuanDefault      = (int) ($infoTransport['id_tujuan_default'] ?? 0);
                    $namaTujuanTerdekat   = (string) ($infoTransport['nama_tujuan_terdekat'] ?? '');
                    $jarakKeWisataKm      = (float) ($infoTransport['jarak_ke_wisata_km'] ?? 0);

                    $daftarLokasiSumut    = array_values(array_filter($daftarLokasiTrans, static fn (array $item): bool => ! empty($item['dalam_sumut'])));
                    $daftarLokasiNasional = array_values(array_filter($daftarLokasiTrans, static fn (array $item): bool => empty($item['dalam_sumut'])));
                    ?>
                    <div class="mb-6 rounded-2xl border border-surface-variant/70 bg-surface-container-low p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                            <div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg text-primary" aria-hidden="true">route</span>
                                    <span>Simulasi Cek Tarif &amp; Jadwal Rute</span>
                                </h3>
                                <?php if ($namaTujuanTerdekat !== '') : ?>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 tabular-nums">
                                        Simpul terdekat dari wisata ini: <strong class="text-on-surface"><?= esc($namaTujuanTerdekat) ?></strong> (sekitar <?= esc(number_format($jarakKeWisataKm, 1, ',', '.')) ?> km)
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <form id="form-cek-tarif-mitra" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            <div class="sm:col-span-4">
                                <label for="input-asal-transport" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                                    Titik Berangkat
                                </label>
                                <select
                                    id="input-asal-transport"
                                    name="id_asal"
                                    class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30"
                                >
                                    <?php if ($daftarLokasiSumut !== []) : ?>
                                        <optgroup label="Sumatera Utara">
                                            <?php foreach ($daftarLokasiSumut as $lokasiItem) : ?>
                                                <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idAsalDefault ? 'selected' : '' ?>>
                                                    <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <?php if ($daftarLokasiNasional !== []) : ?>
                                        <optgroup label="Bandara &amp; Kota Luar Sumut">
                                            <?php foreach ($daftarLokasiNasional as $lokasiItem) : ?>
                                                <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idAsalDefault ? 'selected' : '' ?>>
                                                    <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="input-tujuan-transport" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                                    Titik Tujuan
                                </label>
                                <select
                                    id="input-tujuan-transport"
                                    name="id_tujuan"
                                    class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30"
                                >
                                    <?php if ($daftarLokasiSumut !== []) : ?>
                                        <optgroup label="Sumatera Utara">
                                            <?php foreach ($daftarLokasiSumut as $lokasiItem) : ?>
                                                <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idTujuanDefault ? 'selected' : '' ?>>
                                                    <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                    <?php if ($daftarLokasiNasional !== []) : ?>
                                        <optgroup label="Bandara &amp; Kota Luar Sumut">
                                            <?php foreach ($daftarLokasiNasional as $lokasiItem) : ?>
                                                <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idTujuanDefault ? 'selected' : '' ?>>
                                                    <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="input-jam-transport" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                                    Jam
                                </label>
                                <input
                                    id="input-jam-transport"
                                    type="time"
                                    name="jam"
                                    value="08:30"
                                    required
                                    class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3 py-2.5 text-sm text-on-surface tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <button
                                    type="submit"
                                    id="tombol-cek-tarif-mitra"
                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-container active:scale-[0.98] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-60"
                                >
                                    <span class="material-symbols-outlined text-base" aria-hidden="true">search</span>
                                    <span>Cek Tarif</span>
                                </button>
                            </div>
                        </form>

                        <div id="hasil-cek-tarif-mitra" class="hidden mt-4 pt-4 border-t border-surface-variant/60" aria-live="polite"></div>
                    </div>
                <?php endif; ?>

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

    // Interaksi form Simulasi Cek Tarif & Jadwal Rute ke proxy API Transportasi
    (function () {
        const formulir = document.getElementById('form-cek-tarif-mitra');
        const wadahHasil = document.getElementById('hasil-cek-tarif-mitra');
        const tombolSubmit = document.getElementById('tombol-cek-tarif-mitra');
        if (!formulir || !wadahHasil || !tombolSubmit) {
            return;
        }

        const urlProxyTarif = <?= json_encode(base_url('mitra/transportasi/tarif')) ?>;

        function tampilkanPesanStatus(teks, apakahError) {
            wadahHasil.replaceChildren();
            wadahHasil.classList.remove('hidden');

            const kotak = document.createElement('p');
            kotak.className = apakahError
                ? 'rounded-xl border border-error/30 bg-error-container/60 px-4 py-3 text-sm text-on-error-container'
                : 'text-sm text-on-surface-variant';
            kotak.textContent = teks;
            wadahHasil.appendChild(kotak);
        }

        function renderHasilTarif(responJson) {
            wadahHasil.replaceChildren();
            wadahHasil.classList.remove('hidden');

            const data = responJson.data || {};
            const rute = data.rute || {};
            const asalNama = (rute.asal && rute.asal.nama_lokasi) ? rute.asal.nama_lokasi : '-';
            const tujuanNama = (rute.tujuan && rute.tujuan.nama_lokasi) ? rute.tujuan.nama_lokasi : '-';
            const jarakKm = typeof rute.jarak_km === 'number' ? rute.jarak_km : (rute.jarak_km || 0);
            const pilihan = Array.isArray(data.pilihan_transportasi) ? data.pilihan_transportasi : [];

            const ringkasan = document.createElement('div');
            ringkasan.className = 'flex flex-wrap items-center justify-between gap-2 mb-3 text-xs text-on-surface-variant';

            const infoRute = document.createElement('span');
            infoRute.className = 'font-medium text-on-surface';
            infoRute.textContent = asalNama + ' -> ' + tujuanNama + ' (' + jarakKm + ' km)';

            const infoLatensi = document.createElement('span');
            infoLatensi.className = 'tabular-nums';
            infoLatensi.textContent = pilihan.length + ' jadwal tersedia • ' + (responJson.waktu_respon_ms || 0) + ' ms';

            ringkasan.appendChild(infoRute);
            ringkasan.appendChild(infoLatensi);
            wadahHasil.appendChild(ringkasan);

            if (pilihan.length === 0) {
                const kosong = document.createElement('p');
                kosong.className = 'rounded-xl border border-surface-variant bg-surface-container-lowest px-4 py-3 text-sm text-on-surface-variant';
                kosong.textContent = 'Belum ada jadwal armada langsung pada rute dan jam keberangkatan tersebut. Coba rute antar-terminal/stasiun utama atau ubah jam berangkat.';
                wadahHasil.appendChild(kosong);
                return;
            }

            const daftarEl = document.createElement('ul');
            daftarEl.className = 'grid grid-cols-1 sm:grid-cols-2 gap-3';

            pilihan.forEach(function (opsi) {
                const itemEl = document.createElement('li');
                itemEl.className = 'rounded-xl border border-surface-variant/80 bg-surface-container-lowest px-4 py-3 flex items-center justify-between gap-3';

                const kolomKiri = document.createElement('div');
                kolomKiri.className = 'min-w-0';

                const namaArmada = document.createElement('p');
                namaArmada.className = 'text-sm font-semibold text-on-surface truncate';
                namaArmada.textContent = opsi.penyedia || 'Armada Mitra';

                const detailJadwal = document.createElement('p');
                detailJadwal.className = 'text-xs text-on-surface-variant mt-0.5 tabular-nums';
                detailJadwal.textContent = (opsi.jenis_transport || '-') + ' • Berlaku ' + (opsi.jam_berlaku || '-');

                kolomKiri.appendChild(namaArmada);
                kolomKiri.appendChild(detailJadwal);

                const hargaEl = document.createElement('span');
                hargaEl.className = 'text-sm font-bold text-primary shrink-0 tabular-nums';
                hargaEl.textContent = opsi.estimasi_tarif_format || '-';

                itemEl.appendChild(kolomKiri);
                itemEl.appendChild(hargaEl);
                daftarEl.appendChild(itemEl);
            });

            wadahHasil.appendChild(daftarEl);
        }

        formulir.addEventListener('submit', async function (event) {
            event.preventDefault();

            const idAsal = document.getElementById('input-asal-transport').value;
            const idTujuan = document.getElementById('input-tujuan-transport').value;
            const jam = document.getElementById('input-jam-transport').value || '08:30';

            if (idAsal === idTujuan) {
                tampilkanPesanStatus('Titik berangkat dan titik tujuan tidak boleh sama.', true);
                return;
            }

            tombolSubmit.disabled = true;
            tampilkanPesanStatus('Menghubungi REST API mitra transportasi...', false);

            try {
                const parameter = new URLSearchParams({
                    id_asal: idAsal,
                    id_tujuan: idTujuan,
                    jam: jam
                });
                const respon = await fetch(urlProxyTarif + '?' + parameter.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const isiJson = await respon.json();

                if (!respon.ok || isiJson.status !== 'berhasil') {
                    tampilkanPesanStatus(isiJson.pesan || 'Gagal menghitung tarif rute transportasi.', true);
                    return;
                }

                renderHasilTarif(isiJson);
            } catch (kesalahan) {
                tampilkanPesanStatus('Koneksi ke proxy API transportasi terputus. Coba ulangi beberapa saat lagi.', true);
            } finally {
                tombolSubmit.disabled = false;
            }
        });
    })();
</script>
