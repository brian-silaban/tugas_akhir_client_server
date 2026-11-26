<?= $this->extend('layout/main') ?>

<?= $this->section('konten') ?>
<?php
$daftarLokasiSemua    = is_array($daftarLokasi ?? null) ? $daftarLokasi : [];
$daftarLokasiSumut    = array_values(array_filter($daftarLokasiSemua, static fn (array $item): bool => ! empty($item['dalam_sumut'])));
$daftarLokasiNasional = array_values(array_filter($daftarLokasiSemua, static fn (array $item): bool => empty($item['dalam_sumut'])));

$idAsalDefault   = (int) ($daftarLokasiSumut[0]['id'] ?? ($daftarLokasiSemua[0]['id'] ?? 0));
$idTujuanDefault = (int) ($daftarLokasiSumut[1]['id'] ?? ($daftarLokasiSemua[1]['id'] ?? $idAsalDefault));
$titikBerangkat  = [
    ['label' => 'Pusat Kota Medan',           'lat' => 3.5952,  'lng' => 98.6722],
    ['label' => 'Bandara Kualanamu (KNO)',    'lat' => 3.6422,  'lng' => 98.8853],
    ['label' => 'Bandara Soekarno-Hatta (CGK)', 'lat' => -6.1275, 'lng' => 106.6537],
];
?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10 space-y-12">
    <!-- Header Halaman Transportasi -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8 border-b border-surface-variant/50">
        <div>
            <span class="font-label-caps text-label-caps uppercase text-primary tracking-wider">
                MITRA TRANSPORTASI
            </span>
            <h1 class="font-headline-lg text-headline-lg-mobile sm:text-headline-lg text-on-surface mt-1">
                Transportasi &amp; Rute Sumatera Utara
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-2xl">
                Periksa estimasi tarif antar bandara, stasiun, maupun terminal serta lihat rekomendasi rute menuju objek wisata di Sumatera Utara.
            </p>
        </div>

        <div class="shrink-0">
            <?php if (! empty($terhubungMitra)) : ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-secondary-container text-on-secondary-fixed-variant font-label-md text-label-md">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">cloud_done</span>
                    <span>Terhubung Langsung</span>
                </span>
            <?php else : ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-error-container text-on-error-container font-label-md text-label-md">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">cloud_off</span>
                    <span>Layanan Sedang Sibuk</span>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($daftarLokasiSemua)) : ?>
        <div class="rounded-3xl bg-surface-container-lowest border border-surface-variant/50 p-10 text-center">
            <span class="material-symbols-outlined text-5xl text-outline" aria-hidden="true">directions_bus</span>
            <h2 class="font-headline-md text-headline-md text-on-surface mt-3">Data Simpul Transportasi Belum Tersedia</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-2 max-w-md mx-auto">
                <?= esc($pesanErrorMitra ?: 'Layanan mitra transportasi sedang tidak merespons. Silakan coba muat ulang beberapa saat lagi.') ?>
            </p>
        </div>
    <?php else : ?>
        <!-- Bagian 1: Cek Tarif & Jadwal Antar Simpul -->
        <section class="bg-surface-container-lowest rounded-3xl border border-surface-variant/60 p-6 sm:p-8 shadow-sm">
            <div class="mb-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" aria-hidden="true">route</span>
                    <span>Cek Tarif &amp; Jadwal Antar Simpul</span>
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Pilih titik keberangkatan, titik tujuan, serta perkiraan jam perjalanan untuk melihat armada yang tersedia.
                </p>
            </div>

            <form id="form-halaman-cek-tarif" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-4">
                    <label for="halaman-asal-transport" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                        Titik Berangkat
                    </label>
                    <select id="halaman-asal-transport" class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-low px-3.5 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none">
                        <?php if ($daftarLokasiSumut !== []) : ?>
                            <optgroup label="Sumatera Utara">
                                <?php foreach ($daftarLokasiSumut as $lokasiItem) : ?>
                                    <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idAsalDefault ? 'selected' : '' ?>>
                                        <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if ($daftarLokasiNasional !== []) : ?>
                            <optgroup label="Bandara &amp; Kota Lainnya">
                                <?php foreach ($daftarLokasiNasional as $lokasiItem) : ?>
                                    <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idAsalDefault ? 'selected' : '' ?>>
                                        <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label for="halaman-tujuan-transport" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                        Titik Tujuan
                    </label>
                    <select id="halaman-tujuan-transport" class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-low px-3.5 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none">
                        <?php if ($daftarLokasiSumut !== []) : ?>
                            <optgroup label="Sumatera Utara">
                                <?php foreach ($daftarLokasiSumut as $lokasiItem) : ?>
                                    <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idTujuanDefault ? 'selected' : '' ?>>
                                        <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if ($daftarLokasiNasional !== []) : ?>
                            <optgroup label="Bandara &amp; Kota Lainnya">
                                <?php foreach ($daftarLokasiNasional as $lokasiItem) : ?>
                                    <option value="<?= esc((string) $lokasiItem['id']) ?>" <?= (int) $lokasiItem['id'] === $idTujuanDefault ? 'selected' : '' ?>>
                                        <?= esc((string) $lokasiItem['nama_lokasi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="halaman-jam-transport" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                        Jam Berangkat
                    </label>
                    <input
                        id="halaman-jam-transport"
                        type="time"
                        value="08:30"
                        required
                        class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-low px-3.5 py-2.5 text-sm text-on-surface tabular-nums focus:border-primary focus:outline-none"
                    />
                </div>

                <div class="sm:col-span-2">
                    <button
                        type="submit"
                        id="tombol-halaman-cek-tarif"
                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-tertiary-container hover:bg-tertiary px-4 py-2.5 text-sm font-semibold text-on-tertiary transition-colors shadow-sm disabled:opacity-60"
                    >
                        <span class="material-symbols-outlined text-base" aria-hidden="true">search</span>
                        <span>Cek Tarif</span>
                    </button>
                </div>
            </form>

            <div id="hasil-halaman-cek-tarif" class="hidden mt-6 pt-6 border-t border-surface-variant/50" aria-live="polite"></div>
        </section>

        <!-- Bagian 2: Rekomendasi Rute Menuju Destinasi Wisata Sumut -->
        <section class="bg-surface-container-low rounded-3xl border border-surface-variant/60 p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" aria-hidden="true">alt_route</span>
                    <span>Rekomendasi Rute Menuju Destinasi</span>
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Rencanakan rute kombinasi perjalanan (udara, kereta, maupun darat) dari kota asal menuju destinasi populer di Sumatera Utara.
                </p>
            </div>

            <form id="form-rekomendasi-rute" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-4">
                    <label for="input-asal-rekomendasi" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                        Berangkat Dari
                    </label>
                    <select id="input-asal-rekomendasi" class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none">
                        <?php foreach ($titikBerangkat as $asalItem) : ?>
                            <option
                                value="<?= esc($asalItem['lat'] . ',' . $asalItem['lng']) ?>"
                            >
                                <?= esc($asalItem['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label for="input-destinasi-rekomendasi" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                        Destinasi Tujuan
                    </label>
                    <select id="input-destinasi-rekomendasi" class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none">
                        <?php foreach ($daftarDestinasiTuju ?? [] as $destTuju) : ?>
                            <option
                                value="<?= esc($destTuju['lat'] . ',' . $destTuju['lng']) ?>"
                                data-nama-wisata="<?= esc($destTuju['nama']) ?>"
                            >
                                <?= esc($destTuju['nama']) ?> (<?= esc($destTuju['kota']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="input-tipe-perjalanan" class="block font-label-caps text-label-caps text-outline uppercase mb-1.5">
                        Perjalanan
                    </label>
                    <select id="input-tipe-perjalanan" class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3.5 py-2.5 text-sm text-on-surface focus:border-primary focus:outline-none">
                        <option value="oneway">Sekali Jalan</option>
                        <option value="roundtrip">Pulang Pergi</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <button
                        type="submit"
                        id="tombol-cari-rekomendasi"
                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-primary hover:bg-primary-container px-4 py-2.5 text-sm font-semibold text-on-primary transition-colors shadow-sm disabled:opacity-60"
                    >
                        <span class="material-symbols-outlined text-base" aria-hidden="true">explore</span>
                        <span>Cari Rute</span>
                    </button>
                </div>
            </form>

            <div id="hasil-rekomendasi-rute" class="hidden mt-6 pt-6 border-t border-surface-variant/60" aria-live="polite"></div>
        </section>

        <!-- Bagian 3: Direktori Simpul Transportasi Aktif di Sumatera Utara -->
        <section>
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-5">
                <div>
                    <h2 class="font-headline-md text-headline-md text-on-surface">
                        Simpul Transportasi Sumatera Utara
                    </h2>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                        Daftar bandara, stasiun, dan terminal di wilayah Sumatera Utara yang melayani rute perjalanan.
                    </p>
                </div>
                <div class="w-full sm:w-72">
                    <label for="cari-simpul-sumut" class="sr-only">Filter nama simpul</label>
                    <input
                        id="cari-simpul-sumut"
                        type="text"
                        placeholder="Filter nama simpul atau kota..."
                        class="w-full rounded-xl border border-outline-variant/80 bg-surface-container-lowest px-3.5 py-2 text-sm text-on-surface focus:border-primary focus:outline-none"
                    />
                </div>
            </div>

            <div id="daftar-simpul-sumut" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($daftarLokasiSumut as $simpul) : ?>
                    <div
                        data-kartu-simpul
                        data-nama-simpul="<?= esc(strtolower((string) $simpul['nama_lokasi'])) ?>"
                        class="rounded-2xl bg-surface-container-lowest border border-surface-variant/50 p-4 flex items-center gap-3"
                    >
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">commute</span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-label-md text-label-md text-on-surface truncate">
                                <?= esc((string) $simpul['nama_lokasi']) ?>
                            </p>
                            <p class="font-body-sm text-body-sm text-outline tabular-nums">
                                <?= esc(number_format((float) $simpul['latitude'], 4, '.', '')) ?>, <?= esc(number_format((float) $simpul['longitude'], 4, '.', '')) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

<script>
    // Interaksi kalkulator tarif, pencari rute wisata, dan filter direktori simpul
    (function () {
        const urlProxyTarif       = <?= json_encode(base_url('mitra/transportasi/tarif')) ?>;
        const urlProxyRekomendasi = <?= json_encode(base_url('mitra/transportasi/rekomendasi')) ?>;

        // 1. Handler Cek Tarif Antar Simpul
        const formTarif   = document.getElementById('form-halaman-cek-tarif');
        const wadahTarif  = document.getElementById('hasil-halaman-cek-tarif');
        const tombolTarif = document.getElementById('tombol-halaman-cek-tarif');

        function tampilkanPesan(wadah, teks, apakahError) {
            wadah.replaceChildren();
            wadah.classList.remove('hidden');
            const p = document.createElement('p');
            p.className = apakahError
                ? 'rounded-xl border border-error/30 bg-error-container/60 px-4 py-3 text-sm text-on-error-container'
                : 'text-sm text-on-surface-variant';
            p.textContent = teks;
            wadah.appendChild(p);
        }

        if (formTarif && wadahTarif && tombolTarif) {
            formTarif.addEventListener('submit', async function (acara) {
                acara.preventDefault();
                const idAsal   = document.getElementById('halaman-asal-transport').value;
                const idTujuan = document.getElementById('halaman-tujuan-transport').value;
                const jam      = document.getElementById('halaman-jam-transport').value || '08:30';

                if (idAsal === idTujuan) {
                    tampilkanPesan(wadahTarif, 'Titik berangkat dan titik tujuan tidak boleh sama.', true);
                    return;
                }

                tombolTarif.disabled = true;
                tampilkanPesan(wadahTarif, 'Memuat jadwal dan estimasi tarif...', false);

                try {
                    const param = new URLSearchParams({ id_asal: idAsal, id_tujuan: idTujuan, jam: jam });
                    const respon = await fetch(urlProxyTarif + '?' + param.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const isiJson = await respon.json();

                    if (!respon.ok || isiJson.status !== 'berhasil') {
                        tampilkanPesan(wadahTarif, isiJson.pesan || 'Gagal memuat tarif rute transportasi.', true);
                        return;
                    }

                    wadahTarif.replaceChildren();
                    wadahTarif.classList.remove('hidden');

                    const data = isiJson.data || {};
                    const rute = data.rute || {};
                    const asalNama = (rute.asal && rute.asal.nama_lokasi) ? rute.asal.nama_lokasi : '-';
                    const tujuanNama = (rute.tujuan && rute.tujuan.nama_lokasi) ? rute.tujuan.nama_lokasi : '-';
                    const jarakKm = rute.jarak_km || 0;
                    const pilihan = Array.isArray(data.pilihan_transportasi) ? data.pilihan_transportasi : [];

                    const ringkasan = document.createElement('p');
                    ringkasan.className = 'text-sm font-medium text-on-surface mb-4 tabular-nums';
                    ringkasan.textContent = asalNama + ' → ' + tujuanNama + ' (' + jarakKm + ' km) • ' + pilihan.length + ' opsi tersedia';
                    wadahTarif.appendChild(ringkasan);

                    if (pilihan.length === 0) {
                        const kosong = document.createElement('p');
                        kosong.className = 'rounded-xl bg-surface-container-low p-4 text-sm text-on-surface-variant';
                        kosong.textContent = 'Belum ada jadwal armada langsung pada rute dan jam tersebut.';
                        wadahTarif.appendChild(kosong);
                        return;
                    }

                    const daftar = document.createElement('ul');
                    daftar.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3';
                    pilihan.forEach(function (opsi) {
                        const item = document.createElement('li');
                        item.className = 'rounded-2xl border border-surface-variant/70 bg-surface-container-low px-4 py-3.5 flex items-center justify-between gap-3';

                        const kiri = document.createElement('div');
                        kiri.className = 'min-w-0';
                        const penyedia = document.createElement('p');
                        penyedia.className = 'text-sm font-semibold text-on-surface truncate';
                        penyedia.textContent = opsi.penyedia || 'Armada Mitra';
                        const ket = document.createElement('p');
                        ket.className = 'text-xs text-on-surface-variant mt-0.5 tabular-nums';
                        ket.textContent = (opsi.jenis_transport || '-') + ' • ' + (opsi.jam_berlaku || '-');
                        kiri.appendChild(penyedia);
                        kiri.appendChild(ket);

                        const harga = document.createElement('span');
                        harga.className = 'text-sm font-bold text-primary shrink-0 tabular-nums';
                        harga.textContent = opsi.estimasi_tarif_format || '-';

                        item.appendChild(kiri);
                        item.appendChild(harga);
                        daftar.appendChild(item);
                    });
                    wadahTarif.appendChild(daftar);
                } catch (kesalahan) {
                    tampilkanPesan(wadahTarif, 'Koneksi ke layanan transportasi terputus.', true);
                } finally {
                    tombolTarif.disabled = false;
                }
            });
        }

        // 2. Handler Rekomendasi Rute Menuju Destinasi
        const formRekom   = document.getElementById('form-rekomendasi-rute');
        const wadahRekom  = document.getElementById('hasil-rekomendasi-rute');
        const tombolRekom = document.getElementById('tombol-cari-rekomendasi');

        function formatRupiah(angka) {
            return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
        }

        function buatBagianSegmen(judulBagian, daftarSegmen) {
            const bungkus = document.createElement('div');
            bungkus.className = 'space-y-3';

            const judul = document.createElement('h3');
            judul.className = 'font-label-caps text-label-caps text-outline uppercase';
            judul.textContent = judulBagian;
            bungkus.appendChild(judul);

            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-1 md:grid-cols-2 gap-3';

            daftarSegmen.forEach(function (segmen) {
                const kartu = document.createElement('div');
                kartu.className = 'rounded-2xl bg-surface-container-lowest border border-surface-variant/70 p-4 space-y-2';

                const berangkat = (segmen.titik_berangkat && segmen.titik_berangkat.nama_lokasi) ? segmen.titik_berangkat.nama_lokasi : '-';
                const tiba      = (segmen.titik_tiba && segmen.titik_tiba.nama_lokasi) ? segmen.titik_tiba.nama_lokasi : '-';
                const opsiPertama = (Array.isArray(segmen.opsi_armada) && segmen.opsi_armada[0]) ? segmen.opsi_armada[0] : {};

                const atas = document.createElement('div');
                atas.className = 'flex items-center justify-between gap-2';
                const badge = document.createElement('span');
                badge.className = 'px-2.5 py-0.5 rounded-full bg-surface-container text-primary text-xs font-semibold';
                badge.textContent = segmen.jenis_segmen || opsiPertama.jenis_transport || 'Segmen Rute';
                const tarif = document.createElement('span');
                tarif.className = 'text-sm font-bold text-on-surface tabular-nums';
                tarif.textContent = formatRupiah(segmen.tarif_termurah || opsiPertama.estimasi_tarif || 0);
                atas.appendChild(badge);
                atas.appendChild(tarif);

                const jalur = document.createElement('p');
                jalur.className = 'text-sm font-semibold text-on-surface';
                jalur.textContent = berangkat + ' → ' + tiba;

                const ket = document.createElement('p');
                ket.className = 'text-xs text-on-surface-variant tabular-nums';
                const jarak = segmen.jarak_km ? String(segmen.jarak_km) + ' km' : '';
                const armada = opsiPertama.penyedia ? opsiPertama.penyedia : '';
                ket.textContent = [armada, jarak, segmen.deskripsi || ''].filter(Boolean).join(' • ');

                kartu.appendChild(atas);
                kartu.appendChild(jalur);
                kartu.appendChild(ket);
                grid.appendChild(kartu);
            });

            bungkus.appendChild(grid);
            return bungkus;
        }

        if (formRekom && wadahRekom && tombolRekom) {
            formRekom.addEventListener('submit', async function (acara) {
                acara.preventDefault();
                const asalSelect = document.getElementById('input-asal-rekomendasi');
                const destSelect = document.getElementById('input-destinasi-rekomendasi');
                const tipeSelect = document.getElementById('input-tipe-perjalanan');

                const [userLat, userLng]     = (asalSelect.value || '3.5952,98.6722').split(',');
                const [wisataLat, wisataLng] = (destSelect.value || '2.68,98.88').split(',');
                const optTerpilih            = destSelect.options[destSelect.selectedIndex];
                const namaWisata             = optTerpilih ? (optTerpilih.getAttribute('data-nama-wisata') || 'Destinasi Sumut') : 'Destinasi Sumut';

                tombolRekom.disabled = true;
                tampilkanPesan(wadahRekom, 'Menyusun rekomendasi rute perjalanan...', false);

                try {
                    const param = new URLSearchParams({
                        user_lat: userLat,
                        user_lng: userLng,
                        wisata_lat: wisataLat,
                        wisata_lng: wisataLng,
                        nama_wisata: namaWisata,
                        trip_type: tipeSelect ? tipeSelect.value : 'oneway'
                    });
                    const respon = await fetch(urlProxyRekomendasi + '?' + param.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    const isiJson = await respon.json();

                    if (!respon.ok || isiJson.status !== 'berhasil') {
                        tampilkanPesan(wadahRekom, isiJson.pesan || 'Gagal memuat rekomendasi rute.', true);
                        return;
                    }

                    wadahRekom.replaceChildren();
                    wadahRekom.classList.remove('hidden');

                    const data = isiJson.data || {};
                    const rutePerjalanan = data.rute_perjalanan || {};
                    const segmenPergi = Array.isArray(rutePerjalanan.pergi) ? rutePerjalanan.pergi : [];
                    const segmenPulang = Array.isArray(rutePerjalanan.pulang) ? rutePerjalanan.pulang : [];

                    if (segmenPergi.length === 0 && segmenPulang.length === 0) {
                        tampilkanPesan(wadahRekom, 'Belum tersedia rekomendasi rute otomatis untuk koordinat tersebut.', false);
                        return;
                    }

                    const kontainerHasil = document.createElement('div');
                    kontainerHasil.className = 'space-y-5';
                    if (segmenPergi.length > 0) {
                        kontainerHasil.appendChild(buatBagianSegmen('Rute Berangkat menuju ' + namaWisata, segmenPergi));
                    }
                    if (segmenPulang.length > 0) {
                        kontainerHasil.appendChild(buatBagianSegmen('Rute Pulang', segmenPulang));
                    }
                    wadahRekom.appendChild(kontainerHasil);
                } catch (kesalahan) {
                    tampilkanPesan(wadahRekom, 'Koneksi ke layanan rekomendasi rute terputus.', true);
                } finally {
                    tombolRekom.disabled = false;
                }
            });
        }

        // 3. Filter Direktori Simpul Sumut
        const inputCariSimpul = document.getElementById('cari-simpul-sumut');
        const kartuSimpul     = document.querySelectorAll('[data-kartu-simpul]');
        if (inputCariSimpul && kartuSimpul.length > 0) {
            inputCariSimpul.addEventListener('input', function () {
                const kata = inputCariSimpul.value.trim().toLowerCase();
                kartuSimpul.forEach(function (kartu) {
                    const nama = kartu.getAttribute('data-nama-simpul') || '';
                    kartu.classList.toggle('hidden', kata !== '' && !nama.includes(kata));
                });
            });
        }
    })();
</script>
<?= $this->endSection() ?>
