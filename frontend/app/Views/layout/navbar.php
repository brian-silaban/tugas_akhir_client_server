<?php
$menuAktif         = $menuAktif ?? 'beranda';
$idPenggunaAktif   = session('id_pengguna');
$namaPenggunaAktif = session('nama_pengguna') ?? 'Wisatawan';

$daftarMenuNavigasi = [
    ['kode' => 'beranda', 'label' => 'Beranda', 'tautan' => base_url('/')],
    ['kode' => 'katalog', 'label' => 'Katalog Wisata', 'tautan' => base_url('/destinasi')],
    ['kode' => 'unggulan', 'label' => 'Destinasi Unggulan', 'tautan' => base_url('/#destinasi-kurator')],
    ['kode' => 'wilayah', 'label' => 'Kabupaten & Kota', 'tautan' => base_url('/#jelajah-kepulauan')],
];
?>
<header class="fixed top-0 left-0 right-0 z-50 bg-surface/90 backdrop-blur-md border-b border-surface-variant/40">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4">
        <!-- Identitas Brand & Logo JejakSumut -->
        <a href="<?= base_url('/') ?>" class="flex items-center gap-3 shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg">
            <img
                src="<?= base_url('assets/logo-jejaksumut.png') ?>"
                alt="Logo JejakSumut"
                class="h-10 w-10 object-contain shrink-0"
            />
            <div class="flex flex-col">
                <span class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-none">
                    JejakSumut
                </span>
                <span class="font-label-caps text-label-caps text-tertiary-container uppercase mt-0.5">
                    Sumatera Utara
                </span>
            </div>
        </a>

        <!-- Tautan Navigasi Tengah -->
        <nav class="hidden lg:flex items-center gap-1" aria-label="Navigasi utama">
            <?php foreach ($daftarMenuNavigasi as $menuItem) : ?>
                <?php $sedangAktif = $menuAktif === $menuItem['kode']; ?>
                <a
                    href="<?= esc($menuItem['tautan']) ?>"
                    class="<?= $sedangAktif
                        ? 'px-3.5 py-2 bg-primary-container text-on-primary font-label-md text-label-md rounded-lg transition-all'
                        : 'px-3.5 py-2 font-label-md text-label-md rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all' ?>"
                    <?= $sedangAktif ? 'aria-current="page"' : '' ?>
                >
                    <?= esc($menuItem['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Tombol Aksi Kanan (1 Secondary/Ghost + 1 Primary Solid sesuai Sloplint) -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <div class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container text-on-surface-variant font-label-md text-label-md">
                <span class="text-tertiary-container font-bold">Rp</span>
                <span>IDR</span>
            </div>

            <?php if (! empty($idPenggunaAktif)) : ?>
                <a
                    href="<?= base_url('pemesanan') ?>"
                    class="hidden md:inline-flex items-center justify-center px-4 py-2 rounded-lg border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-all"
                >
                    Tiket Saya
                </a>
                <a
                    href="<?= base_url('destinasi') ?>"
                    class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-full bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
                >
                    Eksplorasi
                </a>
                <span class="hidden xl:inline-block text-xs font-semibold text-on-surface-variant px-2">
                    <?= esc($namaPenggunaAktif) ?>
                </span>
            <?php else : ?>
                <a
                    href="<?= base_url('masuk') ?>"
                    class="hidden md:inline-flex items-center justify-center px-4 py-2 rounded-lg border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-all"
                >
                    Masuk
                </a>
                <a
                    href="<?= base_url('destinasi') ?>"
                    class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-full bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
                >
                    Jelajahi Wisata
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
