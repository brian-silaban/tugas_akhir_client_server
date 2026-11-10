<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'IndWalk — Explore Indonesia Together') ?></title>
    <meta name="description" content="<?= esc($metaDescription ?? 'Direktori kurasi destinasi wisata Nusantara dengan transparansi harga tiket, jam operasional, dan integrasi REST API ekosistem perjalanan.') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('assets/IndWalk_ Explore Indonesia Together.png') ?>">

    <!-- Google Fonts: Fraunces (Editorial Display Serif) + Plus Jakarta Sans (UI & Data) + JetBrains Mono (API Telemetry) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN Configured with Official IndWalk Design Tokens -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        canvas: '#FAF8F5',
                        brand: {
                            teal: '#0B6E77',
                            tealDark: '#08555C',
                            forest: '#2D5A36',
                            forestDark: '#1B4332',
                            olive: '#6A8E3F',
                            terracotta: '#C2410C',
                            terracottaHover: '#9A3412',
                            amber: '#D97706',
                        }
                    },
                    fontFamily: {
                        serif: ['"Fraunces"', 'Georgia', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        };
    </script>

    <style>
        body {
            background-color: #FAF8F5;
            color: #1C1917;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
        /* Subtle organic paper grain accent */
        .editorial-grid-line {
            background-image: linear-gradient(to right, rgba(231, 229, 228, 0.55) 1px, transparent 1px);
            background-size: 25% 100%;
        }
    </style>

    <?= $this->renderSection('head') ?>
</head>
<body class="bg-[#FAF8F5] text-stone-900 min-h-screen flex flex-col overflow-x-hidden selection:bg-[#0B6E77]/15 selection:text-[#0B6E77]">

    <!-- Top Editorial Telemetry Bar -->
    <div class="bg-[#1B4332] text-stone-100 text-xs border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <span class="inline-block w-2 h-2 rounded-full bg-[#6A8E3F]"></span>
                <span class="font-medium tracking-wide text-stone-200">Edisi Kurasi Nusantara · 6 Destinasi Prioritas &amp; 4 Zona Ekologi</span>
            </div>
            <div class="hidden md:flex items-center gap-5 text-stone-300 font-mono text-[11px] tabular-nums">
                <span>PROVIDER: <strong class="text-white font-medium">GET /api/v1/destinations</strong></span>
                <span class="text-white/30">|</span>
                <span>CONSUMER: <strong class="text-white font-medium">PartnerApiService (3s Timeout · Auto-Fallback)</strong></span>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <?= $this->include('layout/navbar') ?>

    <!-- Main Page Content -->
    <main class="flex-grow">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <?= $this->include('layout/footer') ?>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
