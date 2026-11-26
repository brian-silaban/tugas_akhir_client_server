<?php
$menuAktif         = $menuAktif ?? 'beranda';
$idPenggunaAktif   = session('id_pengguna');
$namaPenggunaAktif = session('nama_pengguna') ?? session('nama') ?? 'Wisatawan';
$sudahLogin        = ! empty($idPenggunaAktif);

$daftarMenuTengah = [
    ['kode' => 'destinasi',    'label' => 'Destinasi',    'tautan' => base_url('destinasi')],
    ['kode' => 'penginapan',   'label' => 'Penginapan',   'tautan' => base_url('penginapan')],
    ['kode' => 'transportasi', 'label' => 'Transportasi', 'tautan' => base_url('transportasi')],
];

// Samakan penanda menu katalog lama dengan menu Destinasi baru
$apakahDestinasiAktif = ($menuAktif === 'destinasi' || $menuAktif === 'katalog');
$apakahLainnyaAktif   = ($menuAktif === 'lainnya');
?>
<header class="fixed top-0 left-0 right-0 z-50 bg-surface/95 backdrop-blur-md border-b border-surface-variant/40">
    <div class="h-20 max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4">
        <!-- Logo JejakSumut (Satu-satunya pemicu kembali ke Beranda) -->
        <a
            href="<?= base_url('/') ?>"
            aria-label="JejakSumut Beranda"
            class="flex items-center gap-3 shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg"
        >
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

        <!-- Navigasi Utama Tengah (Desktop) -->
        <nav class="hidden lg:flex items-center gap-1" aria-label="Navigasi utama">
            <?php foreach ($daftarMenuTengah as $menuItem) : ?>
                <?php
                $sedangAktif = ($menuItem['kode'] === 'destinasi')
                    ? $apakahDestinasiAktif
                    : ($menuAktif === $menuItem['kode']);
                ?>
                <a
                    href="<?= esc($menuItem['tautan']) ?>"
                    class="<?= $sedangAktif
                        ? 'px-4 py-2 rounded-lg bg-primary-container text-on-primary font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary'
                        : 'px-4 py-2 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary' ?>"
                    <?= $sedangAktif ? 'aria-current="page"' : '' ?>
                >
                    <?= esc($menuItem['label']) ?>
                </a>
            <?php endforeach; ?>

            <!-- Dropdown Lainnya (Kuliner & Tur Guide + Tentang Kami) -->
            <div class="relative" data-wadah-dropdown="menu-lainnya">
                <button
                    type="button"
                    data-tombol-dropdown="menu-lainnya"
                    aria-expanded="false"
                    aria-haspopup="true"
                    class="<?= $apakahLainnyaAktif
                        ? 'inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-primary-container text-on-primary font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary'
                        : 'inline-flex items-center gap-1 px-4 py-2 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary' ?>"
                >
                    <span>Lainnya</span>
                    <span class="material-symbols-outlined text-base transition-transform duration-200" data-panah-dropdown="menu-lainnya" aria-hidden="true">expand_more</span>
                </button>

                <div
                    id="menu-lainnya"
                    data-panel-dropdown="menu-lainnya"
                    class="hidden absolute left-0 mt-2 w-60 rounded-2xl bg-surface-container-lowest border border-surface-variant/70 shadow-lg py-2 z-50"
                    role="menu"
                >
                    <a
                        href="<?= base_url('mitra-lainnya') ?>"
                        role="menuitem"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors"
                    >
                        <span class="material-symbols-outlined text-lg text-secondary" aria-hidden="true">restaurant_menu</span>
                        <div>
                            <span class="font-semibold block leading-tight">Kuliner &amp; Tur Guide</span>
                            <span class="text-xs text-outline">Layanan mitra wisata Sumut</span>
                        </div>
                    </a>
                    <a
                        href="<?= base_url('tentang') ?>"
                        role="menuitem"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors"
                    >
                        <span class="material-symbols-outlined text-lg text-primary" aria-hidden="true">groups</span>
                        <div>
                            <span class="font-semibold block leading-tight">Tentang Kami</span>
                            <span class="text-xs text-outline">Profil &amp; tim pengembang</span>
                        </div>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Aksi Kanan: Dropdown Akun (Person) & Tombol Menu Mobile -->
        <div class="flex items-center gap-2 shrink-0">
            <div class="relative" data-wadah-dropdown="menu-akun">
                <button
                    type="button"
                    data-tombol-dropdown="menu-akun"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="<?= $sudahLogin ? 'Menu akun ' . esc($namaPenggunaAktif) : 'Menu masuk dan daftar akun' ?>"
                    title="<?= $sudahLogin ? esc($namaPenggunaAktif) : 'Akun JejakSumut' ?>"
                    class="relative inline-flex items-center justify-center w-10 h-10 rounded-full border border-outline-variant/80 bg-surface-container-lowest text-on-surface hover:border-primary hover:bg-surface-container transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                    <span class="material-symbols-outlined text-xl" aria-hidden="true">person</span>
                    <?php if ($sudahLogin) : ?>
                        <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-secondary ring-2 ring-surface" aria-hidden="true"></span>
                    <?php endif; ?>
                </button>

                <div
                    id="menu-akun"
                    data-panel-dropdown="menu-akun"
                    class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-surface-container-lowest border border-surface-variant/70 shadow-lg py-2 z-50"
                    role="menu"
                >
                    <?php if ($sudahLogin) : ?>
                        <div class="px-4 py-2.5 border-b border-surface-variant/50">
                            <span class="font-label-caps text-label-caps text-outline uppercase block">Masuk sebagai</span>
                            <span class="font-label-md text-label-md text-on-surface block truncate mt-0.5" title="<?= esc($namaPenggunaAktif) ?>">
                                <?= esc($namaPenggunaAktif) ?>
                            </span>
                        </div>
                        <a
                            href="<?= base_url('favorit-saya') ?>"
                            role="menuitem"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors"
                        >
                            <span class="material-symbols-outlined text-base text-tertiary-container" aria-hidden="true">favorite</span>
                            <span>Favorit Saya</span>
                        </a>
                        <a
                            href="<?= base_url('pemesanan') ?>"
                            role="menuitem"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container transition-colors"
                        >
                            <span class="material-symbols-outlined text-base text-primary" aria-hidden="true">confirmation_number</span>
                            <span>Tiket Saya</span>
                        </a>
                        <div class="my-1 border-t border-surface-variant/50"></div>
                        <a
                            href="<?= base_url('keluar') ?>"
                            role="menuitem"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-error hover:bg-error-container/25 transition-colors"
                        >
                            <span class="material-symbols-outlined text-base" aria-hidden="true">logout</span>
                            <span>Keluar</span>
                        </a>
                    <?php else : ?>
                        <a
                            href="<?= base_url('masuk') ?>"
                            role="menuitem"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container transition-colors"
                        >
                            <span class="material-symbols-outlined text-base text-primary" aria-hidden="true">login</span>
                            <span>Masuk</span>
                        </a>
                        <a
                            href="<?= base_url('daftar') ?>"
                            role="menuitem"
                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container transition-colors"
                        >
                            <span class="material-symbols-outlined text-base text-secondary" aria-hidden="true">person_add</span>
                            <span>Daftar</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tombol Drawer Navigasi Mobile -->
            <button
                type="button"
                data-tombol-menu-mobile
                aria-expanded="false"
                aria-controls="panel-navigasi-mobile"
                aria-label="Buka menu navigasi"
                class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-outline-variant/80 bg-surface-container-lowest text-on-surface hover:bg-surface-container transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
                <span class="material-symbols-outlined text-xl" data-ikon-menu-mobile aria-hidden="true">menu</span>
            </button>
        </div>
    </div>

    <!-- Drawer Navigasi Layar Mobile -->
    <div
        id="panel-navigasi-mobile"
        class="hidden lg:hidden border-t border-surface-variant/50 bg-surface-container-lowest px-4 py-4 shadow-md"
    >
        <nav class="flex flex-col gap-1 max-w-7xl mx-auto" aria-label="Navigasi mobile">
            <?php foreach ($daftarMenuTengah as $menuItem) : ?>
                <?php
                $sedangAktif = ($menuItem['kode'] === 'destinasi')
                    ? $apakahDestinasiAktif
                    : ($menuAktif === $menuItem['kode']);
                ?>
                <a
                    href="<?= esc($menuItem['tautan']) ?>"
                    class="<?= $sedangAktif
                        ? 'px-3.5 py-2.5 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md'
                        : 'px-3.5 py-2.5 rounded-xl text-on-surface hover:bg-surface-container font-label-md text-label-md transition-colors' ?>"
                >
                    <?= esc($menuItem['label']) ?>
                </a>
            <?php endforeach; ?>
            <div class="pt-2 mt-2 border-t border-surface-variant/40">
                <span class="px-3.5 font-label-caps text-label-caps text-outline uppercase block mb-1">Lainnya</span>
                <a href="<?= base_url('mitra-lainnya') ?>" class="block px-3.5 py-2.5 rounded-xl text-on-surface hover:bg-surface-container font-label-md text-label-md transition-colors">
                    Kuliner &amp; Tur Guide
                </a>
                <a href="<?= base_url('tentang') ?>" class="block px-3.5 py-2.5 rounded-xl text-on-surface hover:bg-surface-container font-label-md text-label-md transition-colors">
                    Tentang Kami
                </a>
            </div>
        </nav>
    </div>
