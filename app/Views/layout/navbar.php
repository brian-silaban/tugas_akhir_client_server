<header class="sticky top-0 z-40 bg-[#FAF8F5]/95 backdrop-blur-md border-b border-stone-200/80 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4">

            <!-- Brand Identity with Official Logo -->
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

            <!-- Primary Desktop Navigation -->
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

            <!-- Action Area: 1 Secondary/Ghost + 1 Primary Solid (Sloplint Rule #1: Never two primary filled buttons side-by-side) -->
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

            <!-- Mobile Menu Toggle Button -->
            <button type="button"
                    id="mobile-nav-toggle"
                    aria-expanded="false"
                    aria-controls="mobile-nav-panel"
                    aria-label="Buka menu navigasi"
                    class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-lg text-stone-700 hover:text-stone-900 hover:bg-stone-200/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B6E77] active:bg-stone-200 transition-colors">
                <svg id="mobile-nav-icon-open" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>
                </svg>
                <svg id="mobile-nav-icon-close" class="w-6 h-6 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Panel -->
    <div id="mobile-nav-panel" class="hidden lg:hidden border-t border-stone-200/80 bg-[#FAF8F5] px-4 pt-3 pb-5 space-y-3">
        <div class="flex flex-col space-y-1">
            <a href="#eksplorasi-destinasi" class="mobile-nav-link px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60">
                Katalog 6 Destinasi Prioritas
            </a>
            <a href="#indeks-kategori" class="mobile-nav-link px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60">
                4 Kategori Wisata Nusantara
            </a>
            <a href="#integrasi-api-mitra" class="mobile-nav-link px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60 flex items-center justify-between">
                <span>Simulasi Integrasi REST API Mitra</span>
                <span class="font-mono text-xs px-2 py-0.5 rounded bg-[#0B6E77]/10 text-[#0B6E77]">3 Endpoint</span>
            </a>
            <a href="#kontrak-endpoint" class="mobile-nav-link px-3 py-2.5 rounded-lg text-sm font-medium text-stone-800 hover:bg-stone-200/60">
                Spesifikasi Kontrak JSON
            </a>
        </div>
        <div class="pt-2 border-t border-stone-200 flex items-center gap-3">
            <a href="#integrasi-api-mitra" class="mobile-nav-link flex-1 text-center py-2.5 px-3 rounded-lg text-sm font-medium text-stone-700 border border-stone-300 hover:bg-stone-100">
                Uji API Mitra
            </a>
            <a href="#eksplorasi-destinasi" class="mobile-nav-link flex-1 text-center py-2.5 px-3 rounded-lg text-sm font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412]">
                Cari Wisata
            </a>
        </div>
    </div>
</header>

<script>
    (function () {
        const toggleBtn = document.getElementById('mobile-nav-toggle');
        const panel = document.getElementById('mobile-nav-panel');
        const iconOpen = document.getElementById('mobile-nav-icon-open');
        const iconClose = document.getElementById('mobile-nav-icon-close');

        if (!toggleBtn || !panel) return;

        toggleBtn.addEventListener('click', function () {
            const isHidden = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !isHidden);
            iconOpen.classList.toggle('hidden', isHidden);
            iconClose.classList.toggle('hidden', !isHidden);
            toggleBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });

        document.querySelectorAll('.mobile-nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                panel.classList.add('hidden');
                iconOpen.classList.remove('hidden');
                iconClose.classList.add('hidden');
                toggleBtn.setAttribute('aria-expanded', 'false');
            });
        });
    })();
</script>
