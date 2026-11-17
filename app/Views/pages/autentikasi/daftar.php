<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — IndWalk</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'indwalk-teal':      '#0B6E77',
                        'indwalk-green':     '#2D5A36',
                        'indwalk-olive':     '#6A8E3F',
                        'indwalk-terra':     '#C2410C',
                        'indwalk-amber':     '#D97706',
                        'indwalk-bg':        '#FAF8F5',
                    },
                },
            },
        }
    </script>

    <!-- Google Fonts: Playfair Display + Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-indwalk-bg font-sans text-stone-700 antialiased">

    <div class="min-h-screen flex">

        <!-- ============================================================ -->
        <!-- KOLOM KIRI: Foto lanskap editorial (tersembunyi di mobile)   -->
        <!-- ============================================================ -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <img
                src="https://images.unsplash.com/photo-1555899434-94d1368aa7af?auto=format&fit=crop&w=1200&q=80"
                alt="Pemandangan sawah terasering di Bali"
                class="absolute inset-0 h-full w-full object-cover"
            >
            <!-- Gradient overlay gelap agar teks terbaca jelas -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-transparent"></div>

            <!-- Teks branding di atas foto -->
            <div class="relative z-10 flex flex-col justify-end p-10 pb-14">
                <a href="<?= base_url('/') ?>" class="flex items-center gap-2 mb-6">
                    <span class="font-serif text-2xl font-bold tracking-tight">
                        <span class="text-teal-300">Ind</span><span class="text-emerald-300">Walk</span>
                    </span>
                </a>
                <h2 class="font-serif text-3xl font-semibold text-white leading-[1.15] tracking-tight max-w-md">
                    Bergabunglah dengan komunitas penjelajah Indonesia
                </h2>
                <p class="mt-3 text-stone-300 text-sm leading-relaxed max-w-sm">
                    Simpan destinasi favorit, pesan tiket masuk, dan bagikan ulasan perjalanan Anda ke sesama wisatawan.
                </p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- KOLOM KANAN: Formulir Daftar                                 -->
        <!-- ============================================================ -->
        <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:px-16 xl:px-24">

            <!-- Logo mobile (tampil hanya di layar kecil) -->
            <div class="lg:hidden mb-8">
                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2">
                    <span class="font-serif text-2xl font-bold tracking-tight">
                        <span class="text-indwalk-teal">Ind</span><span class="text-indwalk-green">Walk</span>
                    </span>
                </a>
            </div>

            <div class="w-full max-w-md mx-auto">
                <!-- Judul halaman -->
                <h1 class="font-serif text-3xl font-semibold text-stone-900 tracking-tight leading-[1.1]">
                    Buat Akun Baru
                </h1>
                <p class="mt-2 text-stone-500 text-sm">
                    Sudah punya akun?
                    <a href="<?= base_url('/masuk') ?>"
                       class="text-indwalk-teal font-medium hover:text-teal-800 transition-colors duration-200 underline underline-offset-2">
                        Masuk di sini
                    </a>
                </p>

                <!-- ================================================== -->
                <!-- Notifikasi Flash: Error Validasi                    -->
                <!-- ================================================== -->

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach (session()->getFlashdata('errors') as $pesanError): ?>
                                <li><?= esc($pesanError) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- ================================================== -->
                <!-- Formulir Daftar                                     -->
                <!-- ================================================== -->
                <form action="<?= base_url('/daftar') ?>" method="post" class="mt-8 space-y-5">
                    <?= csrf_field() ?>

                    <!-- Input Nama Lengkap -->
                    <div>
                        <label for="nama" class="block text-sm font-medium text-stone-700 mb-1.5">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="<?= esc(old('nama')) ?>"
                            required
                            autocomplete="name"
                            placeholder="Masukkan nama lengkap Anda"
                            class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-stone-900 text-sm
                                   placeholder:text-stone-400
                                   focus:outline-none focus:ring-2 focus:ring-indwalk-teal/40 focus:border-indwalk-teal
                                   transition-colors duration-200"
                        >
                    </div>

                    <!-- Input Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">
                            Alamat Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= esc(old('email')) ?>"
                            required
                            autocomplete="email"
                            placeholder="contoh@email.com"
                            class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-stone-900 text-sm
                                   placeholder:text-stone-400
                                   focus:outline-none focus:ring-2 focus:ring-indwalk-teal/40 focus:border-indwalk-teal
                                   transition-colors duration-200"
                        >
                    </div>

                    <!-- Input Kata Sandi -->
                    <div>
                        <label for="kata_sandi" class="block text-sm font-medium text-stone-700 mb-1.5">
                            Kata Sandi
                        </label>
                        <input
                            type="password"
                            id="kata_sandi"
                            name="kata_sandi"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 6 karakter"
                            class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-stone-900 text-sm
                                   placeholder:text-stone-400
                                   focus:outline-none focus:ring-2 focus:ring-indwalk-teal/40 focus:border-indwalk-teal
                                   transition-colors duration-200"
                        >
                    </div>

                    <!-- Input Konfirmasi Kata Sandi -->
                    <div>
                        <label for="konfirmasi_kata_sandi" class="block text-sm font-medium text-stone-700 mb-1.5">
                            Konfirmasi Kata Sandi
                        </label>
                        <input
                            type="password"
                            id="konfirmasi_kata_sandi"
                            name="konfirmasi_kata_sandi"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi yang sama"
                            class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-stone-900 text-sm
                                   placeholder:text-stone-400
                                   focus:outline-none focus:ring-2 focus:ring-indwalk-teal/40 focus:border-indwalk-teal
                                   transition-colors duration-200"
                        >
                    </div>

                    <!-- Tombol Daftar (Primary CTA — Terracotta) -->
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-indwalk-terra px-4 py-2.5 text-sm font-semibold text-white
                               hover:bg-orange-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indwalk-terra/50
                               active:scale-[0.98] transition-all duration-200"
                    >
                        Daftar Akun
                    </button>
                </form>

                <!-- Pemisah visual -->
                <div class="relative mt-8">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-stone-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-indwalk-bg px-3 text-xs text-stone-400">atau</span>
                    </div>
                </div>

                <!-- Tombol kembali ke Beranda (Secondary / Ghost) -->
                <a href="<?= base_url('/') ?>"
                   class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg border border-stone-300 bg-white px-4 py-2.5
                          text-sm font-medium text-stone-700
                          hover:bg-stone-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-stone-400/40
                          active:scale-[0.98] transition-all duration-200">
                    <!-- Ikon panah kiri (Lucide: ArrowLeft) -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>

            <!-- Footer kecil -->
            <p class="mt-10 text-center text-xs text-stone-400">
                &copy; <?= date('Y') ?> IndWalk — Explore Indonesia Together
            </p>
        </div>

    </div>

</body>
</html>
