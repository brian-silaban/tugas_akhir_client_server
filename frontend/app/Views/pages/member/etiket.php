<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul_halaman) ?> — JejakSumut</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF8F5; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .etiket-card { box-shadow: none !important; }
        }
    </style>
</head>
<body class="min-h-screen">

    <!-- Navigasi (tidak tampil saat cetak) -->
    <div class="no-print bg-white border-b border-stone-200 py-4 px-4">
        <div class="max-w-2xl mx-auto flex items-center justify-between">
            <a href="/pemesanan" class="text-sm text-[#0B6E77] hover:underline">← Kembali ke Tiket Saya</a>
            <button onclick="window.print()"
                    class="text-sm bg-[#0B6E77] text-white px-4 py-2 rounded-lg font-medium hover:bg-[#00545B] transition-colors">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- E-Ticket Card -->
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="etiket-card bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-lg">

            <!-- Header E-Ticket: Foto Destinasi -->
            <?php if (! empty($data_etiket['foto_destinasi'])): ?>
                <div class="relative h-48 overflow-hidden">
                    <img src="<?= esc($data_etiket['foto_destinasi']) ?>"
                         alt="<?= esc($data_etiket['nama_destinasi'] ?? '') ?>"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-4 left-5 text-white">
                        <p class="text-xs font-medium opacity-80 mb-0.5">🏔️ Destinasi Wisata Sumatera Utara</p>
                        <h1 class="font-serif text-2xl font-bold leading-tight"><?= esc($data_etiket['nama_destinasi'] ?? '') ?></h1>
                        <p class="text-sm opacity-80 mt-0.5"><?= esc($data_etiket['kota_destinasi'] ?? '') ?>, Sumatera Utara</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Kode Tiket & Status -->
            <div class="px-6 pt-5 pb-4 bg-[#0B6E77] text-white flex items-center justify-between">
                <div>
                    <p class="text-xs opacity-70 mb-0.5 font-medium tracking-widest uppercase">Kode Pemesanan</p>
                    <p class="text-2xl font-bold font-mono tabular-nums tracking-wider"><?= esc($data_etiket['kode_pemesanan'] ?? '-') ?></p>
                </div>
                <?php
                    $labelStatus = match($data_etiket['status'] ?? '') {
                        'menunggu'   => '⏳ Menunggu Pembayaran',
                        'lunas'      => '✅ Tiket Aktif / Lunas',
                        'dibatalkan' => '❌ Dibatalkan',
                        default      => ucfirst($data_etiket['status'] ?? '-'),
                    };
                    $bgStatus = match($data_etiket['status'] ?? '') {
                        'lunas'      => 'bg-green-500',
                        'dibatalkan' => 'bg-stone-500',
                        default      => 'bg-amber-500',
                    };
                ?>
                <span class="<?= $bgStatus ?> text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                    <?= $labelStatus ?>
                </span>
            </div>

            <!-- Garis putus-putus pemisah (gaya tiket fisik) -->
            <div class="flex items-center px-5 py-2 bg-stone-50 border-y border-dashed border-stone-200">
                <div class="w-5 h-5 rounded-full bg-[#FAF8F5] border border-stone-200 -ml-8 flex-shrink-0"></div>
                <div class="flex-1 border-t border-dashed border-stone-300 mx-2"></div>
                <div class="w-5 h-5 rounded-full bg-[#FAF8F5] border border-stone-200 -mr-8 flex-shrink-0"></div>
            </div>

            <!-- Detail Rincian Pemesanan -->
            <div class="px-6 py-5 grid grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <p class="text-xs text-stone-400 font-medium uppercase tracking-wide mb-1">Tanggal Kunjungan</p>
                    <p class="font-semibold text-stone-900 tabular-nums">
                        <?= date('l, d F Y', strtotime($data_etiket['tanggal_kunjungan'] ?? '')) ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-stone-400 font-medium uppercase tracking-wide mb-1">Jumlah Pengunjung</p>
                    <p class="font-semibold text-stone-900 tabular-nums">
                        <?= esc($data_etiket['jumlah_pengunjung'] ?? 0) ?> Orang
                    </p>
                </div>
                <div>
                    <p class="text-xs text-stone-400 font-medium uppercase tracking-wide mb-1">Total Bayar</p>
                    <p class="font-bold text-xl text-[#C2410C] tabular-nums">
                        <?= esc($data_etiket['total_harga_terformat'] ?? '-') ?>
                    </p>
                </div>
                <?php if (! empty($data_etiket['alamat_destinasi'])): ?>
                    <div>
                        <p class="text-xs text-stone-400 font-medium uppercase tracking-wide mb-1">Alamat Wisata</p>
                        <p class="text-sm text-stone-700 leading-snug"><?= esc($data_etiket['alamat_destinasi']) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (! empty($data_etiket['jam_buka_destinasi']) && ! empty($data_etiket['jam_tutup_destinasi'])): ?>
                    <div>
                        <p class="text-xs text-stone-400 font-medium uppercase tracking-wide mb-1">Jam Operasional</p>
                        <p class="font-medium text-stone-800 tabular-nums">
                            <?= substr($data_etiket['jam_buka_destinasi'], 0, 5) ?> –
                            <?= substr($data_etiket['jam_tutup_destinasi'], 0, 5) ?> WIB
                        </p>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="text-xs text-stone-400 font-medium uppercase tracking-wide mb-1">Tanggal Pesan</p>
                    <p class="text-sm text-stone-700 tabular-nums">
                        <?= date('d M Y, H:i', strtotime($data_etiket['dibuat_pada'] ?? '')) ?> WIB
                    </p>
                </div>
            </div>

            <!-- Footer E-Ticket -->
            <div class="px-6 py-4 bg-stone-50 border-t border-stone-100 text-center">
                <p class="text-xs text-stone-400">
                    Tunjukkan kode tiket ini kepada petugas di pintu masuk. &bull; JejakSumut — Jelajahi Pesona Sumatera Utara
                </p>
            </div>
        </div>

        <!-- Tombol Aksi (tidak tampil saat cetak) -->
        <div class="no-print mt-5 flex gap-3 justify-center">
            <a href="/pemesanan"
               class="border border-stone-300 text-stone-700 px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-stone-50 transition-colors">
                ← Kembali ke Tiket Saya
            </a>
            <?php if (($data_etiket['status'] ?? '') === 'menunggu'): ?>
                <form action="<?= base_url('pemesanan/batal/' . $data_etiket['id']) ?>" method="post"
                      onsubmit="return confirm('Yakin mau membatalkan tiket ini?')">
                    <?= csrf_field() ?>
                    <button type="submit"
                            class="border border-red-200 text-red-600 px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-red-50 transition-colors">
                        Batalkan Tiket
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
