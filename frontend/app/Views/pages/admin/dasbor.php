<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('pengantar_halaman') ?>
Pantau pemesanan, ulasan wisatawan, dan ketersediaan layanan JejakSumut.
<?= $this->endSection() ?>
<?= $this->section('aksi_halaman') ?>
<a class="tombol tombol-ghost" href="<?= esc(site_url('admin'), 'attr') ?>">
    <span class="material-symbols-outlined" aria-hidden="true">refresh</span> Perbarui data
</a>
<?= $this->endSection() ?>

<?= $this->section('isi') ?>
<?php if ($statistik !== []): ?>
    <section class="ringkasan" aria-label="Statistik JejakSumut">
        <div class="pendapatan">
            <p>Pendapatan tiket lunas</p>
            <strong>Rp <?= esc(number_format($statistik['total_pendapatan'], 0, ',', '.')) ?></strong>
            <span class="catatan-pendapatan">Akumulasi pemesanan berstatus lunas</span>
        </div>
        <dl class="metrik">
            <div><dt>Destinasi</dt><dd><?= esc(number_format($statistik['jumlah_destinasi'], 0, ',', '.')) ?></dd></div>
            <div><dt>Pengguna</dt><dd><?= esc(number_format($statistik['jumlah_pengguna'], 0, ',', '.')) ?></dd></div>
            <div><dt>Menunggu</dt><dd><?= esc(number_format($statistik['jumlah_pemesanan']['menunggu'], 0, ',', '.')) ?></dd></div>
            <div><dt>Lunas</dt><dd><?= esc(number_format($statistik['jumlah_pemesanan']['lunas'], 0, ',', '.')) ?></dd></div>
            <div><dt>Dibatalkan</dt><dd><?= esc(number_format($statistik['jumlah_pemesanan']['dibatalkan'], 0, ',', '.')) ?></dd></div>
        </dl>
    </section>
    <a class="antrian-pemesanan" href="<?= esc(site_url('admin/pemesanan?status=menunggu'), 'attr') ?>">
        <span class="material-symbols-outlined" aria-hidden="true">confirmation_number</span>
        <span><strong><?= esc(number_format($statistik['jumlah_pemesanan']['menunggu'], 0, ',', '.')) ?> pemesanan menunggu</strong><small>Periksa pembayaran sebelum memverifikasi tiket.</small></span>
        <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
    </a>
<?php else: ?>
    <div class="keadaan-kosong" role="status">
        <h2>Ringkasan belum tersedia</h2>
        <p>Statistik tidak dapat dimuat. Periksa koneksi layanan di bawah, lalu perbarui data.</p>
    </div>
<?php endif ?>

<div class="kolom-dasbor">
    <section aria-labelledby="judul-koneksi">
        <h2 id="judul-koneksi">Status layanan</h2>
        <p class="keterangan-bagian">Koneksi backend dan layanan mitra perjalanan.</p>
        <ul class="daftar-koneksi">
            <?php $backendAktif = $status_backend['terhubung'] && $status_backend['status_kode'] >= 200 && $status_backend['status_kode'] < 300 ?>
            <li>
                <div><strong>JejakSumut Backend</strong><small><?= esc($status_backend['pesan']) ?></small></div>
                <span class="status <?= $backendAktif ? 'aktif' : 'putus' ?>">
                    <?= $backendAktif ? esc($status_backend['waktu_respon_ms']) . ' ms' : 'Tidak tersedia' ?>
                </span>
            </li>
            <?php foreach ($status_mitra as $mitra): ?>
                <li>
                    <div>
                        <strong><?= esc($mitra['nama_layanan']) ?></strong>
                        <small>
                            <?php if ($mitra['url_api'] === ''): ?>URL API belum diatur
                            <?php elseif ($mitra['terhubung']): ?>Terhubung
                            <?php else: ?>API mitra gagal merespons
                            <?php endif ?>
                        </small>
                    </div>
                    <span class="status <?= $mitra['terhubung'] ? 'aktif' : 'putus' ?>">
                        <?php if ($mitra['terhubung']): ?>
                            <?= esc($mitra['waktu_respon_ms']) ?> ms
                        <?php else: ?>
                            <?= $mitra['sumber_aktif'] === 'simulasi_fallback' ? 'Simulasi' : 'Tidak tersedia' ?>
                        <?php endif ?>
                    </span>
                </li>
            <?php endforeach ?>
        </ul>
    </section>

    <section aria-labelledby="judul-ulasan">
        <div class="kepala-bagian">
            <h2 id="judul-ulasan">Ulasan terbaru</h2>
            <a href="<?= esc(site_url('admin/ulasan'), 'attr') ?>">Lihat ulasan</a>
        </div>
        <?php if ($statistik !== []): ?>
            <ul class="daftar-koneksi daftar-ringkas">
                <?php foreach ($statistik['ulasan_terbaru'] as $ulasan): ?>
                    <li>
                        <div>
                            <strong><?= esc($ulasan['nama_destinasi']) ?></strong>
                            <small><?= esc($ulasan['nama_pengguna']) ?></small>
                            <p><?= esc($ulasan['komentar']) ?></p>
                        </div>
                        <span class="status rating" aria-label="Rating <?= esc($ulasan['rating'], 'attr') ?> dari 5"><?= esc($ulasan['rating']) ?>/5</span>
                    </li>
                <?php endforeach ?>
                <?php if ($statistik['ulasan_terbaru'] === []): ?><li class="keadaan-kosong">Belum ada ulasan dari wisatawan.</li><?php endif ?>
            </ul>
        <?php else: ?>
            <p class="keadaan-kosong">Ulasan belum dapat dimuat. Perbarui data untuk mencoba kembali.</p>
        <?php endif ?>
    </section>
</div>
<?= $this->endSection() ?>
