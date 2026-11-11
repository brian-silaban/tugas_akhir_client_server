<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
/**
 * Menentukan warna badge kategori sesuai palet warna resmi IndWalk
 */
$gayaBadgeKategori = static function (string $slugKategori): string {
    if ($slugKategori === 'cagar-budaya') {
        return 'bg-[#2D5A36] text-stone-50';
    }
    if ($slugKategori === 'pegunungan-geopark') {
        return 'bg-[#0B6E77] text-stone-50';
    }
    if ($slugKategori === 'wisata-bahari') {
        return 'bg-[#08555C] text-stone-50';
    }
    if ($slugKategori === 'hutan-air-terjun') {
        return 'bg-[#6A8E3F] text-stone-950 font-semibold';
    }

    return 'bg-stone-800 text-stone-50';
};

// Menyiapkan destinasi unggulan untuk bagian Hero dan Bento Showcase
$destinasiUtama   = $daftarDestinasi[2]; // Pulau Padar, Taman Nasional Komodo
$bentoUtama       = $daftarDestinasi[0]; // Candi Borobudur
$bentoPendampingA = $daftarDestinasi[1]; // Kawah Ijen
$bentoPendampingB = $daftarDestinasi[4]; // Taman Nasional Bromo Tengger Semeru
?>

<!-- =====================================================================
     BAGIAN 1: EDITORIAL SPLIT & PENCARIAN UTAMA (HERO)
     ===================================================================== -->
