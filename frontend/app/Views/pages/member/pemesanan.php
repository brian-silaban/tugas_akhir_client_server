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
    </style>
</head>
<body class="min-h-screen">

    <!-- Header Halaman -->
    <div class="bg-white border-b border-stone-200">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <a href="/" class="text-sm text-[#0B6E77] hover:underline">← Kembali ke Beranda</a>
            <h1 class="font-serif text-3xl font-bold text-stone-900 mt-2">Tiket Saya</h1>
            <p class="text-stone-500 mt-1">Kelola semua tiket wisata Sumatera Utara yang sudah kamu pesan.</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">

        <!-- Pesan Flash Sukses / Error -->
        <?php if (session('pesan_sukses')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
                ✅ <?= esc(session('pesan_sukses')) ?>
            </div>
        <?php endif; ?>
        <?php if (session('pesan_error') || ! empty($pesan_error)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                ⚠️ <?= esc(session('pesan_error') ?: $pesan_error) ?>
            </div>
        <?php endif; ?>

        <!-- Daftar Tiket -->
        <?php if (empty($daftar_tiket)): ?>
            <!-- State Kosong -->
            <div class="text-center py-20">
                <div class="text-6xl mb-4">🎟️</div>
                <h2 class="font-serif text-xl text-stone-700 mb-2">Belum Ada Tiket</h2>
                <p class="text-stone-500 text-sm mb-6">Kamu belum pernah memesan tiket wisata. Yuk eksplorasi destinasi indah Sumatera Utara!</p>
                <a href="/" class="inline-block bg-[#C2410C] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#B53801] transition-colors">
                    Jelajahi Destinasi
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($daftar_tiket as $tiket): ?>
                    <?php
                        // Tentukan warna badge status tiket
                        $warnaStatus = match($tiket['status']) {
                            'menunggu'   => 'bg-amber-50 text-amber-700 border border-amber-200',
                            'lunas'      => 'bg-green-50 text-green-700 border border-green-200',
                            'dibatalkan' => 'bg-stone-100 text-stone-500 border border-stone-200',
                            default      => 'bg-stone-100 text-stone-500',
                        };
                        $labelStatus = match($tiket['status']) {
                            'menunggu'   => '⏳ Menunggu Pembayaran',
                            'lunas'      => '✅ Lunas / Aktif',
                            'dibatalkan' => '❌ Dibatalkan',
                            default      => ucfirst($tiket['status']),
                        };
                    ?>
                    <!-- Kartu Tiket -->
                    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="flex flex-col sm:flex-row">
                            <!-- Foto Destinasi -->
                            <div class="sm:w-36 h-32 sm:h-auto flex-shrink-0 bg-stone-100">
                                <?php if (! empty($tiket['foto_destinasi'])): ?>
                                    <img src="<?= esc($tiket['foto_destinasi']) ?>" alt="<?= esc($tiket['nama_destinasi']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🏔️</div>
                                <?php endif; ?>
                            </div>

                            <!-- Info Tiket -->
                            <div class="flex-1 p-4 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h3 class="font-semibold text-stone-900 leading-tight"><?= esc($tiket['nama_destinasi']) ?></h3>
                                        <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap <?= $warnaStatus ?>">
                                            <?= $labelStatus ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-stone-500 mb-3"><?= esc($tiket['kota_destinasi']) ?>, Sumatera Utara</p>

                                    <!-- Detail Pemesanan -->
                                    <div class="grid grid-cols-3 gap-2 text-xs text-stone-600">
                                        <div>
                                            <span class="text-stone-400 block">Kode Tiket</span>
                                            <span class="font-mono font-semibold text-[#0B6E77] tabular-nums"><?= esc($tiket['kode_pemesanan']) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-stone-400 block">Tanggal Kunjungan</span>
                                            <span class="font-medium tabular-nums"><?= date('d M Y', strtotime($tiket['tanggal_kunjungan'])) ?></span>
                                        </div>
                                        <div>
                                            <span class="text-stone-400 block">Total Bayar</span>
                                            <span class="font-semibold text-stone-900 tabular-nums"><?= esc($tiket['total_harga_terformat']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="flex items-center gap-2 mt-4">
                                    <a href="<?= base_url('pemesanan/etiket/' . $tiket['id']) ?>"
                                       class="inline-flex items-center gap-1.5 bg-[#0B6E77] text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-[#00545B] transition-colors focus:outline-none focus:ring-2 focus:ring-[#0B6E77] focus:ring-offset-1">
                                        🎟️ Lihat E-Ticket
                                    </a>
                                    <?php if ($tiket['status'] === 'menunggu'): ?>
                                        <form action="<?= base_url('pemesanan/batal/' . $tiket['id']) ?>" method="post"
                                              onsubmit="return confirm('Yakin mau membatalkan tiket ini?')">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 border border-stone-300 text-stone-600 px-4 py-2 rounded-lg text-xs font-medium hover:bg-stone-50 transition-colors focus:outline-none focus:ring-2 focus:ring-stone-400 focus:ring-offset-1">
                                                Batalkan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
