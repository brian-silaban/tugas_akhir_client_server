<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$daftarTiket = is_array($daftar_tiket ?? null) ? $daftar_tiket : [];
$pesanSukses = (string) (session('pesan_sukses') ?? '');
$pesanError  = (string) (session('pesan_error') ?: ($pesan_error ?? ''));

// Peta nama bulan Indonesia agar tanggal konsisten tanpa bergantung pada locale OS server.
$namaBulanId = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
];

$formatTanggalSingkat = static function (?string $tanggal) use ($namaBulanId): string {
    $waktu = strtotime((string) $tanggal);
    if ($waktu === false) {
        return '-';
    }
    $bulan = $namaBulanId[(int) date('n', $waktu)] ?? date('M', $waktu);

    return date('d', $waktu) . ' ' . $bulan . ' ' . date('Y', $waktu);
};
?>

<style>
    @media (prefers-reduced-motion: reduce) {
        .transisi-tiket {
            transition: none !important;
        }
    }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 w-full">

    <div class="mb-8">
        <nav aria-label="Jejak halaman" class="flex items-center gap-2 text-xs text-on-surface-variant mb-3">
            <a href="<?= base_url('/') ?>"
               class="cursor-pointer py-1 hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                Beranda
            </a>
            <span aria-hidden="true">/</span>
            <span class="text-on-surface font-medium" aria-current="page">Tiket Saya</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div class="min-w-0">
                <h1 class="font-serif text-2xl sm:text-4xl font-semibold text-on-surface tracking-tight leading-tight">
                    Tiket Saya
                </h1>
                <p class="mt-2 text-sm text-on-surface-variant max-w-xl">
                    Pantau status pemesanan, buka E-Ticket untuk ditunjukkan di gerbang masuk, atau batalkan pesanan yang belum dibayar.
                </p>
            </div>

            <div class="shrink-0">
                <a href="<?= base_url('destinasi') ?>"
                   class="transisi-tiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-lg bg-tertiary-container px-5 py-2.5 text-sm font-semibold text-on-tertiary hover:bg-tertiary active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">explore</span>
                    <span>Lihat Katalog Wisata</span>
                </a>
            </div>
        </div>
    </div>

    <noscript>
        <?php if ($pesanSukses !== '') : ?>
            <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-secondary/40 bg-[#e8f6ea] px-4 py-3.5 text-sm text-on-secondary-fixed-variant">
                <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">check_circle</span>
                <span><?= esc($pesanSukses) ?></span>
            </div>
        <?php endif; ?>
    </noscript>

    <?php if (! empty($pesan_error)) : ?>
        <div role="alert" class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-error/40 bg-error-container px-4 py-3.5 text-sm text-on-error-container">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">error</span>
                <span><?= esc((string) $pesan_error) ?></span>
            </div>
            <a href="<?= base_url('pemesanan') ?>"
               class="transisi-tiket cursor-pointer inline-flex items-center justify-center gap-1.5 min-h-[44px] shrink-0 rounded-lg border border-on-error-container bg-surface-container-lowest px-4 py-2 text-xs font-semibold text-on-error-container hover:bg-surface-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                <span class="material-symbols-outlined text-base" aria-hidden="true">refresh</span>
                <span>Muat Ulang</span>
            </a>
        </div>
    <?php elseif ($pesanError !== '') : ?>
        <noscript>
            <div role="alert" class="mb-6 flex items-start gap-3 rounded-xl border border-error/40 bg-error-container px-4 py-3.5 text-sm text-on-error-container">
                <span class="material-symbols-outlined text-lg shrink-0" aria-hidden="true">error</span>
                <span><?= esc($pesanError) ?></span>
            </div>
        </noscript>
    <?php endif; ?>

    <?php if ($daftarTiket === [] && $pesanError === '') : ?>
        <section aria-labelledby="judul-tiket-kosong" class="rounded-2xl border border-surface-variant bg-surface-container-lowest px-6 py-12 sm:p-14 text-center max-w-2xl mx-auto">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-surface-container text-primary">
                <span class="material-symbols-outlined text-3xl" aria-hidden="true">confirmation_number</span>
            </div>
            <h2 id="judul-tiket-kosong" class="font-serif text-xl sm:text-2xl font-semibold text-on-surface">
                Belum ada pemesanan tiket
            </h2>
            <p class="mt-2 text-sm text-on-surface-variant max-w-md mx-auto leading-relaxed">
                Akun kamu belum memiliki riwayat tiket wisata. Pilih destinasi Sumatera Utara dari katalog untuk memesan tiket kunjungan.
            </p>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?= base_url('destinasi') ?>"
                   class="transisi-tiket cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-2 min-h-[44px] rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                    <span>Pilih Destinasi Wisata</span>
                </a>
                <a href="<?= base_url('/') ?>"
                   class="transisi-tiket cursor-pointer w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] rounded-lg border border-outline bg-surface-container-lowest px-5 py-2.5 text-sm font-medium text-on-surface hover:bg-surface-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                    Kembali ke Beranda
                </a>
            </div>
        </section>
    <?php elseif ($daftarTiket !== []) : ?>
        <div class="mb-4 flex items-center justify-between text-xs text-on-surface-variant">
            <span class="tabular-nums font-medium">Menampilkan <?= esc((string) count($daftarTiket)) ?> pemesanan</span>
        </div>

        <ul class="space-y-4">
            <?php foreach ($daftarTiket as $tiket) : ?>
                <?php
                    $statusTiket = (string) ($tiket['status'] ?? '');

                    if ($statusTiket === 'lunas') {
                        $kelasStatus = 'bg-[#e8f6ea] text-on-secondary-fixed-variant border border-secondary/35';
                        $ikonStatus  = 'check_circle';
                        $labelStatus = 'Lunas / Aktif';
                    } elseif ($statusTiket === 'dibatalkan') {
                        $kelasStatus = 'bg-surface-container text-on-surface-variant border border-outline/40';
                        $ikonStatus  = 'cancel';
                        $labelStatus = 'Dibatalkan';
                    } else {
                        $kelasStatus = 'bg-[#fef3c7] text-[#78350f] border border-[#d97706]/40';
                        $ikonStatus  = 'schedule';
                        $labelStatus = 'Menunggu Pembayaran';
                    }

                    $kotaDestinasi = trim((string) ($tiket['kota_destinasi'] ?? ''));
                    $lokasiTampil  = $kotaDestinasi !== '' ? $kotaDestinasi . ', Sumatera Utara' : 'Sumatera Utara';
                ?>
                <li class="transisi-tiket rounded-2xl border border-surface-variant bg-surface-container-lowest overflow-hidden hover:border-primary/40 transition-colors">
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-44 md:w-52 shrink-0 bg-surface-container aspect-[16/10] sm:aspect-auto">
                            <?php if (! empty($tiket['foto_destinasi'])) : ?>
                                <img
                                    src="<?= esc((string) $tiket['foto_destinasi']) ?>"
                                    alt="Foto destinasi <?= esc((string) ($tiket['nama_destinasi'] ?? '')) ?>"
                                    width="416"
                                    height="260"
                                    loading="lazy"
                                    class="h-full w-full object-cover"
                                />
                            <?php else : ?>
                                <div class="h-full min-h-[140px] w-full flex flex-col items-center justify-center gap-1 text-on-surface-variant px-3 text-center">
                                    <span class="material-symbols-outlined text-3xl" aria-hidden="true">landscape</span>
                                    <span class="text-xs">Foto belum tersedia</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0 p-4 sm:p-5 flex flex-col justify-between gap-4">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                    <div class="min-w-0">
                                        <h2 class="font-serif text-lg sm:text-xl font-semibold text-on-surface leading-snug break-words">
                                            <?= esc((string) ($tiket['nama_destinasi'] ?? 'Destinasi')) ?>
                                        </h2>
                                        <p class="mt-1 flex items-center gap-1 text-xs text-on-surface-variant">
                                            <span class="material-symbols-outlined text-sm shrink-0" aria-hidden="true">location_on</span>
                                            <span class="truncate"><?= esc($lokasiTampil) ?></span>
                                        </p>
                                    </div>

                                    <span class="inline-flex items-center gap-1.5 self-start rounded-md px-2.5 py-1 text-xs font-semibold whitespace-nowrap <?= $kelasStatus ?>">
                                        <span class="material-symbols-outlined text-sm" aria-hidden="true"><?= $ikonStatus ?></span>
                                        <span><?= esc($labelStatus) ?></span>
                                    </span>
                                </div>

                                <dl class="mt-4 pt-3 border-t border-surface-variant/70 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                    <div class="min-w-0">
                                        <dt class="text-on-surface-variant">Kode Tiket</dt>
                                        <dd class="mt-0.5 font-mono font-semibold text-primary tabular-nums break-all">
                                            <?= esc((string) ($tiket['kode_pemesanan'] ?? '-')) ?>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-on-surface-variant">Tanggal Kunjungan</dt>
                                        <dd class="mt-0.5 font-semibold text-on-surface tabular-nums">
                                            <?= esc($formatTanggalSingkat($tiket['tanggal_kunjungan'] ?? null)) ?>
                                            <span class="font-normal text-on-surface-variant">
                                                (<?= esc((string) ($tiket['jumlah_pengunjung'] ?? 0)) ?> orang)
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-on-surface-variant">Total Bayar</dt>
                                        <dd class="mt-0.5 font-bold text-sm text-on-surface tabular-nums">
                                            <?= esc((string) ($tiket['total_harga_terformat'] ?? '-')) ?>
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <a href="<?= base_url('pemesanan/etiket/' . (int) $tiket['id']) ?>"
                                   class="transisi-tiket cursor-pointer inline-flex items-center justify-center gap-2 min-h-[44px] rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-container active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                                    <span class="material-symbols-outlined text-lg" aria-hidden="true">confirmation_number</span>
                                    <span>Buka E-Ticket</span>
                                </a>

                                <?php if ($statusTiket === 'menunggu') : ?>
                                    <form action="<?= base_url('pemesanan/batal/' . (int) $tiket['id']) ?>"
                                          method="post"
                                          data-form-batal
                                          data-kode="<?= esc((string) ($tiket['kode_pemesanan'] ?? '')) ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="transisi-tiket cursor-pointer inline-flex items-center justify-center gap-1.5 min-h-[44px] rounded-lg border border-outline bg-surface-container-lowest px-4 py-2.5 text-sm font-medium text-on-surface hover:border-on-error-container hover:text-on-error-container hover:bg-error-container/30 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 transition">
                                            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
                                            <span data-teks-tombol>Batalkan Tiket</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</div>

<script>
    // Konfirmasi pembatalan tiket menggunakan SweetAlert2 melalui atribut data-swal-konfirmasi.
    (function () {
        document.querySelectorAll('[data-form-batal]').forEach(function (formulir) {
            var kode = formulir.getAttribute('data-kode') || 'ini';
            formulir.setAttribute('data-swal-konfirmasi', '');
            formulir.setAttribute('data-swal-ikon', 'warning');
            formulir.setAttribute('data-swal-judul', 'Batalkan Tiket?');
            formulir.setAttribute('data-swal-teks', 'Tiket ' + kode + ' yang dibatalkan tidak dapat dikembalikan.');
            formulir.setAttribute('data-swal-tombol-ya', 'Ya, Batalkan Tiket');
            formulir.setAttribute('data-swal-bahaya', 'true');
            formulir.setAttribute('data-teks-proses', 'Membatalkan...');
        });
    })();
</script>
<?= $this->endSection() ?>