<section class="relative pt-8 pb-14 lg:pt-12 lg:pb-20 border-b border-stone-200/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            <!-- Kolom Kiri: Judul Editorial, Deskripsi & Statistik Ringkas -->
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

                <!-- Baris Metrik Utama -->
                <div class="pt-3 pb-1 border-y border-stone-300/80 grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-0 sm:divide-x sm:divide-stone-300/80 tabular-nums">
                    <div class="sm:pr-5 py-2">
                        <div class="font-serif text-2xl sm:text-3xl font-bold text-[#0B6E77]"><?= esc((string) count($daftarDestinasi)) ?> Lokasi</div>
                        <div class="text-xs font-medium text-stone-600 mt-0.5">Destinasi Prioritas Nasional</div>
                    </div>
                    <div class="sm:px-5 py-2">
                        <div class="font-serif text-2xl sm:text-3xl font-bold text-[#2D5A36]"><?= esc((string) count($daftarKategori)) ?> Kategori</div>
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

                <!-- Pasangan Tombol Aksi: 1 Tombol Utama Solid + 1 Tombol Sekunder Outline -->
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

            <!-- Kolom Kanan: Kartu Sorotan Utama ($destinasiUtama) -->
            <div class="lg:col-span-5">
                <article class="group relative rounded-2xl overflow-hidden border border-stone-200/80 bg-stone-900">
                    <div class="aspect-[4/3] sm:aspect-[16/11] lg:aspect-[4/3.4] w-full overflow-hidden">
                        <img src="<?= esc($destinasiUtama['foto_utama']) ?>"
                             alt="<?= esc($destinasiUtama['nama']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    </div>

                    <!-- Lapisan Gelap (Dark Scrim) untuk Kontras Teks di Atas Foto -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/15"></div>

                    <!-- Bagian Atas Kartu: Badge Kategori + Koordinat Garis Lintang & Bujur -->
                    <div class="absolute top-4 inset-x-4 flex items-center justify-between gap-2">
                        <span class="px-3 py-1 rounded-lg text-xs font-medium bg-[#0B6E77] text-white">
                            <?= esc($destinasiUtama['nama_kategori']) ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-black/70 text-stone-100 backdrop-blur-sm tabular-nums">
                            <?= esc(number_format((float) $destinasiUtama['garis_lintang'], 4)) ?>, <?= esc(number_format((float) $destinasiUtama['garis_bujur'], 4)) ?>
                        </span>
                    </div>

                    <!-- Bagian Bawah Kartu: Informasi Destinasi Utama -->
                    <div class="absolute bottom-0 inset-x-0 p-5 sm:p-6 text-white space-y-3">
                        <div class="flex items-center gap-2 text-xs font-medium text-stone-200">
                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span><?= esc($destinasiUtama['kota']) ?>, <?= esc($destinasiUtama['provinsi']) ?></span>
                            <span class="text-white/40">·</span>
                            <span class="text-amber-300 font-semibold tabular-nums">★ <?= esc(number_format((float) $destinasiUtama['rata_rating'], 2)) ?> (<?= esc(number_format((int) $destinasiUtama['jumlah_ulasan'], 0, ',', '.')) ?>)</span>
                        </div>

                        <h2 class="font-serif text-2xl font-semibold text-white leading-snug">
                            <?= esc($destinasiUtama['nama']) ?>
                        </h2>

                        <p class="text-xs sm:text-sm text-stone-200 line-clamp-2 leading-relaxed">
                            <?= esc($destinasiUtama['deskripsi']) ?>
                        </p>

                        <div class="pt-3 border-t border-white/20 flex items-center justify-between gap-4 tabular-nums">
                            <div>
                                <span class="block text-[11px] uppercase tracking-wider text-stone-300">Tiket Masuk Resmi</span>
                                <span class="text-lg font-bold text-white">
                                    Rp <?= esc(number_format((int) $destinasiUtama['harga_tiket'], 0, ',', '.')) ?>
                                    <span class="text-xs font-normal text-stone-300">/ orang</span>
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[11px] uppercase tracking-wider text-stone-300">Jam Operasional</span>
                                <span class="text-xs font-medium text-stone-100">
                                    <?= esc($destinasiUtama['jam_buka']) ?> – <?= esc($destinasiUtama['jam_tutup']) ?> WITA
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

        </div>

        <!-- Bar Pencarian & Penyaringan Interaktif -->
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
                    Parameter Sinkron dengan Endpoint: GET /api/v1/destinasi?kota=&amp;kategori=&amp;harga_maksimal=
                </span>
            </div>

            <form id="form-filter-destinasi" action="<?= base_url('/') ?>#eksplorasi-destinasi" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">

                <!-- Input Kata Kunci -->
                <div class="lg:col-span-3">
                    <label for="filter-kata-kunci" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                        Nama Destinasi / Kata Kunci
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="filter-kata-kunci"
                               name="kata_kunci"
                               value="<?= esc($filterPencarian['kata_kunci'] ?? '') ?>"
                               placeholder="Mis. Borobudur, Kawah, Sabana..."
                               class="w-full pl-9 pr-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 placeholder:text-stone-500 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors">
                        <svg class="w-4 h-4 text-stone-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                </div>

                <!-- Pilihan Kota (?kota=) -->
                <div class="lg:col-span-3">
                    <label for="filter-kota" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                        Kota / Kabupaten (`?kota=`)
                    </label>
                    <select id="filter-kota"
                            name="kota"
                            class="w-full px-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors">
                        <option value="">Semua Kota (6 Kota)</option>
                        <?php foreach ($daftarKota as $namaKota): ?>
                            <?php $kotaTerpilih = (($filterPencarian['kota'] ?? '') === $namaKota); ?>
                            <option value="<?= esc($namaKota) ?>" <?= $kotaTerpilih ? 'selected' : '' ?>>
                                <?= esc($namaKota) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pilihan Kategori (?kategori=) -->
                <div class="lg:col-span-3">
                    <label for="filter-kategori" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                        Kategori Wisata (`?kategori=`)
                    </label>
                    <select id="filter-kategori"
                            name="kategori"
                            class="w-full px-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors">
                        <option value="">Semua Kategori (4 Zona)</option>
                        <?php foreach ($daftarKategori as $itemKategori): ?>
                            <?php $kategoriTerpilih = (($filterPencarian['kategori'] ?? '') === $itemKategori['slug']); ?>
                            <option value="<?= esc($itemKategori['slug']) ?>" <?= $kategoriTerpilih ? 'selected' : '' ?>>
                                <?= esc($itemKategori['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pilihan Batas Harga Tiket (?harga_maksimal=) & Tombol Aksi -->
                <div class="lg:col-span-3 flex flex-col sm:flex-row lg:flex-col xl:flex-row gap-2.5 items-stretch xl:items-end">
                    <div class="flex-1">
                        <label for="filter-harga" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">
                            Batas Tarif Tiket
                        </label>
                        <select id="filter-harga"
                                name="harga_maksimal"
                                class="w-full px-3 py-2.5 rounded-lg text-sm text-stone-900 bg-[#FAF8F5] border border-stone-300 focus:bg-white focus:border-[#0B6E77] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77]/30 transition-colors tabular-nums">
                            <option value="">Semua Tarif</option>
                            <option value="20000" <?= ($filterPencarian['harga_maksimal'] ?? '') === '20000' ? 'selected' : '' ?>>&le; Rp 20.000</option>
                            <option value="35000" <?= ($filterPencarian['harga_maksimal'] ?? '') === '35000' ? 'selected' : '' ?>>&le; Rp 35.000</option>
                            <option value="50000" <?= ($filterPencarian['harga_maksimal'] ?? '') === '50000' ? 'selected' : '' ?>>&le; Rp 50.000</option>
                            <option value="150000" <?= ($filterPencarian['harga_maksimal'] ?? '') === '150000' ? 'selected' : '' ?>>&le; Rp 150.000</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="flex-1 xl:flex-initial px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] active:translate-y-px transition-all">
                            Terapkan
                        </button>
                        <button type="button"
                                id="tombol-reset-filter"
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
     BAGIAN 2: INDEKS 4 KATEGORI NUSANTARA
     ===================================================================== -->
<section id="indeks-kategori" class="py-14 lg:py-16 border-b border-stone-200/90 bg-[#F4F1EA]/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-mono font-semibold uppercase tracking-wider text-[#2D5A36]">
                    Taksonomi Eksplorasi · Tabel `kategori`
                </span>
                <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-stone-900 mt-1 tracking-tight">
                    4 Zona Lanskap &amp; Warisan Budaya
                </h2>
            </div>
            <p class="text-sm text-stone-600 max-w-md">
                Pilih salah satu zona di bawah untuk menyaring katalog destinasi secara langsung berdasarkan karakteristik geografis dan historisnya.
            </p>
        </div>

        <div class="rounded-2xl bg-white border border-stone-200/90 divide-y md:divide-y-0 md:grid md:grid-cols-2 lg:grid-cols-4 md:divide-x divide-stone-200/90 overflow-hidden">
            <?php foreach ($daftarKategori as $kategori): ?>
                <button type="button"
                        data-pemicu-kategori="<?= esc($kategori['slug']) ?>"
                        class="tombol-indeks-kategori group text-left p-6 flex flex-col justify-between h-full hover:bg-[#FAF8F5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0B6E77] active:bg-stone-100 transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-4">
                            <span class="font-mono text-xs font-semibold px-2.5 py-1 rounded-md bg-stone-100 text-stone-700 group-hover:bg-[#0B6E77] group-hover:text-white transition-colors tabular-nums">
                                ZONA <?= esc($kategori['kode']) ?>
                            </span>
                            <span class="font-mono text-xs text-stone-500 tabular-nums">
                                <?= esc((string) $kategori['jumlah_destinasi']) ?> Destinasi
                            </span>
                        </div>

                        <h3 class="font-serif text-xl font-semibold text-stone-900 group-hover:text-[#0B6E77] transition-colors">
                            <?= esc($kategori['nama']) ?>
                        </h3>

                        <p class="text-sm text-stone-600 mt-2 leading-relaxed">
                            <?= esc($kategori['deskripsi']) ?>
                        </p>
                    </div>

                    <div class="pt-5 mt-5 border-t border-stone-200/80 space-y-1.5 w-full tabular-nums">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-stone-500">Cakupan Wilayah</span>
                            <span class="font-medium text-stone-800"><?= esc($kategori['wilayah_unggulan']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-stone-500">Rentang Tiket</span>
                            <span class="font-semibold text-[#2D5A36]"><?= esc($kategori['rentang_harga']) ?></span>
                        </div>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- =====================================================================
     BAGIAN 3: BENTO SHOWCASE ASIMETRIS & KATALOG 6 DESTINASI
     ===================================================================== -->
<section id="eksplorasi-destinasi" class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        <!-- Sub-Bagian A: Bento Grid Asimetris (1 Kartu Utama Besar + 2 Kartu Pendamping) -->
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

                <!-- Kartu Bento Utama (7 Kolom) -->
                <article class="lg:col-span-7 rounded-2xl bg-white border border-stone-200/90 overflow-hidden flex flex-col justify-between group">
                    <div>
                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-stone-900">
                            <img src="<?= esc($bentoUtama['foto_utama']) ?>"
                                 alt="<?= esc($bentoUtama['nama']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                            <div class="absolute top-4 left-4 right-4 flex items-center justify-between gap-2">
                                <span class="px-3 py-1 rounded-lg text-xs font-medium <?= esc($gayaBadgeKategori($bentoUtama['slug_kategori'])) ?>">
                                    <?= esc($bentoUtama['nama_kategori']) ?>
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono bg-black/70 text-stone-100 tabular-nums">
                                    <?= esc($bentoUtama['catatan_editorial']) ?>
                                </span>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-end justify-between gap-2 text-white">
                                <div>
                                    <div class="text-xs font-medium text-stone-200 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <span><?= esc($bentoUtama['kota']) ?>, <?= esc($bentoUtama['provinsi']) ?></span>
                                    </div>
                                    <h3 class="font-serif text-2xl sm:text-3xl font-semibold mt-0.5">
                                        <?= esc($bentoUtama['nama']) ?>
                                    </h3>
                                </div>
                                <div class="px-3 py-1.5 rounded-lg bg-black/75 text-amber-300 text-xs font-semibold tabular-nums">
                                    ★ <?= esc(number_format((float) $bentoUtama['rata_rating'], 2)) ?> (<?= esc(number_format((int) $bentoUtama['jumlah_ulasan'], 0, ',', '.')) ?> ulasan)
                                </div>
                            </div>
                        </div>

                        <div class="p-6 space-y-4">
                            <p class="text-sm sm:text-base text-stone-700 leading-relaxed">
                                <?= esc($bentoUtama['deskripsi']) ?>
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 border-t border-stone-200/80 text-xs tabular-nums">
                                <div>
                                    <span class="text-stone-500 block">Jam Operasional</span>
                                    <span class="font-semibold text-stone-900 mt-0.5 block"><?= esc($bentoUtama['jam_buka']) ?> – <?= esc($bentoUtama['jam_tutup']) ?> WIB</span>
                                </div>
                                <div>
                                    <span class="text-stone-500 block">Durasi Kunjungan</span>
                                    <span class="font-semibold text-stone-900 mt-0.5 block"><?= esc($bentoUtama['estimasi_durasi']) ?></span>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <span class="text-stone-500 block">Titik Koordinat</span>
                                    <span class="font-mono font-medium text-stone-800 mt-0.5 block"><?= esc(number_format((float) $bentoUtama['garis_lintang'], 4)) ?>, <?= esc(number_format((float) $bentoUtama['garis_bujur'], 4)) ?></span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <?php foreach ($bentoUtama['fasilitas'] as $namaFasilitas): ?>
                                    <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-stone-100 text-stone-700">
                                        <?= esc($namaFasilitas) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-[#FAF8F5] border-t border-stone-200/80 flex flex-wrap items-center justify-between gap-4 tabular-nums">
                        <div>
                            <span class="text-xs text-stone-500 block">Harga Tiket Masuk</span>
                            <span class="text-xl font-bold text-stone-900">
                                Rp <?= esc(number_format((int) $bentoUtama['harga_tiket'], 0, ',', '.')) ?>
                                <span class="text-xs font-normal text-stone-600">/ orang</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <button type="button"
                                    data-lompat-kota="<?= esc($bentoUtama['kota']) ?>"
                                    class="tombol-lompat-kota-mitra px-3.5 py-2 rounded-lg text-xs font-semibold text-[#0B6E77] border border-[#0B6E77]/40 bg-transparent hover:bg-[#0B6E77]/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-[#0B6E77]/20 transition-colors">
                                Cek Mitra <?= esc($bentoUtama['kota']) ?>
                            </button>
                            <button type="button"
                                    data-buka-destinasi="<?= esc((string) $bentoUtama['id']) ?>"
                                    class="tombol-buka-modal-destinasi px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] active:translate-y-px transition-all">
                                Rincian &amp; Simulasi Tiket
                            </button>
                        </div>
                    </div>
                </article>

                <!-- 2 Kartu Bento Pendamping (5 Kolom) -->
                <div class="lg:col-span-5 flex flex-col justify-between gap-6">
                    <?php foreach ([$bentoPendampingA, $bentoPendampingB] as $destinasiPendamping): ?>
                        <article class="rounded-2xl bg-white border border-stone-200/90 overflow-hidden flex flex-col justify-between flex-1 group">
                            <div class="p-5 sm:p-6 space-y-3.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-medium <?= esc($gayaBadgeKategori($destinasiPendamping['slug_kategori'])) ?>">
                                        <?= esc($destinasiPendamping['nama_kategori']) ?>
                                    </span>
                                    <span class="text-xs font-semibold text-[#D97706] tabular-nums">
                                        ★ <?= esc(number_format((float) $destinasiPendamping['rata_rating'], 2)) ?>
                                        <span class="text-stone-500 font-normal">(<?= esc(number_format((int) $destinasiPendamping['jumlah_ulasan'], 0, ',', '.')) ?>)</span>
                                    </span>
                                </div>

                                <div class="flex gap-4 items-start">
                                    <div class="w-28 sm:w-32 aspect-[4/3] rounded-xl overflow-hidden shrink-0 bg-stone-200">
                                        <img src="<?= esc($destinasiPendamping['foto_utama']) ?>"
                                             alt="<?= esc($destinasiPendamping['nama']) ?>"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                                    </div>
                                    <div class="space-y-1 min-w-0">
                                        <div class="text-xs font-medium text-stone-500">
                                            <?= esc($destinasiPendamping['kota']) ?>, <?= esc($destinasiPendamping['provinsi']) ?>
                                        </div>
                                        <h3 class="font-serif text-lg sm:text-xl font-semibold text-stone-900 leading-snug">
                                            <?= esc($destinasiPendamping['nama']) ?>
                                        </h3>
                                        <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed">
                                            <?= esc($destinasiPendamping['deskripsi']) ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-stone-200/80 flex flex-wrap items-center justify-between gap-2 text-xs text-stone-600 tabular-nums">
                                    <span>Jam Buka: <strong class="text-stone-800"><?= esc($destinasiPendamping['jam_buka']) ?> – <?= esc($destinasiPendamping['jam_tutup']) ?> WIB</strong></span>
                                    <span class="font-mono text-[11px] text-stone-500"><?= esc($destinasiPendamping['catatan_editorial']) ?></span>
                                </div>
                            </div>

                            <div class="px-5 py-3.5 bg-[#FAF8F5] border-t border-stone-200/80 flex items-center justify-between gap-3 tabular-nums">
                                <div class="text-base font-bold text-stone-900">
                                    Rp <?= esc(number_format((int) $destinasiPendamping['harga_tiket'], 0, ',', '.')) ?>
                                    <span class="text-xs font-normal text-stone-500">/ orang</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            data-lompat-kota="<?= esc($destinasiPendamping['kota']) ?>"
                                            class="tombol-lompat-kota-mitra px-3 py-1.5 rounded-lg text-xs font-medium text-stone-700 border border-stone-300 hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] transition-colors">
                                        API <?= esc($destinasiPendamping['kota']) ?>
                                    </button>
                                    <button type="button"
                                            data-buka-destinasi="<?= esc((string) $destinasiPendamping['id']) ?>"
                                            class="tombol-buka-modal-destinasi px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#2D5A36] hover:bg-[#1B4332] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2D5A36] transition-colors">
                                        Lihat Detail
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

        <!-- Sub-Bagian B: Katalog Lengkap 6 Destinasi dengan Filter Cepat -->
        <div class="pt-6 border-t border-stone-200/90">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="font-serif text-2xl font-semibold text-stone-900">
                        Katalog Lengkap Destinasi Kurasi
                    </h3>
                    <p id="status-jumlah-katalog" class="text-sm text-stone-600 mt-0.5 tabular-nums">
                        Menampilkan seluruh <strong class="text-stone-900"><?= esc((string) count($daftarDestinasi)) ?></strong> destinasi dari <code class="font-mono text-xs bg-stone-200/70 px-1.5 py-0.5 rounded">IndWalkSeeder.php</code>
                    </p>
                </div>

                <!-- Tombol Pill Filter Cepat Kategori -->
                <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="Filter cepat kategori">
                    <button type="button"
                            data-kategori-cepat=""
                            class="pill-kategori-cepat px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0B6E77] text-white transition-colors">
                        Semua (6)
                    </button>
                    <?php foreach ($daftarKategori as $pillKategori): ?>
                        <button type="button"
                                data-kategori-cepat="<?= esc($pillKategori['slug']) ?>"
                                class="pill-kategori-cepat px-3 py-1.5 rounded-lg text-xs font-medium bg-stone-200/70 text-stone-700 hover:bg-stone-300/70 transition-colors">
                            <?= esc($pillKategori['nama']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tampilan Kosong Jika Filter Tidak Menemukan Hasil -->
            <div id="status-katalog-kosong" class="hidden rounded-2xl bg-white border border-stone-200 p-10 text-center space-y-3">
                <p class="font-serif text-xl text-stone-800">Tidak ada destinasi yang sesuai dengan kriteria filter.</p>
                <p class="text-sm text-stone-600">Coba ubah kata kunci pencarian, kota, atau batas harga tiket.</p>
                <button type="button" id="tombol-reset-kosong" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-white bg-[#0B6E77] hover:bg-[#08555C]">
                    Tampilkan Semua Destinasi
                </button>
            </div>

            <!-- Grid Kartu Destinasi -->
            <div id="grid-destinasi" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                <?php foreach ($daftarDestinasi as $destinasi): ?>
                    <?php
                    $teksPencarianKartu = strtolower($destinasi['nama'] . ' ' . $destinasi['deskripsi'] . ' ' . $destinasi['alamat']);
                    $daftarFasilitasRingkas = array_slice($destinasi['fasilitas'], 0, 4);
                    $sisaFasilitas = count($destinasi['fasilitas']) - 4;
                    ?>
                    <article class="kartu-destinasi rounded-2xl bg-white border border-stone-200/90 overflow-hidden flex flex-col justify-between h-full group transition-colors hover:border-[#0B6E77]/50"
                             data-nama="<?= esc($teksPencarianKartu) ?>"
                             data-kota="<?= esc($destinasi['kota']) ?>"
                             data-kategori="<?= esc($destinasi['slug_kategori']) ?>"
                             data-harga="<?= esc((string) $destinasi['harga_tiket']) ?>">

                        <div>
                            <!-- Foto Destinasi dengan Lapisan Gelap untuk Kontras Teks -->
                            <div class="relative aspect-[16/10] w-full overflow-hidden bg-stone-900">
                                <img src="<?= esc($destinasi['foto_utama']) ?>"
                                     alt="<?= esc($destinasi['nama']) ?>"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/25 to-transparent"></div>

                                <!-- Badge Kategori & Rating -->
                                <div class="absolute top-3.5 inset-x-3.5 flex items-center justify-between gap-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium <?= esc($gayaBadgeKategori($destinasi['slug_kategori'])) ?>">
                                        <?= esc($destinasi['nama_kategori']) ?>
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-black/75 text-amber-300 backdrop-blur-sm tabular-nums">
                                        ★ <?= esc(number_format((float) $destinasi['rata_rating'], 2)) ?> (<?= esc(number_format((int) $destinasi['jumlah_ulasan'], 0, ',', '.')) ?>)
                                    </span>
                                </div>

                                <!-- Lokasi & Jam Buka di Dalam Gambar -->
                                <div class="absolute bottom-3 inset-x-3.5 flex items-center justify-between text-xs text-stone-100">
                                    <span class="font-medium flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <?= esc($destinasi['kota']) ?>, <?= esc($destinasi['provinsi']) ?>
                                    </span>
                                    <span class="font-mono text-[11px] text-stone-200 tabular-nums">
                                        <?= esc($destinasi['jam_buka']) ?>–<?= esc($destinasi['jam_tutup']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Isi Kartu Destinasi -->
                            <div class="p-5 space-y-3.5">
                                <div>
                                    <h4 class="font-serif text-xl font-semibold text-stone-900 group-hover:text-[#0B6E77] transition-colors leading-snug">
                                        <?= esc($destinasi['nama']) ?>
                                    </h4>
                                    <p class="text-xs text-stone-500 mt-0.5 truncate">
                                        <?= esc($destinasi['alamat']) ?>
                                    </p>
                                </div>

                                <p class="text-sm text-stone-700 leading-relaxed line-clamp-3">
                                    <?= esc($destinasi['deskripsi']) ?>
                                </p>

                                <!-- Daftar Fasilitas -->
                                <div class="pt-2 border-t border-stone-200/80 flex flex-wrap gap-1.5">
                                    <?php foreach ($daftarFasilitasRingkas as $itemFasilitas): ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-700">
                                            <?= esc($itemFasilitas) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if ($sisaFasilitas > 0): ?>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-mono text-stone-500 bg-stone-100 tabular-nums">
                                            +<?= esc((string) $sisaFasilitas) ?> lainnya
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Kartu: Harga Tiket & Tombol Aksi -->
                        <div class="px-5 py-3.5 bg-[#FAF8F5] border-t border-stone-200/80 flex items-center justify-between gap-2 tabular-nums">
                            <div>
                                <span class="block text-[11px] text-stone-500">Harga Tiket</span>
                                <span class="text-base font-bold text-stone-900">
                                    Rp <?= esc(number_format((int) $destinasi['harga_tiket'], 0, ',', '.')) ?>
                                    <span class="text-xs font-normal text-stone-500">/ org</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        data-lompat-kota="<?= esc($destinasi['kota']) ?>"
                                        class="tombol-lompat-kota-mitra px-2.5 py-2 rounded-lg text-xs font-medium text-stone-700 border border-stone-300 bg-transparent hover:bg-stone-200/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] transition-colors">
                                    API Mitra
                                </button>
                                <button type="button"
                                        data-buka-destinasi="<?= esc((string) $destinasi['id']) ?>"
                                        class="tombol-buka-modal-destinasi px-3.5 py-2 rounded-lg text-xs font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] active:translate-y-px transition-all">
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
     BAGIAN 4: SIMULASI INTERAKTIF INTEGRASI REST API KELOMPOK MITRA
     (Kuliner, Penginapan, Transportasi berdasarkan parameter ?kota=)
     ===================================================================== -->
<section id="integrasi-api-mitra" class="py-16 lg:py-20 bg-[#F4F1EA] border-t border-stone-300/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Header Bagian Integrasi API Mitra -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-6 border-b border-stone-300/80">
            <div class="space-y-2 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-[#0B6E77]/10 text-[#0B6E77] text-xs font-mono font-semibold">
                    <span>SIMULASI INTERAKTIF CLIENT-SERVER</span>
                    <span>·</span>
                    <span>App\Libraries\LayananApiMitra</span>
                </div>
                <h2 class="font-serif text-3xl sm:text-4xl font-semibold text-stone-900 tracking-tight">
                    Ekosistem Layanan Mitra Berdasarkan Kota Wisata
                </h2>
                <p class="text-sm sm:text-base text-stone-700 leading-relaxed">
                    Saat wisatawan membuka destinasi di kota tertentu, server <strong class="font-semibold text-stone-900">IndWalk</strong> memanggil endpoint REST API milik kelompok <strong class="font-semibold text-stone-900">Kuliner</strong>, <strong class="font-semibold text-stone-900">Penginapan</strong>, dan <strong class="font-semibold text-stone-900">Transportasi</strong> menggunakan parameter <code class="font-mono text-xs bg-stone-200 px-1.5 py-0.5 rounded">?kota={nama_kota}</code> dengan proteksi <em>Fallback Mock JSON</em> (<code class="font-mono text-xs">timeout: 3 dtk</code>).
                </p>
            </div>

            <!-- Tombol Tampilkan / Sembunyikan Payload JSON -->
            <div class="flex items-center gap-2 self-start lg:self-end">
                <button type="button"
                        id="tombol-inspeksi-json"
                        aria-pressed="false"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-mono font-semibold text-stone-800 border border-stone-400/80 bg-white hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] transition-colors">
                    <svg class="w-4 h-4 text-[#0B6E77]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                    </svg>
                    <span id="label-inspeksi-json">Tampilkan Payload JSON</span>
                </button>
            </div>
        </div>

        <!-- Langkah 1: Pilihan Kota Tujuan (?kota=) -->
        <div class="space-y-2.5">
            <label class="block text-xs font-mono font-semibold uppercase tracking-wider text-stone-600">
                1. Pilih Parameter Kota Tujuan (`?kota=`)
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5" role="tablist" aria-label="Pilih Kota Simulasi API">
                <?php foreach ($ekosistemMitra as $kunciKota => $dataKotaMitra): ?>
                    <?php
                    $adalahKotaAwal = ($kunciKota === 'Magelang');
                    if ($adalahKotaAwal) {
                        $kelasTabKota = 'bg-[#0B6E77] text-white border-[#0B6E77]';
                        $kelasSubTeks = 'text-stone-100';
                    } else {
                        $kelasTabKota = 'bg-white text-stone-800 border-stone-300/90 hover:border-[#0B6E77]/60';
                        $kelasSubTeks = 'text-stone-500';
                    }
                    ?>
                    <button type="button"
                            role="tab"
                            aria-selected="<?= $adalahKotaAwal ? 'true' : 'false' ?>"
                            data-kota-mitra="<?= esc($kunciKota) ?>"
                            class="tab-kota-mitra text-left px-3.5 py-3 rounded-xl border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] <?= $kelasTabKota ?>">
                        <div class="font-semibold text-sm truncate"><?= esc($kunciKota) ?></div>
                        <div class="subteks-kota-mitra text-[11px] truncate mt-0.5 <?= $kelasSubTeks ?>">
                            <?= esc($dataKotaMitra['destinasi_induk']) ?>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panel Status Request & Daftar Rekomendasi Mitra -->
        <div class="rounded-2xl bg-white border border-stone-200/90 overflow-hidden">

            <!-- Bar Status HTTP Request -->
            <div class="px-5 py-4 bg-stone-900 text-stone-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4 font-mono text-xs tabular-nums">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold">HTTP 200 OK</span>
                    <span class="text-stone-400">GET</span>
                    <span id="endpoint-aktif-api" class="text-white font-medium break-all">
                        /api/v1/mitra-ekosistem?kota=Magelang
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-[11px] text-stone-300">
                    <span>Destinasi Induk: <strong id="destinasi-induk-api" class="text-amber-300">Candi Borobudur</strong></span>
                    <span>·</span>
                    <span>Mode: <strong class="text-teal-300">simulasi_fallback (Timeout 3000ms)</strong></span>
                    <span>·</span>
                    <span>Latensi: <strong id="latensi-aktif-api" class="text-white">42 ms</strong></span>
                </div>
            </div>

            <!-- Tab Filter Jenis Layanan Mitra (Semua, Kuliner, Penginapan, Transportasi) -->
            <div class="px-5 py-3.5 bg-[#FAF8F5] border-b border-stone-200/90 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Filter Layanan Mitra">
                    <button type="button"
                            data-tab-layanan="semua"
                            class="tab-layanan-mitra px-3.5 py-2 rounded-lg text-xs font-semibold bg-[#2D5A36] text-white transition-colors">
                        Semua Layanan Mitra (6)
                    </button>
                    <button type="button"
                            data-tab-layanan="kuliner"
                            class="tab-layanan-mitra px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>
                        </svg>
                        <span>API Kuliner (2)</span>
                    </button>
                    <button type="button"
                            data-tab-layanan="penginapan"
                            class="tab-layanan-mitra px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/>
                        </svg>
                        <span>API Penginapan (2)</span>
                    </button>
                    <button type="button"
                            data-tab-layanan="transportasi"
                            class="tab-layanan-mitra px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8 6v6"/><path d="M15 6v6"/><path d="M2 12h19.6"/><path d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/>
                        </svg>
                        <span>API Transportasi (2)</span>
                    </button>
                </div>

                <div class="text-xs font-mono text-stone-500 tabular-nums">
                    Koordinat Simpul: <span id="koordinat-aktif-api" class="text-stone-800 font-medium">-7.6079, 110.2038</span>
                </div>
            </div>

            <!-- Kontainer Kartu Ekosistem Mitra per Kota -->
            <div class="p-5 sm:p-6">
                <?php foreach ($ekosistemMitra as $kunciKota => $paketKota): ?>
                    <?php $tampilkanPanelKota = ($kunciKota === 'Magelang'); ?>
                    <div data-panel-kota="<?= esc($kunciKota) ?>"
                         class="panel-kota-mitra <?= $tampilkanPanelKota ? 'block' : 'hidden' ?>">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-stone-200/90">

                            <!-- Kolom 1: Kelompok Kuliner -->
                            <div data-kolom-layanan="kuliner" class="kolom-layanan-mitra pt-4 md:pt-0 md:pr-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#C2410C]"></span>
                                        <h3 class="font-serif text-lg font-semibold text-stone-900">Kuliner &amp; Rumah Makan</h3>
                                    </div>
                                    <span class="font-mono text-[11px] text-stone-500">:8081/api/kuliner</span>
                                </div>

                                <div class="space-y-4 divide-y divide-stone-200/80">
                                    <?php foreach ($paketKota['kuliner'] as $indeks => $itemKuliner): ?>
                                        <div class="<?= $indeks > 0 ? 'pt-4' : '' ?> space-y-1.5 tabular-nums">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-semibold text-stone-900 text-sm leading-snug">
                                                    <?= esc($itemKuliner['nama']) ?>
                                                </h4>
                                                <span class="text-xs font-semibold text-[#D97706] shrink-0">
                                                    ★ <?= esc(number_format((float) $itemKuliner['rating'], 1)) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-stone-600 leading-relaxed">
                                                <?= esc($itemKuliner['keunggulan']) ?>
                                            </p>
                                            <div class="text-[11px] text-stone-500 space-y-0.5 pt-1">
                                                <div>Jarak: <strong class="text-stone-700"><?= esc($itemKuliner['jarak']) ?></strong></div>
                                                <div>Lokasi: <?= esc($itemKuliner['alamat']) ?></div>
                                                <div>Jam Buka: <?= esc($itemKuliner['jam_operasional']) ?></div>
                                            </div>
                                            <div class="pt-1.5 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">Estimasi Harga</span>
                                                <span class="text-sm font-bold text-[#C2410C]">
                                                    Rp <?= esc(number_format((int) $itemKuliner['harga'], 0, ',', '.')) ?>
                                                    <span class="text-[11px] font-normal text-stone-500">/ <?= esc($itemKuliner['satuan']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Kolom 2: Kelompok Penginapan -->
                            <div data-kolom-layanan="penginapan" class="kolom-layanan-mitra pt-6 md:pt-0 md:px-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#0B6E77]"></span>
                                        <h3 class="font-serif text-lg font-semibold text-stone-900">Penginapan &amp; Homestay</h3>
                                    </div>
                                    <span class="font-mono text-[11px] text-stone-500">:8082/api/penginapan</span>
                                </div>

                                <div class="space-y-4 divide-y divide-stone-200/80">
                                    <?php foreach ($paketKota['penginapan'] as $indeks => $itemPenginapan): ?>
                                        <div class="<?= $indeks > 0 ? 'pt-4' : '' ?> space-y-1.5 tabular-nums">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-semibold text-stone-900 text-sm leading-snug">
                                                    <?= esc($itemPenginapan['nama']) ?>
                                                </h4>
                                                <span class="text-xs font-semibold text-[#D97706] shrink-0">
                                                    ★ <?= esc(number_format((float) $itemPenginapan['rating'], 1)) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-stone-600 leading-relaxed">
                                                <?= esc($itemPenginapan['keunggulan']) ?>
                                            </p>
                                            <div class="text-[11px] text-stone-500 space-y-0.5 pt-1">
                                                <div>Jarak: <strong class="text-stone-700"><?= esc($itemPenginapan['jarak']) ?></strong></div>
                                                <div>Lokasi: <?= esc($itemPenginapan['alamat']) ?></div>
                                                <div>Info: <?= esc($itemPenginapan['jam_operasional']) ?></div>
                                            </div>
                                            <div class="pt-1.5 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">Tarif Menginap</span>
                                                <span class="text-sm font-bold text-[#0B6E77]">
                                                    Rp <?= esc(number_format((int) $itemPenginapan['harga'], 0, ',', '.')) ?>
                                                    <span class="text-[11px] font-normal text-stone-500">/ <?= esc($itemPenginapan['satuan']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Kolom 3: Kelompok Transportasi -->
                            <div data-kolom-layanan="transportasi" class="kolom-layanan-mitra pt-6 md:pt-0 md:pl-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-stone-200 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#2D5A36]"></span>
                                        <h3 class="font-serif text-lg font-semibold text-stone-900">Transportasi &amp; Rute</h3>
                                    </div>
                                    <span class="font-mono text-[11px] text-stone-500">:8083/api/transportasi</span>
                                </div>

                                <div class="space-y-4 divide-y divide-stone-200/80">
                                    <?php foreach ($paketKota['transportasi'] as $indeks => $itemTransportasi): ?>
                                        <div class="<?= $indeks > 0 ? 'pt-4' : '' ?> space-y-1.5 tabular-nums">
                                            <div class="flex items-start justify-between gap-2">
                                                <h4 class="font-semibold text-stone-900 text-sm leading-snug">
                                                    <?= esc($itemTransportasi['nama']) ?>
                                                </h4>
                                                <span class="text-xs font-semibold text-[#D97706] shrink-0">
                                                    ★ <?= esc(number_format((float) $itemTransportasi['rating'], 1)) ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-stone-600 leading-relaxed">
                                                <?= esc($itemTransportasi['keunggulan']) ?>
                                            </p>
                                            <div class="text-[11px] text-stone-500 space-y-0.5 pt-1">
                                                <div>Akses: <strong class="text-stone-700"><?= esc($itemTransportasi['jarak']) ?></strong></div>
                                                <div>Titik Temu: <?= esc($itemTransportasi['alamat']) ?></div>
                                                <div>Jadwal: <?= esc($itemTransportasi['jam_operasional']) ?></div>
                                            </div>
                                            <div class="pt-1.5 flex items-center justify-between">
                                                <span class="text-xs text-stone-500">Tarif Layanan</span>
                                                <span class="text-sm font-bold text-[#2D5A36]">
                                                    Rp <?= esc(number_format((int) $itemTransportasi['harga'], 0, ',', '.')) ?>
                                                    <span class="text-[11px] font-normal text-stone-500">/ <?= esc($itemTransportasi['satuan']) ?></span>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>

                <!-- Panel Inspeksi Payload JSON -->
                <div id="panel-inspeksi-json" class="hidden mt-6 pt-6 border-t border-stone-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-mono font-semibold text-stone-700">
                            Respons JSON Aktual (`LayananApiMitra::ambilRekomendasiBerdasarkanKota`)
                        </span>
                        <span class="text-xs font-mono text-stone-500">Content-Type: application/json; charset=UTF-8</span>
                    </div>
                    <pre id="kode-inspeksi-json" class="rounded-xl bg-stone-950 text-emerald-300 p-4 text-xs font-mono overflow-x-auto leading-relaxed max-h-96 tabular-nums"></pre>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- =====================================================================
     MODAL INTERAKTIF: DETAIL DESTINASI & SIMULASI TIKET
     ===================================================================== -->
<div id="modal-detail-destinasi"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-nama-destinasi"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 sm:p-6 bg-black/65 backdrop-blur-xs">

    <div class="relative w-full max-w-3xl rounded-2xl bg-[#FAF8F5] border border-stone-300 overflow-hidden max-h-[90vh] flex flex-col">

        <!-- Gambar Header Modal -->
        <div class="relative h-56 sm:h-64 w-full bg-stone-900 shrink-0">
            <img id="modal-foto-destinasi" src="" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/20"></div>

            <button type="button"
                    id="tombol-tutup-modal"
                    aria-label="Tutup jendela detail"
                    class="absolute top-4 right-4 px-3 py-1.5 rounded-lg bg-black/70 text-white text-xs font-medium hover:bg-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                Tutup [ESC]
            </button>

            <div class="absolute bottom-4 inset-x-5 text-white">
                <div class="flex items-center gap-2 text-xs text-stone-200">
                    <span id="modal-kategori-destinasi" class="px-2.5 py-0.5 rounded bg-[#0B6E77] text-white font-medium"></span>
                    <span id="modal-lokasi-destinasi"></span>
                </div>
                <h3 id="modal-nama-destinasi" class="font-serif text-2xl sm:text-3xl font-semibold mt-1"></h3>
            </div>
        </div>

        <!-- Konten Detail di Dalam Modal -->
        <div class="p-5 sm:p-6 overflow-y-auto space-y-5 text-sm text-stone-700">
            <p id="modal-deskripsi-destinasi" class="leading-relaxed text-stone-800"></p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-white border border-stone-200/90 text-xs tabular-nums">
                <div>
                    <span class="text-stone-500 block">Harga Tiket</span>
                    <strong id="modal-harga-destinasi" class="text-base text-[#C2410C] font-bold"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Jam Operasional</span>
                    <strong id="modal-jam-destinasi" class="text-stone-900"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Rating Wisatawan</span>
                    <strong id="modal-rating-destinasi" class="text-[#D97706]"></strong>
                </div>
                <div>
                    <span class="text-stone-500 block">Koordinat GPS</span>
                    <strong id="modal-koordinat-destinasi" class="font-mono text-stone-800"></strong>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Fasilitas Lapangan Tersedia</h4>
                <div id="modal-fasilitas-destinasi" class="flex flex-wrap gap-1.5"></div>
            </div>

            <div class="p-4 rounded-xl bg-stone-200/60 border border-stone-300/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xs text-stone-700">
                    <strong class="text-stone-900 block">Endpoint Detail REST API Provider IndWalk:</strong>
                    <code id="modal-url-api-destinasi" class="font-mono text-[#0B6E77]"></code>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button"
                            id="modal-tombol-lompat-mitra"
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
        // Data destinasi dan ekosistem mitra dari Controller PHP
        const dataDestinasi = <?= json_encode($daftarDestinasi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const dataMitra = <?= json_encode($ekosistemMitra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        // -----------------------------------------------------------------
        // 1. Fungsi Filter Katalog Destinasi (Kata Kunci, Kota, Kategori, Harga)
        // -----------------------------------------------------------------
        const formFilter = document.getElementById('form-filter-destinasi');
        const inputKataKunci = document.getElementById('filter-kata-kunci');
        const pilihanKota = document.getElementById('filter-kota');
        const pilihanKategori = document.getElementById('filter-kategori');
        const pilihanHarga = document.getElementById('filter-harga');
        const tombolReset = document.getElementById('tombol-reset-filter');
        const tombolResetKosong = document.getElementById('tombol-reset-kosong');
        const daftarKartuDestinasi = Array.from(document.querySelectorAll('.kartu-destinasi'));
        const teksStatusJumlah = document.getElementById('status-jumlah-katalog');
        const elemenKatalogKosong = document.getElementById('status-katalog-kosong');
        const daftarPillKategori = Array.from(document.querySelectorAll('.pill-kategori-cepat'));

        // Menyelaraskan tampilan tombol pill kategori cepat yang sedang aktif
        function selaraskanPillKategori(kategoriAktif) {
            daftarPillKategori.forEach(function (pill) {
                const nilaiKategoriPill = pill.getAttribute('data-kategori-cepat');
                if (nilaiKategoriPill === kategoriAktif) {
                    pill.className = 'pill-kategori-cepat px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0B6E77] text-white transition-colors';
                } else {
                    pill.className = 'pill-kategori-cepat px-3 py-1.5 rounded-lg text-xs font-medium bg-stone-200/70 text-stone-700 hover:bg-stone-300/70 transition-colors';
                }
            });
        }

        // Menyaring kartu destinasi berdasarkan input pengguna
        function terapkanFilterKatalog() {
            const kataKunci = (inputKataKunci ? inputKataKunci.value : '').trim().toLowerCase();
            const kotaTerpilih = pilihanKota ? pilihanKota.value : '';
            const kategoriTerpilih = pilihanKategori ? pilihanKategori.value : '';
            const hargaMaksimal = (pilihanHarga && pilihanHarga.value) ? parseInt(pilihanHarga.value, 10) : null;

            let jumlahTampil = 0;

            daftarKartuDestinasi.forEach(function (kartu) {
                const namaKartu = kartu.getAttribute('data-nama') || '';
                const kotaKartu = kartu.getAttribute('data-kota') || '';
                const kategoriKartu = kartu.getAttribute('data-kategori') || '';
                const hargaKartu = parseInt(kartu.getAttribute('data-harga') || '0', 10);

                const cocokKataKunci = !kataKunci || namaKartu.includes(kataKunci) || kotaKartu.toLowerCase().includes(kataKunci);
                const cocokKota = !kotaTerpilih || kotaKartu === kotaTerpilih;
                const cocokKategori = !kategoriTerpilih || kategoriKartu === kategoriTerpilih;
                const cocokHarga = !hargaMaksimal || hargaKartu <= hargaMaksimal;

                if (cocokKataKunci && cocokKota && cocokKategori && cocokHarga) {
                    kartu.classList.remove('hidden');
                    jumlahTampil++;
                } else {
                    kartu.classList.add('hidden');
                }
            });

            if (teksStatusJumlah) {
                teksStatusJumlah.innerHTML = 'Menampilkan <strong class="text-stone-900">' + jumlahTampil + '</strong> dari <strong>' + daftarKartuDestinasi.length + '</strong> destinasi kurasi';
            }

            if (elemenKatalogKosong) {
                if (jumlahTampil === 0) {
                    elemenKatalogKosong.classList.remove('hidden');
                } else {
                    elemenKatalogKosong.classList.add('hidden');
                }
            }

            selaraskanPillKategori(kategoriTerpilih);
        }

        if (formFilter) {
            formFilter.addEventListener('submit', function (event) {
                event.preventDefault();
                terapkanFilterKatalog();
                const bagianKatalog = document.getElementById('eksplorasi-destinasi');
                if (bagianKatalog) {
                    bagianKatalog.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }

        [inputKataKunci, pilihanKota, pilihanKategori, pilihanHarga].forEach(function (elemenInput) {
            if (!elemenInput) {
                return;
            }
            elemenInput.addEventListener('input', terapkanFilterKatalog);
            elemenInput.addEventListener('change', terapkanFilterKatalog);
        });

        function resetSemuaFilter() {
            if (inputKataKunci) inputKataKunci.value = '';
            if (pilihanKota) pilihanKota.value = '';
            if (pilihanKategori) pilihanKategori.value = '';
            if (pilihanHarga) pilihanHarga.value = '';
            terapkanFilterKatalog();
        }

        if (tombolReset) tombolReset.addEventListener('click', resetSemuaFilter);
        if (tombolResetKosong) tombolResetKosong.addEventListener('click', resetSemuaFilter);

        daftarPillKategori.forEach(function (pill) {
            pill.addEventListener('click', function () {
                const slugKategori = pill.getAttribute('data-kategori-cepat') || '';
                if (pilihanKategori) pilihanKategori.value = slugKategori;
                terapkanFilterKatalog();
            });
        });

        document.querySelectorAll('.tombol-indeks-kategori').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                const slugKategori = tombol.getAttribute('data-pemicu-kategori') || '';
                if (pilihanKategori) pilihanKategori.value = slugKategori;
                terapkanFilterKatalog();
                const elemenGrid = document.getElementById('grid-destinasi');
                if (elemenGrid) {
                    elemenGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        document.querySelectorAll('.pemicu-kategori-footer').forEach(function (tautan) {
            tautan.addEventListener('click', function () {
                const slugKategori = tautan.getAttribute('data-kategori-footer') || '';
                if (pilihanKategori) pilihanKategori.value = slugKategori;
                terapkanFilterKatalog();
            });
        });

        terapkanFilterKatalog();

        // -----------------------------------------------------------------
        // 2. Simulator Interaktif REST API Mitra (Berdasarkan Kota & Layanan)
        // -----------------------------------------------------------------
        let kotaMitraAktif = 'Magelang';
        let tabLayananAktif = 'semua';

        const daftarTabKota = Array.from(document.querySelectorAll('.tab-kota-mitra'));
        const daftarPanelKota = Array.from(document.querySelectorAll('.panel-kota-mitra'));
        const daftarTabLayanan = Array.from(document.querySelectorAll('.tab-layanan-mitra'));
        const teksEndpointAktif = document.getElementById('endpoint-aktif-api');
        const teksDestinasiInduk = document.getElementById('destinasi-induk-api');
        const teksLatensiAktif = document.getElementById('latensi-aktif-api');
        const teksKoordinatAktif = document.getElementById('koordinat-aktif-api');
        const panelInspeksiJson = document.getElementById('panel-inspeksi-json');
        const kodeInspeksiJson = document.getElementById('kode-inspeksi-json');
        const tombolInspeksiJson = document.getElementById('tombol-inspeksi-json');
        const labelInspeksiJson = document.getElementById('label-inspeksi-json');

        // Memperbarui tampilan payload JSON sesuai kota dan layanan yang dipilih
        function perbaruiInspeksiJson() {
            if (!kodeInspeksiJson || !dataMitra[kotaMitraAktif]) {
                return;
            }
            const infoKota = dataMitra[kotaMitraAktif];
            let dataLayananTerpilih = {};

            if (tabLayananAktif === 'semua') {
                dataLayananTerpilih = {
                    kuliner: infoKota.kuliner,
                    penginapan: infoKota.penginapan,
                    transportasi: infoKota.transportasi
                };
            } else {
                dataLayananTerpilih[tabLayananAktif] = infoKota[tabLayananAktif] || [];
            }

            const objekPayload = {
                status: 'berhasil',
                pemanggil: 'IndWalk LayananApiMitra',
                parameter: {
                    kota: kotaMitraAktif,
                    filter_layanan: tabLayananAktif
                },
                mode_cadangan_mock: true,
                sumber_data: infoKota.sumber_data,
                latensi_ms: infoKota.latensi_ms,
                destinasi_induk: infoKota.destinasi_induk,
                data: dataLayananTerpilih
            };

            kodeInspeksiJson.textContent = JSON.stringify(objekPayload, null, 2);
        }

        // Mengganti kota aktif pada simulator REST API Mitra
        function pilihKotaMitra(namaKota) {
            if (!dataMitra[namaKota]) {
                return;
            }
            kotaMitraAktif = namaKota;
            const infoKota = dataMitra[namaKota];

            daftarTabKota.forEach(function (tab) {
                const sedangDipilih = tab.getAttribute('data-kota-mitra') === namaKota;
                tab.setAttribute('aria-selected', sedangDipilih ? 'true' : 'false');

                if (sedangDipilih) {
                    tab.className = 'tab-kota-mitra text-left px-3.5 py-3 rounded-xl border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] bg-[#0B6E77] text-white border-[#0B6E77]';
                } else {
                    tab.className = 'tab-kota-mitra text-left px-3.5 py-3 rounded-xl border transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] bg-white text-stone-800 border-stone-300/90 hover:border-[#0B6E77]/60';
                }

                const subTeks = tab.querySelector('.subteks-kota-mitra');
                if (subTeks) {
                    if (sedangDipilih) {
                        subTeks.className = 'subteks-kota-mitra text-[11px] truncate mt-0.5 text-stone-100';
                    } else {
                        subTeks.className = 'subteks-kota-mitra text-[11px] truncate mt-0.5 text-stone-500';
                    }
                }
            });

            daftarPanelKota.forEach(function (panel) {
                const milikKotaIni = panel.getAttribute('data-panel-kota') === namaKota;
                panel.classList.toggle('hidden', !milikKotaIni);
            });

            if (teksEndpointAktif) {
                teksEndpointAktif.textContent = '/api/v1/mitra-ekosistem?kota=' + encodeURIComponent(namaKota);
            }
            if (teksDestinasiInduk) teksDestinasiInduk.textContent = infoKota.destinasi_induk;
            if (teksLatensiAktif) teksLatensiAktif.textContent = infoKota.latensi_ms + ' ms';
            if (teksKoordinatAktif) teksKoordinatAktif.textContent = infoKota.koordinat;

            perbaruiInspeksiJson();
        }

        // Mengganti tab jenis layanan mitra (semua, kuliner, penginapan, transportasi)
        function pilihTabLayanan(kunciLayanan) {
            tabLayananAktif = kunciLayanan;

            daftarTabLayanan.forEach(function (tab) {
                const sedangDipilih = tab.getAttribute('data-tab-layanan') === kunciLayanan;
                if (sedangDipilih) {
                    tab.className = 'tab-layanan-mitra px-3.5 py-2 rounded-lg text-xs font-semibold bg-[#2D5A36] text-white transition-colors flex items-center gap-1.5';
                } else {
                    tab.className = 'tab-layanan-mitra px-3.5 py-2 rounded-lg text-xs font-medium bg-stone-200/75 text-stone-700 hover:bg-stone-300/70 transition-colors flex items-center gap-1.5';
                }
            });

            daftarPanelKota.forEach(function (panel) {
                const kontainerGrid = panel.querySelector('.grid');
                const daftarKolom = Array.from(panel.querySelectorAll('.kolom-layanan-mitra'));

                if (kunciLayanan === 'semua') {
                    if (kontainerGrid) {
                        kontainerGrid.className = 'grid grid-cols-1 md:grid-cols-3 gap-6 divide-y md:divide-y-0 md:divide-x divide-stone-200/90';
                    }
                    daftarKolom.forEach(function (kolom) {
                        kolom.classList.remove('hidden');
                    });
                } else {
                    if (kontainerGrid) {
                        kontainerGrid.className = 'grid grid-cols-1 gap-6';
                    }
                    daftarKolom.forEach(function (kolom) {
                        const cocokLayanan = kolom.getAttribute('data-kolom-layanan') === kunciLayanan;
                        kolom.classList.toggle('hidden', !cocokLayanan);
                    });
                }
            });

            perbaruiInspeksiJson();
        }

        daftarTabKota.forEach(function (tab) {
            tab.addEventListener('click', function () {
                pilihKotaMitra(tab.getAttribute('data-kota-mitra'));
            });
        });

        daftarTabLayanan.forEach(function (tab) {
            tab.addEventListener('click', function () {
                pilihTabLayanan(tab.getAttribute('data-tab-layanan'));
            });
        });

        document.querySelectorAll('.tombol-lompat-kota-mitra').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                const kotaTujuan = tombol.getAttribute('data-lompat-kota');
                pilihKotaMitra(kotaTujuan);
                const bagianMitra = document.getElementById('integrasi-api-mitra');
                if (bagianMitra) {
                    bagianMitra.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        document.querySelectorAll('.pemicu-kota-footer').forEach(function (tautan) {
            tautan.addEventListener('click', function () {
                const kotaTujuan = tautan.getAttribute('data-kota-footer');
                pilihKotaMitra(kotaTujuan);
            });
        });

        if (tombolInspeksiJson && panelInspeksiJson) {
            tombolInspeksiJson.addEventListener('click', function () {
                const sedangTersembunyi = panelInspeksiJson.classList.contains('hidden');
                panelInspeksiJson.classList.toggle('hidden', !sedangTersembunyi);
                tombolInspeksiJson.setAttribute('aria-pressed', sedangTersembunyi ? 'true' : 'false');
                if (labelInspeksiJson) {
                    if (sedangTersembunyi) {
                        labelInspeksiJson.textContent = 'Sembunyikan Payload JSON';
                    } else {
                        labelInspeksiJson.textContent = 'Tampilkan Payload JSON';
                    }
                }
                perbaruiInspeksiJson();
            });
        }

        perbaruiInspeksiJson();

        // -----------------------------------------------------------------
        // 3. Pengaturan Modal Detail Destinasi
        // -----------------------------------------------------------------
        const elemenModal = document.getElementById('modal-detail-destinasi');
        const tombolTutupModal = document.getElementById('tombol-tutup-modal');
        const modalFoto = document.getElementById('modal-foto-destinasi');
        const modalKategori = document.getElementById('modal-kategori-destinasi');
        const modalLokasi = document.getElementById('modal-lokasi-destinasi');
        const modalNama = document.getElementById('modal-nama-destinasi');
        const modalDeskripsi = document.getElementById('modal-deskripsi-destinasi');
        const modalHarga = document.getElementById('modal-harga-destinasi');
        const modalJam = document.getElementById('modal-jam-destinasi');
        const modalRating = document.getElementById('modal-rating-destinasi');
        const modalKoordinat = document.getElementById('modal-koordinat-destinasi');
        const modalFasilitas = document.getElementById('modal-fasilitas-destinasi');
        const modalUrlApi = document.getElementById('modal-url-api-destinasi');
        const modalTombolMitra = document.getElementById('modal-tombol-lompat-mitra');

        let kotaModalSaatIni = 'Magelang';

        function bukaModalDestinasi(idDestinasi) {
            const destinasiTerpilih = dataDestinasi.find(function (item) {
                return String(item.id) === String(idDestinasi);
            });

            if (!destinasiTerpilih || !elemenModal) {
                return;
            }

            kotaModalSaatIni = destinasiTerpilih.kota;
            modalFoto.src = destinasiTerpilih.foto_utama;
            modalFoto.alt = destinasiTerpilih.nama;
            modalKategori.textContent = destinasiTerpilih.nama_kategori;
            modalLokasi.textContent = destinasiTerpilih.kota + ', ' + destinasiTerpilih.provinsi + ' · ' + destinasiTerpilih.alamat;
            modalNama.textContent = destinasiTerpilih.nama;
            modalDeskripsi.textContent = destinasiTerpilih.deskripsi;
            modalHarga.textContent = 'Rp ' + Number(destinasiTerpilih.harga_tiket).toLocaleString('id-ID') + ' / org';
            modalJam.textContent = destinasiTerpilih.jam_buka + ' – ' + destinasiTerpilih.jam_tutup + ' (' + destinasiTerpilih.estimasi_durasi + ')';
            modalRating.textContent = '★ ' + Number(destinasiTerpilih.rata_rating).toFixed(2) + ' (' + Number(destinasiTerpilih.jumlah_ulasan).toLocaleString('id-ID') + ' ulasan)';
            modalKoordinat.textContent = Number(destinasiTerpilih.garis_lintang).toFixed(4) + ', ' + Number(destinasiTerpilih.garis_bujur).toFixed(4);
            modalUrlApi.textContent = 'GET /api/v1/destinasi/' + destinasiTerpilih.slug;

            modalFasilitas.innerHTML = '';
            (destinasiTerpilih.fasilitas || []).forEach(function (namaFasilitas) {
                const badgeFasilitas = document.createElement('span');
                badgeFasilitas.className = 'px-2.5 py-1 rounded-md text-xs font-medium bg-white border border-stone-200 text-stone-800';
                badgeFasilitas.textContent = namaFasilitas;
                modalFasilitas.appendChild(badgeFasilitas);
            });

            elemenModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function tutupModalDestinasi() {
            if (!elemenModal) {
                return;
            }
            elemenModal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.tombol-buka-modal-destinasi').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                bukaModalDestinasi(tombol.getAttribute('data-buka-destinasi'));
            });
        });

        if (tombolTutupModal) tombolTutupModal.addEventListener('click', tutupModalDestinasi);
        if (elemenModal) {
            elemenModal.addEventListener('click', function (event) {
                if (event.target === elemenModal) {
                    tutupModalDestinasi();
                }
            });
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                tutupModalDestinasi();
            }
        });

        if (modalTombolMitra) {
            modalTombolMitra.addEventListener('click', function () {
                tutupModalDestinasi();
                pilihKotaMitra(kotaModalSaatIni);
                const bagianMitra = document.getElementById('integrasi-api-mitra');
                if (bagianMitra) {
                    bagianMitra.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
    })();
</script>
<?= $this->endSection() ?>
