<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi — JejakSumut</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/logo-jejaksumut.png') ?>" />

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
                        "secondary-container": "#c2f0c5",
                        "on-secondary-fixed-variant": "#1a4826",
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
                        "on-error-container": "#410002",
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
</head>

<body class="bg-surface font-sans text-on-surface antialiased">

    <div class="min-h-screen flex">

        <!-- Kolom Kiri: Lanskap editorial Sumatera Utara -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-surface-container">
            <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/47/Air_terjun_Ponot.jpg/1280px-Air_terjun_Ponot.jpg"
                alt="Air Terjun Ponot di Asahan, Sumatera Utara"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

            <div class="relative z-10 flex flex-col justify-end p-10 pb-14">
                <a href="<?= base_url('/') ?>" class="flex items-center gap-3 mb-6">
                    <img src="<?= base_url('assets/logo-jejaksumut.png') ?>" alt="Logo JejakSumut" class="h-10 w-10 object-contain" />
                    <span class="font-serif text-2xl font-bold tracking-tight text-white">
                        Jejak<span class="text-teal-300">Sumut</span>
                    </span>
                </a>
                <h2 class="font-serif text-3xl font-semibold text-white leading-[1.15] tracking-tight max-w-md">
                    Pulihkan Akses Akun Anda dengan Aman
                </h2>
                <p class="mt-3 text-white/80 text-sm leading-relaxed max-w-sm">
                    Verifikasi identitas akun untuk membuat tautan atur ulang kata sandi berdurasi 15 menit.
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Formulir Lupa Kata Sandi -->
        <div class="flex flex-1 flex-col justify-center px-6 py-12 lg:px-16 xl:px-24">

            <div class="lg:hidden mb-8">
                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-3">
                    <img src="<?= base_url('assets/logo-jejaksumut.png') ?>" alt="Logo JejakSumut" class="h-9 w-9 object-contain" />
                    <span class="font-serif text-2xl font-bold tracking-tight text-primary">
                        JejakSumut
                    </span>
                </a>
            </div>

            <div class="w-full max-w-md mx-auto">
                <h1 class="font-serif text-3xl font-semibold text-on-surface tracking-tight leading-[1.1]">
                    Lupa Kata Sandi
                </h1>
                <p class="mt-2 text-on-surface-variant text-sm">
                    Masukkan alamat email dan nama lengkap yang terdaftar pada akun member atau pengelola Anda.
                </p>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mt-5 rounded-xl border border-error/30 bg-error-container/30 px-4 py-3 text-sm text-on-error-container flex items-start gap-2.5" role="alert">
                        <span class="material-symbols-outlined text-error text-lg shrink-0">error</span>
                        <span><?= esc(session()->getFlashdata('error')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="mt-5 rounded-xl border border-secondary/30 bg-secondary-container/30 px-4 py-3 text-sm text-on-secondary-fixed-variant flex items-start gap-2.5" role="status">
                        <span class="material-symbols-outlined text-secondary text-lg shrink-0">check_circle</span>
                        <span><?= esc(session()->getFlashdata('success')) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($tokenSimulasi = (string) session()->getFlashdata('token_reset_simulasi')): ?>
                    <div class="mt-4 rounded-xl border border-primary/30 bg-surface-container-low p-4 text-sm text-on-surface space-y-3">
                        <p class="font-semibold text-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-lg" aria-hidden="true">verified_user</span>
                            <span>Mode Simulasi Lokal (SMTP belum aktif)</span>
                        </p>
                        <p class="text-xs text-on-surface-variant leading-relaxed">
                            Token verifikasi sekali pakai (berlaku 15 menit) siap digunakan. Klik tombol di bawah untuk mengatur kata sandi baru.
                        </p>
                        <a href="<?= base_url('reset-sandi?token=' . urlencode($tokenSimulasi)) ?>"
                           class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-container transition-colors">
                            <span>Lanjut Atur Ulang Kata Sandi</span>
                            <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
                        </a>
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

                <form action="<?= base_url('/lupa-sandi') ?>" method="post" class="mt-8 space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label for="email" class="block text-sm font-medium text-on-surface mb-1.5">
                            Alamat Email Terdaftar
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
                                   placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container transition-colors duration-200"
                        />
                    </div>

                    <div>
                        <label for="nama" class="block text-sm font-medium text-on-surface mb-1.5">
                            Nama Lengkap Akun
                        </label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="<?= esc(old('nama')) ?>"
                            required
                            autocomplete="name"
                            placeholder="Masukkan nama lengkap sesuai profil akun"
                            class="block w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-4 py-2.5 text-on-surface text-sm
                                   placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary-container/40 focus:border-primary-container transition-colors duration-200"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-tertiary-container hover:bg-tertiary px-4 py-2.5 text-sm font-semibold text-on-tertiary
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-tertiary-container/50
                               active:scale-[0.98] transition-all duration-200 shadow-sm"
                    >
                        Ajukan Reset Kata Sandi
                    </button>
                </form>

                <div class="mt-6 flex items-center justify-between text-xs text-on-surface-variant">
                    <a href="<?= base_url('/masuk') ?>"
                       class="inline-flex items-center gap-1 font-medium text-on-surface hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_back</span>
                        <span>Kembali ke Halaman Masuk</span>
                    </a>
                    <a href="<?= base_url('/ganti-sandi') ?>"
                       class="font-semibold text-primary-container hover:text-primary underline underline-offset-2">
                        Ingat sandi lama? Ganti di sini
                    </a>
                </div>
            </div>

            <p class="mt-10 text-center text-xs text-on-surface-variant">
                &copy; <?= date('Y') ?> JejakSumut — Jelajahi Pesona Sumatera Utara
            </p>
        </div>

    </div>

</body>
</html>
