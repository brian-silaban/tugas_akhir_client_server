<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php if (empty($destinasi)) : ?>
    <section class="max-w-4xl mx-auto px-4 sm:px-6 py-16 text-center">
        <span class="material-symbols-outlined text-5xl text-error">cloud_off</span>
        <h1 class="font-headline-lg text-headline-lg text-on-surface mt-4">Data Destinasi Tidak Dapat Dimuat</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">
            <?= esc($pesanErrorApi ?? 'Pastikan server REST API Backend aktif di port 8080.') ?>
        </p>
        <a href="<?= base_url('destinasi') ?>" class="inline-flex mt-6 px-5 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md">
            Kembali ke Katalog
        </a>
    </section>
<?php else : ?>
    <?php
    $idDestinasi       = (int) ($destinasi['id'] ?? 0);
    $slugDestinasi     = (string) ($destinasi['slug'] ?? '');
    $hargaTiket        = (int) ($destinasi['harga_tiket'] ?? 0);
    $jamOperasional    = ambilTeksJamOperasional($destinasi['jam_operasional'] ?? null);
    $garisLintang      = $destinasi['koordinat']['garis_lintang'] ?? null;
    $garisBujur        = $destinasi['koordinat']['garis_bujur'] ?? null;
    $daftarFasilitas   = is_array($destinasi['daftar_fasilitas'] ?? null) ? $destinasi['daftar_fasilitas'] : [];
    $galeriFoto        = is_array($destinasi['galeri'] ?? null) ? $destinasi['galeri'] : [];
    $idPenggunaLogin   = session('id_pengguna');
    ?>

    <article class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <!-- Breadcrumb Navigasi -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-outline mb-6" aria-label="Breadcrumb">
            <a href="<?= base_url('/') ?>" class="hover:text-primary transition-colors">Beranda</a>
            <span>/</span>
            <a href="<?= base_url('destinasi') ?>" class="hover:text-primary transition-colors">Katalog</a>
            <?php if (! empty($destinasi['slug_kategori'])) : ?>
                <span>/</span>
                <a href="<?= base_url('destinasi?kategori=' . urlencode($destinasi['slug_kategori'])) ?>" class="hover:text-primary transition-colors">
                    <?= esc($destinasi['kategori'] ?? '') ?>
                </a>
            <?php endif; ?>
            <span>/</span>
            <span class="text-on-surface font-semibold truncate max-w-xs sm:max-w-md"><?= esc($destinasi['nama']) ?></span>
        </nav>

        <!-- Galeri Foto Utama & Pendukung -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-8">
            <div class="<?= $galeriFoto !== [] ? 'lg:col-span-8' : 'lg:col-span-12' ?> relative h-72 sm:h-[420px] rounded-3xl overflow-hidden bg-surface-container shadow-sm">
                <img
                    src="<?= esc($destinasi['foto_utama'] ?? '') ?>"
                    alt="<?= esc($destinasi['nama']) ?>"
                    class="w-full h-full object-cover"
                />
                <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                    <span class="font-label-caps text-label-caps px-3.5 py-1.5 rounded-full bg-surface-container-lowest/95 text-primary font-bold shadow-sm">
                        <?= esc($destinasi['kategori'] ?? 'Wisata Sumut') ?>
                    </span>
                    <?php if (! empty($destinasi['sub_kategori'])) : ?>
                        <span class="font-label-caps text-label-caps px-3.5 py-1.5 rounded-full bg-secondary text-on-secondary shadow-sm">
                            <?= esc($destinasi['sub_kategori']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($galeriFoto !== []) : ?>
                <div class="lg:col-span-4 grid grid-cols-2 lg:grid-cols-1 gap-4">
                    <?php foreach (array_slice($galeriFoto, 0, 2) as $fotoTambahan) : ?>
                        <div class="relative h-40 lg:h-[202px] rounded-2xl overflow-hidden bg-surface-container">
                            <img
                                src="<?= esc($fotoTambahan['url_foto'] ?? '') ?>"
                                alt="<?= esc($fotoTambahan['keterangan'] ?? $destinasi['nama']) ?>"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Editorial Split Layout: Kiri (8 Kolom Info) + Kanan (4 Kolom Sticky Tiket) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Kolom Kiri: Informasi Lengkap Destinasi -->
            <div class="lg:col-span-8 space-y-8">
                <header>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-on-surface-variant mb-2">
                        <span class="inline-flex items-center gap-1 font-medium text-primary">
                            <span class="material-symbols-outlined text-base">pin_drop</span>
                            <?= esc($destinasi['nama_kabupaten_kota'] ?? $destinasi['kota']) ?>
                            (Kode <?= esc($destinasi['kode_kabupaten'] ?? '-') ?>)
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1 tabular-nums font-semibold text-on-surface">
                            <span class="material-symbols-outlined text-base text-amber-600" style="font-variation-settings: 'FILL' 1;">star</span>
                            <?= esc(number_format((float) ($destinasi['rata_rating'] ?? 0), 2, '.', '')) ?>
                            <span class="text-outline font-normal">(<?= esc(number_format((int) ($destinasi['jumlah_ulasan'] ?? 0), 0, ',', '.')) ?> ulasan)</span>
                        </span>
                        <?php if (! empty($destinasi['klasifikasi_status'])) : ?>
                            <span>•</span>
                            <span class="px-2.5 py-0.5 rounded-md bg-surface-container text-xs font-semibold text-on-surface-variant">
                                <?= esc($destinasi['klasifikasi_status']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface leading-tight">
                        <?= esc($destinasi['nama']) ?>
                    </h1>

                    <?php if (! empty($destinasi['alamat'])) : ?>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                            <?= esc($destinasi['alamat']) ?>, <?= esc($destinasi['provinsi'] ?? 'Sumatera Utara') ?>
                        </p>
                    <?php endif; ?>
                </header>

                <!-- Deskripsi Naratif -->
                <section class="pt-6 border-t border-surface-variant/50">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-3">Tentang Destinasi</h2>
                    <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed whitespace-pre-line">
                        <?= esc($destinasi['deskripsi'] ?? '') ?>
                    </p>
                </section>

                <!-- Informasi Operasional, Pengelola & Kontak -->
                <section class="pt-6 border-t border-surface-variant/50">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Informasi Operasional &amp; Pengelola</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-surface-container-lowest rounded-2xl p-5 border border-surface-variant/50">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary mt-0.5">schedule</span>
                            <div>
                                <span class="font-label-caps text-label-caps text-outline uppercase block">Jam Operasional</span>
                                <span class="font-label-md text-label-md text-on-surface tabular-nums"><?= esc($jamOperasional) ?></span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary mt-0.5">domain</span>
                            <div>
                                <span class="font-label-caps text-label-caps text-outline uppercase block">Pengelola</span>
                                <span class="font-label-md text-label-md text-on-surface">
                                    <?= esc(! empty($destinasi['pengelola']) ? $destinasi['pengelola'] : 'Pengelola Lokal / Pemerintah Daerah') ?>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary mt-0.5">my_location</span>
                            <div>
                                <span class="font-label-caps text-label-caps text-outline uppercase block">Titik Koordinat GPS</span>
                                <span class="font-label-md text-label-md text-on-surface tabular-nums">
                                    <?= esc(formatKoordinatLokasi($garisLintang !== null ? (float) $garisLintang : null, $garisBujur !== null ? (float) $garisBujur : null)) ?>
                                </span>
                                <?php if ($garisLintang !== null && $garisBujur !== null) : ?>
                                    <a
                                        href="https://www.google.com/maps?q=<?= esc((string) $garisLintang) ?>,<?= esc((string) $garisBujur) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs text-primary hover:underline block mt-0.5"
                                    >
                                        Buka di Google Maps →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary mt-0.5">call</span>
                            <div>
                                <span class="font-label-caps text-label-caps text-outline uppercase block">Kontak &amp; Informasi</span>
                                <span class="font-body-sm text-body-sm text-on-surface block">
                                    <?= esc(! empty($destinasi['no_telp']) ? $destinasi['no_telp'] : 'Telepon belum tersedia') ?>
                                </span>
                                <?php if (! empty($destinasi['email'])) : ?>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant block"><?= esc($destinasi['email']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (! empty($destinasi['sumber_data'])) : ?>
                        <p class="text-xs text-outline mt-3">
                            Sumber data resmi: <?= esc($destinasi['sumber_data']) ?>
                        </p>
                    <?php endif; ?>
                </section>

                <!-- Fasilitas Lapangan -->
                <section class="pt-6 border-t border-surface-variant/50">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Fasilitas Tersedia</h2>
                    <?php if ($daftarFasilitas === []) : ?>
                        <p class="font-body-md text-body-md text-on-surface-variant">
                            Informasi fasilitas rinci belum tercatat pada basis data untuk objek wisata ini.
                        </p>
                    <?php else : ?>
                        <div class="flex flex-wrap gap-2.5">
                            <?php foreach ($daftarFasilitas as $namaFasilitas) : ?>
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container text-on-surface font-label-md text-label-md">
                                    <span class="material-symbols-outlined text-base text-secondary">check_circle</span>
                                    <?= esc($namaFasilitas) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Komponen Ulasan & Rating Bintang (Anggota 4) -->
                <?php if (is_file(APPPATH . 'Views/components/bagian_ulasan.php')) : ?>
                    <?= view('components/bagian_ulasan', [
                        'id_destinasi'   => $idDestinasi,
                        'slug_destinasi' => $slugDestinasi,
                    ]) ?>
                <?php endif; ?>

                <!-- Slot Komponen Rekomendasi 4 Layanan Mitra (Anggota 2) -->
                <?php if (is_file(APPPATH . 'Views/components/rekomendasi_mitra.php')) : ?>
                    <?= view('components/rekomendasi_mitra', [
                        'kota'      => $destinasi['kota'] ?? '',
                        'destinasi' => $destinasi,
                    ]) ?>
                <?php else : ?>
                    <section class="mt-12 pt-8 border-t border-surface-variant/50">
                        <h2 class="font-headline-md text-headline-md text-on-surface">Rekomendasi Mitra Sekitar <?= esc($destinasi['kota'] ?? 'Lokasi') ?></h2>
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                            Slot integrasi REST API kelompok Tur Guide, Kuliner, Penginapan, dan Transportasi siap disambungkan pada modul Anggota 2.
                        </p>
                    </section>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan (4 Kolom): Sticky Card Harga & Pemesanan Tiket (Anggota 4) -->
            <aside class="lg:col-span-4 lg:sticky lg:top-24 space-y-4">
                <div class="bg-surface-container-lowest rounded-3xl p-6 border border-surface-variant/60 shadow-sm">
                    <div class="flex items-start justify-between gap-3 pb-4 border-b border-surface-variant/40">
                        <div>
                            <span class="font-label-caps text-label-caps text-outline uppercase block">
                                <?= esc($destinasi['label_harga_tiket'] ?? 'Tarif Tiket Masuk') ?>
                            </span>
                            <div class="font-headline-md text-headline-md text-on-surface tabular-nums mt-1">
                                <?= esc(formatHargaRupiah($hargaTiket)) ?>
                                <?php if ($hargaTiket > 0) : ?>
                                    <span class="font-body-sm text-body-sm text-outline font-normal">/ orang</span>
                                <?php endif; ?>
                            </div>
                            <?php if (! empty($destinasi['keterangan_tiket'])) : ?>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                                    <?= esc($destinasi['keterangan_tiket']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Slot Tombol Favorit (Anggota 3) -->
                        <?php if (is_file(APPPATH . 'Views/components/tombol_favorit.php')) : ?>
                            <?= view('components/tombol_favorit', ['id_destinasi' => $idDestinasi]) ?>
                        <?php endif; ?>
                    </div>

                    <!-- Form Pemesanan Tiket (Anggota 4: POST /pemesanan/buat) -->
                    <?php if (! empty($idPenggunaLogin)) : ?>
                        <form action="<?= base_url('pemesanan/buat') ?>" method="post" class="mt-5 space-y-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_destinasi" value="<?= esc((string) $idDestinasi) ?>" />

                            <div>
                                <label for="tanggal_kunjungan" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                                    Tanggal Kunjungan
                                </label>
                                <input
                                    id="tanggal_kunjungan"
                                    type="date"
                                    name="tanggal_kunjungan"
                                    min="<?= esc(date('Y-m-d')) ?>"
                                    value="<?= esc(date('Y-m-d')) ?>"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/60 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary"
                                />
                            </div>

                            <div>
                                <label for="jumlah_pengunjung" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                                    Jumlah Pengunjung
                                </label>
                                <input
                                    id="jumlah_pengunjung"
                                    type="number"
                                    name="jumlah_pengunjung"
                                    min="1"
                                    max="50"
                                    value="1"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/60 font-body-md text-body-md text-on-surface tabular-nums focus:outline-none focus:border-primary"
                                />
                            </div>

                            <button
                                type="submit"
                                class="w-full py-3 px-5 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
                            >
                                Pesan Tiket Kunjungan
                            </button>
                        </form>
                    <?php else : ?>
                        <div class="mt-5 space-y-3">
                            <p class="font-body-sm text-body-sm text-on-surface-variant">
                                Masuk dengan akun JejakSumut untuk memesan e-ticket kunjungan atau menyimpan destinasi ini ke daftar favorit.
                            </p>
                            <a
                                href="<?= base_url('masuk') ?>"
                                class="block w-full text-center py-3 px-5 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
                            >
                                Masuk untuk Pesan Tiket
                            </a>
                            <a
                                href="<?= base_url('destinasi') ?>"
                                class="block w-full text-center py-2.5 px-5 rounded-xl border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-colors"
                            >
                                Lihat Destinasi Lainnya
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

        </div>
    </article>
<?php endif; ?>
<?= $this->endSection() ?>
