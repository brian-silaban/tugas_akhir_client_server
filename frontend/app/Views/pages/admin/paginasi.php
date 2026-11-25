<?php
$halaman = (int) ($paginasi['halaman'] ?? $paginasi['halaman_sekarang'] ?? 1);
$totalHalaman = (int) ($paginasi['total_halaman'] ?? 1);
$tautanHalaman = static fn (int $nomor): string =>
    site_url($rute_paginasi) . '?' . http_build_query(['halaman' => $nomor] + ($query_paginasi ?? []));
?>
<?php if ($totalHalaman > 1): ?>
    <nav class="paginasi" aria-label="Paginasi">
        <?php if ($halaman > 1): ?>
            <a class="tombol tombol-ghost" rel="prev" href="<?= esc($tautanHalaman($halaman - 1), 'attr') ?>">Sebelumnya</a>
        <?php endif ?>
        <span>Halaman <?= esc($halaman) ?> dari <?= esc($totalHalaman) ?></span>
        <?php if ($halaman < $totalHalaman): ?>
            <a class="tombol tombol-ghost" rel="next" href="<?= esc($tautanHalaman($halaman + 1), 'attr') ?>">Berikutnya</a>
        <?php endif ?>
    </nav>
<?php endif ?>
