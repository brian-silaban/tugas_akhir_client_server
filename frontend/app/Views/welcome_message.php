<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$tagPencarianPopuler = [
    'Danau Toba',
    'Pulau Samosir',
    'Air Terjun Ponot',
    'Istana Maimun',
    'Bukit Lawang',
    'Sipiso-piso',
];

$slideBawaan = [
    'nama'              => 'Danau Toba',
    'slug'              => 'danau-toba',
    'kategori'          => 'Wisata Alam',
    'sub_kategori'      => 'Danau Kaldera',
    'kota'              => 'Kabupaten Samosir',
    'provinsi'          => 'Sumatera Utara',
    'deskripsi'         => 'Danau vulkanik terbesar di Asia Tenggara dengan panorama perbukitan hijau yang mengelilingi Pulau Samosir serta kekayaan tradisi Batak Toba.',
    'harga_tiket'       => 0,
    'label_harga_tiket' => 'Gratis',
    'rata_rating'       => 0,
    'jumlah_ulasan'     => 0,
    'foto_utama'        => 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Lake_Toba_and_the_surrounding_hills.jpg?utm_source=id.wikipedia.org&utm_campaign=api&utm_content=original',
];

$daftarSlide = is_array($daftarSlideHero ?? null) && $daftarSlideHero !== []
    ? array_values($daftarSlideHero)
    : [$destinasiHero ?? $slideBawaan];

$totalSlide                        = count($daftarSlide);
$daftarBento                       = $daftarSorotanBento ?? [];
$destinasiUnggulanUtama            = $daftarBento[0] ?? $daftarSlide[0];
$daftarDestinasiUnggulanPendamping = array_slice($daftarBento, 1, 2);
$daftarWilayahUnggulan             = array_slice($daftarKabupatenKota ?? [], 0, 6);
?>

