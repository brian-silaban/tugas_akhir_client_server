<!-- ============================================================
     Komponen: Bagian Ulasan & Rating Bintang
     Dipakai di: Halaman Detail Destinasi (Anggota 1)
     Cara pakai: view('components/bagian_ulasan', ['id_destinasi' => ..., 'slug_destinasi' => ...])
     Anggota 4 — Sistem Ulasan & Rating (1–5 Bintang)
     ============================================================ -->

<?php
    // Ambil data ulasan dari Backend REST API
    $urlApiBackend = env('INDWALK_BACKEND_API_URL', 'http://localhost:8080/api/v1');
    $daftarUlasan  = [];
    $rataRating    = 0;
    $jumlahUlasan  = 0;

    try {
        $klienHttp   = \Config\Services::curlrequest();
        $responApi   = $klienHttp->get($urlApiBackend . '/ulasan?id_destinasi=' . (int) ($id_destinasi ?? 0), ['timeout' => 3]);
        $hasilJson   = json_decode($responApi->getBody(), true);
        $daftarUlasan = $hasilJson['data']          ?? [];
        $rataRating   = $hasilJson['rata_rating']   ?? 0;
        $jumlahUlasan = $hasilJson['jumlah_ulasan'] ?? 0;
    } catch (\Throwable $e) {
        // Diam-diam tangani error agar halaman detail tidak crash
    }

    // Cek apakah pengguna sudah login (dari sesi Anggota 3)
    $idPengguna = session('id_pengguna');
?>

