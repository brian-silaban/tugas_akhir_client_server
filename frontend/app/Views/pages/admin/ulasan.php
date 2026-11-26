<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('pengantar_halaman') ?>
Tinjau pengalaman wisatawan dan hapus ulasan yang tidak sesuai.
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<div class="bungkus-tabel" role="region" aria-label="Daftar ulasan" tabindex="0">
    <table>
        <thead>
            <tr><th scope="col">Destinasi dan penulis</th><th scope="col">Ulasan</th><th scope="col">Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($daftar_ulasan as $ulasan): ?>
                <tr>
                    <th scope="row">
                        <?= esc($ulasan['nama_destinasi']) ?>
                        <small><?= esc($ulasan['nama_pengguna']) ?></small>
                    </th>
                    <td class="sel-ulasan">
                        <span class="status rating" aria-label="Rating <?= esc($ulasan['rating'], 'attr') ?> dari 5"><?= esc($ulasan['rating']) ?>/5</span>
                        <p><?= esc($ulasan['komentar']) ?></p>
                    </td>
                    <td>
                        <details>
                            <summary>Hapus ulasan</summary>
                            <form method="post" action="<?= esc(site_url('admin/ulasan/' . $ulasan['id'] . '/hapus'), 'attr') ?>">
                                <?= csrf_field() ?>
                                <button class="tombol tombol-bahaya" type="submit">Ya, hapus ulasan</button>
                            </form>
                        </details>
                    </td>
                </tr>
            <?php endforeach ?>
            <?php if ($daftar_ulasan === []): ?>
                <tr><td colspan="3"><?= $pesan_api ? 'Data gagal dimuat.' : 'Belum ada ulasan.' ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= view('pages/admin/paginasi', ['paginasi' => $paginasi, 'rute_paginasi' => 'admin/ulasan']) ?>
<?= $this->endSection() ?>