<div class="flex flex-col w-full">

    <?php if (isset($terhubungBackend) && ! $terhubungBackend) : ?>
        <div class="max-w-7xl mx-auto px-6 pt-4 pb-2 w-full">
            <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-base" aria-hidden="true">cloud_off</span>
                <span><?= esc($pesanErrorApi ?: 'Koneksi ke server utama sedang terganggu. Silakan coba muat ulang beberapa saat lagi.') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. HERO FULL-WIDTH SLIDER DESTINASI POPULER SUMATERA UTARA -->
    <section
        id="hero-slider-destinasi"
        aria-roledescription="carousel"
        aria-label="Sorotan destinasi populer Sumatera Utara"
        class="relative w-full h-[620px] sm:h-[680px] lg:h-[92vh] lg:min-h-[640px] lg:max-h-[820px] overflow-hidden bg-inverse-surface select-none"
    >
        <?php foreach ($daftarSlide as $indeksSlide => $slideDestinasi) : ?>
            <?php
            $apakahAktif   = ($indeksSlide === 0);
            $kelasSlide    = $apakahAktif ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-[1.03] z-0 pointer-events-none';
            $namaSlide     = (string) ($slideDestinasi['nama'] ?? 'Destinasi Sumatera Utara');
            $slugSlide     = (string) ($slideDestinasi['slug'] ?? 'danau-toba');
            $kategoriSlide = (string) ($slideDestinasi['kategori'] ?? 'Wisata Sumut');
            $subKatSlide   = trim((string) ($slideDestinasi['sub_kategori'] ?? ''));
            $kotaSlide     = (string) ($slideDestinasi['kota'] ?? 'Sumatera Utara');
            $provinsiSlide = (string) ($slideDestinasi['provinsi'] ?? 'Sumatera Utara');
            $ratingSlide   = (float) ($slideDestinasi['rata_rating'] ?? 0);
            $ulasanSlide   = (int) ($slideDestinasi['jumlah_ulasan'] ?? 0);
            $hargaSlide    = (int) ($slideDestinasi['harga_tiket'] ?? 0);
            $teksTiket     = $hargaSlide > 0 ? formatHargaRupiah($hargaSlide) . ' / orang' : 'Gratis';
            $deskripsiAsli = trim((string) ($slideDestinasi['deskripsi'] ?? ''));
            if ($deskripsiAsli === '') {
                $deskripsiAsli = 'Jelajahi daya tarik alam, budaya, dan pengalaman wisata pilihan di ' . $kotaSlide . ', ' . $provinsiSlide . '.';
            }
            ?>
            <article
                data-slide-hero="<?= esc((string) $indeksSlide) ?>"
                aria-hidden="<?= $apakahAktif ? 'false' : 'true' ?>"
                class="absolute inset-0 transition-all duration-700 ease-out <?= esc($kelasSlide) ?>"
            >
                <img
                    src="<?= esc((string) ($slideDestinasi['foto_utama'] ?? '')) ?>"
                    alt="<?= esc($namaSlide) ?>"
                    loading="<?= $apakahAktif ? 'eager' : 'lazy' ?>"
                    class="absolute inset-0 w-full h-full object-cover object-center"
                />

                <!-- Scrim gelap dua arah agar tipografi selalu kontras dan nyaman dibaca -->
                <div class="absolute inset-0 bg-gradient-to-r from-[#171412]/90 via-[#171412]/55 to-[#171412]/15"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#171412]/90 via-[#171412]/25 to-transparent"></div>

                <!-- Konten Editorial Kiri Bawah (memberi ruang untuk navbar glass & kabut blur bawah) -->
                <div class="relative z-10 max-w-7xl mx-auto h-full px-6 pt-28 pb-36 sm:pb-40 flex flex-col justify-end">
                    <div class="max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3.5 py-1 rounded-md bg-tertiary-container text-on-tertiary font-label-caps text-label-caps uppercase tracking-wider shadow-sm">
                                <?= esc($kategoriSlide) ?>
                            </span>
                            <?php if ($subKatSlide !== '') : ?>
                                <span class="px-3 py-1 rounded-md bg-white/15 backdrop-blur-sm text-white/95 font-label-caps text-label-caps uppercase tracking-wider">
                                    <?= esc($subKatSlide) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($apakahAktif) : ?>
                            <h1 class="font-serif font-bold text-4xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.08] mt-3.5 drop-shadow-sm">
                                <?= esc($namaSlide) ?>
                            </h1>
                        <?php else : ?>
                            <h2 class="font-serif font-bold text-4xl sm:text-5xl lg:text-6xl text-white tracking-tight leading-[1.08] mt-3.5 drop-shadow-sm">
                                <?= esc($namaSlide) ?>
                            </h2>
                        <?php endif; ?>

                        <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-white/90">
                            <span class="inline-flex items-center gap-1 font-semibold tabular-nums">
                                <span class="material-symbols-outlined text-base text-amber-400" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                                <?= esc(number_format($ratingSlide, 1, '.', '')) ?>
                                <span class="text-white/75 font-normal">(<?= esc(number_format($ulasanSlide, 0, ',', '.')) ?> ulasan)</span>
                            </span>
                            <span class="text-white/50">•</span>
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-base text-primary-fixed" aria-hidden="true">pin_drop</span>
                                <span><?= esc($kotaSlide) ?>, <?= esc($provinsiSlide) ?></span>
                            </span>
                            <span class="text-white/50">•</span>
                            <span class="px-2.5 py-0.5 rounded border border-white/30 bg-white/10 backdrop-blur-sm text-xs font-semibold text-white tabular-nums">
                                <?= esc($teksTiket) ?>
                            </span>
                        </div>

                        <p class="mt-4 text-sm sm:text-base text-white/85 max-w-xl leading-relaxed line-clamp-3">
                            <?= esc($deskripsiAsli) ?>
                        </p>

                        <div class="mt-7 flex flex-wrap items-center gap-3">
                            <a
                                href="<?= base_url('destinasi/' . urlencode($slugSlide)) ?>"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            >
                                <span>Lihat Detail</span>
                                <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
                            </a>
                            <a
                                href="<?= base_url('destinasi') ?>"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/30 text-white font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            >
                                <span>Jelajahi Semua</span>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <!-- Efek Blur & Gradasi Halus di Bawah Gambar agar Batas Bawah Menyatu ke Permukaan Halaman -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 sm:h-32 z-[15] backdrop-blur-[6px] kabut-bawah-hero" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 sm:h-36 z-[16] bg-gradient-to-t from-[#fff8f5] via-[#fff8f5]/80 via-35% to-transparent" aria-hidden="true"></div>

        <!-- Kontrol Indikator Titik, Penghitung Slide & Tombol Geser Kiri/Kanan -->
        <?php if ($totalSlide > 1) : ?>
            <div class="absolute inset-x-0 bottom-20 sm:bottom-24 z-20 pointer-events-none">
                <div class="max-w-7xl mx-auto px-6 flex items-center justify-between gap-4">
                    <div class="pointer-events-auto flex items-center gap-2" role="tablist" aria-label="Pilih slide destinasi populer">
                        <?php foreach ($daftarSlide as $indeksTitik => $slideTitik) : ?>
                            <?php
                            $titikAktif = ($indeksTitik === 0);
                            $kelasTitik = $titikAktif
                                ? 'w-9 h-2 rounded-full bg-tertiary-container transition-all duration-300'
                                : 'w-2.5 h-2.5 rounded-full bg-white/40 hover:bg-white/75 transition-all duration-300';
                            ?>
                            <button
                                type="button"
                                role="tab"
                                data-indikator-hero="<?= esc((string) $indeksTitik) ?>"
                                aria-selected="<?= $titikAktif ? 'true' : 'false' ?>"
                                aria-label="Tampilkan <?= esc((string) ($slideTitik['nama'] ?? ('Slide ' . ($indeksTitik + 1)))) ?>"
                                class="<?= esc($kelasTitik) ?> focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            ></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="pointer-events-auto flex items-center gap-2">
                        <span
                            id="penghitung-slide-hero"
                            class="hidden sm:inline-block text-xs font-semibold text-white/85 tabular-nums mr-1"
                        >
                            01 / <?= esc(str_pad((string) $totalSlide, 2, '0', STR_PAD_LEFT)) ?>
                        </span>
                        <button
                            type="button"
                            data-geser-hero="sebelumnya"
                            aria-label="Destinasi sebelumnya"
                            class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-md border border-white/25 text-white inline-flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">chevron_left</span>
                        </button>
                        <button
                            type="button"
                            data-geser-hero="berikutnya"
                            aria-label="Destinasi berikutnya"
                            class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-md border border-white/25 text-white inline-flex items-center justify-center transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            <span class="material-symbols-outlined text-xl" aria-hidden="true">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Strip Pencarian Cepat Destinasi Populer (menyatu mulus dari kabut bawah Hero) -->
    <div class="w-full bg-surface border-b border-surface-variant/40" data-animasi="naik">
        <div class="max-w-7xl mx-auto px-6 pt-2 pb-5 flex flex-wrap items-center gap-2.5 text-on-surface-variant">
            <span class="font-label-caps text-label-caps uppercase text-outline mr-1">Populer:</span>
            <?php foreach ($tagPencarianPopuler as $namaTag) : ?>
                <a
                    href="<?= base_url('destinasi?q=' . urlencode($namaTag)) ?>"
                    class="font-label-md text-label-md px-3.5 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high hover:text-primary transition-colors text-on-surface"
                >
                    <?= esc($namaTag) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 2. SEKSI BENTO GRID ASIMETRIS (DESTINASI UNGGULAN SUMATERA UTARA) -->
    <?php if (! empty($destinasiUnggulanUtama['nama'])) : ?>
    <section id="destinasi-kurator" class="w-full bg-surface-container-low py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div data-animasi="naik">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-tertiary-container"></span>
                    <span class="font-label-caps text-label-caps uppercase text-tertiary-container tracking-wider">
                        DESTINASI PILIHAN SUMATERA UTARA
                    </span>
                </div>
                <h2 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-2 max-w-xl">
                    Lanskap Alam, Bahari &amp; Warisan Budaya Tanah Batak–Melayu
                </h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-10">
                <!-- Kartu Bento Utama Kiri (7 Kolom) -->
                <article class="lg:col-span-7 bg-surface-container-lowest rounded-3xl overflow-hidden shadow-sm border border-surface-variant/40 flex flex-col justify-between group" data-animasi="naik">
                    <a href="<?= base_url('destinasi/' . ($destinasiUnggulanUtama['slug'] ?? '')) ?>" class="block relative h-80 sm:h-96 w-full overflow-hidden">
                        <img
                            src="<?= esc($destinasiUnggulanUtama['foto_utama'] ?? '') ?>"
                            alt="<?= esc($destinasiUnggulanUtama['nama'] ?? '') ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                        />
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            <span class="font-label-caps text-label-caps px-3 py-1.5 rounded-full bg-secondary text-on-secondary shadow-sm">
                                <?= esc($destinasiUnggulanUtama['kategori'] ?? 'Wisata Sumut') ?>
                            </span>
                            <?php if (! empty($destinasiUnggulanUtama['sub_kategori'])) : ?>
                                <span class="font-label-caps text-label-caps px-3 py-1.5 rounded-full bg-surface-container-lowest/90 text-on-surface shadow-sm backdrop-blur-sm">
                                    <?= esc($destinasiUnggulanUtama['sub_kategori']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="absolute bottom-4 right-4 bg-surface-container-lowest/95 backdrop-blur-md px-3 py-1.5 rounded-xl text-on-surface shadow-sm flex items-center gap-1 font-metric-rating text-metric-rating tabular-nums">
                            <span class="material-symbols-outlined text-sm text-amber-600" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                            <span><?= esc(number_format((float) ($destinasiUnggulanUtama['rata_rating'] ?? 0), 2, '.', '')) ?></span>
                            <span class="text-outline font-normal text-xs">
                                (<?= esc(number_format((int) ($destinasiUnggulanUtama['jumlah_ulasan'] ?? 0), 0, ',', '.')) ?> ulasan)
                            </span>
                        </div>
                    </a>

                    <div class="p-6 sm:p-8 flex flex-col justify-between flex-grow">
                        <div>
                            <div class="flex items-center gap-2 text-outline font-body-sm text-body-sm">
                                <span class="material-symbols-outlined text-sm text-primary" aria-hidden="true">pin_drop</span>
                                <span><?= esc(($destinasiUnggulanUtama['alamat'] ?? '') !== '' ? $destinasiUnggulanUtama['alamat'] : ($destinasiUnggulanUtama['kota'] . ', Sumatera Utara')) ?></span>
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface mt-2">
                                <a href="<?= base_url('destinasi/' . ($destinasiUnggulanUtama['slug'] ?? '')) ?>" class="hover:text-primary transition-colors">
                                    <?= esc($destinasiUnggulanUtama['nama'] ?? '') ?>
                                </a>
                            </h3>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-3 leading-relaxed line-clamp-3">
                                <?= esc($destinasiUnggulanUtama['deskripsi'] ?? '') ?>
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6 bg-surface-container/50 rounded-2xl p-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-primary text-xl" aria-hidden="true">schedule</span>
                                    <div class="flex flex-col">
                                        <span class="font-label-caps text-label-caps text-outline uppercase">Jam Operasional</span>
                                        <span class="font-label-md text-label-md text-on-surface tabular-nums">
                                            <?= esc(ambilTeksJamOperasional($destinasiUnggulanUtama['jam_operasional'] ?? null)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <span class="material-symbols-outlined text-secondary text-xl" aria-hidden="true">domain</span>
                                    <div class="flex flex-col">
                                        <span class="font-label-caps text-label-caps text-outline uppercase">Pengelola / Fasilitas</span>
                                        <span class="font-label-md text-label-md text-on-surface">
                                            <?php
                                            $daftarFasilitasUtama = $destinasiUnggulanUtama['daftar_fasilitas'] ?? [];
                                            if ($daftarFasilitasUtama !== []) {
                                                $ringkasanUtama = implode(', ', array_slice($daftarFasilitasUtama, 0, 3));
                                            } elseif (! empty($destinasiUnggulanUtama['pengelola'])) {
                                                $ringkasanUtama = (string) $destinasiUnggulanUtama['pengelola'];
                                            } else {
                                                $ringkasanUtama = 'Pengelola Lokal / Pemda';
                                            }
                                            ?>
                                            <?= esc($ringkasanUtama) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-8 pt-4 gap-4">
                            <div>
                                <span class="font-label-caps text-label-caps text-outline uppercase block">
                                    <?= esc($destinasiUnggulanUtama['label_harga_tiket'] ?? 'Tarif Masuk') ?>
                                </span>
                                <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                    <?= esc(formatHargaRupiah((int) ($destinasiUnggulanUtama['harga_tiket'] ?? 0))) ?>
                                </span>
                                <?php if (! empty($destinasiUnggulanUtama['keterangan_tiket'])) : ?>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant block mt-0.5">
                                        <?= esc($destinasiUnggulanUtama['keterangan_tiket']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <a
                                href="<?= base_url('destinasi/' . ($destinasiUnggulanUtama['slug'] ?? '')) ?>"
                                class="px-5 py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container transition-colors font-label-md text-label-md flex items-center gap-2 shrink-0"
                            >
                                <span>Lihat Detail</span>
                                <span class="material-symbols-outlined text-sm" aria-hidden="true">north_east</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Kolom Kanan Bento (5 Kolom: 2 Kartu Pendamping) -->
                <div class="lg:col-span-5 flex flex-col gap-6" data-animasi-berjenjang>
                    <?php foreach ($daftarDestinasiUnggulanPendamping as $destinasiPendamping) : ?>
                        <article class="bg-surface-container-lowest rounded-3xl overflow-hidden shadow-sm border border-surface-variant/40 flex flex-col sm:flex-row lg:flex-col group">
                            <a href="<?= base_url('destinasi/' . ($destinasiPendamping['slug'] ?? '')) ?>" class="block relative h-52 sm:w-1/2 lg:w-full overflow-hidden shrink-0">
                                <img
                                    src="<?= esc($destinasiPendamping['foto_utama'] ?? '') ?>"
                                    alt="<?= esc($destinasiPendamping['nama'] ?? '') ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                />
                                <div class="absolute top-3 left-3">
                                    <span class="font-label-caps text-label-caps px-3 py-1 rounded-full bg-surface-container-lowest/95 backdrop-blur-sm text-primary font-bold">
                                        <?= esc($destinasiPendamping['kategori'] ?? 'Wisata Sumut') ?>
                                    </span>
                                </div>
                            </a>

                            <div class="p-6 flex flex-col justify-between flex-grow">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-body-sm text-body-sm text-outline flex items-center gap-1 truncate">
                                            <span class="material-symbols-outlined text-xs text-primary" aria-hidden="true">pin_drop</span>
                                            <?= esc($destinasiPendamping['kota'] ?? '') ?>
                                        </span>
                                        <span class="font-metric-rating text-metric-rating text-on-surface flex items-center gap-0.5 tabular-nums shrink-0">
                                            <span class="material-symbols-outlined text-xs text-amber-600" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                                            <?= esc(number_format((float) ($destinasiPendamping['rata_rating'] ?? 0), 2, '.', '')) ?>
                                            (<?= esc(number_format((int) ($destinasiPendamping['jumlah_ulasan'] ?? 0), 0, ',', '.')) ?>)
                                        </span>
                                    </div>

                                    <h3 class="font-headline-sm text-headline-sm text-on-surface mt-2">
                                        <a href="<?= base_url('destinasi/' . ($destinasiPendamping['slug'] ?? '')) ?>" class="hover:text-primary transition-colors">
                                            <?= esc($destinasiPendamping['nama'] ?? '') ?>
                                        </a>
                                    </h3>

                                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5 line-clamp-2">
                                        <?= esc($destinasiPendamping['deskripsi'] ?? '') ?>
                                    </p>

                                    <div class="flex items-center gap-2 mt-3 text-on-surface-variant font-body-sm text-body-sm tabular-nums">
                                        <span class="material-symbols-outlined text-xs text-primary" aria-hidden="true">schedule</span>
                                        <span class="truncate"><?= esc(ambilTeksJamOperasional($destinasiPendamping['jam_operasional'] ?? null)) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-4 pt-3">
                                    <span class="font-metric-price text-metric-price text-on-surface tabular-nums">
                                        <?= esc($destinasiPendamping['label_harga_tiket'] ?? formatHargaRupiah((int) ($destinasiPendamping['harga_tiket'] ?? 0))) ?>
                                    </span>
                                    <a href="<?= base_url('destinasi/' . ($destinasiPendamping['slug'] ?? '')) ?>" class="text-primary hover:underline font-label-md text-label-md flex items-center gap-1">
                                        <span>Detail</span>
                                        <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 3. SEKSI JELAJAH BERDASARKAN KABUPATEN & KOTA SUMATERA UTARA (Tanpa angka jumlah destinasi) -->
    <?php if ($daftarWilayahUnggulan !== []) : ?>
    <section id="jelajah-kepulauan" class="w-full bg-surface py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col items-center text-center" data-animasi="naik">
                <span class="font-label-caps text-label-caps uppercase text-secondary tracking-wider">
                    GEOGRAFI SUMATERA UTARA
                </span>
                <h2 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-2">
                    Jelajah Berdasarkan Kabupaten &amp; Kota
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-lg mt-2">
                    Dari pesisir barat Nias hingga kaldera Toba dan dataran tinggi Karo, temukan destinasi menarik di setiap wilayah.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-10" data-animasi-berjenjang>
                <?php foreach ($daftarWilayahUnggulan as $wilayah) : ?>
                    <a
                        href="<?= base_url('destinasi?kabupaten=' . urlencode($wilayah['kode_kabupaten'])) ?>"
                        class="flex flex-col items-center justify-center p-5 rounded-2xl bg-surface-container-lowest hover:bg-surface-container transition-all text-center group shadow-sm border border-surface-variant/30"
                    >
                        <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined" aria-hidden="true">location_city</span>
                        </div>
                        <span class="font-label-md text-label-md text-on-surface">
                            <?= esc($wilayah['nama_kabupaten_kota']) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php if ($totalSlide > 1) : ?>
<script>
    // Mengelola perpindahan slide destinasi populer di Hero (otomatis 6 detik, tombol, titik, & geser sentuh)
    (function () {
        const wadahSlider    = document.getElementById('hero-slider-destinasi');
        if (!wadahSlider) {
            return;
        }

        const daftarSlide    = Array.from(wadahSlider.querySelectorAll('[data-slide-hero]'));
        const daftarTitik    = Array.from(wadahSlider.querySelectorAll('[data-indikator-hero]'));
        const tombolSebelum  = wadahSlider.querySelector('[data-geser-hero="sebelumnya"]');
        const tombolBerikut  = wadahSlider.querySelector('[data-geser-hero="berikutnya"]');
        const labelPenghitung = document.getElementById('penghitung-slide-hero');

        if (daftarSlide.length < 2) {
            return;
        }

        const kelasSlideAktif    = ['opacity-100', 'scale-100', 'z-10'];
        const kelasSlideNonAktif = ['opacity-0', 'scale-[1.03]', 'z-0', 'pointer-events-none'];
        const kelasTitikAktif    = ['w-9', 'h-2', 'bg-tertiary-container'];
        const kelasTitikNonAktif = ['w-2.5', 'h-2.5', 'bg-white/40', 'hover:bg-white/75'];

        const hematGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let indeksAktif  = 0;
        let pewaktuSlide = null;
        let sentuhAwalX  = null;
        let sentuhAwalY  = null;

        function tampilkanSlide(indeksTujuan) {
            const total      = daftarSlide.length;
            indeksAktif      = ((indeksTujuan % total) + total) % total;

            daftarSlide.forEach(function (elemenSlide, indeks) {
                const aktif = (indeks === indeksAktif);
                elemenSlide.setAttribute('aria-hidden', aktif ? 'false' : 'true');
                if (aktif) {
                    elemenSlide.classList.remove(...kelasSlideNonAktif);
                    elemenSlide.classList.add(...kelasSlideAktif);
                } else {
                    elemenSlide.classList.remove(...kelasSlideAktif);
                    elemenSlide.classList.add(...kelasSlideNonAktif);
                }
            });

            daftarTitik.forEach(function (tombolTitik, indeks) {
                const aktif = (indeks === indeksAktif);
                tombolTitik.setAttribute('aria-selected', aktif ? 'true' : 'false');
                if (aktif) {
                    tombolTitik.classList.remove(...kelasTitikNonAktif);
                    tombolTitik.classList.add(...kelasTitikAktif);
                } else {
                    tombolTitik.classList.remove(...kelasTitikAktif);
                    tombolTitik.classList.add(...kelasTitikNonAktif);
                }
            });

            if (labelPenghitung) {
                const angkaSekarang = String(indeksAktif + 1).padStart(2, '0');
                const angkaTotal    = String(total).padStart(2, '0');
                labelPenghitung.textContent = angkaSekarang + ' / ' + angkaTotal;
            }
        }

        function hentikanAutoSlide() {
            if (pewaktuSlide !== null) {
                clearInterval(pewaktuSlide);
                pewaktuSlide = null;
            }
        }

        function mulaiAutoSlide() {
            if (hematGerak) {
                return;
            }
            hentikanAutoSlide();
            pewaktuSlide = setInterval(function () {
                tampilkanSlide(indeksAktif + 1);
            }, 6000);
        }

        if (tombolSebelum) {
            tombolSebelum.addEventListener('click', function () {
                tampilkanSlide(indeksAktif - 1);
                mulaiAutoSlide();
            });
        }

        if (tombolBerikut) {
            tombolBerikut.addEventListener('click', function () {
                tampilkanSlide(indeksAktif + 1);
                mulaiAutoSlide();
            });
        }

        daftarTitik.forEach(function (tombolTitik) {
            tombolTitik.addEventListener('click', function () {
                const indeks = Number(tombolTitik.getAttribute('data-indikator-hero') || 0);
                tampilkanSlide(indeks);
                mulaiAutoSlide();
            });
        });

        wadahSlider.addEventListener('mouseenter', hentikanAutoSlide);
        wadahSlider.addEventListener('mouseleave', mulaiAutoSlide);

        wadahSlider.addEventListener('touchstart', function (acara) {
            if (acara.touches.length !== 1) {
                return;
            }
            sentuhAwalX = acara.touches[0].clientX;
            sentuhAwalY = acara.touches[0].clientY;
        }, { passive: true });

        wadahSlider.addEventListener('touchend', function (acara) {
            if (sentuhAwalX === null || sentuhAwalY === null || acara.changedTouches.length === 0) {
                return;
            }
            const selisihX = acara.changedTouches[0].clientX - sentuhAwalX;
            const selisihY = acara.changedTouches[0].clientY - sentuhAwalY;
            sentuhAwalX    = null;
            sentuhAwalY    = null;

            if (Math.abs(selisihX) >= 45 && Math.abs(selisihX) > Math.abs(selisihY)) {
                if (selisihX < 0) {
                    tampilkanSlide(indeksAktif + 1);
                } else {
                    tampilkanSlide(indeksAktif - 1);
                }
                mulaiAutoSlide();
            }
        }, { passive: true });

        wadahSlider.addEventListener('keydown', function (acara) {
            if (acara.key === 'ArrowLeft') {
                tampilkanSlide(indeksAktif - 1);
                mulaiAutoSlide();
            } else if (acara.key === 'ArrowRight') {
                tampilkanSlide(indeksAktif + 1);
                mulaiAutoSlide();
            }
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                hentikanAutoSlide();
            } else {
                mulaiAutoSlide();
            }
        });

        mulaiAutoSlide();
    })();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
