<?php
/**
 * Komponen: Tombol Simpan/Hapus Favorit
 *
 * Variabel yang diterima:
 *   $id_destinasi    (int)  – ID destinasi untuk difavoritkan.
 *   $sudah_difavorit (bool) – Opsional, menandakan destinasi sudah tersimpan di favorit.
 *
 * Jika pengguna belum login -> tampilkan tombol link ke /masuk.
 * Jika sudah login -> tampilkan form POST standar ke /favorit/ubah dengan token CSRF.
 * Menggunakan form POST standar agar 100% stabil saat diklik berulang kali tanpa isu token CSRF kadaluarsa.
 *
 * Anggota 3 – Autentikasi, Sesi Pengguna & Fitur Favorit
 */

$sudahLogin     = (bool) session()->get('sudah_masuk');
$idDestinasi    = (int) ($id_destinasi ?? 0);
$sudahDifavorit = (bool) ($sudah_difavorit ?? false);

if ($sudahDifavorit) {
    $kelasTombol   = 'inline-flex items-center justify-center w-10 h-10 rounded-xl bg-error-container text-error border border-error/40 hover:bg-error-container/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error/40 active:scale-95 transition-all duration-200';
    $gayaIkon      = "font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 20";
    $labelAksi     = 'Hapus dari Favorit';
    $statusDitekan = 'true';
} else {
    $kelasTombol   = 'inline-flex items-center justify-center w-10 h-10 rounded-xl bg-surface-container border border-surface-variant/60 text-on-surface-variant hover:bg-error-container hover:text-error hover:border-error/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error/40 active:scale-95 transition-all duration-200';
    $gayaIkon      = "font-variation-settings:'FILL' 0,'wght' 300,'GRAD' 0,'opsz' 20";
    $labelAksi     = 'Simpan ke Favorit';
    $statusDitekan = 'false';
}
?>

<?php if ($sudahLogin && $idDestinasi > 0) : ?>
    <!-- Tombol Favorit: Form POST Standar ke /favorit/ubah -->
    <form
        action="<?= base_url('favorit/ubah') ?>"
        method="post"
        class="inline-block m-0 p-0"
    >
        <?= csrf_field() ?>
        <input type="hidden" name="id_destinasi" value="<?= esc((string) $idDestinasi) ?>" />

        <button
            type="submit"
            title="<?= esc($labelAksi) ?>"
            aria-label="<?= esc($labelAksi) ?>"
            aria-pressed="<?= $statusDitekan ?>"
            class="<?= $kelasTombol ?>"
        >
            <span class="material-symbols-outlined text-[20px]" style="<?= $gayaIkon ?>">
                favorite
            </span>
        </button>
    </form>
<?php else : ?>
    <!-- Belum login: arahkan ke halaman masuk -->
    <a
        href="<?= base_url('masuk') ?>"
        title="Masuk untuk menyimpan ke Favorit"
        aria-label="Masuk untuk menyimpan ke Favorit"
        class="inline-flex items-center justify-center w-10 h-10 rounded-xl
               bg-surface-container border border-surface-variant/60
               text-on-surface-variant
               hover:bg-surface-container-high hover:text-on-surface
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-outline/40
               active:scale-95 transition-all duration-200"
    >
        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings:'FILL' 0,'wght' 300,'GRAD' 0,'opsz' 20">
            favorite
        </span>
    </a>
<?php endif; ?>
