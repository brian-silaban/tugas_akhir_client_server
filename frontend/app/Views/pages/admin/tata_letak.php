<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($judul_halaman) ?> · Pengelola JejakSumut</title>
    <link rel="icon" type="image/png" href="<?= esc(base_url('assets/logo-jejaksumut.png'), 'attr') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&amp;family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;family=Material+Symbols+Outlined&amp;display=swap">
    <link rel="stylesheet" href="<?= esc(base_url('css/panel-admin.css'), 'attr') ?>">
</head>
<body>
    <a class="lewati" href="#isi">Lewati ke konten</a>
    <aside class="navigasi">
        <a class="merek" href="<?= esc(site_url('admin'), 'attr') ?>">
            <img width="36" height="36" src="<?= esc(base_url('assets/logo-jejaksumut.png'), 'attr') ?>" alt="">
            <span><strong>JejakSumut</strong><small>Sumatera Utara</small></span>
        </a>
        <nav aria-label="Panel Pengelola">
            <p class="label-navigasi">Ruang pengelola</p>
            <?php foreach ([
                'admin' => ['Dasbor', 'space_dashboard'],
                'admin/destinasi' => ['Destinasi', 'landscape'],
                'admin/pemesanan' => ['Pemesanan', 'confirmation_number'],
                'admin/ulasan' => ['Moderasi ulasan', 'rate_review'],
            ] as $rute => [$label, $ikon]): ?>
                <?php $aktif = $rute === 'admin' ? uri_string() === 'admin' : str_starts_with(uri_string(), $rute); ?>
                <a href="<?= esc(site_url($rute), 'attr') ?>" <?= $aktif ? 'aria-current="page"' : '' ?>>
                    <span class="material-symbols-outlined" aria-hidden="true"><?= esc($ikon) ?></span>
                    <?= esc($label) ?>
                </a>
            <?php endforeach ?>
        </nav>
        <div class="kaki-navigasi">
            <a class="tautan-wisatawan" href="<?= esc(site_url('/'), 'attr') ?>">
                <span class="material-symbols-outlined" aria-hidden="true">travel_explore</span>
                Laman wisatawan
                <span class="material-symbols-outlined" aria-hidden="true">arrow_outward</span>
            </a>
            <form method="post" action="<?= esc(site_url('admin/keluar'), 'attr') ?>">
                <?= csrf_field() ?>
                <button class="tombol tombol-ghost" type="submit">
                    <span class="material-symbols-outlined" aria-hidden="true">logout</span> Keluar
                </button>
            </form>
        </div>
    </aside>

    <main id="isi" class="konten" tabindex="-1">
        <header class="kepala-halaman">
            <div>
                <p class="label-halaman">Panel Admin / JejakSumut</p>
                <h1><?= esc($judul_halaman) ?></h1>
                <p class="pengantar-halaman"><?= $this->renderSection('pengantar_halaman') ?></p>
            </div>
            <?= $this->renderSection('aksi_halaman') ?>
        </header>

        <?php $pesanGagal = session()->getFlashdata('error') ?? $pesan_api ?? null ?>
        <?php if ($pesanGagal): ?>
            <p class="notifikasi gagal" role="alert"><?= esc($pesanGagal) ?></p>
        <?php endif ?>
        <?php if ($pesanSukses = session()->getFlashdata('success')): ?>
            <p class="notifikasi sukses" role="status"><?= esc($pesanSukses) ?></p>
        <?php endif ?>
        <?= $this->renderSection('isi') ?>
    </main>
</body>
</html>
