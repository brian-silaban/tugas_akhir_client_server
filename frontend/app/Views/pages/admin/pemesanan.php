<?= $this->extend('pages/admin/tata_letak') ?>
<?= $this->section('isi') ?>
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
                    <td><span class="status <?= $status === 'lunas' ? 'aktif' : '' ?>"><?= esc(ucfirst($status)) ?></span></td>
                    <td>
                        <?php if ($status === 'menunggu'): ?>
                            <form class="aksi" method="post" action="<?= esc(site_url('admin/pemesanan/' . $pemesanan['id'] . '/status'), 'attr') ?>">
                                <?= csrf_field() ?>
                                <button class="tombol tombol-utama" name="status" value="lunas" type="submit">Verifikasi lunas</button>
                                <button class="tombol tombol-ghost" name="status" value="dibatalkan" type="submit">Batalkan</button>
                            </form>
                        <?php else: ?>
                            <span aria-label="Pemesanan sudah diproses">—</span>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            <?php if ($daftar_pemesanan === []): ?>
                <tr><td colspan="5"><?= $pesan_api ? 'Data gagal dimuat.' : 'Belum ada pemesanan.' ?></td></tr>
            <?php endif ?>
        </tbody>
    </table>
</div>
<?= view('pages/admin/paginasi', ['paginasi' => $paginasi, 'rute_paginasi' => 'admin/pemesanan']) ?>
<?= $this->endSection() ?>
