<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="shell-type" content="web_standard" />
    <title><?= esc($judulHalaman ?? 'JejakSumut — Jelajahi Pesona Sumatera Utara') ?></title>

    <!-- Google Fonts: Playfair Display (Heading Serif) & Plus Jakarta Sans (Body Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap"
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

<body class="bg-surface font-body-md text-on-surface antialiased overflow-x-hidden">
    <?= $this->include('layout/navbar') ?>

    <main class="w-full pt-20 bg-surface min-h-[calc(100vh-80px)]">
        <?= $this->renderSection('konten') ?>
    </main>

    <?= $this->include('layout/footer') ?>
</body>
</html>
