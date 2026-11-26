<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('pengantar_halaman') ?>
Ringkasan pendapatan, antrian verifikasi tiket, ulasan wisatawan, dan status layanan API.
<?= $this->endSection() ?>
<?= $this->section('aksi_halaman') ?>
<div class="aksi">
    <a class="tombol tombol-ghost" href="<?= esc(site_url('admin'), 'attr') ?>">
        <span class="material-symbols-outlined" aria-hidden="true">refresh</span> Perbarui data
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('isi') ?>
<?php if ($statistik !== []): ?>
    <?php
    $jumlahMenunggu = (int) ($statistik['jumlah_pemesanan']['menunggu'] ?? 0);
    $jumlahLunas = (int) ($statistik['jumlah_pemesanan']['lunas'] ?? 0);
    $jumlahBatal = (int) ($statistik['jumlah_pemesanan']['dibatalkan'] ?? 0);
    ?>
    <section class="kisi-kpi-utama" aria-label="Ringkasan statistik JejakSumut">
        <div class="kartu-kpi unggulan">
            <div class="kepala-kpi">
                <span class="label-kpi">Pendapatan Lunas</span>
                <span class="ikon-kpi" aria-hidden="true">
                    <span class="material-symbols-outlined">payments</span>
                </span>
            </div>
            <strong class="angka-kpi">Rp <?= esc(number_format($statistik['total_pendapatan'], 0, ',', '.')) ?></strong>
            <div class="kaki-kpi">
                <span><?= esc(number_format($jumlahLunas, 0, ',', '.')) ?> lunas · <?= esc(number_format($jumlahBatal, 0, ',', '.')) ?> dibatalkan</span>
            </div>
        </div>

        <a class="kartu-kpi <?= $jumlahMenunggu > 0 ? 'perlu-tindakan' : '' ?>"
           href="<?= esc(site_url('admin/pemesanan?status=menunggu'), 'attr') ?>">
            <div class="kepala-kpi">
                <span class="label-kpi">Antrian Verifikasi</span>
                <span class="ikon-kpi" aria-hidden="true">
                    <span class="material-symbols-outlined"><?= $jumlahMenunggu > 0 ? 'pending_actions' : 'task_alt' ?></span>
                </span>
            </div>
            <strong class="angka-kpi"><?= esc(number_format($jumlahMenunggu, 0, ',', '.')) ?></strong>
            <div class="kaki-kpi">
                <span><?= $jumlahMenunggu > 0 ? 'Menunggu konfirmasi bayar' : 'Semua pesanan beres' ?></span>
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </div>
        </a>

        <a class="kartu-kpi" href="<?= esc(site_url('admin/destinasi'), 'attr') ?>">
            <div class="kepala-kpi">
                <span class="label-kpi">Katalog Destinasi</span>
                <span class="ikon-kpi" aria-hidden="true">
                    <span class="material-symbols-outlined">landscape</span>
                </span>
            </div>
            <strong class="angka-kpi"><?= esc(number_format($statistik['jumlah_destinasi'], 0, ',', '.')) ?></strong>
            <div class="kaki-kpi">
                <span>Objek wisata Sumut</span>
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </div>
        </a>

        <div class="kartu-kpi">
            <div class="kepala-kpi">
                <span class="label-kpi">Pengguna Terdaftar</span>
                <span class="ikon-kpi" aria-hidden="true">
                    <span class="material-symbols-outlined">group</span>
                </span>
            </div>
            <strong class="angka-kpi"><?= esc(number_format($statistik['jumlah_pengguna'], 0, ',', '.')) ?></strong>
            <div class="kaki-kpi">
                <span>Akun terdaftar di platform</span>
            </div>
        </div>
    </section>
<?php else: ?>
    <div class="keadaan-kosong" role="status">
        <h2>Ringkasan belum tersedia</h2>
        <p>Statistik tidak dapat dimuat. Periksa koneksi layanan API, lalu perbarui data.</p>
    </div>
<?php endif ?>

