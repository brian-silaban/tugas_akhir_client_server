<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="shell-type" content="web_standard" />
    <title><?= esc($judulHalaman ?? 'JejakSumut — Jelajahi Pesona Sumatera Utara') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/logo-jejaksumut.png') ?>" />

    <!-- Tandai dukungan JS sedini mungkin agar tidak ada kedipan sebelum animasi masuk -->
    <script>document.documentElement.classList.add('animasi-aktif');</script>

    <!-- Google Fonts: Playfair Display (Heading Serif) & Plus Jakarta Sans (Body Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap"
        rel="stylesheet"
    />

    <!-- Reset Dasar Halaman -->
    <style>
        @layer base {
            html,
            body {
                margin: 0;
                padding: 0;
            }
            body {
                overscroll-behavior: none;
            }
            main > :first-child {
                margin-top: 0 !important;
            }
            main > :last-child {
                margin-bottom: 0 !important;
            }
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        /* Animasi Scroll-Reveal Editorial JejakSumut (tanpa library eksternal) */
        .animasi-aktif [data-animasi] {
            opacity: 0;
            transform: translateY(24px);
            transition:
                opacity 0.68s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.68s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        }
        .animasi-aktif [data-animasi="skala"] {
            opacity: 0;
            transform: translateY(18px) scale(0.975);
        }
        .animasi-aktif [data-animasi="kiri"] {
            opacity: 0;
            transform: translateX(-20px);
        }
        .animasi-aktif [data-animasi="kanan"] {
            opacity: 0;
            transform: translateX(20px);
        }
        .animasi-aktif [data-animasi].animasi-tampil {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
            will-change: auto;
        }

        @keyframes munculDropdown {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        [data-panel-dropdown]:not(.hidden) {
            animation: munculDropdown 0.2s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* Kabut blur bertahap di sisi bawah Hero Beranda */
        .kabut-bawah-hero {
            -webkit-mask-image: linear-gradient(to top, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.7) 45%, rgba(0, 0, 0, 0) 100%);
            mask-image: linear-gradient(to top, rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.7) 45%, rgba(0, 0, 0, 0) 100%);
        }

        /* Navbar frosted glass khusus saat berada di atas foto Hero Beranda */
        header.navbar-kaca-beranda {
            background-color: rgba(23, 20, 18, 0.36) !important;
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border-bottom-color: rgba(255, 255, 255, 0.16) !important;
        }
        header.navbar-kaca-beranda [data-nav-judul] {
            color: #ffffff !important;
        }
        header.navbar-kaca-beranda [data-nav-subjudul] {
            color: #ffd9cd !important;
        }
        header.navbar-kaca-beranda [data-nav-tombol-ikon] {
            background-color: rgba(255, 255, 255, 0.14) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
        }
        header.navbar-kaca-beranda [data-nav-tombol-ikon]:hover {
            background-color: rgba(255, 255, 255, 0.24) !important;
            border-color: rgba(255, 255, 255, 0.48) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto !important;
            }
            .animasi-aktif [data-animasi],
            .animasi-aktif [data-animasi].animasi-tampil,
            [data-panel-dropdown]:not(.hidden) {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
                animation: none !important;
            }
        }
    </style>

    <!-- Tailwind CSS CDN & Konfigurasi Design Tokens Resmi JejakSumut -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "outline": "#6f797a",
                        "error-container": "#ffdad6",
                        "surface-tint": "#006972",
                        "tertiary": "#8c2900",
                        "on-secondary-fixed-variant": "#23502d",
                        "secondary": "#3b6843",
                        "inverse-surface": "#33302d",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#f4ece8",
                        "tertiary-fixed": "#ffdbd0",
                        "tertiary-container": "#b53801",
                        "inverse-on-surface": "#f7efeb",
                        "on-surface": "#1e1b19",
                        "surface": "#fff8f5",
                        "surface-container-low": "#faf2ee",
                        "on-primary-container": "#9eedf7",
                        "on-error-container": "#93000a",
                        "on-tertiary-container": "#ffd9cd",
                        "surface-variant": "#e9e1dd",
                        "on-secondary-fixed": "#00210a",
                        "on-error": "#ffffff",
                        "surface-dim": "#e0d8d5",
                        "on-surface-variant": "#3f494a",
                        "on-secondary": "#ffffff",
                        "surface-container-high": "#eee7e3",
                        "on-tertiary": "#ffffff",
                        "inverse-primary": "#84d3dd",
                        "on-primary": "#ffffff",
                        "primary-container": "#0b6e77",
                        "tertiary-fixed-dim": "#ffb59d",
                        "primary-fixed-dim": "#84d3dd",
                        "outline-variant": "#bec8ca",
                        "on-secondary-container": "#3f6c47",
                        "secondary-fixed": "#bcefc0",
                        "on-primary-fixed": "#001f23",
                        "on-tertiary-fixed-variant": "#832600",
                        "on-background": "#1e1b19",
                        "surface-bright": "#fff8f5",
                        "background": "#fff8f5",
                        "primary-fixed": "#a0eff9",
                        "secondary-fixed-dim": "#a1d3a5",
                        "secondary-container": "#b9ecbd",
                        "on-primary-fixed-variant": "#004f56",
                        "error": "#ba1a1a",
                        "on-tertiary-fixed": "#390c00",
                        "surface-container-highest": "#e9e1dd",
                        "primary": "#00545b"
                    },
                    fontFamily: {
                        "serif": ["Playfair Display", "serif"],
                        "sans": ["Plus Jakarta Sans", "sans-serif"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "display-hero": ["Playfair Display"],
                        "headline-md": ["Playfair Display"],
                        "metric-price": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Playfair Display"],
                        "title-editorial": ["Playfair Display"],
                        "display-hero-mobile": ["Playfair Display"],
                        "body-sm": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "headline-lg-mobile": ["Playfair Display"],
                        "headline-sm": ["Playfair Display"],
                        "label-caps": ["Plus Jakarta Sans"],
                        "metric-rating": ["Plus Jakarta Sans"]
                    },
                    fontSize: {
                        "body-lg": ["16px", { "lineHeight": "26px", "fontWeight": "400" }],
                        "display-hero": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "metric-price": ["18px", { "lineHeight": "22px", "fontWeight": "700" }],
                        "label-md": ["13px", { "lineHeight": "16px", "letterSpacing": "0.01em", "fontWeight": "600" }],
                        "headline-lg": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.015em", "fontWeight": "600" }],
                        "title-editorial": ["18px", { "lineHeight": "24px", "fontWeight": "500" }],
                        "display-hero-mobile": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }],
                        "body-sm": ["12px", { "lineHeight": "18px", "fontWeight": "400" }],
                        "body-md": ["14px", { "lineHeight": "22px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["26px", { "lineHeight": "34px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "label-caps": ["11px", { "lineHeight": "14px", "letterSpacing": "0.08em", "fontWeight": "700" }],
                        "metric-rating": ["14px", { "lineHeight": "18px", "fontWeight": "700" }]
                    }
                }
            }
        };
    </script>
