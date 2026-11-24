<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($mode_admin ?? false) ? 'Masuk Panel Pengelola' : 'Masuk' ?> — JejakSumut</title>

    <!-- Tailwind CSS CDN & Design Tokens Resmi JejakSumut -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#00545b",
                        "primary-container": "#0b6e77",
                        "on-primary": "#ffffff",
                        "secondary": "#3b6843",
                        "tertiary": "#8c2900",
                        "tertiary-container": "#b53801",
                        "on-tertiary": "#ffffff",
                        "surface": "#fff8f5",
                        "surface-container-low": "#faf2ee",
                        "surface-container": "#f4ece8",
                        "surface-container-lowest": "#ffffff",
                        "surface-variant": "#e9e1dd",
                        "on-surface": "#1e1b19",
                        "on-surface-variant": "#3f494a",
                        "outline": "#6f797a",
                        "outline-variant": "#bec8ca",
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <!-- Google Fonts: Playfair Display + Plus Jakarta Sans + Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
</head>

<body class="bg-surface font-sans text-on-surface antialiased">

    <div class="min-h-screen flex">

        <!-- ============================================================ -->
        <!-- KOLOM KIRI: Foto lanskap editorial (tersembunyi di mobile)   -->
        <!-- ============================================================ -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-surface-container">
            <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Lake_Toba_and_the_surrounding_hills.jpg/1280px-Lake_Toba_and_the_surrounding_hills.jpg"
                alt="Panorama Danau Toba dan Perbukitan Sumatera Utara"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <!-- Gradient overlay gelap agar teks terbaca jelas -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

            <!-- Teks branding di atas foto -->
            <div class="relative z-10 flex flex-col justify-end p-10 pb-14">
                <a href="<?= base_url('/') ?>" class="flex items-center gap-3 mb-6">
                    <img src="<?= base_url('assets/logo-jejaksumut.png') ?>" alt="Logo JejakSumut" class="h-10 w-10 object-contain" />
                    <span class="font-serif text-2xl font-bold tracking-tight text-white">
                        Jejak<span class="text-on-primary-container text-teal-300">Sumut</span>
                    </span>
                </a>
                <h2 class="font-serif text-3xl font-semibold text-white leading-[1.15] tracking-tight max-w-md">
                    Jelajahi 621 Pesona Wisata Sumatera Utara
                </h2>
                <p class="mt-3 text-white/80 text-sm leading-relaxed max-w-sm">
                    Dari keajaiban Kaldera Toba hingga eksotisme Nias dan Leuser. Masuk ke akun Anda untuk merencanakan liburan impian.
                </p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- KOLOM KANAN: Formulir Masuk                                  -->
        <!-- ============================================================ -->
        <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:px-16 xl:px-24">

            <!-- Logo mobile (tampil hanya di layar kecil) -->
            <div class="lg:hidden mb-8">
                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-3">
                    <img src="<?= base_url('assets/logo-jejaksumut.png') ?>" alt="Logo JejakSumut" class="h-9 w-9 object-contain" />
                    <span class="font-serif text-2xl font-bold tracking-tight text-primary">
                        JejakSumut
                    </span>
                </a>
            </div>

            <div class="w-full max-w-md mx-auto">
                <!-- Judul halaman -->
                <h1 class="font-serif text-3xl font-semibold text-on-surface tracking-tight leading-[1.1]">
                    <?= ($mode_admin ?? false) ? 'Masuk Panel Pengelola' : 'Masuk ke Akun Anda' ?>
                </h1>
                <?php if (! ($mode_admin ?? false)): ?>
                <p class="mt-2 text-on-surface-variant text-sm">
                    Belum punya akun?
                    <a href="<?= base_url('/daftar') ?>"
                       class="text-primary-container font-semibold hover:text-primary transition-colors underline underline-offset-2">
                        Daftar sekarang
                    </a>
                </p>
                <?php endif ?>

                <!-- ================================================== -->
                <!-- Notifikasi Flash: Error / Sukses                   -->
                <!-- ================================================== -->

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mt-5 rounded-xl border border-error/30 bg-error-container/30 px-4 py-3 text-sm text-on-error-container flex items-start gap-2.5" role="alert">
                        <span class="material-symbols-outlined text-error text-lg shrink-0">error</span>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mt-5 rounded-xl border border-secondary/30 bg-secondary-container/30 px-4 py-3 text-sm text-on-secondary-fixed-variant flex items-start gap-2.5" role="alert">
                        <span class="material-symbols-outlined text-secondary text-lg shrink-0">check_circle</span>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mt-5 rounded-xl border border-error/30 bg-error-container/30 px-4 py-3 text-sm text-on-error-container" role="alert">
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
                        <label for="email" class="block text-sm font-medium text-on-surface mb-1.5">
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
                            class="block w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-on-surface text-sm
                                   placeholder:text-outline
                                   focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container
                                   transition-colors duration-200"
                        />
                    </div>

                    <!-- Input Kata Sandi -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="kata_sandi" class="block text-sm font-medium text-on-surface">
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
                            class="block w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-on-surface text-sm
                                   placeholder:text-outline
                                   focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container
                                   transition-colors duration-200"
                        />
                    </div>

                    <!-- Tombol Masuk (Primary CTA — Terracotta Container) -->
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-tertiary-container hover:bg-tertiary px-4 py-2.5 text-sm font-semibold text-on-tertiary
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tertiary-container/50
                               active:scale-[0.98] transition-all duration-200 shadow-sm"
                    >
                        Masuk ke Akun
                    </button>
                </form>

                <!-- Pemisah visual -->
                <?php if (! ($mode_admin ?? false)): ?>
                <div class="relative mt-8">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-surface-variant"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-surface px-3 text-xs text-on-surface-variant">atau</span>
                    </div>
                </div>

                <!-- Tombol kembali ke Beranda (Secondary / Ghost) -->
                <a href="<?= base_url('/') ?>"
                   class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5
                          text-sm font-medium text-on-surface
                          hover:bg-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-outline/40
                          active:scale-[0.98] transition-all duration-200">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    <span>Kembali ke Beranda</span>
                </a>
                <?php endif ?>
            </div>

            <!-- Footer kecil -->
            <p class="mt-10 text-center text-xs text-on-surface-variant">
                &copy; <?= date('Y') ?> JejakSumut — Jelajahi Pesona Sumatera Utara
            </p>
        </div>

    </div>

</body>
</html>