<div class="kisi-dasbor-utama">
    <section class="panel-kartu" aria-labelledby="judul-pemesanan-terbaru">
        <div class="kepala-bagian">
            <div>
                <h2 id="judul-pemesanan-terbaru">Pemesanan Terbaru</h2>
                <p class="keterangan-bagian">Transaksi tiket wisatawan terbaru yang masuk ke sistem.</p>
            </div>
            <a href="<?= esc(site_url('admin/pemesanan'), 'attr') ?>">Lihat semua</a>
        </div>

        <?php if (! empty($pemesanan_terbaru)): ?>
            <div class="bungkus-tabel-ringkas" role="region" aria-label="Pemesanan terbaru" tabindex="0">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Kode &amp; Pemesan</th>
                            <th scope="col">Destinasi</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pemesanan_terbaru as $pesanan): ?>
                            <?php $status = (string) ($pesanan['status'] ?? 'menunggu'); ?>
                            <tr>
                                <td>
                                    <strong class="angka"><?= esc($pesanan['kode_pemesanan']) ?></strong>
                                    <small><?= esc($pesanan['nama_pemesan']) ?></small>
                                </td>
                                <td>
                                    <?= esc($pesanan['nama_destinasi']) ?>
                                    <small><?= esc($pesanan['jumlah_pengunjung']) ?> orang · <?= esc(date('d/m/Y', strtotime((string) $pesanan['tanggal_kunjungan']))) ?></small>
                                </td>
                                <td class="angka">Rp <?= esc(number_format((int) $pesanan['total_harga'], 0, ',', '.')) ?></td>
                                <td><span class="status <?= esc($status, 'attr') ?>"><?= esc(ucfirst($status)) ?></span></td>
                                <td>
                                    <?php if ($status === 'menunggu'): ?>
                                        <form method="post"
                                              action="<?= esc(site_url('admin/pemesanan/' . $pesanan['id'] . '/status'), 'attr') ?>"
                                              data-swal-konfirmasi>
                                            <?= csrf_field() ?>
                                            <button class="tombol tombol-kecil tombol-teal"
                                                    type="submit"
                                                    name="status"
                                                    value="lunas"
                                                    data-swal-ikon="question"
                                                    data-swal-judul="Verifikasi Tiket Lunas?"
                                                    data-swal-teks="Sahkan pembayaran Rp <?= esc(number_format((int) $pesanan['total_harga'], 0, ',', '.'), 'attr') ?> untuk tiket <?= esc($pesanan['kode_pemesanan'], 'attr') ?>?"
                                                    data-swal-tombol-ya="Ya, Verifikasi"
                                                    data-swal-bahaya="false"
                                                    data-teks-proses="Memproses...">
                                                <span data-teks-tombol>Verifikasi</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span aria-label="Sudah diproses">—</span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="pesan-kosong-halus">Belum ada transaksi pemesanan yang tercatat.</p>
        <?php endif ?>
    </section>

    <div class="kolom-kanan-dasbor">
        <section class="panel-kartu" aria-labelledby="judul-koneksi">
            <h2 id="judul-koneksi">Status Layanan API</h2>
            <p class="keterangan-bagian">Backend JejakSumut &amp; 4 API kelompok mitra.</p>
            <ul class="daftar-koneksi">
                <?php $backendAktif = $status_backend['terhubung'] && $status_backend['status_kode'] >= 200 && $status_backend['status_kode'] < 300 ?>
                <li>
                    <div>
                        <strong>JejakSumut Backend</strong>
                        <small><?= esc($status_backend['pesan']) ?></small>
                    </div>
                    <span class="status <?= $backendAktif ? 'aktif' : 'putus' ?>">
                        <?= $backendAktif ? esc($status_backend['waktu_respon_ms']) . ' ms' : 'Offline' ?>
                    </span>
                </li>
                <?php foreach ($status_mitra as $mitra): ?>
                    <li>
                        <div>
                            <strong><?= esc($mitra['nama_layanan']) ?></strong>
                            <small>
                                <?php if ($mitra['url_api'] === ''): ?>URL belum diatur
                                <?php elseif ($mitra['terhubung']): ?>Terhubung langsung
                                <?php else: ?>Cadangan simulasi lokal
                                <?php endif ?>
                            </small>
                        </div>
                        <?php if ($mitra['terhubung']): ?>
                            <span class="status aktif"><?= esc($mitra['waktu_respon_ms']) ?> ms</span>
                        <?php elseif ($mitra['sumber_aktif'] === 'simulasi_fallback'): ?>
                            <span class="status simulasi">Simulasi</span>
                        <?php else: ?>
                            <span class="status putus">Offline</span>
                        <?php endif ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>

        <section class="panel-kartu" aria-labelledby="judul-ulasan">
            <div class="kepala-bagian">
                <div>
                    <h2 id="judul-ulasan">Ulasan Terbaru</h2>
                    <p class="keterangan-bagian">Masukan wisatawan pada destinasi.</p>
                </div>
                <a href="<?= esc(site_url('admin/ulasan'), 'attr') ?>">Lihat semua</a>
            </div>
            <?php if ($statistik !== [] && ! empty($statistik['ulasan_terbaru'])): ?>
                <ul class="daftar-koneksi daftar-ringkas">
                    <?php foreach ($statistik['ulasan_terbaru'] as $ulasan): ?>
                        <li>
                            <div>
                                <strong><?= esc($ulasan['nama_destinasi']) ?></strong>
                                <small><?= esc($ulasan['nama_pengguna']) ?></small>
                                <p><?= esc($ulasan['komentar']) ?></p>
                            </div>
                            <span class="status rating" aria-label="Rating <?= esc($ulasan['rating'], 'attr') ?> dari 5">
                                <span class="material-symbols-outlined" aria-hidden="true">star</span>
                                <?= esc($ulasan['rating']) ?>/5
                            </span>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php else: ?>
                <p class="pesan-kosong-halus">Belum ada ulasan dari wisatawan.</p>
            <?php endif ?>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
