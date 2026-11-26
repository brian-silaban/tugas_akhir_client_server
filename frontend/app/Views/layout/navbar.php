<?php
$menuAktif         = $menuAktif ?? 'beranda';
$idPenggunaAktif   = session('id_pengguna');
$namaPenggunaAktif = session('nama_pengguna') ?? session('nama') ?? 'Wisatawan';
$sudahLogin         = ! empty($idPenggunaAktif);
$apakahBeranda      = ($menuAktif === 'beranda');
$apakahKatalogAktif = ($menuAktif === 'katalog' || $menuAktif === 'destinasi');
?>
<header
    data-navbar-beranda="<?= $apakahBeranda ? 'true' : 'false' ?>"
    class="fixed top-0 left-0 right-0 z-50 bg-surface/95 backdrop-blur-md border-b border-surface-variant/40 transition-all duration-300<?= $apakahBeranda ? ' navbar-kaca-beranda' : '' ?>"
>
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
                <span data-nav-judul class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-none transition-colors duration-300">
                    JejakSumut
                </span>
                <span data-nav-subjudul class="font-label-caps text-label-caps text-tertiary-container uppercase mt-0.5 transition-colors duration-300">
                    Sumatera Utara
                </span>
            </div>
        </a>

        <!-- Aksi Kanan: Jelajahi Wisata + Dropdown Akun (Person) -->
        <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
            <a
                href="<?= base_url('destinasi') ?>"
                class="<?= $apakahKatalogAktif
                    ? 'inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-full bg-primary-container text-on-primary font-label-md text-label-md whitespace-nowrap shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary'
                    : 'inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-full bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md whitespace-nowrap shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tertiary' ?>"
                <?= $apakahKatalogAktif ? 'aria-current="page"' : '' ?>
            >
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">travel_explore</span>
                <span>Jelajahi Wisata</span>
            </a>

            <div class="relative" data-wadah-dropdown="menu-akun">
                <button
                    type="button"
                    data-nav-tombol-ikon
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
        </div>
    </div>
</header>

<script>
    // Mengelola buka-tutup dropdown menu akun pada ikon person
    (function () {
        const tombolAkun = document.querySelector('[data-tombol-dropdown="menu-akun"]');
        const panelAkun  = document.getElementById('menu-akun');

        if (!tombolAkun || !panelAkun) {
            return;
        }

        function tutupMenuAkun() {
            panelAkun.classList.add('hidden');
            tombolAkun.setAttribute('aria-expanded', 'false');
        }

        tombolAkun.addEventListener('click', function (acara) {
            acara.stopPropagation();
            const akanTerbuka = panelAkun.classList.contains('hidden');
            panelAkun.classList.toggle('hidden', !akanTerbuka);
            tombolAkun.setAttribute('aria-expanded', akanTerbuka ? 'true' : 'false');
        });

        document.addEventListener('click', function (acara) {
            const target = acara.target;
            if (target instanceof Element && !target.closest('[data-wadah-dropdown="menu-akun"]')) {
                tutupMenuAkun();
            }
        });

        document.addEventListener('keydown', function (acara) {
            if (acara.key === 'Escape') {
                tutupMenuAkun();
            }
        });
    })();
</script>
