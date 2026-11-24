<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$kataKunciAktif     = $filterAktif['kata_kunci'] ?? '';
$kategoriAktif      = $filterAktif['kategori'] ?? '';
$kabupatenAktif     = $filterAktif['kode_kabupaten'] ?? '';
$hargaMaksimalAktif = $filterAktif['harga_maksimal'] ?? '';
$urutanAktif        = $filterAktif['urutan'] ?? 'terbaik';

$halamanSekarang = (int) ($paginasi['halaman_sekarang'] ?? 1);
$totalData       = (int) ($paginasi['total_data'] ?? count($daftarDestinasi ?? []));
$totalHalaman    = max(1, (int) ($paginasi['total_halaman'] ?? 1));

// Helper kecil lokal untuk membangun URL paginasi tanpa kehilangan filter aktif
$bangunUrlHalaman = static function (int $nomorHalaman) use ($filterAktif): string {
    $query = array_filter([
        'q'              => $filterAktif['kata_kunci'] ?? '',
        'kategori'       => $filterAktif['kategori'] ?? '',
        'kabupaten'      => $filterAktif['kode_kabupaten'] ?? '',
        'harga_maksimal' => $filterAktif['harga_maksimal'] ?? '',
        'urutan'         => ($filterAktif['urutan'] ?? 'terbaik') !== 'terbaik' ? $filterAktif['urutan'] : '',
        'halaman'        => $nomorHalaman > 1 ? $nomorHalaman : '',
    ], static fn ($nilai): bool => $nilai !== '');

    $suffix = $query !== [] ? '?' . http_build_query($query) : '';

    return base_url('destinasi' . $suffix);
};
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <!-- Header Katalog -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-surface-variant/50">
        <div>
            <span class="font-label-caps text-label-caps uppercase text-primary tracking-wider">
                DIREKTORI WISATA SUMATERA UTARA
            </span>
            <h1 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-1">
                Katalog Eksplorasi Destinasi
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 tabular-nums">
                Menampilkan <strong class="text-on-surface"><?= esc((string) count($daftarDestinasi ?? [])) ?></strong> dari <strong class="text-on-surface"><?= esc(number_format($totalData, 0, ',', '.')) ?></strong> objek wisata terdata.
            </p>
        </div>

        <a href="<?= base_url('/') ?>" class="text-primary hover:underline font-label-md text-label-md inline-flex items-center gap-1 self-start md:self-auto">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <?php if (isset($terhubungBackend) && ! $terhubungBackend) : ?>
        <div class="mt-6 rounded-xl bg-error-container text-on-error-container px-4 py-3 text-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-base">cloud_off</span>
            <span><?= esc($pesanErrorApi ?: 'Gagal terhubung ke REST API Backend.') ?></span>
        </div>
    <?php endif; ?>

    <!-- Form Filter Multi-Kriteria -->
    <form action="<?= base_url('destinasi') ?>" method="get" class="mt-6 bg-surface-container-lowest rounded-2xl p-4 sm:p-5 border border-surface-variant/50 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
        <div class="lg:col-span-3">
            <label for="filter-q" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Kata Kunci</label>
            <input
                id="filter-q"
                type="text"
                name="q"
                value="<?= esc($kataKunciAktif) ?>"
                placeholder="Nama wisata, air terjun, danau..."
                class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary"
            />
        </div>

        <div class="lg:col-span-3">
            <label for="filter-kabupaten" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Kabupaten / Kota (33)</label>
            <select id="filter-kabupaten" name="kabupaten" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                <option value="">Semua Kabupaten/Kota</option>
                <?php foreach ($daftarKabupatenKota ?? [] as $wil) : ?>
                    <option value="<?= esc($wil['kode_kabupaten']) ?>" <?= $kabupatenAktif === $wil['kode_kabupaten'] ? 'selected' : '' ?>>
                        <?= esc($wil['nama_kabupaten_kota']) ?> (<?= esc((string) ($wil['jumlah_destinasi'] ?? 0)) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="filter-kategori" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Kategori</label>
            <select id="filter-kategori" name="kategori" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                <option value="">Semua Kategori</option>
                <?php foreach ($daftarKategori ?? [] as $kat) : ?>
                    <option value="<?= esc($kat['slug']) ?>" <?= $kategoriAktif === $kat['slug'] ? 'selected' : '' ?>>
                        <?= esc($kat['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="filter-harga" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Batas Harga Tiket</label>
            <select id="filter-harga" name="harga_maksimal" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                <option value="">Semua Tarif</option>
                <option value="0" <?= $hargaMaksimalAktif === '0' ? 'selected' : '' ?>>Gratis (Rp 0)</option>
                <option value="25000" <?= $hargaMaksimalAktif === '25000' ? 'selected' : '' ?>>Maks Rp 25.000</option>
                <option value="50000" <?= $hargaMaksimalAktif === '50000' ? 'selected' : '' ?>>Maks Rp 50.000</option>
                <option value="150000" <?= $hargaMaksimalAktif === '150000' ? 'selected' : '' ?>>Maks Rp 150.000</option>
            </select>
        </div>

        <div class="lg:col-span-2 flex gap-2">
            <button
                type="submit"
                class="flex-1 py-2.5 px-4 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
            >
                Terapkan
            </button>
            <a
                href="<?= base_url('destinasi') ?>"
                class="py-2.5 px-3 rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-container font-label-md text-label-md transition-colors"
                title="Reset semua filter"
            >
                Reset
            </a>
        </div>

        <!-- Baris Pengurutan -->
        <div class="sm:col-span-2 lg:col-span-12 flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-surface-variant/40">
            <div class="flex flex-wrap items-center gap-2">
                <?php foreach ($daftarKategori ?? [] as $kat) : ?>
                    <?php $sedangAktif = $kategoriAktif === $kat['slug']; ?>
                    <a
                        href="<?= base_url('destinasi?' . http_build_query(array_filter(['q' => $kataKunciAktif, 'kabupaten' => $kabupatenAktif, 'kategori' => $sedangAktif ? '' : $kat['slug']]))) ?>"
                        class="<?= $sedangAktif
                            ? 'px-3 py-1.5 rounded-full bg-primary-container text-on-primary font-label-md text-label-md'
                            : 'px-3 py-1.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-label-md text-label-md transition-colors' ?>"
                    >
                        <?= esc($kat['nama']) ?> (<?= esc((string) ($kat['jumlah_destinasi'] ?? 0)) ?>)
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-2">
                <label for="filter-urutan" class="font-body-sm text-body-sm text-outline">Urutkan:</label>
                <select id="filter-urutan" name="urutan" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg bg-surface-container font-label-md text-label-md text-on-surface focus:outline-none cursor-pointer">
                    <option value="terbaik" <?= $urutanAktif === 'terbaik' ? 'selected' : '' ?>>Rating Tertinggi</option>
                    <option value="harga_terendah" <?= $urutanAktif === 'harga_terendah' ? 'selected' : '' ?>>Harga Terendah</option>
                    <option value="terbaru" <?= $urutanAktif === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                </select>
            </div>
        </div>
    </form>

    <!-- Grid Hasil Katalog -->
    <?php if (empty($daftarDestinasi)) : ?>
        <div class="mt-10 bg-surface-container-lowest rounded-3xl p-12 text-center border border-surface-variant/40">
            <span class="material-symbols-outlined text-5xl text-outline">search_off</span>
            <h2 class="font-headline-md text-headline-md text-on-surface mt-3">
                Belum Ada Destinasi yang Cocok
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-md mx-auto">
                Belum ada objek wisata terdata untuk filter atau kabupaten/kota yang dipilih. Coba ubah kata kunci pencarian, pilih kabupaten/kota lain, atau reset filter.
            </p>
            <a href="<?= base_url('destinasi') ?>" class="inline-flex mt-6 px-5 py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-md text-label-md transition-colors">
                Tampilkan Semua Destinasi
            </a>
        </div>
    <?php else : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <?php foreach ($daftarDestinasi as $destinasi) : ?>
                <?= view('components/kartu_destinasi', ['destinasi' => $destinasi]) ?>
            <?php endforeach; ?>
        </div>

        <!-- Navigasi Paginasi -->
        <?php if ($totalHalaman > 1) : ?>
            <?php
            $halamanAwal  = max(1, $halamanSekarang - 2);
            $halamanAkhir = min($totalHalaman, $halamanSekarang + 2);
            ?>
            <nav class="mt-12 flex flex-wrap items-center justify-center gap-2" aria-label="Paginasi katalog destinasi">
                <?php if ($halamanSekarang > 1) : ?>
                    <a href="<?= esc($bangunUrlHalaman($halamanSekarang - 1)) ?>" class="px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors">
                        ← Sebelumnya
                    </a>
                <?php endif; ?>

                <?php if ($halamanAwal > 1) : ?>
                    <a href="<?= esc($bangunUrlHalaman(1)) ?>" class="px-3.5 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md tabular-nums">1</a>
                    <?php if ($halamanAwal > 2) : ?>
                        <span class="px-2 text-outline">…</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($hal = $halamanAwal; $hal <= $halamanAkhir; $hal++) : ?>
                    <a
                        href="<?= esc($bangunUrlHalaman($hal)) ?>"
                        class="<?= $hal === $halamanSekarang
                            ? 'px-3.5 py-2 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md tabular-nums shadow-sm'
                            : 'px-3.5 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md tabular-nums transition-colors' ?>"
                        <?= $hal === $halamanSekarang ? 'aria-current="page"' : '' ?>
                    >
                        <?= esc((string) $hal) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($halamanAkhir < $totalHalaman) : ?>
                    <?php if ($halamanAkhir < $totalHalaman - 1) : ?>
                        <span class="px-2 text-outline">…</span>
                    <?php endif; ?>
                    <a href="<?= esc($bangunUrlHalaman($totalHalaman)) ?>" class="px-3.5 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md tabular-nums"><?= esc((string) $totalHalaman) ?></a>
                <?php endif; ?>

                <?php if ($halamanSekarang < $totalHalaman) : ?>
                    <a href="<?= esc($bangunUrlHalaman($halamanSekarang + 1)) ?>" class="px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors">
                        Berikutnya →
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
