<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — IndWalk</title>

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
                src="https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?auto=format&fit=crop&w=1200&q=80"
                alt="Pemandangan Candi Borobudur saat matahari terbit"
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
                    Temukan destinasi wisata Nusantara yang menanti langkah Anda
                </h2>
                <p class="mt-3 text-stone-300 text-sm leading-relaxed max-w-sm">
                    Direktori kurasi wisata Indonesia dengan informasi harga, jam operasional, dan ulasan terverifikasi dari pengunjung.
                </p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- KOLOM KANAN: Formulir Masuk                                  -->
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
                    Masuk ke Akun Anda
                </h1>
                <p class="mt-2 text-stone-500 text-sm">
                    Belum punya akun?
                    <a href="<?= base_url('/daftar') ?>"
                       class="text-indwalk-teal font-medium hover:text-teal-800 transition-colors duration-200 underline underline-offset-2">
                        Daftar sekarang
                    </a>
                </p>

                <!-- ================================================== -->
                <!-- Notifikasi Flash: Error / Sukses                   -->
                <!-- ================================================== -->

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <div class="flex items-start gap-2">
                            <!-- Ikon peringatan (Lucide: AlertCircle) -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                 stroke-linejoin="round" class="mt-0.5 shrink-0 text-red-500">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span><?= esc(session()->getFlashdata('error')) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="alert">
                        <div class="flex items-start gap-2">
                            <!-- Ikon centang (Lucide: CheckCircle2) -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                 stroke-linejoin="round" class="mt-0.5 shrink-0 text-emerald-500">
                                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                            <span><?= esc(session()->getFlashdata('success')) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

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
                <!-- Formulir Masuk                                      -->
                <!-- ================================================== -->
                <form action="<?= base_url('/masuk') ?>" method="post" class="mt-8 space-y-5">
                    <?= csrf_field() ?>

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
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="kata_sandi" class="block text-sm font-medium text-stone-700">
                                Kata Sandi
                            </label>
                        </div>
                        <input
                            type="password"
                            id="kata_sandi"
                            name="kata_sandi"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi"
                            class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-stone-900 text-sm
                                   placeholder:text-stone-400
                                   focus:outline-none focus:ring-2 focus:ring-indwalk-teal/40 focus:border-indwalk-teal
                                   transition-colors duration-200"
                        >
                    </div>

                    <!-- Tombol Masuk (Primary CTA — Terracotta) -->
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-indwalk-terra px-4 py-2.5 text-sm font-semibold text-white
                               hover:bg-orange-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indwalk-terra/50
                               active:scale-[0.98] transition-all duration-200"
                    >
                        Masuk
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