</header>

<script>
    // Mengelola dropdown Lainnya, dropdown ikon akun, serta drawer navigasi mobile
    (function () {
        const daftarTombolDropdown = document.querySelectorAll('[data-tombol-dropdown]');
        const daftarPanelDropdown  = document.querySelectorAll('[data-panel-dropdown]');
        const tombolMobile         = document.querySelector('[data-tombol-menu-mobile]');
        const panelMobile          = document.getElementById('panel-navigasi-mobile');
        const ikonMobile           = document.querySelector('[data-ikon-menu-mobile]');

        function tutupSemuaDropdown(kecualiId) {
            daftarPanelDropdown.forEach(function (panel) {
                const idPanel = panel.getAttribute('data-panel-dropdown');
                if (kecualiId && idPanel === kecualiId) {
                    return;
                }
                panel.classList.add('hidden');
            });

            daftarTombolDropdown.forEach(function (tombol) {
                const idTarget = tombol.getAttribute('data-tombol-dropdown');
                if (kecualiId && idTarget === kecualiId) {
                    return;
                }
                tombol.setAttribute('aria-expanded', 'false');
                const panah = document.querySelector('[data-panah-dropdown="' + idTarget + '"]');
                if (panah) {
                    panah.classList.remove('rotate-180');
                }
            });
        }

        function tutupMenuMobile() {
            if (!panelMobile || !tombolMobile) {
                return;
            }
            panelMobile.classList.add('hidden');
            tombolMobile.setAttribute('aria-expanded', 'false');
            if (ikonMobile) {
                ikonMobile.textContent = 'menu';
            }
        }

        daftarTombolDropdown.forEach(function (tombol) {
            tombol.addEventListener('click', function (acara) {
                acara.stopPropagation();
                const idTarget = tombol.getAttribute('data-tombol-dropdown');
                const panel    = document.querySelector('[data-panel-dropdown="' + idTarget + '"]');
                if (!panel) {
                    return;
                }

                const sedangTerbuka = !panel.classList.contains('hidden');
                tutupSemuaDropdown(null);
                tutupMenuMobile();

                if (!sedangTerbuka) {
                    panel.classList.remove('hidden');
                    tombol.setAttribute('aria-expanded', 'true');
                    const panah = document.querySelector('[data-panah-dropdown="' + idTarget + '"]');
                    if (panah) {
                        panah.classList.add('rotate-180');
                    }
                }
            });
        });

        if (tombolMobile && panelMobile) {
            tombolMobile.addEventListener('click', function (acara) {
                acara.stopPropagation();
                tutupSemuaDropdown(null);
                const akanTerbuka = panelMobile.classList.contains('hidden');
                panelMobile.classList.toggle('hidden', !akanTerbuka);
                tombolMobile.setAttribute('aria-expanded', akanTerbuka ? 'true' : 'false');
                if (ikonMobile) {
                    ikonMobile.textContent = akanTerbuka ? 'close' : 'menu';
                }
            });
        }

        document.addEventListener('click', function (acara) {
            const target = acara.target;
            if (!(target instanceof Element)) {
                return;
            }
            if (!target.closest('[data-wadah-dropdown]')) {
                tutupSemuaDropdown(null);
            }
            if (panelMobile && !panelMobile.contains(target) && tombolMobile && !tombolMobile.contains(target)) {
                tutupMenuMobile();
            }
        });

        document.addEventListener('keydown', function (acara) {
            if (acara.key === 'Escape') {
                tutupSemuaDropdown(null);
                tutupMenuMobile();
            }
        });
    })();
</script>
