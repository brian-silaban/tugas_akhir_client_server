<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<div class="w-full">
    <!-- Seksi Pengantar Editorial JejakSumut -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-14 border-b border-surface-variant/50">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <div class="lg:col-span-7">
                <span class="font-label-caps text-label-caps uppercase text-primary tracking-wider">
                    TENTANG PLATFORM
                </span>
                <h1 class="font-display-hero text-display-hero-mobile sm:text-display-hero text-on-surface tracking-tight mt-2">
                    Mengenal JejakSumut Lebih Dekat
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mt-5 leading-relaxed max-w-2xl">
                    JejakSumut dibangun sebagai platform panduan perjalanan terpadu untuk memperkenalkan kekayaan alam, sejarah, dan budaya di seluruh kabupaten dan kota Provinsi Sumatera Utara dalam satu pengalaman web yang nyaman.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a
                        href="<?= base_url('destinasi') ?>"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
                    >
                        <span>Jelajahi Destinasi</span>
                        <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
                    </a>
                    <a
                        href="<?= base_url('penginapan') ?>"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-primary text-primary hover:bg-primary-container hover:text-on-primary font-label-md text-label-md transition-colors"
                    >
                        <span>Lihat Penginapan</span>
                    </a>
                </div>
            </div>

            <!-- Pilar Layanan Terhubung (Daftar Editorial Bersih dengan Garis Pembatas) -->
            <div class="lg:col-span-5 bg-surface-container-low rounded-3xl p-6 sm:p-8 border border-surface-variant/50 divide-y divide-surface-variant/60">
                <div class="pb-5">
                    <span class="font-label-caps text-label-caps text-tertiary-container uppercase">01 • Eksplorasi Wisata</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface mt-1">Katalog Destinasi Sumatera Utara</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5">
                        Informasi lengkap destinasi dari Toba, Karo, Langkat, Medan, hingga Kepulauan Nias beserta pemesanan tiket kunjungan dan ulasan.
                    </p>
                </div>
                <div class="py-5">
                    <span class="font-label-caps text-label-caps text-primary uppercase">02 • Akomodasi Mitra</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface mt-1">Terhubung dengan Singgah Medan</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5">
                        Menyajikan pilihan hotel terverifikasi lengkap dengan pencarian kecamatan, status sarapan, jarak lokasi, dan ulasan hotel secara langsung.
                    </p>
                </div>
                <div class="pt-5">
                    <span class="font-label-caps text-label-caps text-secondary uppercase">03 • Rute Perjalanan</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface mt-1">Terhubung dengan Mitra Transportasi</h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5">
                        Membantu wisatawan memeriksa estimasi tarif antar simpul dan rekomendasi rute perjalanan menuju koordinat objek wisata.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Seksi Tim Pengembang Kelas TRPL 5A -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <span class="font-label-caps text-label-caps uppercase text-secondary tracking-wider">
                    POLITEKNIK NEGERI MEDAN • KELAS TRPL 5A
                </span>
                <h2 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-1">
                    Tim Pengembang JejakSumut
                </h2>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md">
                Dikembangkan oleh mahasiswa Program Studi D-4 Teknologi Rekayasa Perangkat Lunak (TRPL 5A) pada mata kuliah Manajemen Database Client-Server.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($daftarAnggotaTim ?? [] as $anggota) : ?>
                <article class="bg-surface-container-lowest rounded-2xl p-6 border border-surface-variant/60 shadow-sm flex flex-col justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center font-headline-sm text-headline-sm shrink-0">
                                <?= esc($anggota['inisial'] ?? 'JS') ?>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-headline-sm text-headline-sm text-on-surface leading-snug">
                                    <?= esc($anggota['nama'] ?? '') ?>
                                </h3>
                                <p class="font-body-sm text-body-sm text-primary font-semibold tabular-nums mt-0.5">
                                    NIM <?= esc($anggota['nim'] ?? '') ?> • TRPL 5A
                                </p>
                            </div>
                        </div>

                        <p class="font-body-md text-body-md text-on-surface-variant mt-4 leading-relaxed">
                            <?= esc($anggota['fokus'] ?? '') ?>
                        </p>
                    </div>

                    <div class="pt-3 border-t border-surface-variant/40">
                        <span class="font-label-caps text-label-caps text-outline uppercase block">Tanggung Jawab Modul</span>
                        <span class="font-label-md text-label-md text-on-surface mt-0.5 block">
                            <?= esc($anggota['peran'] ?? '') ?>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
