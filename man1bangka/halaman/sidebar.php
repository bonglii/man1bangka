<?php
// ============================================================
// halaman/sidebar.php — Navigasi Halaman Publik
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Komponen navigasi untuk halaman publik berbasis PHP.
// Di-include oleh halaman/index.php jika diperlukan.
// BUKAN admin sidebar — untuk admin gunakan admin/sidebar.php
// ============================================================

$current = basename($_SERVER['PHP_SELF']);
function pubNavLink($href, $icon, $label)
{
  global $current;
  $active = ($current === basename($href)) ? ' class="active"' : '';
  echo "<a href=\"$href\"$active><i class=\"fas $icon\"></i> $label</a>";
}
?>
<nav class="navbar" id="navbar">
  <div class="navbar__container">
    <a href="../index.html" class="navbar__brand">
      <div class="navbar__logo">M1B</div>
      <div class="navbar__name">
        <span class="navbar__school">MAN 1 Bangka</span>
        <span class="navbar__tagline">Portal Kegiatan Siswa</span>
      </div>
    </a>
    <ul class="navbar__links">
      <?php pubNavLink('../index.html',            'fa-home',         'Beranda'); ?>
      <?php pubNavLink('pengumuman.html',          'fa-bell',         'Pengumuman'); ?>
      <?php pubNavLink('agenda.html',              'fa-calendar',     'Agenda'); ?>
      <?php pubNavLink('ekstrakurikuler.html',     'fa-star',         'Ekskul'); ?>
      <?php pubNavLink('prestasi.html',            'fa-trophy',       'Prestasi'); ?>
      <?php pubNavLink('organisasi.html',          'fa-users',        'Organisasi'); ?>
      <?php pubNavLink('dokumentasi.html',         'fa-images',       'Galeri'); ?>
      <?php pubNavLink('kontak.html',              'fa-address-book', 'Kontak'); ?>
    </ul>
  </div>
</nav>