<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$tagPencarianPopuler = [
    'Danau Toba',
    'Pulau Samosir',
    'Sipiso-piso',
    'Bukit Lawang',
];

$destinasiSorotanUtama = $destinasiHero ?? [
    'nama'              => 'Danau Toba',
    'slug'              => 'danau-toba',
    'kategori'          => 'Wisata Alam',
    'sub_kategori'      => 'Danau',
    'kota'              => 'Kabupaten Samosir',
    'provinsi'          => 'Sumatera Utara',
    'harga_tiket'       => 0,
    'label_harga_tiket' => 'Gratis (Rp 0)',
    'jam_operasional'   => 'Informasi jam operasional belum tersedia',
    'daftar_fasilitas'  => [],
    'rata_rating'       => 0,
    'jumlah_ulasan'     => 0,
    'foto_utama'        => 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Lake_Toba_and_the_surrounding_hills.jpg?utm_source=id.wikipedia.org&utm_campaign=api&utm_content=original',
];

$daftarBento                       = $daftarSorotanBento ?? [];
$destinasiUnggulanUtama            = $daftarBento[0] ?? $destinasiSorotanUtama;
$daftarDestinasiUnggulanPendamping = array_slice($daftarBento, 1, 2);
$daftarWilayahUnggulan             = array_slice($daftarKabupatenKota ?? [], 0, 6);
?>

