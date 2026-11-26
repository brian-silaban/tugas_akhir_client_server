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
                        <form method="post"
                              action="<?= esc(site_url('admin/ulasan/' . $ulasan['id'] . '/hapus'), 'attr') ?>"
                              data-swal-konfirmasi
                              data-swal-ikon="warning"
                              data-swal-judul="Hapus Ulasan Ini?"
                              data-swal-teks="Ulasan dari <?= esc($ulasan['nama_pengguna'], 'attr') ?> untuk <?= esc($ulasan['nama_destinasi'], 'attr') ?> akan dihapus permanen dan rating destinasi diperbarui otomatis."
                              data-swal-tombol-ya="Ya, Hapus Ulasan"
                              data-swal-bahaya="true">
                            <?= csrf_field() ?>
                            <button class="tombol tombol-kecil tombol-bahaya" type="submit" data-teks-proses="Menghapus...">
                                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                <span data-teks-tombol>Hapus</span>
                            </button>
                        </form>
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
