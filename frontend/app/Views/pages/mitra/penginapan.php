<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$kataKunciAktif  = (string) ($filterAktif['q'] ?? '');
$kecamatanAktif  = (string) ($filterAktif['kecamatan'] ?? '');
$sarapanAktif    = (string) ($filterAktif['sarapan'] ?? '');
$ratingMinAktif  = (string) ($filterAktif['rating_min'] ?? '');
$halamanSekarang = max(1, (int) ($metaPaginasi['page'] ?? 1));
$totalHalaman    = max(1, (int) ($metaPaginasi['total_pages'] ?? 1));
$apakahLiveApi   = (($hasilKatalog['sumber_data'] ?? '') === 'api_mitra');
$waktuResponMs   = (int) ($hasilKatalog['waktu_respon_ms'] ?? 0);

// Helper lokal untuk menjaga parameter filter tetap utuh saat berpindah halaman
$bangunUrlHalaman = static function (int $nomorHalaman) use ($filterAktif): string {
    $query = array_filter([
        'q'          => $filterAktif['q'] ?? '',
        'kecamatan'  => $filterAktif['kecamatan'] ?? '',
        'sarapan'    => $filterAktif['sarapan'] ?? '',
        'rating_min' => $filterAktif['rating_min'] ?? '',
        'halaman'    => $nomorHalaman > 1 ? $nomorHalaman : '',
    ], static fn ($nilai): bool => $nilai !== '');

    $akhiran = $query !== [] ? '?' . http_build_query($query) : '';

    return base_url('penginapan' . $akhiran);
};
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <!-- Header Katalog Penginapan -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-surface-variant/50">
        <div>
            <span class="font-label-caps text-label-caps uppercase text-primary tracking-wider">
                MITRA SINGGAH MEDAN
            </span>
            <h1 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-1">
                Penginapan &amp; Hotel Sumatera Utara
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-2xl">
                Telusuri pilihan hotel dan penginapan nyaman di Kota Medan dan sekitarnya, lengkap dengan info sarapan serta ulasan pengunjung.
            </p>
        </div>

        <div class="shrink-0">
            <?php if ($apakahLiveApi) : ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-secondary-container text-on-secondary-fixed-variant font-label-md text-label-md tabular-nums">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">cloud_done</span>
                    <span>Terhubung Langsung<?= $waktuResponMs > 0 ? ' • ' . esc((string) $waktuResponMs) . ' ms' : '' ?></span>
                </span>
            <?php else : ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-surface-container text-on-surface-variant border border-surface-variant/70 font-label-md text-label-md">
                    <span class="material-symbols-outlined text-base text-amber-600" aria-hidden="true">cloud_sync</span>
                    <span>Mode Simulasi</span>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form Filter Multi-Kriteria -->
    <form action="<?= base_url('penginapan') ?>" method="get" class="mt-6 bg-surface-container-lowest rounded-2xl p-4 sm:p-5 border border-surface-variant/50 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
        <div class="lg:col-span-4">
            <label for="filter-hotel-q" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Cari Nama Hotel</label>
            <input
                id="filter-hotel-q"
                type="text"
                name="q"
                value="<?= esc($kataKunciAktif) ?>"
                placeholder="Misalnya: Adimulia, Kesawan, Santika..."
                class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary"
            />
        </div>

        <div class="lg:col-span-3">
            <label for="filter-hotel-kecamatan" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Kecamatan</label>
            <select id="filter-hotel-kecamatan" name="kecamatan" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                <option value="">Semua Kecamatan</option>
                <?php foreach ($daftarKecamatan ?? [] as $itemKecamatan) : ?>
                    <?php $namaKec = (string) ($itemKecamatan['kecamatan'] ?? ''); ?>
                    <?php if ($namaKec !== '') : ?>
                        <option value="<?= esc($namaKec) ?>" <?= $kecamatanAktif === $namaKec ? 'selected' : '' ?>>
                            <?= esc($namaKec) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="lg:col-span-2">
            <label for="filter-hotel-sarapan" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Sarapan</label>
            <select id="filter-hotel-sarapan" name="sarapan" class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                <option value="">Semua Pilihan</option>
                <option value="ya" <?= $sarapanAktif === 'ya' ? 'selected' : '' ?>>Termasuk Sarapan</option>
            </select>
        </div>

        <div class="lg:col-span-1">
            <label for="filter-hotel-rating" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Rating</label>
            <select id="filter-hotel-rating" name="rating_min" class="w-full px-2.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/50 font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary cursor-pointer">
                <option value="">Semua</option>
                <option value="4" <?= $ratingMinAktif === '4' ? 'selected' : '' ?>>4.0+</option>
                <option value="4.5" <?= $ratingMinAktif === '4.5' ? 'selected' : '' ?>>4.5+</option>
            </select>
        </div>

        <div class="lg:col-span-2 flex gap-2">
            <button
                type="submit"
                class="flex-1 py-2.5 px-4 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm"
            >
                Terapkan
            </button>
            <a
                href="<?= base_url('penginapan') ?>"
                class="py-2.5 px-3 rounded-xl border border-outline-variant text-on-surface-variant hover:bg-surface-container font-label-md text-label-md transition-colors"
            >
                Reset
            </a>
        </div>
    </form>

    <!-- Daftar Kartu Penginapan -->
    <?php if (empty($daftarHotel)) : ?>
        <div class="mt-10 bg-surface-container-lowest rounded-3xl p-12 text-center border border-surface-variant/40">
            <span class="material-symbols-outlined text-5xl text-outline" aria-hidden="true">hotel_class</span>
            <h2 class="font-headline-md text-headline-md text-on-surface mt-3">
                Penginapan Tidak Ditemukan
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-md mx-auto">
                Tidak ada penginapan yang sesuai dengan kata kunci atau filter yang dipilih. Coba reset filter untuk melihat seluruh daftar hotel.
            </p>
            <a href="<?= base_url('penginapan') ?>" class="inline-flex mt-6 px-5 py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-md text-label-md transition-colors">
                Tampilkan Semua Penginapan
            </a>
        </div>
    <?php else : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <?php foreach ($daftarHotel as $hotel) : ?>
                <?php
                $idHotel        = (int) ($hotel['id'] ?? 0);
                $namaHotel      = (string) ($hotel['nama'] ?? 'Penginapan Medan');
                $kecamatanHotel = (string) ($hotel['kecamatan'] ?? $hotel['kota'] ?? 'Medan');
                $adaSarapan     = ! empty($hotel['ada_sarapan']);
                $ratingHotel    = (float) ($hotel['rating'] ?? 0);
                $ulasanHotel    = (int) ($hotel['jumlah_ulasan'] ?? 0);
                $urlEksternal   = trim((string) ($hotel['url_eksternal'] ?? ''));
                $urlValidAman   = (preg_match('/^https?:\/\//i', $urlEksternal) === 1);
                $ulasanUnggulan = is_array($hotel['ulasan_unggulan'] ?? null) ? $hotel['ulasan_unggulan'] : null;
                ?>
                <article class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm border border-surface-variant/40 flex flex-col justify-between h-full group hover:shadow-md transition-shadow">
                    <div>
                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-surface-container">
                            <img
                                src="<?= esc((string) ($hotel['foto'] ?? '')) ?>"
                                alt="<?= esc($namaHotel) ?>"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                            />
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                <span class="font-label-caps text-label-caps px-3 py-1 rounded-full bg-surface-container-lowest/95 backdrop-blur-sm text-primary font-bold shadow-sm">
                                    <?= esc($kecamatanHotel) ?>
                                </span>
                                <?php if ($adaSarapan) : ?>
                                    <span class="font-label-caps text-label-caps px-2.5 py-1 rounded-full bg-secondary text-on-secondary shadow-sm">
                                        Sarapan
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center justify-between gap-2 text-outline font-body-sm text-body-sm">
                                <span class="inline-flex items-center gap-1 truncate">
                                    <span class="material-symbols-outlined text-xs text-primary" aria-hidden="true">pin_drop</span>
                                    <?= esc((string) ($hotel['kota'] ?? 'Medan')) ?>
                                </span>
                                <span class="font-metric-rating text-metric-rating text-on-surface inline-flex items-center gap-0.5 shrink-0 tabular-nums">
                                    <span class="material-symbols-outlined text-xs text-amber-600" style="font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                                    <?= esc(number_format($ratingHotel, 1, '.', '')) ?>
                                    <span class="text-outline font-normal text-xs">(<?= esc(number_format($ulasanHotel, 0, ',', '.')) ?>)</span>
                                </span>
                            </div>

                            <h2 class="font-headline-sm text-headline-sm text-on-surface mt-2 line-clamp-2">
                                <?= esc($namaHotel) ?>
                            </h2>

                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-2 line-clamp-2">
                                <?= esc((string) ($hotel['alamat'] ?? '')) ?>
                            </p>

                            <?php if ($ulasanUnggulan !== null && ! empty($ulasanUnggulan['judul'])) : ?>
                                <p class="mt-3 pt-3 border-t border-surface-variant/30 font-body-sm text-body-sm text-outline italic line-clamp-2">
                                    “<?= esc((string) $ulasanUnggulan['judul']) ?>”
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-3 border-t border-surface-variant/30 flex items-center justify-between gap-2">
                        <?php if ($apakahLiveApi && $idHotel > 0) : ?>
                            <button
                                type="button"
                                data-buka-ulasan-hotel="<?= esc((string) $idHotel) ?>"
                                data-nama-hotel="<?= esc($namaHotel) ?>"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-surface-container hover:bg-primary-container hover:text-on-primary text-on-surface font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            >
                                <span class="material-symbols-outlined text-base" aria-hidden="true">rate_review</span>
                                <span>Ulasan</span>
                            </button>
                        <?php else : ?>
                            <span class="font-body-sm text-body-sm text-outline">
                                <?= esc($adaSarapan ? 'Sarapan tersedia' : 'Penginapan Medan') ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($urlValidAman) : ?>
                            <a
                                href="<?= esc($urlEksternal) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-primary text-primary hover:bg-primary hover:text-on-primary font-label-md text-label-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            >
                                <span>Singgah Medan</span>
                                <span class="material-symbols-outlined text-sm" aria-hidden="true">open_in_new</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Navigasi Paginasi -->
        <?php if ($totalHalaman > 1) : ?>
            <?php
            $halamanAwal  = max(1, $halamanSekarang - 2);
            $halamanAkhir = min($totalHalaman, $halamanSekarang + 2);
            ?>
            <nav class="mt-12 flex flex-wrap items-center justify-center gap-2" aria-label="Paginasi katalog penginapan">
                <?php if ($halamanSekarang > 1) : ?>
                    <a href="<?= esc($bangunUrlHalaman($halamanSekarang - 1)) ?>" class="px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors">
                        ← Sebelumnya
                    </a>
                <?php endif; ?>

                <?php for ($hal = $halamanAwal; $hal <= $halamanAkhir; $hal++) : ?>
                    <a
                        href="<?= esc($bangunUrlHalaman($hal)) ?>"
                        class="<?= $hal === $halamanSekarang
                            ? 'px-3.5 py-2 rounded-xl bg-primary-container text-on-primary font-label-md text-label-md tabular-nums shadow-sm'
                            : 'px-3.5 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md tabular-nums transition-colors' ?>"
                        <?= $hal === $halamanSekarang ? 'aria-current="page"' : '' ?>
                    >
                        <?= esc((string) $hal) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($halamanSekarang < $totalHalaman) : ?>
                    <a href="<?= esc($bangunUrlHalaman($halamanSekarang + 1)) ?>" class="px-4 py-2 rounded-xl bg-surface-container hover:bg-surface-container-high text-on-surface font-label-md text-label-md transition-colors">
                        Berikutnya →
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- Modal Ulasan Lintas-Layanan Singgah Medan -->
<div
    id="modal-ulasan-hotel"
    class="hidden fixed inset-0 z-50 bg-on-surface/55 backdrop-blur-sm flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="judul-modal-ulasan-hotel"
