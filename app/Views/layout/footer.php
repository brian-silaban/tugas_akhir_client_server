<footer id="kontrak-endpoint" class="bg-[#1C1917] text-stone-300 border-t border-stone-800 mt-20">
    <!-- Top Architectural Banner: REST API Provider & Consumer Contract Summary -->
    <div class="border-b border-stone-800 bg-stone-900/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <div class="lg:col-span-5">
                    <span class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-[#6A8E3F]">
                        <span class="w-2 h-2 rounded-full bg-[#6A8E3F]"></span>
                        Arsitektur Client-Server Dua Arah
                    </span>
                    <h2 class="font-serif text-xl sm:text-2xl text-stone-100 mt-1">
                        IndWalk sebagai API Provider &amp; Consumer
                    </h2>
                    <p class="text-sm text-stone-400 mt-1.5 leading-relaxed">
                        Menyediakan data destinasi terstruktur untuk kelompok mitra sekaligus mengonsumsi endpoint Kuliner, Penginapan, dan Transportasi berdasarkan parameter kota.
                    </p>
                </div>

                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs tabular-nums">
                    <div class="rounded-xl bg-stone-950/90 border border-stone-800 p-3.5">
                        <div class="flex items-center justify-between text-[11px] text-stone-400 mb-1">
                            <span class="px-1.5 py-0.5 rounded bg-[#0B6E77]/25 text-teal-300 font-semibold">GET</span>
                            <span>200 OK</span>
                        </div>
                        <div class="text-stone-100 font-medium truncate">/api/v1/destinations</div>
                        <div class="text-stone-400 text-[11px] mt-1">?city=&amp;category=&amp;max_price=</div>
                    </div>

                    <div class="rounded-xl bg-stone-950/90 border border-stone-800 p-3.5">
                        <div class="flex items-center justify-between text-[11px] text-stone-400 mb-1">
                            <span class="px-1.5 py-0.5 rounded bg-[#0B6E77]/25 text-teal-300 font-semibold">GET</span>
                            <span>200 OK</span>
                        </div>
                        <div class="text-stone-100 font-medium truncate">/api/v1/destinations/{slug}</div>
                        <div class="text-stone-400 text-[11px] mt-1">Detail + Galeri + Koordinat</div>
                    </div>

                    <div class="rounded-xl bg-stone-950/90 border border-stone-800 p-3.5">
                        <div class="flex items-center justify-between text-[11px] text-stone-400 mb-1">
                            <span class="px-1.5 py-0.5 rounded bg-[#2D5A36]/40 text-emerald-300 font-semibold">GET</span>
                            <span>200 OK</span>
                        </div>
                        <div class="text-stone-100 font-medium truncate">/api/v1/categories</div>
                        <div class="text-stone-400 text-[11px] mt-1">4 Taksonomi Wisata Utama</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">

            <!-- Column 1: Brand & Logo -->
            <div class="lg:col-span-4 space-y-4">
                <div class="inline-flex items-center gap-3 bg-[#FAF8F5] px-3.5 py-2 rounded-xl">
                    <img src="<?= base_url('assets/IndWalk_ Explore Indonesia Together.png') ?>"
                         alt="Logo IndWalk"
                         class="h-10 w-auto object-contain">
                    <div class="pr-1">
                        <div class="font-serif text-lg font-bold leading-none">
                            <span class="text-[#0B6E77]">Ind</span><span class="text-[#2D5A36]">Walk</span>
                        </div>
                        <span class="text-[10px] font-semibold tracking-widest uppercase text-stone-500">
                            Explore Indonesia Together
                        </span>
                    </div>
                </div>
                <p class="text-sm text-stone-300 leading-relaxed max-w-sm">
                    Platform direktori kurasi dan pemesanan tiket destinasi wisata Nusantara dengan kepadatan informasi faktual: transparansi tarif, jam operasional, titik koordinat GPS, serta interkoneksi REST API antar-layanan perjalanan.
                </p>
                <div class="pt-1 flex flex-wrap gap-2 text-xs font-mono text-stone-300 tabular-nums">
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800/90 border border-stone-700">CodeIgniter 4 (PHP 8.2+)</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800/90 border border-stone-700">PostgreSQL 5432</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800/90 border border-stone-700">RESTful JSON</span>
                </div>
            </div>

            <!-- Column 2: 4 Kategori Kurasi -->
            <div class="lg:col-span-3 space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-200">
                    Taksonomi Kategori
                </h3>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="#eksplorasi-destinasi" data-footer-category="cagar-budaya" class="footer-cat-trigger text-stone-300 hover:text-white transition-colors flex items-center justify-between">
                            <span>01 · Cagar Budaya</span>
                            <span class="font-mono text-xs text-stone-400 tabular-nums">2 Lokasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#eksplorasi-destinasi" data-footer-category="pegunungan-geopark" class="footer-cat-trigger text-stone-300 hover:text-white transition-colors flex items-center justify-between">
                            <span>02 · Pegunungan &amp; Geopark</span>
                            <span class="font-mono text-xs text-stone-400 tabular-nums">2 Lokasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#eksplorasi-destinasi" data-footer-category="wisata-bahari" class="footer-cat-trigger text-stone-300 hover:text-white transition-colors flex items-center justify-between">
                            <span>03 · Wisata Bahari</span>
                            <span class="font-mono text-xs text-stone-400 tabular-nums">1 Lokasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="#eksplorasi-destinasi" data-footer-category="hutan-air-terjun" class="footer-cat-trigger text-stone-300 hover:text-white transition-colors flex items-center justify-between">
                            <span>04 · Hutan &amp; Air Terjun</span>
                            <span class="font-mono text-xs text-stone-400 tabular-nums">1 Lokasi</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Column 3: 6 Kota Simpul API -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-200">
                    Simpul Kota (`?city=`)
                </h3>
                <ul class="space-y-2 text-sm text-stone-300">
                    <li><a href="#integrasi-api-mitra" data-footer-city="Magelang" class="footer-city-trigger hover:text-white transition-colors">Magelang, Jateng</a></li>
                    <li><a href="#integrasi-api-mitra" data-footer-city="Banyuwangi" class="footer-city-trigger hover:text-white transition-colors">Banyuwangi, Jatim</a></li>
                    <li><a href="#integrasi-api-mitra" data-footer-city="Labuan Bajo" class="footer-city-trigger hover:text-white transition-colors">Labuan Bajo, NTT</a></li>
                    <li><a href="#integrasi-api-mitra" data-footer-city="Bangli" class="footer-city-trigger hover:text-white transition-colors">Bangli, Bali</a></li>
                    <li><a href="#integrasi-api-mitra" data-footer-city="Malang" class="footer-city-trigger hover:text-white transition-colors">Malang, Jatim</a></li>
                    <li><a href="#integrasi-api-mitra" data-footer-city="Lumajang" class="footer-city-trigger hover:text-white transition-colors">Lumajang, Jatim</a></li>
                </ul>
            </div>

            <!-- Column 4: Ekosistem Mitra Client-Server -->
            <div class="lg:col-span-3 space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-200">
                    Konfigurasi Endpoint Mitra
                </h3>
                <div class="rounded-xl bg-stone-900 border border-stone-800 p-4 space-y-2.5 text-xs font-mono tabular-nums">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-300">PARTNER_KULINER</span>
                        <span class="text-emerald-400">:8081/api/kuliner</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-300">PARTNER_PENGINAPAN</span>
                        <span class="text-emerald-400">:8082/api/penginapan</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-300">PARTNER_TRANSPORTASI</span>
                        <span class="text-emerald-400">:8083/api/transportasi</span>
                    </div>
                    <div class="pt-2 border-t border-stone-800 flex items-center justify-between text-[11px] text-stone-400">
                        <span>MOCK_FALLBACK</span>
                        <span class="text-amber-400 font-semibold">ACTIVE (true)</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bottom Copyright & Academic Attribution -->
        <div class="mt-12 pt-8 border-t border-stone-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-400 tabular-nums">
            <p>&copy; <?= date('Y') ?> <strong class="text-stone-200">IndWalk — Explore Indonesia Together</strong>. Tugas Akhir Mata Kuliah Pemrograman Client-Server.</p>
            <p class="font-mono">7 Tabel PostgreSQL · 6 Destinasi Kurasi · Standar Desain Anti-AI-Slop</p>
        </div>
    </div>
</footer>
