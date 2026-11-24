<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('aksi_halaman') ?>
<a class="tombol tombol-ghost" href="<?= esc(site_url('admin/destinasi'), 'attr') ?>">
    <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span> Daftar destinasi
</a>
<?= $this->endSection() ?>

<?= $this->section('isi') ?>
<?php
$id = $destinasi['id'] ?? null;
$tujuan = $id === null ? site_url('admin/destinasi') : site_url('admin/destinasi/' . $id);
$nilai = static function (string $nama, string $bawaan = '') use ($destinasi): string {
    $isian = old($nama, $destinasi[$nama] ?? $bawaan, false);
    return is_scalar($isian) ? (string) $isian : '';
};
$kesalahan = session()->getFlashdata('kesalahan_isian') ?? [];
?>
<?php if ($kesalahan !== []): ?>
    <ul class="notifikasi gagal" role="alert">
        <?php foreach ($kesalahan as $pesan): ?><li><?= esc($pesan) ?></li><?php endforeach ?>
    </ul>
<?php endif ?>

<form class="formulir" method="post" action="<?= esc($tujuan, 'attr') ?>">
    <?= csrf_field() ?>
    <fieldset>
        <legend>Informasi utama</legend>
        <div class="kisi-formulir">
            <label>Nama destinasi
                <input name="nama" maxlength="180" required value="<?= esc($nilai('nama'), 'attr') ?>">
            </label>
            <label>Slug
                <input name="slug" maxlength="220" <?= $id === null ? '' : 'required' ?> value="<?= esc($nilai('slug'), 'attr') ?>">
                <small><?= $id === null ? 'Kosongkan untuk membuat slug dari nama.' : 'Perubahan slug akan mengubah tautan katalog.' ?></small>
            </label>
            <label>Kategori
                <select name="id_kategori" required>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($daftar_kategori as $kategori): ?>
                        <option value="<?= esc($kategori['id'], 'attr') ?>" <?= $nilai('id_kategori') === (string) $kategori['id'] ? 'selected' : '' ?>>
                            <?= esc($kategori['nama']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </label>
            <label>Subkategori
                <input name="sub_kategori" maxlength="100" value="<?= esc($nilai('sub_kategori'), 'attr') ?>">
            </label>
            <label>Kabupaten/kota
                <select name="kode_kabupaten" required>
                    <option value="">Pilih wilayah</option>
                    <?php foreach ($daftar_wilayah as $wilayah): ?>
                        <option value="<?= esc($wilayah['kode_kabupaten'], 'attr') ?>" <?= $nilai('kode_kabupaten') === (string) $wilayah['kode_kabupaten'] ? 'selected' : '' ?>>
                            <?= esc($wilayah['nama_kabupaten_kota']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </label>
            <label>Harga tiket per orang (Rp)
                <input name="harga_tiket" type="number" min="0" max="2147483647" required value="<?= esc($nilai('harga_tiket', '0'), 'attr') ?>">
            </label>
            <label class="lebar-penuh">Deskripsi
                <textarea name="deskripsi" rows="5" maxlength="20000" required><?= esc($nilai('deskripsi')) ?></textarea>
            </label>
            <label class="lebar-penuh">URL foto utama
                <input name="foto_utama" type="url" maxlength="2000" value="<?= esc($nilai('foto_utama'), 'attr') ?>">
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Lokasi dan jam kunjung</legend>
        <div class="kisi-formulir">
            <label class="lebar-penuh">Alamat
                <input name="alamat" maxlength="2000" value="<?= esc($nilai('alamat'), 'attr') ?>">
            </label>
            <label>Garis lintang
                <input name="garis_lintang" type="number" step="any" min="-90" max="90" value="<?= esc($nilai('garis_lintang'), 'attr') ?>">
            </label>
            <label>Garis bujur
                <input name="garis_bujur" type="number" step="any" min="-180" max="180" value="<?= esc($nilai('garis_bujur'), 'attr') ?>">
            </label>
            <label>Jam buka (WIB)
                <input name="jam_buka" type="time" step="1" value="<?= esc($nilai('jam_buka'), 'attr') ?>">
            </label>
            <label>Jam tutup (WIB)
                <input name="jam_tutup" type="time" step="1" value="<?= esc($nilai('jam_tutup'), 'attr') ?>">
            </label>
        </div>
    </fieldset>
    <button class="tombol tombol-utama" type="submit" <?= $pesan_api ? 'disabled' : '' ?>>Simpan destinasi</button>
</form>

<?php if ($id !== null): ?>
    <details class="bagian-hapus">
        <summary>Hapus destinasi</summary>
        <form method="post" action="<?= esc(site_url('admin/destinasi/' . $id . '/hapus'), 'attr') ?>">
            <?= csrf_field() ?>
            <p>Hapus <strong><?= esc($destinasi['nama']) ?></strong> dari katalog wisatawan?</p>
            <button type="submit" class="tombol tombol-bahaya">Ya, hapus destinasi</button>
        </form>
    </details>
<?php endif ?>
<?= $this->endSection() ?>
