<?php
/**
 * Komponen: Bagian Ulasan & Rating Bintang
 * Dipakai di halaman detail destinasi (Anggota 1):
 *   view('components/bagian_ulasan', ['id_destinasi' => ..., 'slug_destinasi' => ...])
 *
 * Anggota 4 — Sistem Ulasan & Rating (1–5 Bintang)
 */

use App\Libraries\LayananApiBackend;

// Gunakan ulasan yang sudah dimuat pada detail destinasi agar tidak memicu request HTTP ganda
$daftarUlasan    = [];
$rataRating      = 0;
$jumlahUlasan    = 0;
$terhubungUlasan = true;

if (isset($ulasan_awal) && is_array($ulasan_awal)) {
    $daftarUlasan = $ulasan_awal;
    $rataRating   = (float) ($rata_rating_awal ?? 0);
    $jumlahUlasan = (int) ($jumlah_ulasan_awal ?? count($daftarUlasan));
} else {
    $layananApi      = new LayananApiBackend();
    $hasilApi        = $layananApi->ambilDaftarUlasan((int) ($id_destinasi ?? 0));
    $terhubungUlasan = (bool) ($hasilApi['terhubung'] ?? false);

    if (! empty($hasilApi['berhasil'])) {
        $daftarUlasan = is_array($hasilApi['data']) ? $hasilApi['data'] : [];
        $rataRating   = (float) ($hasilApi['isi']['rata_rating'] ?? 0);
        $jumlahUlasan = (int) ($hasilApi['isi']['jumlah_ulasan'] ?? 0);
    }
}

// Form ulasan hanya muncul untuk pengguna yang sudah masuk
$sudahMasuk = (int) session('id_pengguna') > 0;
?>

