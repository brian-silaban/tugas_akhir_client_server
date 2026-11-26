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
            <img width="38" height="38" src="<?= esc(base_url('assets/logo-jejaksumut.png'), 'attr') ?>" alt="">
            <span><strong>JejakSumut</strong><small>Panel Pengelola</small></span>
        </a>
        <nav aria-label="Panel Pengelola">
            <p class="label-navigasi">Menu Utama</p>
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
            <?php
            $namaAdmin = (string) (session('nama') ?? 'Admin JejakSumut');
            $emailAdmin = (string) (session('email') ?? 'admin@jejaksumut.id');
            $inisialAdmin = strtoupper(substr($namaAdmin !== '' ? $namaAdmin : 'A', 0, 1));
            ?>
            <div class="kartu-pengelola">
                <span class="avatar-pengelola" aria-hidden="true"><?= esc($inisialAdmin) ?></span>
                <div>
                    <strong><?= esc($namaAdmin) ?></strong>
                    <small><?= esc($emailAdmin) ?></small>
                </div>
            </div>
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
                <p class="label-halaman">Panel Pengelola · Sumatera Utara</p>
                <h1><?= esc($judul_halaman) ?></h1>
                <p class="pengantar-halaman"><?= $this->renderSection('pengantar_halaman') ?></p>
            </div>
            <?= $this->renderSection('aksi_halaman') ?>
        </header>

        <?php
        $pesanGagal = session()->getFlashdata('error') ?? $pesan_api ?? null;
        $pesanSukses = session()->getFlashdata('success');
        ?>
        <div id="wadah-notifikasi-swal"
             data-swal-sukses="<?= esc((string) ($pesanSukses ?? ''), 'attr') ?>"
             data-swal-gagal="<?= esc((string) ($pesanGagal ?? ''), 'attr') ?>"
             aria-hidden="true"></div>

        <noscript>
            <?php if ($pesanGagal): ?>
                <p class="notifikasi gagal" role="alert"><?= esc($pesanGagal) ?></p>
            <?php endif ?>
            <?php if ($pesanSukses): ?>
                <p class="notifikasi sukses" role="status"><?= esc($pesanSukses) ?></p>
            <?php endif ?>
        </noscript>

        <?= $this->renderSection('isi') ?>
    </main>

    <script src="<?= esc(base_url('js/sweetalert2.all.min.js'), 'attr') ?>"></script>
    <script src="<?= esc(base_url('js/notifikasi-swal.js?v=' . @filemtime(FCPATH . 'js/notifikasi-swal.js')), 'attr') ?>"></script>
</body>
</html>
