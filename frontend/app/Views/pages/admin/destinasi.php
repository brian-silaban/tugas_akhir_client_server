<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('pengantar_halaman') ?>
Kelola informasi, harga tiket, dan lokasi destinasi pada katalog wisatawan.
<?= $this->endSection() ?>
<?= $this->section('aksi_halaman') ?>
<a class="tombol tombol-utama" href="<?= esc(site_url('admin/destinasi/tambah'), 'attr') ?>">
    <span class="material-symbols-outlined" aria-hidden="true">add</span> Tambah destinasi
</a>
<?= $this->endSection() ?>

<?= $this->section('isi') ?>
<div class="bilah">
    <form method="get" action="<?= esc(site_url('admin/destinasi'), 'attr') ?>" role="search">
        <label for="kata_kunci">Cari destinasi</label>
        <div class="aksi">
            <input id="kata_kunci" type="search" name="kata_kunci" value="<?= esc($kata_kunci, 'attr') ?>">
            <button class="tombol tombol-ghost" type="submit">Cari</button>
            <?php if ($kata_kunci !== ''): ?>
                <a class="tombol tombol-ghost" href="<?= esc(site_url('admin/destinasi'), 'attr') ?>">Reset</a>
            <?php endif ?>
        </div>
    </form>
    <?php if ($paginasi !== []): ?><p><?= esc(number_format($paginasi['total_data'], 0, ',', '.')) ?> destinasi</p><?php endif ?>
</div>

<div class="bungkus-tabel" role="region" aria-label="Daftar destinasi" tabindex="0">
    <table>
        <thead>
            <tr><th scope="col">Destinasi</th><th scope="col">Wilayah</th><th scope="col">Harga tiket</th><th scope="col">Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($daftar_destinasi as $destinasi): ?>
                <tr>
                    <th scope="row">
                        <div class="identitas-destinasi">
                            <?php if (in_array(parse_url($destinasi['foto_utama'] ?? '', PHP_URL_SCHEME), ['https', 'http'], true)): ?>
                                <img src="<?= esc($destinasi['foto_utama'], 'attr') ?>" alt="" loading="lazy" width="60" height="46">
                            <?php endif ?>
                            <span><?= esc($destinasi['nama']) ?><small><?= esc($destinasi['kategori']) ?></small></span>
                        </div>
                    </th>
                    <td><?= esc($destinasi['nama_kabupaten_kota']) ?></td>
                    <td class="angka">Rp <?= esc(number_format($destinasi['harga_tiket'], 0, ',', '.')) ?></td>
                    <td>
                        <a class="tombol tombol-ghost" href="<?= esc(site_url('admin/destinasi/' . $destinasi['id'] . '/edit'), 'attr') ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach ?>
            <?php if ($daftar_destinasi === []): ?>
                <tr><td colspan="4"><?= $pesan_api ? 'Data gagal dimuat.' : 'Tidak ada destinasi yang cocok.' ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= view('pages/admin/paginasi', ['paginasi' => $paginasi, 'rute_paginasi' => 'admin/destinasi', 'query_paginasi' => ['kata_kunci' => $kata_kunci]]) ?>
<?= $this->endSection() ?>
