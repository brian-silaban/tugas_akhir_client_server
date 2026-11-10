<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
/**
 * Helper penentuan warna badge kategori sesuai palet resmi IndWalk
 */
$categoryBadgeStyle = static function (string $slug): string {
    return match ($slug) {
        'cagar-budaya'       => 'bg-[#2D5A36] text-stone-50',
        'pegunungan-geopark' => 'bg-[#0B6E77] text-stone-50',
        'wisata-bahari'      => 'bg-[#08555C] text-stone-50',
        'hutan-air-terjun'   => 'bg-[#6A8E3F] text-stone-950 font-semibold',
        default              => 'bg-stone-800 text-stone-50',
    };
};

// Destinasi unggulan untuk Hero & Bento Showcase
$heroDestination = $destinations[2]; // Pulau Padar, Taman Nasional Komodo
$bentoPrimary    = $destinations[0]; // Candi Borobudur
$bentoSecondaryA = $destinations[1]; // Kawah Ijen
$bentoSecondaryB = $destinations[4]; // Taman Nasional Bromo Tengger Semeru
?>

<!-- =====================================================================
     SECTION 1: EDITORIAL SPLIT & IMMERSIVE SEARCH HERO
     ===================================================================== -->
<section class="relative pt-8 pb-14 lg:pt-12 lg:pb-20 border-b border-stone-200/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            <!-- Left Column: Editorial Headline, Factual Lead & Telemetry Metrics -->
            <div class="lg:col-span-7 space-y-6 pt-2">
                <div class="inline-flex flex-wrap items-center gap-2.5 px-3 py-1.5 rounded-lg bg-stone-200/75 border border-stone-300/80 text-xs font-medium text-stone-700">
                    <span class="font-mono uppercase tracking-wider text-[#0B6E77] font-semibold">Direktori Kurasi Nusantara</span>
                    <span class="text-stone-400">·</span>
                    <span>Data Lapangan Terverifikasi &amp; Interkoneksi REST API</span>
                </div>

                <h1 class="font-serif text-4xl sm:text-5xl lg:text-[3.35rem] font-semibold text-stone-900 tracking-tight leading-[1.08]">
                    Eksplorasi Lanskap &amp; Warisan Indonesia melalui Data Perjalanan Faktual.
                </h1>

                <p class="text-base sm:text-lg text-stone-700 leading-relaxed max-w-2xl">
                    Setiap destinasi di <strong class="font-semibold text-stone-900">IndWalk</strong> dilengkapi rincian harga tiket resmi, jam operasional harian, titik koordinat GPS, ketersediaan fasilitas fisik, serta rekomendasi lintas-layanan dari sistem kuliner, penginapan, dan transportasi mitra.
                </p>

                <!-- Factual Key Metrics Strip (No nested cards; uses clean divide-x border discipline) -->
                <div class="pt-3 pb-1 border-y border-stone-300/80 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-0 sm:divide-x sm:divide-stone-300/80 tabular-nums">
                    <div class="sm:pr-5 py-2">
                        <div class="font-serif text-2xl sm:text-3xl font-bold text-[#0B6E77]"><?= esc((string) count($destinations)) ?> Lokasi</div>
                        <div class="text-xs font-medium text-stone-600 mt-0.5">Destinasi Prioritas Nasional</div>
                    </div>
                    <div class="sm:px-5 py-2">
                        <div class="font-serif text-2xl sm:text-3xl font-bold text-[#2D5A36]"><?= esc((string) count($categories)) ?> Kategori</div>
                        <div class="text-xs font-medium text-stone-600 mt-0.5">Budaya, Geopark, Bahari, Hutan</div>
                    </div>
                    <div class="sm:px-5 py-2">
                        <div class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Rp 15rb<span class="text-base font-normal text-stone-600">+</span></div>
                        <div class="text-xs font-medium text-stone-600 mt-0.5">Transparansi Tarif Tiket Resmi</div>
                    </div>
                    <div class="sm:pl-5 py-2">
                        <div class="font-serif text-2xl sm:text-3xl font-bold text-[#C2410C]">3 Layanan</div>
                        <div class="text-xs font-medium text-stone-600 mt-0.5">Integrasi REST API Mitra Kota</div>
                    </div>
                </div>

                <!-- Hero Action Pair: 1 Primary Solid + 1 Secondary Ghost (Sloplint Rule #1) -->
                <div class="flex flex-wrap items-center gap-3.5 pt-1">
                    <a href="#eksplorasi-destinasi"
                       class="inline-flex items-center gap-2.5 px-5 py-3 rounded-lg text-sm font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] focus-visible:ring-offset-2 active:translate-y-px transition-all">
                        <span>Jelajahi 6 Destinasi</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="#integrasi-api-mitra"
                       class="inline-flex items-center gap-2.5 px-5 py-3 rounded-lg text-sm font-semibold text-stone-800 border border-stone-300 bg-white/80 hover:bg-stone-100 hover:border-stone-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-all">
                        <svg class="w-4 h-4 text-[#0B6E77]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="7" height="7" x="14" y="3" rx="1"/><path d="M10 21V8a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H3"/>
                        </svg>
                        <span>Simulasi Ekosistem API Mitra</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: Featured Field Dispatch Photo Card with High-Contrast Scrim -->
            <div class="lg:col-span-5">
                <article class="group relative rounded-2xl overflow-hidden border border-stone-200/80 bg-stone-900">
                    <div class="aspect-[4/3] sm:aspect-[16/11] lg:aspect-[4/3.4] w-full overflow-hidden">
                        <img src="<?= esc($heroDestination['thumbnail']) ?>"
                             alt="<?= esc($heroDestination['name']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    </div>

                    <!-- Dark Gradient Overlay for Guaranteed Text Contrast (Sloplint Rule #4) -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/15"></div>

                    <!-- Top Bar Inside Image: Category Pill + GPS Coordinate Pill -->
                    <div class="absolute top-4 inset-x-4 flex items-center justify-between gap-2">
                        <span class="px-3 py-1 rounded-lg text-xs font-medium bg-[#0B6E77] text-white">
                            <?= esc($heroDestination['category_name']) ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-black/70 text-stone-100 backdrop-blur-sm tabular-nums">
                            <?= esc(number_format((float) $heroDestination['latitude'], 4)) ?>, <?= esc(number_format((float) $heroDestination['longitude'], 4)) ?>
                        </span>
                    </div>

                    <!-- Bottom Content Inside Image -->
                    <div class="absolute bottom-0 inset-x-0 p-5 sm:p-6 text-white space-y-3">
                        <div class="flex items-center gap-2 text-xs font-medium text-stone-200">
                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span><?= esc($heroDestination['city']) ?>, <?= esc($heroDestination['province']) ?></span>
                            <span class="text-white/40">·</span>
                            <span class="text-amber-300 font-semibold tabular-nums">★ <?= esc(number_format((float) $heroDestination['rating_avg'], 2)) ?> (<?= esc(number_format((int) $heroDestination['review_count'], 0, ',', '.')) ?>)</span>
                        </div>

                        <h2 class="font-serif text-2xl font-semibold text-white leading-snug">
                            <?= esc($heroDestination['name']) ?>
                        </h2>

                        <p class="text-xs sm:text-sm text-stone-200 line-clamp-2 leading-relaxed">
                            <?= esc($heroDestination['description']) ?>
                        </p>

                        <div class="pt-3 border-t border-white/20 flex items-center justify-between gap-4 tabular-nums">
                            <div>
                                <span class="block text-[11px] uppercase tracking-wider text-stone-300">Tiket Masuk Resmi</span>
                                <span class="text-lg font-bold text-white">
                                    Rp <?= esc(number_format((int) $heroDestination['ticket_price'], 0, ',', '.')) ?>
                                    <span class="text-xs font-normal text-stone-300">/ orang</span>
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[11px] uppercase tracking-wider text-stone-300">Jam Operasional</span>
                                <span class="text-xs font-medium text-stone-100">
                                    <?= esc($heroDestination['open_time']) ?> – <?= esc($heroDestination['close_time']) ?> WITA
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

        </div>

        <!-- Interactive Search & Filter Bar -->
        <div id="pencarian-utama" class="mt-10 rounded-2xl bg-white border border-stone-200/90 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-stone-200/80">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#0B6E77]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    <h2 class="text-sm font-semibold text-stone-900">
                        Pencarian &amp; Penyaringan Katalog Wisata
                    </h2>
                </div>
                <span class="text-xs font-mono text-stone-500 tabular-nums">
                    Parameter Sinkron dengan Endpoint: GET /api/v1/destinations?city=&amp;category=&amp;max_price=
                </span>
            </div>

            <form id="destination-filter-form" action="<?= base_url('/') ?>#eksplorasi-destinasi" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">

                <!-- Input Kata Kunci -->
                <div class="lg:col-span-3">
                    <label for="filter-q" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                        Nama Destinasi / Kata Kunci
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="filter-q"
                               name="q"
                               value="<?= esc($filters['q'] ?? '') ?>"
                               placeholder="Mis. Borobudur, Kawah, Sabana..."
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 placeholder:text-stone-500 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors">
                        <svg class="w-4 h-4 text-stone-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                </div>

                <!-- Pilihan Kota (?city=) -->
                <div class="lg:col-span-3">
                    <label for="filter-city" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                        Kota / Kabupaten (`?city=`)
                    </label>
                    <select id="filter-city"
                            name="city"
                            class="w-full px-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors">
                        <option value="">Semua Kota (6 Kota)</option>
                        <?php foreach ($cities as $cityItem): ?>
                            <option value="<?= esc($cityItem) ?>" <?= ($filters['city'] ?? '') === $cityItem ? 'selected' : '' ?>>
                                <?= esc($cityItem) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pilihan Kategori (?category=) -->
                <div class="lg:col-span-3">
                    <label for="filter-category" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                        Kategori Wisata (`?category=`)
                    </label>
                    <select id="filter-category"
                            name="category"
                            class="w-full px-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors">
                        <option value="">Semua Kategori (4 Zona)</option>
                        <?php foreach ($categories as $catItem): ?>
                            <option value="<?= esc($catItem['slug']) ?>" <?= ($filters['category'] ?? '') === $catItem['slug'] ? 'selected' : '' ?>>
                                <?= esc($catItem['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Rentang Harga Tiket (?max_price=) & Action Buttons -->
                <div class="lg:col-span-3 flex flex-col sm:flex-row lg:flex-col xl:flex-row gap-2.5 items-stretch xl:items-end">
                    <div class="flex-1">
                        <label for="filter-price" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                            Batas Tarif Tiket
                        </label>
                        <select id="filter-price"
                                name="max_price"
                                class="w-full px-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors tabular-nums">
                            <option value="">Semua Tarif</option>
                            <option value="20000" <?= ($filters['max_price'] ?? '') === '20000' ? 'selected' : '' ?>>&le; Rp 20.000</option>
                            <option value="35000" <?= ($filters['max_price'] ?? '') === '35000' ? 'selected' : '' ?>>&le; Rp 35.000</option>
                            <option value="50000" <?= ($filters['max_price'] ?? '') === '50000' ? 'selected' : '' ?>>&le; Rp 50.000</option>
                            <option value="150000" <?= ($filters['max_price'] ?? '') === '150000' ? 'selected' : '' ?>>&le; Rp 150.000</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 xl:flex-initial px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] active:translate-y-px transition-all">
                            Terapkan
                        </button>
                        <button type="button"
                                id="filter-reset-btn"
                                title="Atur ulang filter"
                                class="px-3 py-2.5 rounded-lg text-sm font-medium text-stone-700 border border-stone-300 bg-transparent hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </div>
</section>

<!-- =====================================================================
     SECTION 2: INDEKS 4 KATEGORI NUSANTARA (EDITORIAL TAXONOMY LEDGER)
     ===================================================================== -->
<section id="indeks-kategori" class="py-14 lg:py-16 border-b border-stone-200/90 bg-[#F4F1EA]/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-mono font-semibold uppercase tracking-wider text-[#2D5A36]">
                    Taksonomi Eksplorasi · IndWalkSeeder
                </span>
                <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-stone-900 mt-1 tracking-tight">
                    4 Zona Lanskap &amp; Warisan Budaya
                </h2>
            </div>
            <p class="text-sm text-stone-600 max-w-md">
                Pilih salah satu zona di bawah untuk menyaring katalog destinasi secara langsung berdasarkan karakteristik geografis dan historisnya.
            </p>
        </div>

        <!-- 4-Column Editorial Index Ledger (Avoids the forbidden 3-card icon tile grid!) -->
        <div class="rounded-2xl bg-white border border-stone-200/90 divide-y md:divide-y-0 md:grid md:grid-cols-2 lg:grid-cols-4 md:divide-x divide-stone-200/90 overflow-hidden">
            <?php foreach ($categories as $category): ?>
                <button type="button"
                        data-category-trigger="<?= esc($category['slug']) ?>"
                        class="category-ledger-btn group text-left p-6 flex flex-col justify-between h-full hover:bg-[#FAF8F5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0B6E77] active:bg-stone-100 transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-4">
                            <span class="font-mono text-xs font-semibold px-2.5 py-1 rounded-md bg-stone-100 text-stone-700 group-hover:bg-[#0B6E77] group-hover:text-white transition-colors tabular-nums">
                                ZONA <?= esc($category['code']) ?>
                            </span>
                            <span class="font-mono text-xs text-stone-500 tabular-nums">
                                <?= esc((string) $category['destination_count']) ?> Destinasi
                            </span>
                        </div>

                        <h3 class="font-serif text-xl font-semibold text-stone-900 group-hover:text-[#0B6E77] transition-colors">
                            <?= esc($category['name']) ?>
                        </h3>

                        <p class="text-sm text-stone-600 mt-2 leading-relaxed">
                            <?= esc($category['description']) ?>
                        </p>
                    </div>

                    <div class="pt-5 mt-5 border-t border-stone-200/80 space-y-1.5 w-full tabular-nums">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-stone-500">Cakupan Wilayah</span>
                            <span class="font-medium text-stone-800"><?= esc($category['highlight_regions']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-stone-500">Rentang Tiket</span>
                            <span class="font-semibold text-[#2D5A36]"><?= esc($category['price_range']) ?></span>
                        </div>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- =====================================================================
     SECTION 3: ASYMMETRIC BENTO HIGHLIGHT & KATALOG 6 DESTINASI
     ===================================================================== -->
<section id="eksplorasi-destinasi" class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        <!-- Part A: Asymmetric Bento Showcase (1 Flagship col-span-7 + 2 Stacked col-span-5) -->
        <div>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <span class="text-xs font-mono font-semibold uppercase tracking-wider text-[#0B6E77]">
                        Sorotan Editorial Utama
                    </span>
                    <h2 class="font-serif text-3xl sm:text-4xl font-semibold text-stone-900 mt-1 tracking-tight">
                        Lanskap Ikonik dengan Kepadatan Data Tinggi
                    </h2>
                </div>
                <div class="text-xs font-mono text-stone-600 tabular-nums">
                    Koordinat GPS Terverifikasi · Jam Buka Aktual · Fasilitas Lapangan
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

                <!-- Primary Bento Highlight (col-span-7) -->
                <article class="lg:col-span-7 rounded-2xl bg-white border border-stone-200/90 overflow-hidden flex flex-col justify-between group">
                    <div>
                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-stone-900">
                            <img src="<?= esc($bentoPrimary['thumbnail']) ?>"
                                 alt="<?= esc($bentoPrimary['name']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                            <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2">
                                <span class="px-3 py-1 rounded-lg text-xs font-medium <?= esc($categoryBadgeStyle($bentoPrimary['category_slug'])) ?>">
                                    <?= esc($bentoPrimary['category_name']) ?>
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono bg-black/70 text-stone-100 tabular-nums">
                                    <?= esc($bentoPrimary['editorial_note']) ?>
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-end justify-between gap-2 text-white">
                                <div>
                                    <div class="text-xs font-medium text-stone-200 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <span><?= esc($bentoPrimary['city']) ?>, <?= esc($bentoPrimary['province']) ?></span>
                                    </div>
                                    <h3 class="font-serif text-2xl sm:text-3xl font-semibold mt-0.5">
                                        <?= esc($bentoPrimary['name']) ?>
                                    </h3>
                                </div>
                                <div class="px-3 py-1.5 rounded-lg bg-black/75 text-amber-300 text-xs font-semibold tabular-nums">
                                    ★ <?= esc(number_format((float) $bentoPrimary['rating_avg'], 2)) ?> (<?= esc(number_format((int) $bentoPrimary['review_count'], 0, ',', '.')) ?> ulasan)
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <p class="text-sm sm:text-base text-stone-700 leading-relaxed">
                                <?= esc($bentoPrimary['description']) ?>
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 border-t border-stone-200/80 text-xs tabular-nums">
                                <div>
                                    <span class="text-stone-500 block">Jam Operasional</span>
                                    <span class="font-semibold text-stone-900 mt-0.5 block"><?= esc($bentoPrimary['open_time']) ?> – <?= esc($bentoPrimary['close_time']) ?> WIB</span>
                                </div>
                                <div>
                                    <span class="text-stone-500 block">Durasi Kunjungan</span>
                                    <span class="font-semibold text-stone-900 mt-0.5 block"><?= esc($bentoPrimary['estimated_duration']) ?></span>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <span class="text-stone-500 block">Titik Koordinat</span>
                                    <span class="font-mono font-medium text-stone-800 mt-0.5 block"><?= esc(number_format((float) $bentoPrimary['latitude'], 4)) ?>, <?= esc(number_format((float) $bentoPrimary['longitude'], 4)) ?></span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <?php foreach ($bentoPrimary['facilities'] as $facility): ?>
                                    <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-stone-100 text-stone-700">
                                        <?= esc($facility) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-[#FAF8F5] border-t border-stone-200/80 flex flex-wrap items-center justify-between gap-4 tabular-nums">
                        <div>
                            <span class="text-xs text-stone-500 block">Harga Tiket Masuk</span>
                            <span class="text-xl font-bold text-stone-900">
                                Rp <?= esc(number_format((int) $bentoPrimary['ticket_price'], 0, ',', '.')) ?>
                                <span class="text-xs font-normal text-stone-600">/ orang</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <button type="button"
                                    data-jump-city="<?= esc($bentoPrimary['city']) ?>"
                                    class="jump-partner-city-btn px-3.5 py-2 rounded-lg text-xs font-semibold text-[#0B6E77] border border-[#0B6E77]/40 bg-transparent hover:bg-[#0B6E77]/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-[#0B6E77]/20 transition-colors">
                                Cek Mitra <?= esc($bentoPrimary['city']) ?>
                            </button>
                            <button type="button"
                                    data-open-destination="<?= esc((string) $bentoPrimary['id']) ?>"
                                    class="open-destination-modal-btn px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] active:translate-y-px transition-all">
                                Rincian &amp; Simulasi Tiket
                            </button>
                        </div>
                    </div>
                </article>

                <!-- Secondary Stacked Bento Cards (col-span-5) -->
                <div class="lg:col-span-5 flex flex-col justify-between gap-6">
                    <?php foreach ([$bentoSecondaryA, $bentoSecondaryB] as $secDest): ?>
                        <article class="rounded-2xl bg-white border border-stone-200/90 overflow-hidden flex flex-col justify-between flex-1 group">
                            <div class="p-5 sm:p-6 space-y-3.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-medium <?= esc($categoryBadgeStyle($secDest['category_slug'])) ?>">
                                        <?= esc($secDest['category_name']) ?>
                                    </span>
                                    <span class="text-xs font-semibold text-[#D97706] tabular-nums">
                                        ★ <?= esc(number_format((float) $secDest['rating_avg'], 2)) ?>
                                        <span class="text-stone-500 font-normal">(<?= esc(number_format((int) $secDest['review_count'], 0, ',', '.')) ?>)</span>
                                    </span>
                                </div>

                                <div class="flex gap-4 items-start">
                                    <div class="w-28 sm:w-32 aspect-[4/3] rounded-xl overflow-hidden shrink-0 bg-stone-200">
                                        <img src="<?= esc($secDest['thumbnail']) ?>"
                                             alt="<?= esc($secDest['name']) ?>"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                                    </div>
                                    <div class="space-y-1 min-w-0">
                                        <div class="text-xs font-medium text-stone-500">
                                            <?= esc($secDest['city']) ?>, <?= esc($secDest['province']) ?>
                                        </div>
                                        <h3 class="font-serif text-lg sm:text-xl font-semibold text-stone-900 leading-snug">
                                            <?= esc($secDest['name']) ?>
                                        </h3>
                                        <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed">
                                            <?= esc($secDest['description']) ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-stone-200/80 flex flex-wrap items-center justify-between gap-2 text-xs text-stone-600 tabular-nums">
                                    <span>Jam Buka: <strong class="text-stone-800"><?= esc($secDest['open_time']) ?> – <?= esc($secDest['close_time']) ?> WIB</strong></span>
                                    <span class="font-mono text-[11px] text-stone-500"><?= esc($secDest['editorial_note']) ?></span>
                                </div>
                            </div>

                            <div class="px-5 py-3.5 bg-[#FAF8F5] border-t border-stone-200/80 flex items-center justify-between gap-3 tabular-nums">
                                <div class="text-base font-bold text-stone-900">
                                    Rp <?= esc(number_format((int) $secDest['ticket_price'], 0, ',', '.')) ?>
                                    <span class="text-xs font-normal text-stone-500">/ orang</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            data-jump-city="<?= esc($secDest['city']) ?>"
                                            class="jump-partner-city-btn px-3 py-1.5 rounded-lg text-xs font-medium text-stone-700 border border-stone-300 hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] transition-colors">
                                        API <?= esc($secDest['city']) ?>
                                    </button>
                                    <button type="button"
                                            data-open-destination="<?= esc((string) $secDest['id']) ?>"
                                            class="open-destination-modal-btn px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#2D5A36] hover:bg-[#1B4332] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2D5A36] transition-colors">
                                        Lihat Detail
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <!-- Part B: Full Filterable Directory Grid (All 6 Destinations from IndWalkSeeder) -->
        <div class="pt-6 border-t border-stone-200/90">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-serif text-2xl font-semibold text-stone-900">
                        Katalog Lengkap Destinasi Kurasi
                    </h3>
                    <p id="catalog-count-status" class="text-sm text-stone-600 mt-0.5 tabular-nums">
                        Menampilkan seluruh <strong class="text-stone-900"><?= esc((string) count($destinations)) ?></strong> destinasi dari <code class="font-mono text-xs bg-stone-200/70 px-1.5 py-0.5 rounded">IndWalkSeeder.php</code>
                    </p>
                </div>

                <!-- Quick Category Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="Filter cepat kategori">
                    <button type="button"
                            data-quick-cat=""
                            class="quick-cat-pill px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0B6E77] text-white transition-colors">
                        Semua (6)
                    </button>
                    <?php foreach ($categories as $catPill): ?>
                        <button type="button"
                                data-quick-cat="<?= esc($catPill['slug']) ?>"
                                class="quick-cat-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-stone-200/70 text-stone-700 hover:bg-stone-300/70 transition-colors">
                            <?= esc($catPill['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Empty State when Filter matches 0 items -->
            <div id="catalog-empty-state" class="hidden rounded-2xl bg-white border border-stone-200 p-10 text-center space-y-3">
                <p class="font-serif text-xl text-stone-800">Tidak ada destinasi yang sesuai dengan kriteria filter.</p>
                <p class="text-sm text-stone-600">Coba ubah kata kunci pencarian, kota, atau batas harga tiket.</p>
                <button type="button" id="empty-reset-btn" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#0B6E77] hover:bg-[#08555C]">
                    Tampilkan Semua Destinasi
                </button>
            </div>

            <!-- 3-Column High-Density Destination Cards Grid -->
            <div id="destinations-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                <?php foreach ($destinations as $dest): ?>
                    <article class="destination-card rounded-2xl bg-white border border-stone-200/90 overflow-hidden flex flex-col justify-between h-full group transition-colors hover:border-[#0B6E77]/50"
                             data-name="<?= esc(strtolower($dest['name'] . ' ' . $dest['description'] . ' ' . $dest['address'])) ?>"
                             data-city="<?= esc($dest['city']) ?>"
                             data-category="<?= esc($dest['category_slug']) ?>"
                             data-price="<?= esc((string) $dest['ticket_price']) ?>">

                        <div>
                            <!-- Card Image with High-Contrast Gradient Scrim -->
                            <div class="relative aspect-[16/10] w-full overflow-hidden bg-stone-900">
                                <img src="<?= esc($dest['thumbnail']) ?>"
                                     alt="<?= esc($dest['name']) ?>"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                                <!-- Top Badge & Rating -->
                                <div class="absolute top-3.5 inset-x-3.5 flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium <?= esc($categoryBadgeStyle($dest['category_slug'])) ?>">
                                        <?= esc($dest['category_name']) ?>
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-black/75 text-amber-300 backdrop-blur-sm tabular-nums">
                                        ★ <?= esc(number_format((float) $dest['rating_avg'], 2)) ?> (<?= esc(number_format((int) $dest['review_count'], 0, ',', '.')) ?>)
                                    </span>
                                </div>

                                <!-- Bottom Geolocation inside Image -->
                                <div class="absolute bottom-3 inset-x-3.5 flex items-center justify-between text-xs text-stone-100">
                                    <span class="font-medium flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <?= esc($dest['city']) ?>, <?= esc($dest['province']) ?>
                                    </span>
                                    <span class="font-mono text-[11px] text-stone-200 tabular-nums">
                                        <?= esc($dest['open_time']) ?>–<?= esc($dest['close_time']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-5 space-y-3.5">
                                <div>
                                    <h4 class="font-serif text-xl font-semibold text-stone-900 group-hover:text-[#0B6E77] transition-colors leading-snug">
                                        <?= esc($dest['name']) ?>
                                    </h4>
                                    <p class="text-xs text-stone-500 mt-0.5 truncate">
                                        <?= esc($dest['address']) ?>
                                    </p>
                                </div>

                                <p class="text-sm text-stone-700 leading-relaxed line-clamp-3">
                                    <?= esc($dest['description']) ?>
                                </p>

                                <!-- Real Facility Badges -->
                                <div class="pt-2 border-t border-stone-200/80 flex flex-wrap gap-1.5">
                                    <?php foreach (array_slice($dest['facilities'], 0, 4) as $fac): ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-700">
                                            <?= esc($fac) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($dest['facilities']) > 4): ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-mono text-stone-500 bg-stone-100 tabular-nums">
                                            +<?= esc((string) (count($dest['facilities']) - 4)) ?> lainnya
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer: Transparent Pricing + 1 Secondary & 1 Primary Action -->
                        <div class="px-5 py-3.5 bg-[#FAF8F5] border-t border-stone-200/80 flex items-center justify-between gap-2 tabular-nums">
                            <div>
                                <span class="block text-[11px] text-stone-500">Harga Tiket</span>
                                <span class="text-base font-bold text-stone-900">
                                    Rp <?= esc(number_format((int) $dest['ticket_price'], 0, ',', '.')) ?>
                                    <span class="text-xs font-normal text-stone-500">/ org</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        data-jump-city="<?= esc($dest['city']) ?>"
                                        class="jump-partner-city-btn px-2.5 py-2 rounded-lg text-xs font-medium text-stone-700 border border-stone-300 bg-transparent hover:bg-stone-200/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] transition-colors">
                                    API Mitra
                                </button>
                                <button type="button"
                                        data-open-destination="<?= esc((string) $dest['id']) ?>"
                                        class="open-destination-modal-btn px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] active:translate-y-px transition-all">
                                    Detail Wisata
                                </button>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<!-- =====================================================================
     SECTION 4: SIMULASI INTERAKTIF INTEGRASI REST API KELOMPOK MITRA
     (Kuliner, Penginapan, Transportasi berdasarkan ?city=)
     ===================================================================== -->
<section id="integrasi-api-mitra" class="py-16 lg:py-20 bg-[#F4F1EA] border-t border-stone-300/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Section Header & Client-Server Architecture Context -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-6 border-b border-stone-300/80">
            <div class="space-y-2 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-[#0B6E77]/10 text-[#0B6E77] text-xs font-mono font-semibold">
                    <span>SIMULASI INTERAKTIF CLIENT-SERVER</span>
                    <span>·</span>
                    <span>App\Libraries\PartnerApiService</span>
                </div>
                <h2 class="font-serif text-3xl sm:text-4xl font-semibold text-stone-900 tracking-tight">
                    Ekosistem Layanan Mitra Berdasarkan Kota Wisata
                </h2>
                <p class="text-sm sm:text-base text-stone-700 leading-relaxed">
                    Saat wisatawan membuka destinasi di kota tertentu, server <strong class="font-semibold text-stone-900">IndWalk</strong> memanggil endpoint REST API milik kelompok <strong class="font-semibold text-stone-900">Kuliner</strong>, <strong class="font-semibold text-stone-900">Penginapan</strong>, dan <strong class="font-semibold text-stone-900">Transportasi</strong> menggunakan parameter <code class="font-mono text-xs bg-stone-200 px-1.5 py-0.5 rounded">?city={nama_kota}</code> dengan proteksi <em>Auto-Fallback Mock JSON</em> (<code class="font-mono text-xs">timeout: 3s</code>).
                </p>
            </div>

            <!-- View Mode Toggle: Editorial UI vs Raw JSON Contract -->
            <div class="flex items-center gap-2 self-start lg:self-end">
                <button type="button"
                        id="toggle-json-inspector"
                        aria-pressed="false"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-mono font-semibold text-stone-800 border border-stone-400/80 bg-white hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] transition-colors">
                    <svg class="w-4 h-4 text-[#0B6E77]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                    </svg>
                    <span id="toggle-json-label">Tampilkan Payload JSON</span>
                </button>
            </div>
        </div>

        <!-- Step 1: Interactive City Selector Pills (6 Kota dari IndWalkSeeder) -->
        <div class="space-y-2.5">
            <label class="block text-xs font-mono font-semibold uppercase tracking-wider text-stone-600">
                1. Pilih Parameter Kota Tujuan (`?city=`)
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5" role="tablist" aria-label="Pilih Kota Simulasi API">
                <?php foreach ($partnerEcosystem as $cityKey => $cityData): ?>
                    <?php $isDefaultCity = ($cityKey === 'Magelang'); ?>
                    <button type="button"
                            role="tab"
                            aria-selected="<?= $isDefaultCity ? 'true' : 'false' ?>"
                            data-partner-city="<?= esc($cityKey) ?>"
                            class="partner-city-tab text-left px-3.5 py-3 rounded-xl border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] <?= $isDefaultCity ? 'bg-[#0B6E77] text-white border-[#0B6E77]' : 'bg-white text-stone-800 border-stone-300/90 hover:border-[#0B6E77]/60' ?>">
                        <div class="font-semibold text-sm truncate"><?= esc($cityKey) ?></div>
                        <div class="partner-city-sub text-[11px] truncate mt-0.5 <?= $isDefaultCity ? 'text-stone-100' : 'text-stone-500' ?>">
                            <?= esc($cityData['anchor_spot']) ?>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Live Telemetry Status Bar & Service Category Filter Tabs -->
        <div class="rounded-2xl bg-white border border-stone-200/90 overflow-hidden">

            <!-- Telemetry Request Header -->
            <div class="px-5 py-4 bg-stone-900 text-stone-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4 font-mono text-xs tabular-nums">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold">HTTP 200 OK</span>
                    <span class="text-stone-400">GET</span>
                    <span id="api-live-endpoint" class="text-white font-medium break-all">
                        /api/v1/partner-discovery?city=Magelang
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-[11px] text-stone-300">
                    <span> Destinasi Induk: <strong id="api-live-anchor" class="text-amber-300">Candi Borobudur</strong></span>
                    <span>·</span>
                    <span>Mode: <strong class="text-teal-300">simulasi_fallback (Timeout 3000ms)</strong></span>
                    <span>·</span>
                    <span>Latensi: <strong id="api-live-latency" class="text-white">42 ms</strong></span>
                </div>
            </div>

            <!-- Service Filter Tabs (Semua, Kuliner, Penginapan, Transportasi) -->
            <div class="px-5 py-3.5 bg-[#FAF8F5] border-b border-stone-200/90 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter Layanan Mitra">
                    <button type="button"
                            data-service-tab="all"
                            class="partner-service-tab px-3.5 py-2 rounded-lg text-xs font-semibold bg-[#2D5A36] text-white transition-colors">
                        Semua Layanan Mitra (6)
                    </button>
                    <button type="button"
                            data-service-tab="kuliner"
                            class="partner-service-tab px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>
                        </svg>
                        <span>API Kuliner (2)</span>
                    </button>
                    <button type="button"
                            data-service-tab="penginapan"
                            class="partner-service-tab px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/>
                        </svg>
                        <span>API Penginapan (2)</span>
                    </button>
                    <button type="button"
                            data-service-tab="transportasi"
                            class="partner-service-tab px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>
                        </svg>
                        <span>API Transportasi (2)</span>
                    </button>
                </div>

                <div class="text-xs font-mono text-stone-500 tabular-nums">
                    Koordinat Simpul: <span id="api-live-coords" class="text-stone-800 font-medium">-7.6079, 110.2038</span>
                </div>
            </div>

            <!-- Partner Ecosystem Cards Container (Rendered per City, toggled cleanly) -->
            <div class="p-5 sm:p-6">
                <?php foreach ($partnerEcosystem as $cityKey => $cityBundle): ?>
                    <?php $isVisibleCity = ($cityKey === 'Magelang'); ?>
                    <div data-city-panel="<?= esc($cityKey) ?>"
                         class="partner-city-panel <?= $isVisibleCity ? 'block' : 'hidden' ?>">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-stone-200/90">

                            <!-- Column 1: Kelompok Kuliner -->
                            <div data-service-col="kuliner" class="partner-service-col pt-4 md:pt-0 md:pr-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#C2410C]"></span>
                                        <h3 class="font-serif text-lg font-semibold text-stone-900">Kuliner &amp; Rumah Makan</h3>
                                    </div>
                                    <span class="font-mono text-[11px] text-stone-500">:8081/api/kuliner</span>
                                </div>

                                <div class="space-y-4 divide-y divide-stone-200/80">
                                    <?php foreach ($cityBundle['kuliner'] as $idx => $item): ?>
                                        <div class="<?= $idx > 0 ? 'pt-4' : '' ?> space-y-1.5 tabular-nums">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-semibold text-stone-900 text-sm leading-snug">
                                                    <?= esc($item['name']) ?>
                                                </h4>
                                                <span class="text-xs font-semibold text-[#D97706] shrink-0">
                                                    ★ <?= esc(number_format((float) $item['rating'], 1)) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-stone-600 leading-relaxed">
                                                <?= esc($item['specialty']) ?>
                                            </p>
                                            <div class="text-[11px] text-stone-500 space-y-0.5 pt-1">
                                                <div>Jarak: <strong class="text-stone-700"><?= esc($item['distance']) ?></strong></div>
                                                <div>Lokasi: <?= esc($item['address']) ?></div>
                                                <div>Jam Buka: <?= esc($item['hours']) ?></div>
                                            </div>
                                            <div class="pt-1.5 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">Estimasi Harga</span>
                                                <span class="text-sm font-bold text-[#C2410C]">
                                                    Rp <?= esc(number_format((int) $item['price'], 0, ',', '.')) ?>
                                                    <span class="text-[11px] font-normal text-stone-500">/ <?= esc($item['unit']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Column 2: Kelompok Penginapan -->
                            <div data-service-col="penginapan" class="partner-service-col pt-6 md:pt-0 md:px-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#0B6E77]"></span>
                                        <h3 class="font-serif text-lg font-semibold text-stone-900">Penginapan &amp; Homestay</h3>
                                    </div>
                                    <span class="font-mono text-[11px] text-stone-500">:8082/api/penginapan</span>
                                </div>

                                <div class="space-y-4 divide-y divide-stone-200/80">
                                    <?php foreach ($cityBundle['penginapan'] as $idx => $item): ?>
                                        <div class="<?= $idx > 0 ? 'pt-4' : '' ?> space-y-1.5 tabular-nums">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-semibold text-stone-900 text-sm leading-snug">
                                                    <?= esc($item['name']) ?>
                                                </h4>
                                                <span class="text-xs font-semibold text-[#D97706] shrink-0">
                                                    ★ <?= esc(number_format((float) $item['rating'], 1)) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-stone-600 leading-relaxed">
                                                <?= esc($item['specialty']) ?>
                                            </p>
                                            <div class="text-[11px] text-stone-500 space-y-0.5 pt-1">
                                                <div>Jarak: <strong class="text-stone-700"><?= esc($item['distance']) ?></strong></div>
                                                <div>Lokasi: <?= esc($item['address']) ?></div>
                                                <div>Info: <?= esc($item['hours']) ?></div>
                                            </div>
                                            <div class="pt-1.5 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">Tarif Menginap</span>
                                                <span class="text-sm font-bold text-[#0B6E77]">
                                                    Rp <?= esc(number_format((int) $item['price'], 0, ',', '.')) ?>
                                                    <span class="text-[11px] font-normal text-stone-500">/ <?= esc($item['unit']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Column 3: Kelompok Transportasi -->
                            <div data-service-col="transportasi" class="partner-service-col pt-6 md:pt-0 md:pl-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#2D5A36]"></span>
                                        <h3 class="font-serif text-lg font-semibold text-stone-900">Transportasi &amp; Rute</h3>
                                    </div>
                                    <span class="font-mono text-[11px] text-stone-500">:8083/api/transportasi</span>
                                </div>

                                <div class="space-y-4 divide-y divide-stone-200/80">
                                    <?php foreach ($cityBundle['transportasi'] as $idx => $item): ?>
                                        <div class="<?= $idx > 0 ? 'pt-4' : '' ?> space-y-1.5 tabular-nums">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-semibold text-stone-900 text-sm leading-snug">
                                                    <?= esc($item['name']) ?>
                                                </h4>
                                                <span class="text-xs font-semibold text-[#D97706] shrink-0">
                                                    ★ <?= esc(number_format((float) $item['rating'], 1)) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-stone-600 leading-relaxed">
                                                <?= esc($item['specialty']) ?>
                                            </p>
                                            <div class="text-[11px] text-stone-500 space-y-0.5 pt-1">
                                                <div>Akses: <strong class="text-stone-700"><?= esc($item['distance']) ?></strong></div>
                                                <div>Titik Temu: <?= esc($item['address']) ?></div>
                                                <div>Jadwal: <?= esc($item['hours']) ?></div>
                                            </div>
                                            <div class="pt-1.5 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">Tarif Layanan</span>
                                                <span class="text-sm font-bold text-[#2D5A36]">
                                                    Rp <?= esc(number_format((int) $item['price'], 0, ',', '.')) ?>
                                                    <span class="text-[11px] font-normal text-stone-500">/ <?= esc($item['unit']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>

                <!-- Collapsible Live JSON Payload Inspector -->
                <div id="json-inspector-panel" class="hidden mt-6 pt-6 border-t border-stone-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-semibold text-stone-700">
                            Respons JSON Aktual (`PartnerApiService::getRecommendationsByCity`)
                        </span>
                        <span class="text-xs font-mono text-stone-500">Content-Type: application/json; charset=UTF-8</span>
                    </div>
                    <pre id="json-inspector-code" class="rounded-xl bg-stone-950 text-emerald-300 p-4 text-xs font-mono overflow-x-auto leading-relaxed max-h-96 tabular-nums"></pre>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- =====================================================================
     MODAL / DRAWER INTERAKTIF: DETAIL DESTINASI & SIMULASI E-TICKET
     ===================================================================== -->
<div id="destination-detail-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-dest-name"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-black/65 backdrop-blur-xs">

    <div class="relative w-full max-w-3xl rounded-2xl bg-[#FAF8F5] border border-stone-300 overflow-hidden max-h-[90vh] flex flex-col">

        <!-- Modal Header Image -->
        <div class="relative h-56 sm:h-64 w-full bg-stone-900 shrink-0">
            <img id="modal-dest-img" src="" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/20"></div>

            <button type="button"
                    id="close-destination-modal"
                    aria-label="Tutup jendela detail"
                    class="absolute top-4 right-4 px-3 py-1.5 rounded-lg bg-black/70 text-white text-xs font-medium hover:bg-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                Tutup [ESC]
            </button>

            <div class="absolute bottom-4 inset-x-5 text-white">
                <div class="flex items-center gap-2 text-xs text-stone-200">
                    <span id="modal-dest-category" class="px-2.5 py-0.5 rounded bg-[#0B6E77] text-white font-medium"></span>
                    <span id="modal-dest-location"></span>
                </div>
                <h3 id="modal-dest-name" class="font-serif text-2xl sm:text-3xl font-semibold mt-1"></h3>
            </div>
        </div>

        <!-- Modal Scrollable Content -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5 text-sm text-stone-700">
            <p id="modal-dest-desc" class="leading-relaxed text-stone-800"></p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-white border border-stone-200/90 text-xs tabular-nums">
                <div>
                    <span class="text-stone-500 block">Harga Tiket</span>
                    <strong id="modal-dest-price" class="text-base text-[#C2410C] font-bold"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Jam Operasional</span>
                    <strong id="modal-dest-hours" class="text-stone-900"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Rating Wisatawan</span>
                    <strong id="modal-dest-rating" class="text-[#D97706]"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Koordinat GPS</span>
                    <strong id="modal-dest-coords" class="font-mono text-stone-800"></strong>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Fasilitas Lapangan Tersedia</h4>
                <div id="modal-dest-facilities" class="flex flex-wrap gap-1.5"></div>
            </div>

            <div class="p-4 rounded-xl bg-stone-200/60 border border-stone-300/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xs text-stone-700">
                    <strong class="text-stone-900 block">Endpoint Detail REST API Provider IndWalk:</strong>
                    <code id="modal-dest-api-url" class="font-mono text-[#0B6E77]"></code>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button"
                            id="modal-jump-partner-btn"
                            class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#0B6E77] hover:bg-[#08555C] transition-colors">
                        Lihat Kuliner &amp; Penginapan Sekitar
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const destinationsData = <?= json_encode($destinations, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const partnerData = <?= json_encode($partnerEcosystem, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        // -----------------------------------------------------------------
        // 1. Live Destination Catalog Filtering (Search, City, Category, Price)
        // -----------------------------------------------------------------
        const filterForm = document.getElementById('destination-filter-form');
        const inputQ = document.getElementById('filter-q');
        const selectCity = document.getElementById('filter-city');
        const selectCategory = document.getElementById('filter-category');
        const selectPrice = document.getElementById('filter-price');
        const resetBtn = document.getElementById('filter-reset-btn');
        const emptyResetBtn = document.getElementById('empty-reset-btn');
        const cards = Array.from(document.querySelectorAll('.destination-card'));
        const countStatus = document.getElementById('catalog-count-status');
        const emptyState = document.getElementById('catalog-empty-state');
        const quickCatPills = Array.from(document.querySelectorAll('.quick-cat-pill'));

        function syncQuickPills(activeCategory) {
            quickCatPills.forEach(function (pill) {
                const isMatch = pill.getAttribute('data-quick-cat') === activeCategory;
                pill.className = isMatch
                    ? 'quick-cat-pill px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0B6E77] text-white transition-colors'
                    : 'quick-cat-pill px-3 py-1.5 rounded-lg text-xs font-medium bg-stone-200/70 text-stone-700 hover:bg-stone-300/70 transition-colors';
            });
        }

        function applyCatalogFilter() {
            const q = (inputQ ? inputQ.value : '').trim().toLowerCase();
            const city = selectCity ? selectCity.value : '';
            const category = selectCategory ? selectCategory.value : '';
            const maxPrice = selectPrice && selectPrice.value ? parseInt(selectPrice.value, 10) : null;

            let visibleCount = 0;

            cards.forEach(function (card) {
                const cardName = card.getAttribute('data-name') || '';
                const cardCity = card.getAttribute('data-city') || '';
                const cardCategory = card.getAttribute('data-category') || '';
                const cardPrice = parseInt(card.getAttribute('data-price') || '0', 10);

                const matchQ = !q || cardName.includes(q) || cardCity.toLowerCase().includes(q);
                const matchCity = !city || cardCity === city;
                const matchCategory = !category || cardCategory === category;
                const matchPrice = !maxPrice || cardPrice <= maxPrice;

                if (matchQ && matchCity && matchCategory && matchPrice) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });

            if (countStatus) {
                countStatus.innerHTML = 'Menampilkan <strong class="text-stone-900">' + visibleCount + '</strong> dari <strong>' + cards.length + '</strong> destinasi kurasi';
            }

            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount > 0);
            }

            syncQuickPills(category);
        }

        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                applyCatalogFilter();
                const catalogEl = document.getElementById('eksplorasi-destinasi');
                if (catalogEl) catalogEl.scrollIntoView({ behavior: 'smooth' });
            });
        }

        [inputQ, selectCity, selectCategory, selectPrice].forEach(function (el) {
            if (!el) return;
            el.addEventListener('input', applyCatalogFilter);
            el.addEventListener('change', applyCatalogFilter);
        });

        function resetAllFilters() {
            if (inputQ) inputQ.value = '';
            if (selectCity) selectCity.value = '';
            if (selectCategory) selectCategory.value = '';
            if (selectPrice) selectPrice.value = '';
            applyCatalogFilter();
        }

        if (resetBtn) resetBtn.addEventListener('click', resetAllFilters);
        if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetAllFilters);

        // Quick Category Pills & Taxonomy Ledger Buttons
        quickCatPills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                const cat = pill.getAttribute('data-quick-cat') || '';
                if (selectCategory) selectCategory.value = cat;
                applyCatalogFilter();
            });
        });

        document.querySelectorAll('.category-ledger-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const cat = btn.getAttribute('data-category-trigger') || '';
                if (selectCategory) selectCategory.value = cat;
                applyCatalogFilter();
                const gridEl = document.getElementById('destinations-grid');
                if (gridEl) gridEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        document.querySelectorAll('.footer-cat-trigger').forEach(function (link) {
            link.addEventListener('click', function () {
                const cat = link.getAttribute('data-footer-category') || '';
                if (selectCategory) selectCategory.value = cat;
                applyCatalogFilter();
            });
        });

        // Initial filter application if query params were passed
        applyCatalogFilter();

        // -----------------------------------------------------------------
        // 2. Interactive Partner REST API Simulator (City & Service Tabs)
        // -----------------------------------------------------------------
        let activePartnerCity = 'Magelang';
        let activeServiceTab = 'all';

        const cityTabs = Array.from(document.querySelectorAll('.partner-city-tab'));
        const cityPanels = Array.from(document.querySelectorAll('.partner-city-panel'));
        const serviceTabs = Array.from(document.querySelectorAll('.partner-service-tab'));
        const liveEndpoint = document.getElementById('api-live-endpoint');
        const liveAnchor = document.getElementById('api-live-anchor');
        const liveLatency = document.getElementById('api-live-latency');
        const liveCoords = document.getElementById('api-live-coords');
        const jsonInspectorPanel = document.getElementById('json-inspector-panel');
        const jsonInspectorCode = document.getElementById('json-inspector-code');
        const toggleJsonBtn = document.getElementById('toggle-json-inspector');
        const toggleJsonLabel = document.getElementById('toggle-json-label');

        function updateJsonInspector() {
            if (!jsonInspectorCode || !partnerData[activePartnerCity]) return;
            const cityPayload = partnerData[activePartnerCity];
            const payloadObj = {
                status: 'success',
                consumer: 'IndWalk PartnerApiService',
                query: {
                    city: activePartnerCity,
                    service_filter: activeServiceTab
                },
                fallback_mode: true,
                source: cityPayload.source,
                latency_ms: cityPayload.latency_ms,
                anchor_destination: cityPayload.anchor_spot,
                data: activeServiceTab === 'all'
                    ? {
                        kuliner: cityPayload.kuliner,
                        penginapan: cityPayload.penginapan,
                        transportasi: cityPayload.transportasi
                    }
                    : {
                        [activeServiceTab]: cityPayload[activeServiceTab] || []
                    }
            };
            jsonInspectorCode.textContent = JSON.stringify(payloadObj, null, 2);
        }

        function selectPartnerCity(cityName) {
            if (!partnerData[cityName]) return;
            activePartnerCity = cityName;
            const info = partnerData[cityName];

            cityTabs.forEach(function (tab) {
                const isSelected = tab.getAttribute('data-partner-city') === cityName;
                tab.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                tab.className = isSelected
                    ? 'partner-city-tab text-left px-3.5 py-3 rounded-xl border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] bg-[#0B6E77] text-white border-[#0B6E77]'
                    : 'partner-city-tab text-left px-3.5 py-3 rounded-xl border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] bg-white text-stone-800 border-stone-300/90 hover:border-[#0B6E77]/60';
                const sub = tab.querySelector('.partner-city-sub');
                if (sub) {
                    sub.className = isSelected
                        ? 'partner-city-sub text-[11px] truncate mt-0.5 text-stone-100'
                        : 'partner-city-sub text-[11px] truncate mt-0.5 text-stone-500';
                }
            });

            cityPanels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.getAttribute('data-city-panel') !== cityName);
            });

            if (liveEndpoint) {
                liveEndpoint.textContent = '/api/v1/partner-discovery?city=' + encodeURIComponent(cityName);
            }
            if (liveAnchor) liveAnchor.textContent = info.anchor_spot;
            if (liveLatency) liveLatency.textContent = info.latency_ms + ' ms';
            if (liveCoords) liveCoords.textContent = info.coordinates;

            updateJsonInspector();
        }

        function selectServiceTab(serviceKey) {
            activeServiceTab = serviceKey;

            serviceTabs.forEach(function (tab) {
                const isSelected = tab.getAttribute('data-service-tab') === serviceKey;
                tab.className = isSelected
                    ? 'partner-service-tab px-3.5 py-2 rounded-lg text-xs font-semibold bg-[#2D5A36] text-white transition-colors flex items-center gap-1.5'
                    : 'partner-service-tab px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5';
            });

            cityPanels.forEach(function (panel) {
                const gridContainer = panel.querySelector('.grid');
                const cols = Array.from(panel.querySelectorAll('.partner-service-col'));

                if (serviceKey === 'all') {
                    if (gridContainer) {
                        gridContainer.className = 'grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-stone-200/90';
                    }
                    cols.forEach(function (col) {
                        col.classList.remove('hidden');
                    });
                } else {
                    if (gridContainer) {
                        gridContainer.className = 'grid grid-cols-1 gap-6';
                    }
                    cols.forEach(function (col) {
                        col.classList.toggle('hidden', col.getAttribute('data-service-col') !== serviceKey);
                    });
                }
            });

            updateJsonInspector();
        }

        cityTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                selectPartnerCity(tab.getAttribute('data-partner-city'));
            });
        });

        serviceTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                selectServiceTab(tab.getAttribute('data-service-tab'));
            });
        });

        document.querySelectorAll('.jump-partner-city-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetCity = btn.getAttribute('data-jump-city');
                selectPartnerCity(targetCity);
                const sectionEl = document.getElementById('integrasi-api-mitra');
                if (sectionEl) sectionEl.scrollIntoView({ behavior: 'smooth' });
            });
        });

        document.querySelectorAll('.footer-city-trigger').forEach(function (link) {
            link.addEventListener('click', function () {
                const targetCity = link.getAttribute('data-footer-city');
                selectPartnerCity(targetCity);
            });
        });

        if (toggleJsonBtn && jsonInspectorPanel) {
            toggleJsonBtn.addEventListener('click', function () {
                const isHidden = jsonInspectorPanel.classList.contains('hidden');
                jsonInspectorPanel.classList.toggle('hidden', !isHidden);
                toggleJsonBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                if (toggleJsonLabel) {
                    toggleJsonLabel.textContent = isHidden ? 'Sembunyikan Payload JSON' : 'Tampilkan Payload JSON';
                }
                updateJsonInspector();
            });
        }

        updateJsonInspector();

        // -----------------------------------------------------------------
        // 3. Interactive Destination Detail Modal
        // -----------------------------------------------------------------
        const modal = document.getElementById('destination-detail-modal');
        const closeModalBtn = document.getElementById('close-destination-modal');
        const modalImg = document.getElementById('modal-dest-img');
        const modalCategory = document.getElementById('modal-dest-category');
        const modalLocation = document.getElementById('modal-dest-location');
        const modalName = document.getElementById('modal-dest-name');
        const modalDesc = document.getElementById('modal-dest-desc');
        const modalPrice = document.getElementById('modal-dest-price');
        const modalHours = document.getElementById('modal-dest-hours');
        const modalRating = document.getElementById('modal-dest-rating');
        const modalCoords = document.getElementById('modal-dest-coords');
        const modalFacilities = document.getElementById('modal-dest-facilities');
        const modalApiUrl = document.getElementById('modal-dest-api-url');
        const modalJumpPartnerBtn = document.getElementById('modal-jump-partner-btn');

        let currentModalCity = 'Magelang';

        function openDestinationModal(destId) {
            const dest = destinationsData.find(function (d) {
                return String(d.id) === String(destId);
            });
            if (!dest || !modal) return;

            currentModalCity = dest.city;
            modalImg.src = dest.thumbnail;
            modalImg.alt = dest.name;
            modalCategory.textContent = dest.category_name;
            modalLocation.textContent = dest.city + ', ' + dest.province + ' · ' + dest.address;
            modalName.textContent = dest.name;
            modalDesc.textContent = dest.description;
            modalPrice.textContent = 'Rp ' + Number(dest.ticket_price).toLocaleString('id-ID') + ' / org';
            modalHours.textContent = dest.open_time + ' – ' + dest.close_time + ' (' + dest.estimated_duration + ')';
            modalRating.textContent = '★ ' + Number(dest.rating_avg).toFixed(2) + ' (' + Number(dest.review_count).toLocaleString('id-ID') + ' ulasan)';
            modalCoords.textContent = Number(dest.latitude).toFixed(4) + ', ' + Number(dest.longitude).toFixed(4);
            modalApiUrl.textContent = 'GET /api/v1/destinations/' + dest.slug;

            modalFacilities.innerHTML = '';
            (dest.facilities || []).forEach(function (fac) {
                const span = document.createElement('span');
                span.className = 'px-2.5 py-1 rounded-md text-xs font-medium bg-white border border-stone-200 text-stone-800';
                span.textContent = fac;
                modalFacilities.appendChild(span);
            });

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDestinationModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.open-destination-modal-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openDestinationModal(btn.getAttribute('data-open-destination'));
            });
        });

        if (closeModalBtn) closeModalBtn.addEventListener('click', closeDestinationModal);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeDestinationModal();
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDestinationModal();
        });

        if (modalJumpPartnerBtn) {
            modalJumpPartnerBtn.addEventListener('click', function () {
                closeDestinationModal();
                selectPartnerCity(currentModalCity);
                const sectionEl = document.getElementById('integrasi-api-mitra');
                if (sectionEl) sectionEl.scrollIntoView({ behavior: 'smooth' });
            });
        }
    })();
</script>
<?= $this->endSection() ?>
