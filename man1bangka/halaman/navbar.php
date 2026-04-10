<?php

/**
 * navbar.php — Navbar dinamis untuk semua halaman di folder halaman/
 *
 * Cara pakai: <?php include 'navbar.php'; ?>
 *
 * Navbar otomatis menandai link aktif berdasarkan nama file saat ini.
 */

// Deteksi nama file saat ini (tanpa ekstensi)
$currentFile = basename($_SERVER['PHP_SELF'], '.php');

// Daftar menu — [href, icon-class, label, key untuk pencocokan aktif]
$navItems = [
  ['../index.html',        'fas fa-home',          'Beranda',       'index_root'],
  ['pengumuman.php',       'far fa-bell',          'Pengumuman',    'pengumuman'],
  ['agenda.php',           'far fa-calendar-alt',  'Agenda',        'agenda'],
  ['ekstrakurikuler.php',  'fas fa-star',          'Ekskul',        'ekstrakurikuler'],
  ['prestasi.php',         'fas fa-trophy',        'Prestasi',      'prestasi'],
  ['organisasi.php',       'fas fa-users',         'OSIM',          'organisasi'],
  ['dokumentasi.php',      'far fa-images',        'Galeri',        'dokumentasi'],
  ['karya-siswa.php',      'fas fa-palette',       'Karya',         'karya-siswa'],
  ['kontak.php',           'far fa-address-card',  'Kontak',        'kontak'],
];

// Daftar menu mobile (lebih lengkap)
$mobileItems = [
  ['../index.html',        'fas fa-home',          'Beranda',          'index_root'],
  ['pengumuman.php',       'far fa-bell',          'Pengumuman',       'pengumuman'],
  ['agenda.php',           'far fa-calendar-alt',  'Agenda',           'agenda'],
  ['ekstrakurikuler.php',  'fas fa-star',          'Ekstrakurikuler',  'ekstrakurikuler'],
  ['prestasi.php',         'fas fa-trophy',        'Prestasi',         'prestasi'],
  ['organisasi.php',       'fas fa-users',         'Organisasi',       'organisasi'],
  ['dokumentasi.php',      'far fa-images',        'Dokumentasi',      'dokumentasi'],
  ['arsip.php',            'fas fa-archive',       'Arsip',            'arsip'],
  ['karya-siswa.php',      'fas fa-palette',       'Karya Siswa',      'karya-siswa'],
  ['testimoni.php',        'far fa-comment-dots',  'Testimoni',        'testimoni'],
  ['kontak.php',           'far fa-address-card',  'Kontak',           'kontak'],
];
?>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="navbar">
  <div class="navbar__brand">
    <div class="navbar__logo">M1B</div>
    <div class="navbar__title">
      <span>MAN 1 Bangka</span>
      <span>KEGIATAN SISWA</span>
    </div>
  </div>
  <ul class="navbar__nav">
    <?php foreach ($navItems as [$href, $icon, $label, $key]): ?>
      <?php $isActive = ($currentFile === $key) ? ' class="active"' : ''; ?>
      <li><a href="<?= $href ?>" <?= $isActive ?>><i class="<?= $icon ?>"></i> <?= $label ?></a></li>
    <?php endforeach; ?>
  </ul>
</nav>

<!-- Mobile Nav -->
<div class="mobile-nav">
  <?php foreach ($mobileItems as [$href, $icon, $label, $key]): ?>
    <?php $isActive = ($currentFile === $key) ? ' class="active"' : ''; ?>
    <a href="<?= $href ?>" <?= $isActive ?>><i class="<?= $icon ?>"></i> <?= $label ?></a>
  <?php endforeach; ?>
</div>