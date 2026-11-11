<header class="sticky top-0 z-40 bg-[#FAF8F5]/95 backdrop-blur-md border-b border-stone-200/80 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4">

            <!-- Identitas Brand & Logo Resmi IndWalk -->
            <a href="<?= base_url('/') ?>"
               class="group flex items-center gap-3.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] rounded-lg py-1">
                <div class="relative flex items-center justify-center bg-white border border-stone-200/80 rounded-xl px-2.5 py-1.5 transition-transform duration-200 group-hover:border-[#0B6E77]/40">
                    <img src="<?= base_url('assets/IndWalk_ Explore Indonesia Together.png') ?>"
                         alt="Logo Resmi IndWalk: Explore Indonesia Together"
                         class="h-11 w-auto object-contain">
                </div>
                <div class="flex flex-col">
                    <div class="flex items-baseline gap-0.5 tracking-tight">
                        <span class="font-serif text-xl sm:text-2xl font-bold text-[#0B6E77]">Ind</span>
                        <span class="font-serif text-xl sm:text-2xl font-bold text-[#2D5A36]">Walk</span>
                    </div>
                    <span class="text-[11px] font-medium uppercase tracking-[0.14em] text-stone-500">
                        Explore Indonesia Together
                    </span>
                </div>
            </a>

            <!-- Navigasi Utama Desktop -->
            <nav aria-label="Navigasi Utama" class="hidden lg:flex items-center gap-1">
                <a href="#eksplorasi-destinasi"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium text-stone-700 hover:text-[#0B6E77] hover:bg-stone-200/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors">
                    Katalog Destinasi
                </a>
                <a href="#indeks-kategori"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium text-stone-700 hover:text-[#0B6E77] hover:bg-stone-200/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors">
                    4 Kategori Nusantara
                </a>
                <a href="#integrasi-api-mitra"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium text-stone-700 hover:text-[#0B6E77] hover:bg-stone-200/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors flex items-center gap-2">
                    <span>Simulasi API Mitra</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-[#0B6E77]/10 text-[#0B6E77] tabular-nums">
                        Client-Server
                    </span>
                </a>
                <a href="#kontrak-endpoint"
                   class="px-3.5 py-2 rounded-lg text-sm font-medium text-stone-700 hover:text-[#0B6E77] hover:bg-stone-200/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors">
                    Spesifikasi JSON
                </a>
            </nav>

            <!-- Tombol Aksi: 1 Tombol Outline + 1 Tombol Utama Solid -->
            <div class="hidden sm:flex items-center gap-3">
                <a href="#integrasi-api-mitra"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-medium text-stone-700 border border-stone-300/90 bg-transparent hover:bg-stone-100 hover:border-stone-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-all">
                    <svg class="w-4 h-4 text-[#0B6E77]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/>
                    </svg>
                    <span>Cek API Mitra</span>
                </a>

                <a href="#eksplorasi-destinasi"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C2410C] focus-visible:ring-offset-2 active:translate-y-px transition-all">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <span>Cari Destinasi</span>
                </a>
            </div>

            <!-- Tombol Buka Menu Mobile -->
            <button type="button"
                    id="tombol-menu-mobile"
                    aria-expanded="false"
                    aria-controls="panel-menu-mobile"
                    aria-label="Buka menu navigasi"
                    class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-lg text-stone-700 hover:text-stone-900 hover:bg-stone-200/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors">
                <svg id="ikon-menu-buka" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>
                </svg>
                <svg id="ikon-menu-tutup" class="w-6 h-6 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Panel Navigasi Mobile -->
    <div id="panel-menu-mobile" class="hidden lg:hidden border-t border-stone-200/80 bg-[#FAF8F5] px-4 pt-3 pb-5 space-y-3">
        <div class="flex flex-col space-y-1">
            <a href="#eksplorasi-destinasi" class="tautan-menu-mobile px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60">
                Katalog 6 Destinasi Prioritas
            </a>
            <a href="#indeks-kategori" class="tautan-menu-mobile px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60">
                4 Kategori Wisata Nusantara
            </a>
            <a href="#integrasi-api-mitra" class="tautan-menu-mobile px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60 flex items-center justify-between">
                <span>Simulasi Integrasi REST API Mitra</span>
                <span class="font-mono text-xs px-2 py-0.5 rounded bg-[#0B6E77]/10 text-[#0B6E77]">3 Endpoint</span>
            </a>
            <a href="#kontrak-endpoint" class="tautan-menu-mobile px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60">
                Spesifikasi Kontrak JSON
            </a>
        </div>
        <div class="pt-2 border-t border-stone-200 flex items-center gap-3">
            <a href="#integrasi-api-mitra" class="tautan-menu-mobile flex-1 text-center py-2.5 px-3 rounded-lg text-sm font-medium text-stone-700 border border-stone-300 hover:bg-stone-100">
                Uji API Mitra
            </a>
            <a href="#eksplorasi-destinasi" class="tautan-menu-mobile flex-1 text-center py-2.5 px-3 rounded-lg text-sm font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412]">
                Cari Wisata
            </a>
        </div>
    </div>
</header>

<script>
    (function () {
        // Mengatur buka-tutup menu navigasi pada layar HP (Mobile)
        const tombolMenu = document.getElementById('tombol-menu-mobile');
        const panelMenu = document.getElementById('panel-menu-mobile');
        const ikonBuka = document.getElementById('ikon-menu-buka');
        const ikonTutup = document.getElementById('ikon-menu-tutup');

        if (!tombolMenu || !panelMenu) {
            return;
        }

        tombolMenu.addEventListener('click', function () {
            const sedangTersembunyi = panelMenu.classList.contains('hidden');

            if (sedangTersembunyi) {
                panelMenu.classList.remove('hidden');
                ikonBuka.classList.add('hidden');
                ikonTutup.classList.remove('hidden');
                tombolMenu.setAttribute('aria-expanded', 'true');
            } else {
                panelMenu.classList.add('hidden');
                ikonBuka.classList.remove('hidden');
                ikonTutup.classList.add('hidden');
                tombolMenu.setAttribute('aria-expanded', 'false');
            }
        });

        // Menutup panel menu otomatis saat salah satu link diklik
        const daftarTautanMobile = document.querySelectorAll('.tautan-menu-mobile');
        daftarTautanMobile.forEach(function (tautan) {
            tautan.addEventListener('click', function () {
                panelMenu.classList.add('hidden');
                ikonBuka.classList.remove('hidden');
                ikonTutup.classList.add('hidden');
                tombolMenu.setAttribute('aria-expanded', 'false');
            });
        });
    })();
</script>
