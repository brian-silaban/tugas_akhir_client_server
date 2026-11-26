<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('pengantar_halaman') ?>
Perbarui kata sandi akun administrator <?= esc((string) ($email_sesi ?? ''), 'html') ?> dengan kombinasi yang kuat.
<?= $this->endSection() ?>

<?= $this->section('isi') ?>
<?php $kesalahan = session()->getFlashdata('errors') ?? []; ?>
<?php if ($kesalahan !== []): ?>
    <ul class="notifikasi gagal" role="alert">
        <?php foreach ($kesalahan as $pesan): ?><li><?= esc($pesan) ?></li><?php endforeach ?>
    </ul>
<?php endif ?>

<form class="formulir" method="post" action="<?= esc(site_url('admin/ganti-sandi'), 'attr') ?>">
    <?= csrf_field() ?>
    <fieldset>
        <legend>Keamanan akun pengelola</legend>
        <div class="kisi-formulir">
            <label class="lebar-penuh">Kata sandi saat ini *
                <span style="position:relative;display:block;">
                    <input id="admin_sandi_lama" type="password" name="kata_sandi_lama" required autocomplete="current-password" style="padding-right:2.75rem;">
                    <button type="button" data-tombol-sandi="admin_sandi_lama" aria-label="Tampilkan kata sandi saat ini" aria-pressed="false" title="Tampilkan kata sandi" style="position:absolute;right:0;top:50%;transform:translateY(-40%);border:0;background:transparent;cursor:pointer;padding:.5rem .75rem;color:#665751;">
                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                    </button>
                </span>
            </label>
            <label>Kata sandi baru *
                <span style="position:relative;display:block;">
                    <input id="admin_sandi_baru" type="password" name="kata_sandi_baru" required minlength="6" autocomplete="new-password" style="padding-right:2.75rem;">
                    <button type="button" data-tombol-sandi="admin_sandi_baru" aria-label="Tampilkan kata sandi baru" aria-pressed="false" title="Tampilkan kata sandi" style="position:absolute;right:0;top:50%;transform:translateY(-40%);border:0;background:transparent;cursor:pointer;padding:.5rem .75rem;color:#665751;">
                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                    </button>
                </span>
            </label>
            <label>Konfirmasi kata sandi baru *
                <span style="position:relative;display:block;">
                    <input id="admin_konfirmasi_sandi" type="password" name="konfirmasi_kata_sandi" required minlength="6" autocomplete="new-password" style="padding-right:2.75rem;">
                    <button type="button" data-tombol-sandi="admin_konfirmasi_sandi" aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false" title="Tampilkan kata sandi" style="position:absolute;right:0;top:50%;transform:translateY(-40%);border:0;background:transparent;cursor:pointer;padding:.5rem .75rem;color:#665751;">
                        <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                    </button>
                </span>
            </label>
        </div>
    </fieldset>

    <div class="aksi">
        <button class="tombol tombol-utama" type="submit">Simpan kata sandi baru</button>
        <a class="tombol tombol-ghost" href="<?= esc(site_url('admin'), 'attr') ?>">Kembali ke dasbor</a>
    </div>
</form>

<script>
    // Mengubah visibilitas kata sandi saat tombol ikon mata diklik
    document.querySelectorAll('[data-tombol-sandi]').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const idInput    = tombol.getAttribute('data-tombol-sandi');
            const inputSandi = document.getElementById(idInput);
            const ikonMata   = tombol.querySelector('.material-symbols-outlined');
            if (!inputSandi || !ikonMata) {
                return;
            }

            if (inputSandi.type === 'password') {
                inputSandi.type      = 'text';
                ikonMata.textContent = 'visibility_off';
                tombol.setAttribute('aria-label', 'Sembunyikan kata sandi');
                tombol.setAttribute('title', 'Sembunyikan kata sandi');
                tombol.setAttribute('aria-pressed', 'true');
            } else {
                inputSandi.type      = 'password';
                ikonMata.textContent = 'visibility';
                tombol.setAttribute('aria-label', 'Tampilkan kata sandi');
                tombol.setAttribute('title', 'Tampilkan kata sandi');
                tombol.setAttribute('aria-pressed', 'false');
            }
        });
    });
</script>
<?= $this->endSection() ?>
