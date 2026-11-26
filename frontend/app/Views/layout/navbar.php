<?php
$menuAktif         = $menuAktif ?? 'beranda';
$idPenggunaAktif   = session('id_pengguna');
$namaPenggunaAktif = session('nama_pengguna') ?? session('nama') ?? 'Wisatawan';
$berandaAktif      = $menuAktif === 'beranda';
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

        <!-- Navigasi & Tombol Aksi Kanan -->
        <nav class="flex items-center gap-2 sm:gap-3 shrink-0" aria-label="Navigasi utama">
            <a
                href="<?= base_url('/') ?>"
                class="<?= $berandaAktif
                    ? 'hidden sm:inline-flex items-center justify-center px-3.5 py-2 bg-primary-container text-on-primary font-label-md text-label-md rounded-lg transition-all'
                    : 'hidden sm:inline-flex items-center justify-center px-3.5 py-2 font-label-md text-label-md rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all' ?>"
                <?= $berandaAktif ? 'aria-current="page"' : '' ?>
            >
                Beranda
            </a>

            <?php if (! empty($idPenggunaAktif)) : ?>
                <!-- Menu Khusus Member yang Login -->
                <a
                    href="<?= base_url('favorit-saya') ?>"
                    class="hidden md:inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-outline-variant/60 text-on-surface-variant hover:border-primary hover:text-primary font-label-md text-label-md transition-all"
                    title="Destinasi Favorit Saya"
                >
                    <span class="material-symbols-outlined text-base text-tertiary-container">favorite</span>
                    <span>Favorit</span>
                </a>
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
                
                <!-- Info Profil & Tombol Keluar -->
                <div class="flex items-center gap-2 pl-1 border-l border-surface-variant/60">
                    <span class="hidden xl:inline-block text-xs font-semibold text-on-surface-variant max-w-[120px] truncate" title="<?= esc($namaPenggunaAktif) ?>">
                        <?= esc($namaPenggunaAktif) ?>
                    </span>
                    <a
                        href="<?= base_url('keluar') ?>"
                        class="p-2 rounded-lg text-on-surface-variant hover:text-error hover:bg-error-container/20 transition-colors"
                        title="Keluar dari akun"
                        aria-label="Keluar dari akun"
                    >
                        <span class="material-symbols-outlined text-lg">logout</span>
                    </a>
                </div>
            <?php else : ?>
                <!-- Menu Pengunjung Publik (Guest) -->
                <a
                    href="<?= base_url('masuk') ?>"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-all"
                >
                    Masuk
                </a>
                <a
                    href="<?= base_url('daftar') ?>"
                    class="hidden sm:inline-flex items-center justify-center px-3.5 py-2 rounded-lg border border-outline-variant/60 text-on-surface-variant hover:text-on-surface hover:bg-surface-container font-label-md text-label-md transition-all"
                >
                    Daftar
                </a>
                <a
                    href="<?= base_url('destinasi') ?>"
                    class="inline-flex items-center justify-center px-4 sm:px-5 py-2.5 rounded-full bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
                >
                    Jelajahi Wisata
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>