<section class="mt-12 pt-8 border-t border-stone-200" aria-label="Ulasan Wisatawan">

    <!-- Header Bagian Ulasan -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="font-serif text-2xl font-bold text-stone-900">Ulasan Wisatawan</h2>
            <p class="text-stone-500 text-sm mt-1">Kata mereka yang sudah berkunjung langsung</p>
        </div>
        <!-- Ringkasan Rating Agregat -->
        <?php if ($jumlahUlasan > 0): ?>
            <div class="flex items-center gap-3 bg-amber-50 border border-amber-100 rounded-2xl px-5 py-3">
                <span class="text-3xl font-bold text-stone-900 tabular-nums"><?= number_format((float) $rataRating, 1) ?></span>
                <div>
                    <div class="flex gap-0.5 text-amber-500 text-sm">
                        <?php for ($bintang = 1; $bintang <= 5; $bintang++): ?>
                            <span><?= $bintang <= round((float) $rataRating) ? '★' : '☆' ?></span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-xs text-stone-500 mt-0.5 tabular-nums"><?= number_format($jumlahUlasan) ?> ulasan</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pesan Flash dari pengiriman ulasan -->
    <?php if (session('pesan_sukses')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
            ✅ <?= esc(session('pesan_sukses')) ?>
        </div>
    <?php endif; ?>
    <?php if (session('pesan_error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
            ⚠️ <?= esc(session('pesan_error')) ?>
        </div>
    <?php endif; ?>

    <!-- Form Tulis Ulasan (hanya tampil jika sudah login) -->
    <?php if (! empty($idPengguna)): ?>
        <div class="bg-stone-50 border border-stone-200 rounded-2xl p-5 mb-8">
            <h3 class="font-semibold text-stone-900 mb-4 text-sm">Bagikan Pengalaman Wisatamu</h3>
            <form action="<?= base_url('ulasan/kirim') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="id_destinasi"   value="<?= esc($id_destinasi ?? '') ?>">
                <input type="hidden" name="slug_destinasi" value="<?= esc($slug_destinasi ?? '') ?>">

                <!-- Pilihan Bintang Rating -->
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-stone-600 mb-2 uppercase tracking-wide">Rating Bintang</label>
                    <div class="flex gap-1" role="radiogroup" aria-label="Pilih rating bintang">
                        <?php for ($bintang = 5; $bintang >= 1; $bintang--): ?>
                            <label class="cursor-pointer text-2xl text-stone-300 hover:text-amber-400 peer-checked:text-amber-400 transition-colors"
                                   title="<?= $bintang ?> Bintang">
                                <input type="radio" name="rating" value="<?= $bintang ?>" class="sr-only peer" required>
                                ★
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Kotak Komentar -->
                <div class="mb-4">
                    <label for="komentar-ulasan" class="block text-xs font-semibold text-stone-600 mb-2 uppercase tracking-wide">
                        Komentar (minimal 10 karakter)
                    </label>
                    <textarea id="komentar-ulasan"
                              name="komentar"
                              rows="4"
                              required
                              minlength="10"
                              placeholder="Ceritakan pengalamanmu: keindahan, jalur akses, harga, fasilitas, atau tips buat pengunjung berikutnya..."
                              class="w-full border border-stone-300 rounded-xl px-4 py-3 text-sm text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-[#0B6E77] focus:border-transparent resize-none transition"></textarea>
                </div>

                <!-- Tombol Kirim -->
                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-[#0B6E77] text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-[#00545B] active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-[#0B6E77] focus:ring-offset-2">
                        Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- Ajakan Login untuk Menulis Ulasan -->
        <div class="bg-stone-50 border border-stone-200 rounded-2xl p-5 mb-8 text-center">
            <p class="text-stone-600 text-sm mb-3">
                Punya pengalaman di sini? <span class="font-semibold">Login</span> untuk berbagi ulasan dan rating bintangmu!
            </p>
            <a href="<?= base_url('masuk') ?>"
               class="inline-block bg-[#C2410C] text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-[#B53801] transition-colors">
                Login untuk Menulis Ulasan
            </a>
        </div>
    <?php endif; ?>

    <!-- Daftar Ulasan dari Wisatawan -->
    <?php if (empty($daftarUlasan)): ?>
        <div class="text-center py-10 text-stone-400">
            <p class="text-4xl mb-3">📝</p>
            <p class="text-sm">Belum ada ulasan untuk destinasi ini. Jadilah yang pertama!</p>
        </div>
    <?php else: ?>
        <div class="space-y-5">
            <?php foreach ($daftarUlasan as $ulasan): ?>
                <article class="bg-white rounded-xl border border-stone-200 p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <!-- Profil Pengulas -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#0B6E77]/10 flex items-center justify-center text-[#0B6E77] font-bold text-sm flex-shrink-0">
                                <?= mb_strtoupper(mb_substr((string) ($ulasan['nama_pengulas'] ?? 'A'), 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-semibold text-sm text-stone-900"><?= esc($ulasan['nama_pengulas'] ?? 'Wisatawan') ?></p>
                                <p class="text-xs text-stone-400 tabular-nums"><?= date('d M Y', strtotime($ulasan['dibuat_pada'] ?? '')) ?></p>
                            </div>
                        </div>
                        <!-- Bintang Rating -->
                        <div class="flex gap-0.5 text-amber-500 text-sm flex-shrink-0" aria-label="Rating <?= (int) ($ulasan['rating'] ?? 0) ?> dari 5">
                            <?php for ($bintang = 1; $bintang <= 5; $bintang++): ?>
                                <span><?= $bintang <= (int) ($ulasan['rating'] ?? 0) ? '★' : '☆' ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <!-- Isi Komentar -->
                    <p class="text-sm text-stone-700 leading-relaxed"><?= esc($ulasan['komentar'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<!-- Script interaktif: highlight bintang yang dipilih -->
<script>
    (function () {
        const grupBintang = document.querySelector('[role="radiogroup"]');
        if (!grupBintang) return;

        const labelBintang = grupBintang.querySelectorAll('label');

        grupBintang.addEventListener('change', function (e) {
            const nilaiDipilih = parseInt(e.target.value, 10);
            // Bintang dirender terbalik (5 ke 1), sesuaikan highlight
            labelBintang.forEach((label, index) => {
                const nilaiLabel = 5 - index;
                if (nilaiLabel <= nilaiDipilih) {
                    label.classList.remove('text-stone-300');
                    label.classList.add('text-amber-400');
                } else {
                    label.classList.remove('text-amber-400');
                    label.classList.add('text-stone-300');
                }
            });
        });
    })();
</script>
