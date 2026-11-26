/**
 * Helper terpusat SweetAlert2 untuk JejakSumut (Wisatawan & Panel Admin).
 * Menggantikan alert()/confirm() bawaan browser dengan Toast & Modal modern yang ringkas.
 */
(function () {
    function pasangGayaSwal() {
        var gayaLama = document.getElementById('gaya-swal-jejaksumut');
        if (gayaLama) {
            // Pastikan selalu berada di urutan terakhir <head> agar menang atas style bawaan SweetAlert2
            document.head.appendChild(gayaLama);
            return;
        }

        var gaya = document.createElement('style');
        gaya.id = 'gaya-swal-jejaksumut';
        gaya.textContent = [
            /* Modal konfirmasi & alert penting */
            '.swal2-popup.swal-jejaksumut { width: min(420px, calc(100vw - 2rem)) !important; border-radius: 20px !important; border: 1px solid #e5d7d0 !important; background: #fff8f5 !important; color: #241d19 !important; font-family: "Plus Jakarta Sans", system-ui, sans-serif !important; padding: 1.6rem 1.5rem 1.4rem !important; box-shadow: 0 24px 48px -12px rgba(36, 29, 25, 0.2) !important; }',
            '.swal2-popup.swal-jejaksumut .swal2-title { font-family: "Playfair Display", Georgia, serif !important; color: #241d19 !important; font-weight: 700 !important; font-size: 1.38rem !important; line-height: 1.25 !important; }',
            '.swal2-popup.swal-jejaksumut .swal2-html-container { color: #665751 !important; font-size: 0.92rem !important; line-height: 1.55 !important; margin-top: 0.55rem !important; }',
            '.swal2-popup.swal-jejaksumut .swal2-actions { gap: 0.6rem !important; margin-top: 1.35rem !important; }',
            '.swal2-popup.swal-jejaksumut .swal2-confirm, .swal2-popup.swal-jejaksumut .swal2-cancel { border-radius: 12px !important; font-weight: 700 !important; font-size: 0.875rem !important; padding: 0.65rem 1.25rem !important; min-height: 44px !important; margin: 0 !important; }',

            /* Wadah Toast pojok kanan atas: ringkas, tidak pernah full-height */
            '.swal2-container.swal2-top-end { position: fixed !important; top: 0 !important; right: 0 !important; left: auto !important; bottom: auto !important; width: auto !important; height: auto !important; max-width: calc(100vw - 1.5rem) !important; z-index: 9999 !important; padding: 5.25rem 1.25rem 0 0 !important; background: transparent !important; pointer-events: none !important; display: flex !important; justify-content: flex-end !important; align-items: flex-start !important; }',
            '@media (max-width: 640px) { .swal2-container.swal2-top-end { padding: 4.75rem 0.75rem 0 0.75rem !important; max-width: 100vw !important; width: 100% !important; } }',

            /* Kartu Toast Modern JejakSumut */
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut { display: flex !important; flex-direction: row !important; align-items: center !important; width: auto !important; min-width: 280px !important; max-width: 390px !important; height: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0.85rem 1rem !important; gap: 0.75rem !important; pointer-events: auto !important; position: relative !important; overflow: hidden !important; border-radius: 14px !important; border: 1px solid #e5d7d0 !important; border-left: 4px solid #00545b !important; background: rgba(255, 255, 255, 0.97) !important; backdrop-filter: blur(12px) !important; color: #241d19 !important; box-shadow: 0 14px 30px -6px rgba(36, 29, 25, 0.16), 0 4px 10px -2px rgba(36, 29, 25, 0.06) !important; font-family: "Plus Jakarta Sans", system-ui, sans-serif !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut.swal-toast-gagal { border-left-color: #b53801 !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-icon { width: 22px !important; height: 22px !important; min-width: 22px !important; margin: 0 !important; font-size: 11px !important; align-self: center !important; border-width: 2px !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-icon .swal2-icon-content { font-size: 0.95rem !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-title { font-size: 0.875rem !important; font-weight: 600 !important; line-height: 1.4 !important; color: #241d19 !important; margin: 0 !important; padding: 0 !important; text-align: left !important; flex: 1 1 auto !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-close { width: 28px !important; height: 28px !important; min-width: 28px !important; margin: 0 0 0 0.25rem !important; padding: 0 !important; border-radius: 8px !important; color: #78716c !important; font-size: 1.15rem !important; line-height: 1 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; align-self: center !important; transition: background-color 0.15s ease, color 0.15s ease !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-close:hover { background: #f5ebe6 !important; color: #241d19 !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-timer-progress-bar-container { position: absolute !important; left: 0 !important; right: 0 !important; bottom: 0 !important; height: 3px !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut .swal2-timer-progress-bar { height: 3px !important; background: linear-gradient(90deg, #00545b, #1d7a82) !important; }',
            '.swal2-popup.swal2-toast.swal-toast-jejaksumut.swal-toast-gagal .swal2-timer-progress-bar { background: linear-gradient(90deg, #b53801, #d97706) !important; }'
        ].join('\n');
        document.head.appendChild(gaya);
    }

    function tampilkanToast(ikon, pesan, durasiMs) {
        if (typeof window.Swal === 'undefined' || !pesan) {
            return;
        }

        pasangGayaSwal();

        var kelasPopup = ikon === 'error'
            ? 'swal-toast-jejaksumut swal-toast-gagal'
            : 'swal-toast-jejaksumut';

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: ikon,
            iconColor: ikon === 'error' ? '#b53801' : '#00545b',
            title: pesan,
            showConfirmButton: false,
            showCloseButton: true,
            timer: durasiMs || 4500,
            timerProgressBar: true,
            customClass: { popup: kelasPopup },
            didOpen: function (elemenToast) {
                pasangGayaSwal();
                elemenToast.addEventListener('mouseenter', window.Swal.stopTimer);
                elemenToast.addEventListener('mouseleave', window.Swal.resumeTimer);
            }
        });
    }

    function tampilkanFlashSwal() {
        if (typeof window.Swal === 'undefined') {
            return;
        }

        var wadah = document.getElementById('wadah-notifikasi-swal');
        if (!wadah) {
            return;
        }

        var pesanSukses = (wadah.getAttribute('data-swal-sukses') || '').trim();
        var pesanGagal = (wadah.getAttribute('data-swal-gagal') || '').trim();

        if (pesanSukses !== '') {
            tampilkanToast('success', pesanSukses, 5000);
        }

        if (pesanGagal !== '') {
            tampilkanToast('error', pesanGagal, 6500);
        }
    }

    function inisialisasiKonfirmasiSwal() {
        document.addEventListener('submit', function (event) {
            var formulir = event.target;
            if (!formulir || !formulir.hasAttribute('data-swal-konfirmasi')) {
                return;
            }
            if (formulir.dataset.swalTerkonfirmasi === 'ya') {
                return;
            }
            if (typeof window.Swal === 'undefined') {
                return;
            }

            event.preventDefault();
            pasangGayaSwal();

            var tombolPemicu = event.submitter || formulir.querySelector('button[type="submit"]');
            var judul = formulir.getAttribute('data-swal-judul') || 'Konfirmasi Tindakan';
            var teks = formulir.getAttribute('data-swal-teks') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            var ikon = formulir.getAttribute('data-swal-ikon') || 'warning';
            var teksYa = formulir.getAttribute('data-swal-tombol-ya') || 'Ya, Lanjutkan';
            var teksBatal = formulir.getAttribute('data-swal-tombol-batal') || 'Batal';
            var modeBahaya = formulir.getAttribute('data-swal-bahaya') === 'true';

            if (tombolPemicu) {
                judul = tombolPemicu.getAttribute('data-swal-judul') || judul;
                teks = tombolPemicu.getAttribute('data-swal-teks') || teks;
                ikon = tombolPemicu.getAttribute('data-swal-ikon') || ikon;
                teksYa = tombolPemicu.getAttribute('data-swal-tombol-ya') || teksYa;
                if (tombolPemicu.hasAttribute('data-swal-bahaya')) {
                    modeBahaya = tombolPemicu.getAttribute('data-swal-bahaya') === 'true';
                }
            }

            window.Swal.fire({
                icon: ikon,
                title: judul,
                text: teks,
                showCancelButton: true,
                confirmButtonText: teksYa,
                cancelButtonText: teksBatal,
                confirmButtonColor: modeBahaya ? '#b53801' : '#00545b',
                cancelButtonColor: '#665751',
                reverseButtons: true,
                focusCancel: modeBahaya,
                customClass: { popup: 'swal-jejaksumut' }
            }).then(function (hasil) {
                if (!hasil.isConfirmed) {
                    return;
                }

                // Pertahankan name & value tombol submitter agar ikut terkirim saat form.submit()
                if (tombolPemicu && tombolPemicu.name) {
                    var inputTambahan = document.createElement('input');
                    inputTambahan.type = 'hidden';
                    inputTambahan.name = tombolPemicu.name;
                    inputTambahan.value = tombolPemicu.value || '';
                    formulir.appendChild(inputTambahan);
                }

                // Nonaktifkan tombol untuk mencegah pengiriman ganda
                if (tombolPemicu) {
                    tombolPemicu.disabled = true;
                    tombolPemicu.setAttribute('aria-busy', 'true');
                    var labelTombol = tombolPemicu.querySelector('[data-teks-tombol]');
                    var teksProses = tombolPemicu.getAttribute('data-teks-proses') || formulir.getAttribute('data-teks-proses');
                    if (labelTombol && teksProses) {
                        labelTombol.textContent = teksProses;
                    }
                }

                formulir.dataset.swalTerkonfirmasi = 'ya';
                formulir.submit();
            });
        });
    }

    // Sediakan helper global untuk interaksi JS ringan (misal salin kode tiket)
    window.tampilkanToastJejakSumut = tampilkanToast;

    function mulai() {
        pasangGayaSwal();
        tampilkanFlashSwal();
        inisialisasiKonfirmasiSwal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai);
    } else {
        mulai();
    }
})();
