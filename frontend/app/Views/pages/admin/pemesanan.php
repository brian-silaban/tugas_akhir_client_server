<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('pengantar_halaman') ?>
Periksa detail kunjungan dan verifikasi pembayaran tiket wisatawan.
<?= $this->endSection() ?>
<?= $this->section('isi') ?>
<div class="bilah">
    <form method="get" action="<?= esc(site_url('admin/pemesanan'), 'attr') ?>">
        <label for="filter-status">Status pemesanan</label>
        <div class="aksi">
            <select id="filter-status" name="status">
                <?php foreach (['' => 'Semua status', 'menunggu' => 'Menunggu', 'lunas' => 'Lunas', 'dibatalkan' => 'Dibatalkan'] as $nilai => $label): ?>
                    <option value="<?= esc($nilai, 'attr') ?>" <?= $filter_status === $nilai ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach ?>
            </select>
            <button class="tombol tombol-utama" type="submit">Terapkan</button>
            <a class="tombol tombol-ghost" href="<?= esc(site_url('admin/pemesanan'), 'attr') ?>">Reset</a>
        </div>
    </form>
    <?php if (! $pesan_api): ?>
        <p><?= esc($paginasi['total_data'] ?? 0) ?> pemesanan ditemukan</p>
    <?php endif ?>
</div>
<div class="bungkus-tabel" role="region" aria-label="Daftar pemesanan wisatawan" tabindex="0">
    <table>
        <thead>
            <tr><th scope="col">Pemesanan</th><th scope="col">Pemesan dan destinasi</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Verifikasi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($daftar_pemesanan as $pemesanan): ?>
                <?php $status = $pemesanan['status']; ?>
                <tr>
                    <th scope="row" class="angka">
                        <?= esc($pemesanan['kode_pemesanan']) ?>
                        <small>Kunjungan <?= esc(date('d/m/Y', strtotime($pemesanan['tanggal_kunjungan']))) ?></small>
                    </th>
                    <td>
                        <strong><?= esc($pemesanan['nama_pemesan']) ?></strong>
                        <small><?= esc($pemesanan['nama_destinasi']) ?> · <?= esc($pemesanan['jumlah_pengunjung']) ?> orang</small>
                    </td>
                    <td class="angka">Rp <?= esc(number_format($pemesanan['total_harga'], 0, ',', '.')) ?></td>
                    <td><span class="status <?= esc($status, 'attr') ?>"><?= esc(ucfirst($status)) ?></span></td>
                    <td>
                        <?php if ($status === 'menunggu'): ?>
                            <form class="aksi"
                                  method="post"
                                  action="<?= esc(site_url('admin/pemesanan/' . $pemesanan['id'] . '/status'), 'attr') ?>"
                                  data-swal-konfirmasi>
                                <?= csrf_field() ?>
                                <input type="hidden" name="filter_status" value="<?= esc($filter_status, 'attr') ?>">
                                <input type="hidden" name="halaman" value="<?= esc($paginasi['halaman'] ?? 1, 'attr') ?>">
                                <button class="tombol tombol-kecil tombol-teal"
                                        type="submit"
                                        name="status"
                                        value="lunas"
                                        data-swal-ikon="question"
                                        data-swal-judul="Verifikasi Tiket Lunas?"
                                        data-swal-teks="Pastikan pembayaran Rp <?= esc(number_format($pemesanan['total_harga'], 0, ',', '.'), 'attr') ?> untuk tiket <?= esc($pemesanan['kode_pemesanan'], 'attr') ?> sudah diterima."
                                        data-swal-tombol-ya="Ya, Verifikasi Lunas"
                                        data-swal-bahaya="false"
                                        data-teks-proses="Memverifikasi...">
                                    <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                                    <span data-teks-tombol>Verifikasi lunas</span>
                                </button>
                                <button class="tombol tombol-kecil tombol-bahaya"
                                        type="submit"
                                        name="status"
                                        value="dibatalkan"
                                        data-swal-ikon="warning"
                                        data-swal-judul="Batalkan Pemesanan?"
                                        data-swal-teks="Tiket <?= esc($pemesanan['kode_pemesanan'], 'attr') ?> yang dibatalkan tidak dapat diverifikasi lunas lagi."
                                        data-swal-tombol-ya="Ya, Batalkan Tiket"
                                        data-swal-bahaya="true"
                                        data-teks-proses="Membatalkan...">
                                    <span class="material-symbols-outlined" aria-hidden="true">close</span>
                                    <span data-teks-tombol>Batalkan</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <span aria-label="Pemesanan sudah diproses">—</span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            <?php if ($daftar_pemesanan === []): ?>
                <tr><td colspan="5"><?= $pesan_api ? 'Data gagal dimuat.' : 'Tidak ada pemesanan yang sesuai dengan filter.' ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= view('pages/admin/paginasi', [
    'paginasi' => $paginasi,
    'rute_paginasi' => 'admin/pemesanan',
    'query_paginasi' => ['status' => $filter_status],
]) ?>
<?= $this->endSection() ?>
