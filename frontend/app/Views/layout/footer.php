<?php
$daftarWilayahFooter = [
    ['kode' => '1217', 'nama' => 'Kabupaten Samosir'],
    ['kode' => '1211', 'nama' => 'Kabupaten Karo'],
    ['kode' => '1213', 'nama' => 'Kabupaten Langkat'],
    ['kode' => '1206', 'nama' => 'Kabupaten Toba'],
    ['kode' => '1275', 'nama' => 'Kota Medan'],
    ['kode' => '1214', 'nama' => 'Kabupaten Nias Selatan'],
];

$daftarKategoriFooter = [
    ['slug' => 'wisata-alam', 'nama' => 'Wisata Alam'],
    ['slug' => 'wisata-bahari-pantai', 'nama' => 'Wisata Bahari & Pantai'],
    ['slug' => 'wisata-budaya-sejarah', 'nama' => 'Wisata Budaya & Sejarah'],
    ['slug' => 'desa-wisata', 'nama' => 'Desa Wisata'],
];
?>
<footer class="w-full bg-surface-container-low border-t border-surface-variant/40">
    <div class="max-w-7xl mx-auto px-6 py-10" data-animasi="naik">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8">
            <!-- Kolom 1: Tentang JejakSumut -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                <a href="<?= base_url('/') ?>" class="flex items-center gap-3 self-start focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg">
                    <img
                        src="<?= base_url('assets/logo-jejaksumut.png') ?>"
                        alt="Logo JejakSumut"
                        class="h-10 w-10 object-contain shrink-0"
                    />
                    <span class="font-headline-sm text-headline-sm text-on-surface">JejakSumut</span>
                </a>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-sm leading-relaxed">
                    JejakSumut — Panduan perjalanan untuk menjelajahi pesona alam, budaya, penginapan, dan transportasi di Provinsi Sumatera Utara.
                </p>
            </div>

            <!-- Kolom 2: Wilayah Populer -->
            <div class="lg:col-span-3 flex flex-col gap-2">
                <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider mb-2">
                    Kabupaten &amp; Kota
                </h3>
                <ul class="flex flex-col gap-2 font-body-md text-body-md text-on-surface-variant">
                    <?php foreach ($daftarWilayahFooter as $wilayahFooter) : ?>
                        <li>
                            <a href="<?= base_url('/destinasi?kabupaten=' . urlencode($wilayahFooter['kode'])) ?>" class="hover:text-on-surface transition-colors">
                                <?= esc($wilayahFooter['nama']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Kolom 3: Kategori & Layanan -->
            <div class="lg:col-span-2 flex flex-col gap-2">
                <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider mb-2">
                    Kategori
                </h3>
                <ul class="flex flex-col gap-2 font-body-md text-body-md text-on-surface-variant">
                    <?php foreach ($daftarKategoriFooter as $katFooter) : ?>
                        <li>
                            <a href="<?= base_url('/destinasi?kategori=' . urlencode($katFooter['slug'])) ?>" class="hover:text-on-surface transition-colors">
                                <?= esc($katFooter['nama']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Kolom 4: Navigasi Cepat -->
            <div class="lg:col-span-3 flex flex-col gap-3">
                <h3 class="font-label-caps text-label-caps text-tertiary-container uppercase tracking-wider">
                    Navigasi Cepat
                </h3>
                <ul class="flex flex-col gap-2 font-body-md text-body-md text-on-surface-variant">
                    <li><a href="<?= base_url('/destinasi') ?>" class="hover:text-on-surface transition-colors">Katalog Destinasi</a></li>
                    <li><a href="<?= base_url('/pemesanan') ?>" class="hover:text-on-surface transition-colors">Tiket Saya</a></li>
                    <?php if (! empty(session('id_pengguna'))) : ?>
                        <li><a href="<?= base_url('/favorit-saya') ?>" class="hover:text-on-surface transition-colors">Favorit Saya</a></li>
                        <li><a href="<?= base_url('/keluar') ?>" class="text-error hover:underline transition-colors">Keluar Akun</a></li>
                    <?php else : ?>
                        <li><a href="<?= base_url('/masuk') ?>" class="hover:text-on-surface transition-colors">Masuk Akun</a></li>
                        <li><a href="<?= base_url('/daftar') ?>" class="hover:text-on-surface transition-colors">Daftar Akun Baru</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Baris Hak Cipta -->
        <div class="mt-10 pt-6 border-t border-surface-variant/40 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="font-body-sm text-body-sm text-on-surface-variant text-center sm:text-left">
                © 2026 JejakSumut — Politeknik Negeri Medan. Manajemen Database Client Server.
            </p>
            <div class="flex items-center gap-4 font-body-sm text-body-sm text-on-surface-variant">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-secondary" aria-hidden="true">eco</span>
                    Pariwisata Berkelanjutan
                </span>
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-primary" aria-hidden="true">explore</span>
                    Sumatera Utara
                </span>
            </div>
        </div>
    </div>
</footer>