</head>

<?php
$apakahBeranda = (($menuAktif ?? '') === 'beranda');
$pesanSwalSukses = session()->getFlashdata('success') ?? session()->getFlashdata('pesan_sukses') ?? '';
$pesanSwalGagal = session()->getFlashdata('error') ?? session()->getFlashdata('pesan_error') ?? '';
?>
<body class="bg-surface font-body-md text-on-surface antialiased overflow-x-hidden">
    <div id="wadah-notifikasi-swal"
         data-swal-sukses="<?= esc((string) $pesanSwalSukses, 'attr') ?>"
         data-swal-gagal="<?= esc((string) $pesanSwalGagal, 'attr') ?>"
         aria-hidden="true"></div>
    <?= $this->include('layout/navbar') ?>

    <main class="w-full <?= $apakahBeranda ? '' : 'pt-20 ' ?>bg-surface min-h-[calc(100vh-80px)]">
        <?= $this->renderSection('konten') ?>
    </main>

    <?= $this->include('layout/footer') ?>

    <script>
        // Mengaktifkan efek muncul bertahap saat scroll dan bayangan halus pada navbar
        (function () {
            function tampilkanElemen(elemen) {
                elemen.classList.add('animasi-tampil');
            }

            function terapkanJedaBerjenjang() {
                document.querySelectorAll('[data-animasi-berjenjang]').forEach(function (wadah) {
                    const daftarAnak = Array.from(wadah.children);
                    daftarAnak.forEach(function (anak, indeks) {
                        if (!anak.hasAttribute('data-animasi')) {
                            anak.setAttribute('data-animasi', 'naik');
                        }
                        if (!anak.style.transitionDelay) {
                            const jedaMs = Math.min(indeks * 75, 420);
                            anak.style.transitionDelay = jedaMs + 'ms';
                        }
                    });
                });
            }

            function daftarkanSeksiOtomatis() {
                document.querySelectorAll('main > section, main > div > section').forEach(function (seksi) {
                    if (seksi.hasAttribute('data-animasi') || seksi.querySelector('[data-animasi]')) {
                        return;
                    }
                    seksi.setAttribute('data-animasi', 'naik');
                });
            }

            function inisialisasiAnimasiScroll() {
                terapkanJedaBerjenjang();
                daftarkanSeksiOtomatis();

                const daftarElemen = Array.from(document.querySelectorAll('[data-animasi]'));
                if (daftarElemen.length === 0) {
                    return;
                }

                daftarElemen.forEach(function (elemen) {
                    const jedaKhusus = elemen.getAttribute('data-animasi-jeda');
                    if (jedaKhusus !== null && jedaKhusus !== '' && !elemen.style.transitionDelay) {
                        elemen.style.transitionDelay = Number(jedaKhusus) + 'ms';
                    }
                });

                const preferensiHematGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (preferensiHematGerak || !('IntersectionObserver' in window)) {
                    daftarElemen.forEach(tampilkanElemen);
                    return;
                }

                const pengamatScroll = new IntersectionObserver(
                    function (daftarEntri) {
                        daftarEntri.forEach(function (entri) {
                            if (entri.isIntersecting) {
                                tampilkanElemen(entri.target);
                                pengamatScroll.unobserve(entri.target);
                            }
                        });
                    },
                    {
                        threshold: 0.08,
                        rootMargin: '0px 0px -48px 0px',
                    }
                );

                // Tunda satu frame agar elemen di atas lipatan layar ikut menganimasi masuk dengan mulus
                requestAnimationFrame(function () {
                    const tinggiLayar = window.innerHeight || document.documentElement.clientHeight;
                    daftarElemen.forEach(function (elemen) {
                        const posisi = elemen.getBoundingClientRect();
                        if (posisi.top < tinggiLayar * 0.96 && posisi.bottom > 0) {
                            tampilkanElemen(elemen);
                        } else {
                            pengamatScroll.observe(elemen);
                        }
                    });
                });
            }

            function inisialisasiBayanganNavbar() {
                const headerNavbar = document.querySelector('header');
                if (!headerNavbar) {
                    return;
                }

                const modeKacaBeranda = headerNavbar.getAttribute('data-navbar-beranda') === 'true';

                function perbaruiTampilanNavbar() {
                    const posisiScroll = window.scrollY || 0;
                    headerNavbar.classList.toggle('shadow-sm', posisiScroll > 12);

                    if (modeKacaBeranda) {
                        headerNavbar.classList.toggle('navbar-kaca-beranda', posisiScroll <= 60);
                    }
                }

                perbaruiTampilanNavbar();
                window.addEventListener('scroll', perbaruiTampilanNavbar, { passive: true });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    inisialisasiAnimasiScroll();
                    inisialisasiBayanganNavbar();
                });
            } else {
                inisialisasiAnimasiScroll();
                inisialisasiBayanganNavbar();
            }
        })();
    </script>
    <script src="<?= esc(base_url('js/sweetalert2.all.min.js'), 'attr') ?>" defer></script>
    <script src="<?= esc(base_url('js/notifikasi-swal.js?v=' . @filemtime(FCPATH . 'js/notifikasi-swal.js')), 'attr') ?>" defer></script>
</body>
</html>
