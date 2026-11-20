<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judulHalaman ?? 'Destinasi Favorit Saya — IndWalk') ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'indwalk-teal':      '#0B6E77',
                        'indwalk-green':     '#2D5A36',
                        'indwalk-olive':     '#6A8E3F',
                        'indwalk-terra':     '#C2410C',
                        'indwalk-amber':     '#D97706',
                        'indwalk-bg':        '#FAF8F5',
                    },
                },
            },
        }
    </script>

    <!-- Google Fonts: Playfair Display + Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-indwalk-bg font-sans text-stone-700 antialiased min-h-screen flex flex-col justify-between">

    <!-- ============================================================ -->
    <!-- HEADER / NAVIGASI RINGKAS                                    -->
    <!-- ============================================================ -->
    <header class="bg-white/80 backdrop-blur-md border-b border-stone-200/80 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="<?= base_url('/') ?>" class="flex items-center gap-2 group">
                <span class="font-serif text-2xl font-bold tracking-tight">
                    <span class="text-indwalk-teal group-hover:opacity-90 transition-opacity">Ind</span><span class="text-indwalk-green group-hover:opacity-90 transition-opacity">Walk</span>
                </span>
            </a>

            <!-- Navigasi Sisi Kanan -->
            <nav class="flex items-center gap-4 sm:gap-6 text-sm">
                <a href="<?= base_url('/destinasi') ?>" class="text-stone-600 hover:text-indwalk-teal transition-colors font-medium">
                    Katalog Destinasi
                </a>
                <a href="<?= base_url('/pemesanan-saya') ?>" class="text-stone-600 hover:text-indwalk-teal transition-colors font-medium hidden sm:inline-block">
                    Pesanan Saya
                </a>
                <div class="h-4 w-px bg-stone-300"></div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-stone-600 bg-stone-100 px-2.5 py-1 rounded-full border border-stone-200">
                        <?= esc(session()->get('nama') ?? 'Member') ?>
                    </span>
                    <a href="<?= base_url('/keluar') ?>" class="text-xs font-medium text-red-600 hover:text-red-700 transition-colors">
                        Keluar
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- ============================================================ -->
    <!-- KONTEN UTAMA                                                  -->
    <!-- ============================================================ -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full">

        <!-- Breadcrumb & Header Halaman -->
        <div class="mb-8">
            <nav class="flex items-center gap-2 text-xs text-stone-500 mb-2">
                <a href="<?= base_url('/') ?>" class="hover:text-indwalk-teal transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-stone-800 font-medium">Favorit Saya</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-stone-900 tracking-tight leading-[1.15]">
                        Destinasi Favorit Saya
                    </h1>
                    <p class="mt-2 text-sm text-stone-600 max-w-xl">
                        Daftar tempat wisata Nusantara pilihan yang Anda simpan untuk rencana perjalanan dan liburan berikutnya.
                    </p>
                </div>
                <div>
                    <a href="<?= base_url('/destinasi') ?>"
                       class="inline-flex items-center gap-2 rounded-lg bg-indwalk-teal px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indwalk-teal/40 transition-colors shadow-sm">
                        <!-- Ikon Pencarian -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        Jelajahi Wisata Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifikasi Pesan Flash -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600 shrink-0">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 shrink-0">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- DAFTAR KARTU DESTINASI ATAU KONDISI KOSONG                   -->
        <!-- ============================================================ -->
        <?php if (empty($daftarFavorit)): ?>
            <!-- Tampilan Kosong (Empty State) -->
            <div class="rounded-2xl border border-stone-200/90 bg-white p-10 sm:p-14 text-center max-w-2xl mx-auto shadow-sm">
                <div class="w-16 h-16 rounded-full bg-stone-100 border border-stone-200 flex items-center justify-center mx-auto mb-4 text-stone-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                    </svg>
                </div>
                <h3 class="font-serif text-2xl font-semibold text-stone-900 tracking-tight">
                    Belum Ada Destinasi Favorit
                </h3>
                <p class="mt-2.5 text-sm text-stone-500 leading-relaxed max-w-md mx-auto">
                    Koleksi destinasi impian Anda masih kosong. Jelajahi keindahan Nusantara dan simpan destinasi favorit untuk perjalanan Anda.
                </p>
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="<?= base_url('/destinasi') ?>"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-indwalk-terra px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-800 transition-colors shadow-sm">
                        Eksplorasi Katalog Wisata
                    </a>
                    <a href="<?= base_url('/') ?>"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-stone-300 bg-white px-5 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50 transition-colors">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

        <?php else: ?>

            <!-- Grid Kartu Destinasi Favorit -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                <?php foreach ($daftarFavorit as $item): ?>
                    <?php
                        // Format mata uang tiket masuk
                        if (!empty($item['harga_tiket']) && $item['harga_tiket'] > 0) {
                            $hargaTerformat = 'Rp ' . number_format($item['harga_tiket'], 0, ',', '.');
                        } else {
                            $hargaTerformat = 'Gratis';
                        }

                        // Foto utama fallback jika kosong
                        $fotoDestinasi = !empty($item['foto_utama'])
                            ? $item['foto_utama']
                            : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80';
                    ?>

                    <div class="group flex flex-col justify-between rounded-2xl border border-stone-200/90 bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                        <!-- Foto & Badge -->
                        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                            <img
                                src="<?= esc($fotoDestinasi) ?>"
                                alt="<?= esc($item['nama']) ?>"
                                class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                                loading="lazy"
                            >
                            <!-- Overlay gradien halus di bawah foto -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                            <!-- Badge Kategori -->
                            <?php if (!empty($item['nama_kategori'])): ?>
                                <span class="absolute top-3 left-3 rounded-full bg-white/90 backdrop-blur-md px-3 py-1 text-xs font-semibold text-stone-800 shadow-sm">
                                    <?= esc($item['nama_kategori']) ?>
                                </span>
                            <?php endif; ?>

                            <!-- Tombol Hapus dari Favorit -->
                            <form action="<?= base_url('/favorit/ubah') ?>" method="post" class="absolute top-3 right-3 z-10">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_destinasi" value="<?= esc($item['id']) ?>">
                                <button
                                    type="submit"
                                    title="Hapus dari favorit"
                                    aria-label="Hapus dari favorit"
                                    class="rounded-full bg-white/90 backdrop-blur-md p-2 text-red-600 hover:bg-white hover:text-red-700 hover:scale-110 active:scale-95 transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500/40"
                                >
                                    <!-- Ikon Heart Terisi Merah -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                    </svg>
                                </button>
                            </form>

                            <!-- Geolokasi di atas gradient foto -->
                            <div class="absolute bottom-3 left-3 text-white text-xs font-medium flex items-center gap-1.5 drop-shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <span><?= esc($item['kota']) ?><?= !empty($item['provinsi']) ? ', ' . esc($item['provinsi']) : '' ?></span>
                            </div>
                        </div>

                        <!-- Konten Informasi Destinasi -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-serif text-lg font-semibold text-stone-900 tracking-tight leading-snug group-hover:text-indwalk-teal transition-colors">
                                    <a href="<?= base_url('/destinasi/' . esc($item['slug'])) ?>">
                                        <?= esc($item['nama']) ?>
                                    </a>
                                </h3>

                                <!-- Rating & Jumlah Ulasan -->
                                <div class="mt-2 flex items-center gap-2 text-xs text-stone-600">
                                    <span class="inline-flex items-center gap-1 font-semibold text-indwalk-amber tabular-nums">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" class="text-indwalk-amber">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                        <?= number_format((float) ($item['rata_rating'] ?? 0), 1) ?>
                                    </span>
                                    <span>•</span>
                                    <span class="tabular-nums"><?= number_format((int) ($item['jumlah_ulasan'] ?? 0)) ?> ulasan</span>
                                    <?php if (!empty($item['jam_buka']) && !empty($item['jam_tutup'])): ?>
                                        <span>•</span>
                                        <span class="tabular-nums text-stone-500"><?= esc($item['jam_buka']) ?> - <?= esc($item['jam_tutup']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Footer Kartu: Harga & Tombol Aksi Detail -->
                            <div class="mt-5 pt-4 border-t border-stone-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] text-stone-400 uppercase tracking-wider block">Harga Tiket</span>
                                    <span class="font-semibold text-stone-900 text-sm tabular-nums"><?= esc($hargaTerformat) ?></span>
                                </div>

                                <a href="<?= base_url('/destinasi/' . esc($item['slug'])) ?>"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indwalk-teal hover:text-teal-900 transition-colors py-1.5 px-3 rounded-lg hover:bg-stone-50">
                                    Lihat Detail
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <!-- ============================================================ -->
    <!-- FOOTER SEDERHANA                                              -->
    <!-- ============================================================ -->
    <footer class="border-t border-stone-200 bg-white py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
            <p>&copy; <?= date('Y') ?> IndWalk — Explore Indonesia Together. Hak cipta dilindungi.</p>
            <div class="flex items-center gap-4">
                <a href="<?= base_url('/') ?>" class="hover:text-indwalk-teal transition-colors">Beranda</a>
                <a href="<?= base_url('/destinasi') ?>" class="hover:text-indwalk-teal transition-colors">Katalog Wisata</a>
                <a href="<?= base_url('/favorit-saya') ?>" class="text-indwalk-teal font-medium">Favorit Saya</a>
            </div>
        </div>
    </footer>

</body>
</html>