<div class="flex flex-col w-full">

    <?php if (isset($terhubungBackend) && ! $terhubungBackend) : ?>
        <div class="max-w-7xl mx-auto px-6 pt-6 w-full">
            <div class="rounded-xl bg-error-container text-on-error-container px-4 py-3 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-base" aria-hidden="true">cloud_off</span>
                <span><?= esc($pesanErrorApi ?: 'Koneksi ke server utama sedang terganggu. Silakan coba muat ulang beberapa saat lagi.') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. SEKSI HERO ASIMETRIS EDITORIAL SUMATERA UTARA -->
    <div class="relative w-full overflow-hidden">
        <div class="absolute -top-40 right-10 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-96 -left-32 w-80 h-80 bg-tertiary-container/5 rounded-full blur-3xl pointer-events-none"></div>

        <section class="max-w-7xl mx-auto px-6 pt-10 pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

                <!-- Kolom Kiri (7 Kolom): Tajuk, Narasi Menggugah, CTA & Tag Populer -->
                <div class="lg:col-span-7 flex flex-col justify-center">
                    <h1 class="font-display-hero text-display-hero-mobile sm:text-display-hero text-on-surface tracking-tight max-w-2xl">
                        Jelajahi Pesona Alam &amp; Budaya Luhur Sumatera Utara
                    </h1>

                    <p class="font-body-lg text-body-lg text-on-surface-variant mt-5 max-w-xl leading-relaxed">
                        Dari kaldera luas Danau Toba, rimba hujan tropis Bukit Lawang, hingga deburan ombak Kepulauan Nias — temukan pesona Sumatera Utara dan rancang perjalananmu dari sini.
                    </p>

                    <!-- Tombol Aksi Utama & Sekunder (Sesuai Sloplint: 1 Primary + 1 Secondary) -->
                    <div class="flex flex-wrap items-center gap-3 mt-8">
                        <a
                            href="<?= base_url('destinasi') ?>"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tertiary"
                        >
                            <span>Mulai Menjelajah</span>
                            <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
                        </a>
                        <a
                            href="#destinasi-kurator"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        >
                            <span>Destinasi Pilihan</span>
                        </a>
                    </div>

                    <!-- Tag Pencarian Populer -->
                    <div class="flex flex-wrap items-center gap-2 mt-7 text-on-surface-variant">
                        <span class="font-label-caps text-label-caps uppercase text-outline">Populer:</span>
                        <?php foreach ($tagPencarianPopuler as $namaTag) : ?>
                            <a
                                href="<?= base_url('destinasi?q=' . urlencode($namaTag)) ?>"
                                class="font-label-md text-label-md px-3 py-1 rounded-full bg-surface-container hover:bg-surface-container-high transition-colors text-on-surface"
                            >
                                <?= esc($namaTag) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Kolom Kanan (5 Kolom): Kartu Sorotan Destinasi Utama (Tanpa Kode Wilayah) -->
                <div class="lg:col-span-5 relative">
                    <div class="relative h-[450px] lg:h-[470px] w-full rounded-3xl overflow-hidden shadow-xl bg-surface-container group">
                        <img
                            src="<?= esc($destinasiSorotanUtama['foto_utama'] ?? '') ?>"
                            alt="<?= esc($destinasiSorotanUtama['nama'] ?? 'Destinasi Sumatera Utara') ?>"
                            class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                        />

                        <div class="absolute top-5 left-5 flex items-center z-10">
                            <span class="font-label-caps text-label-caps px-3.5 py-1.5 rounded-full bg-surface-container-lowest/90 text-primary font-bold shadow-sm backdrop-blur-sm">
                                <?= esc(! empty($destinasiSorotanUtama['sub_kategori']) ? $destinasiSorotanUtama['sub_kategori'] : ($destinasiSorotanUtama['kategori'] ?? 'Pilihan Utama')) ?>
                            </span>
                        </div>

                        <div class="absolute inset-x-0 bottom-0 pt-28 pb-6 px-6 bg-gradient-to-t from-inverse-surface via-inverse-surface/85 to-transparent text-inverse-on-surface flex flex-col justify-end">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-label-caps text-label-caps text-secondary-fixed uppercase tracking-wider">
                                    <?= esc($destinasiSorotanUtama['kategori'] ?? 'Wisata Alam') ?>
                                </span>
                                <span class="text-surface-variant text-xs">•</span>
                                <span class="font-metric-rating text-metric-rating text-tertiary-fixed-dim flex items-center gap-0.5 tabular-nums">
                                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                                    <?= esc(number_format((float) ($destinasiSorotanUtama['rata_rating'] ?? 0), 1, '.', '')) ?>
                                    (<?= esc(number_format((int) ($destinasiSorotanUtama['jumlah_ulasan'] ?? 0), 0, ',', '.')) ?> ulasan)
                                </span>
                            </div>

                            <h2 class="font-headline-lg text-headline-lg text-inverse-on-surface leading-tight">
                                <?= esc($destinasiSorotanUtama['nama'] ?? '') ?>
                            </h2>

                            <p class="font-body-sm text-body-sm text-surface-variant mt-1.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-xs" aria-hidden="true">pin_drop</span>
                                <span><?= esc($destinasiSorotanUtama['kota'] ?? '') ?>, <?= esc($destinasiSorotanUtama['provinsi'] ?? 'Sumatera Utara') ?></span>
                            </p>

                            <div class="flex items-center justify-between mt-5 pt-4 bg-inverse-surface/50 backdrop-blur-md rounded-xl px-4 py-2.5">
                                <div class="flex flex-col">
                                    <span class="font-label-caps text-label-caps text-surface-variant uppercase">
                                        <?= esc($destinasiSorotanUtama['label_harga_tiket'] ?? 'Tarif Masuk') ?>
                                    </span>
                                    <span class="font-metric-price text-metric-price text-inverse-on-surface tabular-nums">
                                        <?= esc(formatHargaRupiah((int) ($destinasiSorotanUtama['harga_tiket'] ?? 0))) ?>
                                    </span>
                                </div>
                                <a
                                    href="<?= base_url('destinasi/' . ($destinasiSorotanUtama['slug'] ?? '')) ?>"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-primary-container text-on-primary hover:bg-primary font-label-md text-label-md transition-colors shadow-sm"
                                >
                                    <span>Lihat Detail</span>
                                    <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <!-- 2. SEKSI BENTO GRID ASIMETRIS (DESTINASI UNGGULAN SUMATERA UTARA) -->
    <?php if (! empty($destinasiUnggulanUtama['nama'])) : ?>
    <section id="destinasi-kurator" class="w-full bg-surface-container-low py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
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
                <p class="font-body-md text-body-md text-on-surface-variant max-w-md">
                    Pilihan destinasi unggulan Sumatera Utara lengkap dengan informasi jam berkunjung, pengelola, dan fasilitas di lokasi.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-10">
                <!-- Kartu Bento Utama Kiri (7 Kolom) -->
                <article class="lg:col-span-7 bg-surface-container-lowest rounded-3xl overflow-hidden shadow-sm border border-surface-variant/40 flex flex-col justify-between group">
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
                <div class="lg:col-span-5 flex flex-col gap-6">
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
            <div class="flex flex-col items-center text-center">
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

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-10">
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
<?= $this->endSection() ?>