>
    <div class="bg-surface-container-lowest rounded-3xl max-w-2xl w-full max-h-[88vh] flex flex-col border border-surface-variant shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-surface-variant/50 flex items-center justify-between gap-4">
            <div>
                <span class="font-label-caps text-label-caps text-primary uppercase block">Ulasan Penginapan</span>
                <h2 id="judul-modal-ulasan-hotel" class="font-headline-sm text-headline-sm text-on-surface">
                    Memuat...
                </h2>
            </div>
            <button
                type="button"
                id="tombol-tutup-modal-hotel"
                aria-label="Tutup jendela ulasan"
                class="w-9 h-9 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface flex items-center justify-center transition-colors"
            >
                <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-6">
            <div id="status-modal-ulasan-hotel" class="hidden" aria-live="polite"></div>

            <!-- Daftar Ulasan dari Singgah Medan -->
            <div>
                <h3 class="font-label-caps text-label-caps text-outline uppercase mb-3">Ulasan Pengunjung</h3>
                <div id="daftar-ulasan-hotel" class="space-y-3"></div>
            </div>

            <!-- Formulir Kirim Ulasan Hotel -->
            <div class="pt-5 border-t border-surface-variant/50">
                <h3 class="font-headline-sm text-headline-sm text-on-surface mb-3">Tulis Ulasan Hotel</h3>
                <?php if (! empty($sudahMasuk)) : ?>
                    <form id="form-kirim-ulasan-hotel" class="space-y-4">
                        <input type="hidden" id="input-ulasan-hotel-id" value="" />

                        <div>
                            <label for="input-ulasan-hotel-rating" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Penilaian Bintang</label>
                            <select id="input-ulasan-hotel-rating" class="w-full sm:w-48 px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/60 text-sm text-on-surface focus:outline-none focus:border-primary">
                                <option value="5">5 — Sangat Baik</option>
                                <option value="4">4 — Baik</option>
                                <option value="3">3 — Cukup</option>
                                <option value="2">2 — Kurang</option>
                                <option value="1">1 — Buruk</option>
                            </select>
                        </div>

                        <div>
                            <label for="input-ulasan-hotel-judul" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Judul Ulasan</label>
                            <input
                                id="input-ulasan-hotel-judul"
                                type="text"
                                required
                                minlength="3"
                                maxlength="120"
                                placeholder="Ringkasan pengalaman menginap Anda..."
                                class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/60 text-sm text-on-surface focus:outline-none focus:border-primary"
                            />
                        </div>

                        <div>
                            <label for="input-ulasan-hotel-isi" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">Komentar Ulasan</label>
                            <textarea
                                id="input-ulasan-hotel-isi"
                                rows="3"
                                required
                                minlength="5"
                                maxlength="1000"
                                placeholder="Ceritakan kenyamanan kamar, lokasi, atau pelayanannya..."
                                class="w-full px-3.5 py-2.5 rounded-xl bg-surface-container-low border border-outline-variant/60 text-sm text-on-surface focus:outline-none focus:border-primary"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            id="tombol-kirim-ulasan-hotel"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-tertiary-container hover:bg-tertiary text-on-tertiary font-label-md text-label-md transition-colors shadow-sm disabled:opacity-60"
                        >
                            <span>Kirim Ulasan</span>
                            <span class="material-symbols-outlined text-base" aria-hidden="true">send</span>
                        </button>
                    </form>
                <?php else : ?>
                    <div class="rounded-2xl bg-surface-container-low border border-surface-variant/60 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <p class="font-body-sm text-body-sm text-on-surface-variant">
                            Masuk ke akun JejakSumut terlebih dahulu untuk menambahkan ulasan pada penginapan ini.
                        </p>
                        <a
                            href="<?= base_url('masuk') ?>"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-md text-label-md shrink-0 transition-colors"
                        >
                            Masuk Akun
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Mengelola modal baca, kirim, dan hapus ulasan hotel melalui proxy server JejakSumut
    (function () {
        const modal         = document.getElementById('modal-ulasan-hotel');
        const judulModal    = document.getElementById('judul-modal-ulasan-hotel');
        const tombolTutup   = document.getElementById('tombol-tutup-modal-hotel');
        const statusKotak   = document.getElementById('status-modal-ulasan-hotel');
        const wadahDaftar   = document.getElementById('daftar-ulasan-hotel');
        const formKirim     = document.getElementById('form-kirim-ulasan-hotel');
        const inputHotelId  = document.getElementById('input-ulasan-hotel-id');
        const tombolKirim   = document.getElementById('tombol-kirim-ulasan-hotel');

        if (!modal || !judulModal || !tombolTutup || !wadahDaftar) {
            return;
        }

        const urlDaftarUlasan = <?= json_encode(base_url('mitra/penginapan/ulasan')) ?>;
        const urlDasarHapus   = <?= json_encode(base_url('mitra/penginapan/ulasan')) ?>;
        const headerCsrf      = <?= json_encode(csrf_header()) ?>;
        let tokenCsrfAktif    = <?= json_encode(csrf_hash()) ?>;
        let idHotelAktif      = 0;

        function tampilkanStatus(teks, apakahError) {
            if (!statusKotak) {
                return;
            }
            if (!teks) {
                statusKotak.classList.add('hidden');
                statusKotak.replaceChildren();
                return;
            }

            statusKotak.classList.remove('hidden');
            statusKotak.replaceChildren();

            const pesan = document.createElement('p');
            pesan.className = apakahError
                ? 'rounded-xl border border-error/30 bg-error-container/60 px-4 py-3 text-sm text-on-error-container'
                : 'rounded-xl border border-secondary/30 bg-secondary-container/40 px-4 py-3 text-sm text-on-secondary-fixed-variant';
            pesan.textContent = teks;
            statusKotak.appendChild(pesan);
        }

        function tutupModal() {
            modal.classList.add('hidden');
            tampilkanStatus('', false);
        }

        function renderDaftarUlasan(daftarUlasan, daftarMilikSesi) {
            wadahDaftar.replaceChildren();

            if (!Array.isArray(daftarUlasan) || daftarUlasan.length === 0) {
                const kosong = document.createElement('p');
                kosong.className = 'rounded-2xl bg-surface-container-low border border-surface-variant/50 p-4 text-sm text-on-surface-variant';
                kosong.textContent = 'Belum ada ulasan tercatat untuk penginapan ini.';
                wadahDaftar.appendChild(kosong);
                return;
            }

            const himpunanMilik = new Set((daftarMilikSesi || []).map(Number));

            daftarUlasan.forEach(function (ulasan) {
                const kartu = document.createElement('article');
                kartu.className = 'rounded-2xl bg-surface-container-low border border-surface-variant/60 p-4 space-y-2';

                const barisAtas = document.createElement('div');
                barisAtas.className = 'flex items-start justify-between gap-3';

                const infoPenulis = document.createElement('div');
                const namaPenulis = document.createElement('p');
                namaPenulis.className = 'text-sm font-semibold text-on-surface';
                namaPenulis.textContent = ulasan.penulis || 'Pengunjung';

                const tanggalEl = document.createElement('p');
                tanggalEl.className = 'text-xs text-outline tabular-nums';
                tanggalEl.textContent = ulasan.created_at || '';

                infoPenulis.appendChild(namaPenulis);
                infoPenulis.appendChild(tanggalEl);

                const blokKanan = document.createElement('div');
                blokKanan.className = 'flex items-center gap-2 shrink-0';

                const badgeRating = document.createElement('span');
                badgeRating.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container-lowest border border-surface-variant text-xs font-bold text-on-surface tabular-nums';
                badgeRating.textContent = '★ ' + String(ulasan.rating || 5) + '/5';
                blokKanan.appendChild(badgeRating);

                const idUlasan = Number(ulasan.id || 0);
                if (idUlasan > 0 && himpunanMilik.has(idUlasan)) {
                    const tombolHapus = document.createElement('button');
                    tombolHapus.type = 'button';
                    tombolHapus.className = 'px-2.5 py-1 rounded-lg border border-error/40 text-xs font-semibold text-error hover:bg-error-container/30 transition-colors';
                    tombolHapus.textContent = 'Hapus';
                    tombolHapus.addEventListener('click', function () {
                        hapusUlasanHotel(idUlasan, tombolHapus);
                    });
                    blokKanan.appendChild(tombolHapus);
                }

                barisAtas.appendChild(infoPenulis);
                barisAtas.appendChild(blokKanan);

                const judulEl = document.createElement('h4');
                judulEl.className = 'text-sm font-semibold text-on-surface';
                judulEl.textContent = ulasan.judul || '';

                const isiEl = document.createElement('p');
                isiEl.className = 'text-sm text-on-surface-variant leading-relaxed';
                isiEl.textContent = ulasan.isi || '';

                kartu.appendChild(barisAtas);
                if (judulEl.textContent !== '') {
                    kartu.appendChild(judulEl);
                }
                kartu.appendChild(isiEl);
                wadahDaftar.appendChild(kartu);
            });
        }

        async function muatUlasanHotel(idHotel) {
            wadahDaftar.replaceChildren();
            const memuatEl = document.createElement('p');
            memuatEl.className = 'text-sm text-outline';
            memuatEl.textContent = 'Memuat ulasan terbaru...';
            wadahDaftar.appendChild(memuatEl);

            try {
                const respon = await fetch(urlDaftarUlasan + '?hotel_id=' + encodeURIComponent(idHotel), {
                    headers: { 'Accept': 'application/json' }
                });
                const isiJson = await respon.json();

                if (!respon.ok || isiJson.status !== 'berhasil') {
                    wadahDaftar.replaceChildren();
                    tampilkanStatus(isiJson.pesan || 'Gagal memuat daftar ulasan hotel.', true);
                    return;
                }

                renderDaftarUlasan(isiJson.data || [], isiJson.daftar_id_milik_sesi || []);
            } catch (kesalahan) {
                wadahDaftar.replaceChildren();
                tampilkanStatus('Koneksi ke layanan ulasan terputus.', true);
            }
        }

        async function hapusUlasanHotel(idUlasan, tombolHapus) {
            tombolHapus.disabled = true;
            try {
                const respon = await fetch(urlDasarHapus + '/' + encodeURIComponent(idUlasan) + '/hapus', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        [headerCsrf]: tokenCsrfAktif
                    }
                });
                const isiJson = await respon.json();
                if (isiJson.csrf_token) {
                    tokenCsrfAktif = isiJson.csrf_token;
                }

                if (!respon.ok || isiJson.status !== 'berhasil') {
                    tampilkanStatus(isiJson.pesan || 'Gagal menghapus ulasan.', true);
                    tombolHapus.disabled = false;
                    return;
                }

                tampilkanStatus('Ulasan berhasil dihapus.', false);
                await muatUlasanHotel(idHotelAktif);
            } catch (kesalahan) {
                tampilkanStatus('Gagal menghubungi server saat menghapus ulasan.', true);
                tombolHapus.disabled = false;
            }
        }

        document.querySelectorAll('[data-buka-ulasan-hotel]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                idHotelAktif = Number(tombol.getAttribute('data-buka-ulasan-hotel') || 0);
                const namaHotel = tombol.getAttribute('data-nama-hotel') || 'Penginapan';
                if (idHotelAktif <= 0) {
                    return;
                }

                judulModal.textContent = namaHotel;
                if (inputHotelId) {
                    inputHotelId.value = String(idHotelAktif);
                }
                tampilkanStatus('', false);
                modal.classList.remove('hidden');
                muatUlasanHotel(idHotelAktif);
            });
        });

        tombolTutup.addEventListener('click', tutupModal);
        modal.addEventListener('click', function (acara) {
            if (acara.target === modal) {
                tutupModal();
            }
        });
        document.addEventListener('keydown', function (acara) {
            if (acara.key === 'Escape' && !modal.classList.contains('hidden')) {
                tutupModal();
            }
        });

        if (formKirim && tombolKirim) {
            formKirim.addEventListener('submit', async function (acara) {
                acara.preventDefault();
                const ratingEl = document.getElementById('input-ulasan-hotel-rating');
                const judulEl  = document.getElementById('input-ulasan-hotel-judul');
                const isiEl    = document.getElementById('input-ulasan-hotel-isi');

                const payload = {
                    hotel_id: idHotelAktif,
                    rating: Number(ratingEl ? ratingEl.value : 5),
                    judul: judulEl ? judulEl.value.trim() : '',
                    isi: isiEl ? isiEl.value.trim() : ''
                };

                tombolKirim.disabled = true;
                tampilkanStatus('Mengirim ulasan Anda...', false);

                try {
                    const respon = await fetch(urlDaftarUlasan, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            [headerCsrf]: tokenCsrfAktif
                        },
                        body: JSON.stringify(payload)
                    });
                    const isiJson = await respon.json();
                    if (isiJson.csrf_token) {
                        tokenCsrfAktif = isiJson.csrf_token;
                    }

                    if (!respon.ok || isiJson.status !== 'berhasil') {
                        tampilkanStatus(isiJson.pesan || 'Gagal mengirim ulasan.', true);
                        return;
                    }

                    if (judulEl) {
                        judulEl.value = '';
                    }
                    if (isiEl) {
                        isiEl.value = '';
                    }
                    tampilkanStatus('Terima kasih, ulasan Anda telah ditambahkan.', false);
                    await muatUlasanHotel(idHotelAktif);
                } catch (kesalahan) {
                    tampilkanStatus('Koneksi terputus saat mengirim ulasan.', true);
                } finally {
                    tombolKirim.disabled = false;
                }
            });
        }
    })();
</script>
<?= $this->endSection() ?>
