<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">

    <!-- Breadcrumb & Header Halaman -->
    <div class="mb-8">
        <nav class="flex items-center gap-2 text-xs text-on-surface-variant mb-2">
            <a href="<?= base_url('/') ?>" class="hover:text-primary transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-on-surface font-medium">Favorit Saya</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight leading-[1.15]">
                    Destinasi Favorit Saya
                </h1>
                <p class="mt-2 text-sm text-on-surface-variant max-w-xl">
                    Daftar objek wisata Sumatera Utara pilihan yang Anda simpan untuk rencana perjalanan dan liburan berikutnya.
                </p>
            </div>
            <div>
                <a href="<?= base_url('/destinasi') ?>"
                   class="inline-flex items-center gap-2 rounded-full bg-primary-container px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-base">explore</span>
                    <span>Jelajahi Wisata Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Notifikasi Pesan Flash cadangan (versi utama tampil via SweetAlert2 toast) -->
    <noscript>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 rounded-xl border border-secondary/40 bg-secondary-container/30 px-4 py-3 text-sm text-on-secondary-fixed-variant flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary">check_circle</span>
                <span><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 rounded-xl border border-error/30 bg-error-container/30 px-4 py-3 text-sm text-on-error-container flex items-center gap-3">
                <span class="material-symbols-outlined text-error">error</span>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif; ?>
    </noscript>

    <!-- ============================================================ -->
    <!-- DAFTAR KARTU DESTINASI ATAU KONDISI KOSONG                   -->
    <!-- ============================================================ -->
    <?php if (empty($daftarFavorit)): ?>
        <!-- Tampilan Kosong (Empty State) -->
        <div class="rounded-2xl border border-surface-variant/80 bg-surface-container-lowest p-10 sm:p-14 text-center max-w-2xl mx-auto shadow-sm my-8">
            <div class="w-16 h-16 rounded-full bg-surface-container border border-surface-variant flex items-center justify-center mx-auto mb-4 text-on-surface-variant">
                <span class="material-symbols-outlined text-3xl text-tertiary-container">favorite</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-surface tracking-tight">
                Belum Ada Destinasi Favorit
            </h3>
            <p class="mt-2.5 text-sm text-on-surface-variant leading-relaxed max-w-md mx-auto">
                Koleksi destinasi impian Anda di Sumatera Utara masih kosong. Jelajahi keindahan Danau Toba, Berastagi, Bukit Lawang, dan simpan objek wisata favorit Anda.
            </p>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?= base_url('/destinasi') ?>"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-tertiary-container px-6 py-2.5 text-sm font-semibold text-on-tertiary hover:bg-tertiary transition-colors shadow-sm">
                    Eksplorasi Katalog Wisata
                </a>
                <a href="<?= base_url('/') ?>"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-outline-variant bg-surface-container-lowest px-6 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container transition-colors">
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
                    if (!empty($item['harga_tiket']) && (int) $item['harga_tiket'] > 0) {
                        $hargaTerformat = 'Rp ' . number_format((int) $item['harga_tiket'], 0, ',', '.');
                    } else {
                        $hargaTerformat = $item['label_harga_tiket'] ?? 'Gratis (Rp 0)';
                    }

                    // Foto utama fallback jika kosong
                    $fotoDestinasi = !empty($item['foto_utama'])
                        ? $item['foto_utama']
                        : 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Lake_Toba_and_the_surrounding_hills.jpg/1280px-Lake_Toba_and_the_surrounding_hills.jpg';
                ?>

                <div class="group flex flex-col justify-between rounded-2xl border border-surface-variant/80 bg-surface-container-lowest overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                    <!-- Foto & Badge -->
                    <div class="relative aspect-[16/10] overflow-hidden bg-surface-container">
                        <img
                            src="<?= esc($fotoDestinasi) ?>"
                            alt="<?= esc($item['nama']) ?>"
                            class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                            loading="lazy"
                        />
                        <!-- Overlay gradien halus di bawah foto -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        <!-- Badge Kategori -->
                        <?php if (!empty($item['nama_kategori'])): ?>
                            <span class="absolute top-3 left-3 rounded-full bg-surface/90 backdrop-blur-md px-3 py-1 text-xs font-semibold text-on-surface shadow-sm">
                                <?= esc($item['nama_kategori']) ?>
                            </span>
                        <?php endif; ?>

                        <!-- Tombol Hapus dari Favorit -->
                        <form action="<?= base_url('/favorit/ubah') ?>" method="post" class="absolute top-3 right-3 z-10">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_destinasi" value="<?= esc($item['id']) ?>" />
                            <button
                                type="submit"
                                title="Hapus dari favorit"
                                aria-label="Hapus dari favorit"
                                class="rounded-full bg-surface/90 backdrop-blur-md p-2 text-error hover:bg-surface hover:text-error hover:scale-110 active:scale-95 transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-error/40"
                            >
                                <span class="material-symbols-outlined text-base">favorite</span>
                            </button>
                        </form>

                        <!-- Geolokasi di atas gradient foto -->
                        <div class="absolute bottom-3 left-3 text-white text-xs font-medium flex items-center gap-1.5 drop-shadow">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            <span><?= esc($item['kota']) ?><?= !empty($item['provinsi']) ? ', ' . esc($item['provinsi']) : '' ?></span>
                        </div>
                    </div>

                    <!-- Konten Informasi Destinasi -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface tracking-tight leading-snug group-hover:text-primary transition-colors line-clamp-1">
                                <a href="<?= base_url('/destinasi/' . esc($item['slug'])) ?>">
                                    <?= esc($item['nama']) ?>
                                </a>
                            </h3>

                            <!-- Rating & Jumlah Ulasan -->
                            <div class="mt-2 flex items-center gap-2 text-xs text-on-surface-variant">
                                <span class="inline-flex items-center gap-1 font-semibold text-tertiary-container tabular-nums">
                                    <span class="material-symbols-outlined text-xs text-tertiary-container">star</span>
                                    <?= number_format((float) ($item['rata_rating'] ?? 0), 1) ?>
                                </span>
                                <span>•</span>
                                <span class="tabular-nums"><?= number_format((int) ($item['jumlah_ulasan'] ?? 0)) ?> ulasan</span>
                                <?php if (!empty($item['sub_kategori'])): ?>
                                    <span>•</span>
                                    <span class="text-on-surface-variant truncate"><?= esc($item['sub_kategori']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Footer Kartu: Harga & Tombol Aksi Detail -->
                        <div class="mt-5 pt-4 border-t border-surface-variant/40 flex items-center justify-between">
                            <div>
                                <span class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider block">Tiket Masuk</span>
                                <span class="font-metric-price text-metric-price text-on-surface tabular-nums"><?= esc($hargaTerformat) ?></span>
                            </div>

                            <a href="<?= base_url('/destinasi/' . esc($item['slug'])) ?>"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-container transition-colors py-1.5 px-3 rounded-lg hover:bg-surface-container">
                                <span>Detail</span>
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
<?= $this->endSection() ?>