<section id="ulasan" class="mt-12 pt-8 border-t border-surface-variant/50" aria-labelledby="judul-ulasan">

    <!-- Judul + ringkasan rating -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h2 id="judul-ulasan" class="font-serif text-2xl font-semibold text-stone-900">Ulasan Wisatawan</h2>
            <p class="text-sm text-stone-600 mt-1">Ditulis oleh pengunjung yang sudah datang langsung.</p>
        </div>

        <?php if ($jumlahUlasan > 0) : ?>
            <div class="flex items-center gap-3">
                <span class="text-3xl font-semibold text-stone-900 tabular-nums"><?= esc(number_format($rataRating, 1)) ?></span>
                <div>
                    <div class="flex text-amber-600" aria-label="Rata-rata <?= esc(number_format($rataRating, 1)) ?> dari 5 bintang">
                        <?php for ($bintang = 1; $bintang <= 5; $bintang++) : ?>
                            <?php
                                // Bintang terisi sampai pembulatan rata-rata, sisanya garis luar
                                if ($bintang <= round($rataRating)) {
                                    $gayaIsi = "font-variation-settings: 'FILL' 1;";
                                } else {
                                    $gayaIsi = "font-variation-settings: 'FILL' 0;";
                                }
                            ?>
                            <span class="material-symbols-outlined text-lg" style="<?= $gayaIsi ?>" aria-hidden="true">star</span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-xs text-stone-500 tabular-nums"><?= esc(number_format($jumlahUlasan, 0, ',', '.')) ?> ulasan</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notifikasi cadangan bila JavaScript nonaktif (versi utama tampil via SweetAlert2 toast) -->
    <noscript>
        <?php if (session('pesan_sukses')) : ?>
            <div class="mb-6 flex items-start gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">check_circle</span>
                <span><?= esc(session('pesan_sukses')) ?></span>
            </div>
        <?php endif; ?>
        <?php if (session('pesan_error')) : ?>
            <div class="mb-6 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <span class="material-symbols-outlined text-lg" aria-hidden="true">error</span>
                <span><?= esc(session('pesan_error')) ?></span>
            </div>
        <?php endif; ?>
    </noscript>

    <?php if (! $terhubungUlasan) : ?>
        <p class="mb-6 text-sm text-stone-600">Ulasan belum bisa dimuat karena server sedang tidak terjangkau. Coba muat ulang beberapa saat lagi.</p>
    <?php endif; ?>

    <!-- Form tulis ulasan -->
    <?php if ($sudahMasuk) : ?>
        <form action="<?= base_url('ulasan/kirim') ?>" method="post" class="mb-10 rounded-2xl border border-stone-200 bg-white p-5">
            <?= csrf_field() ?>
            <input type="hidden" name="id_destinasi" value="<?= esc((string) ($id_destinasi ?? '')) ?>">
            <input type="hidden" name="slug_destinasi" value="<?= esc((string) ($slug_destinasi ?? '')) ?>">

            <fieldset class="mb-4">
                <legend class="mb-2 text-sm font-semibold text-stone-800">Rating kamu</legend>
                <div class="flex gap-1" data-pilihan-bintang>
                    <?php for ($bintang = 1; $bintang <= 5; $bintang++) : ?>
                        <label class="cursor-pointer rounded-lg p-1 text-stone-300 transition-colors hover:text-amber-500 focus-within:ring-2 focus-within:ring-primary-container">
                            <input type="radio" name="rating" value="<?= $bintang ?>" class="sr-only" required <?= old('rating') == $bintang ? 'checked' : '' ?>>
                            <span class="material-symbols-outlined text-3xl" aria-hidden="true">star</span>
                            <span class="sr-only"><?= $bintang ?> bintang</span>
                        </label>
                    <?php endfor; ?>
                </div>
            </fieldset>

            <label for="komentar-ulasan" class="mb-2 block text-sm font-semibold text-stone-800">Komentar</label>
            <textarea id="komentar-ulasan" name="komentar" rows="4" required minlength="10"
                      placeholder="Contoh: jalur tangga ke air terjun cukup curam, datang sebelum jam 9 supaya belum ramai."
                      class="w-full resize-none rounded-xl border border-stone-300 px-4 py-3 text-sm text-stone-800 placeholder:text-stone-500 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-primary-container"><?= esc((string) old('komentar')) ?></textarea>
            <p class="mt-1 text-xs text-stone-500">Minimal 10 karakter.</p>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="rounded-lg bg-primary-container px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#00545B] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-container focus-visible:ring-offset-2 active:scale-[0.98]">
                    Kirim ulasan
                </button>
            </div>
        </form>
    <?php else : ?>
        <div class="mb-10 flex flex-col items-start gap-3 border-l-4 border-primary-container bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-stone-700">Pernah ke sini? Masuk ke akunmu untuk memberi rating dan ulasan.</p>
            <a href="<?= base_url('masuk') ?>"
               class="rounded-lg border border-primary-container px-4 py-2 text-sm font-semibold text-primary-container transition hover:bg-primary-container hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-container focus-visible:ring-offset-2">
                Masuk
            </a>
        </div>
    <?php endif; ?>

    <!-- Daftar ulasan -->
    <?php if ($daftarUlasan === []) : ?>
        <p class="text-sm text-stone-600">Belum ada ulasan untuk destinasi ini.</p>
    <?php else : ?>
        <ul class="divide-y divide-stone-200">
            <?php foreach ($daftarUlasan as $ulasan) : ?>
                <?php
                    $namaPengulas = (string) ($ulasan['nama_pengulas'] ?? 'Wisatawan');
                    $inisial      = mb_strtoupper(mb_substr($namaPengulas, 0, 1));
                    $nilaiBintang = (int) ($ulasan['rating'] ?? 0);
                ?>
                <li class="py-5">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-surface-container text-sm font-semibold text-primary-container" aria-hidden="true">
                                <?= esc($inisial) ?>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-stone-900"><?= esc($namaPengulas) ?></p>
                                <p class="text-xs text-stone-500 tabular-nums"><?= esc(date('d M Y', strtotime((string) ($ulasan['dibuat_pada'] ?? 'now')))) ?></p>
                            </div>
                        </div>
                        <div class="flex text-amber-600" aria-label="Rating <?= esc((string) $nilaiBintang) ?> dari 5">
                            <?php for ($bintang = 1; $bintang <= 5; $bintang++) : ?>
                                <?php
                                    if ($bintang <= $nilaiBintang) {
                                        $gayaIsi = "font-variation-settings: 'FILL' 1;";
                                    } else {
                                        $gayaIsi = "font-variation-settings: 'FILL' 0;";
                                    }
                                ?>
                                <span class="material-symbols-outlined text-base" style="<?= $gayaIsi ?>" aria-hidden="true">star</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed text-stone-700"><?= esc((string) ($ulasan['komentar'] ?? '')) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<script>
    // Warnai bintang 1 sampai bintang yang dipilih
    (function () {
        const grupBintang = document.querySelector('[data-pilihan-bintang]');
        if (!grupBintang) return;

        const daftarLabel = grupBintang.querySelectorAll('label');

        function warnaiBintang(nilaiDipilih) {
            daftarLabel.forEach(function (label, indeks) {
                const ikon = label.querySelector('.material-symbols-outlined');
                const terpilih = indeks + 1 <= nilaiDipilih;
                label.classList.toggle('text-amber-600', terpilih);
                label.classList.toggle('text-stone-300', !terpilih);
                ikon.style.fontVariationSettings = terpilih ? "'FILL' 1" : "'FILL' 0";
            });
        }

        grupBintang.addEventListener('change', function (event) {
            warnaiBintang(parseInt(event.target.value, 10));
        });

        // Pertahankan pilihan lama setelah validasi gagal
        const pilihanLama = grupBintang.querySelector('input:checked');
        if (pilihanLama) warnaiBintang(parseInt(pilihanLama.value, 10));
    })();
</script>
