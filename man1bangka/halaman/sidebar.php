<?php
// ============================================================
// halaman/sidebar.php — Sidebar Navigasi Halaman Publik
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Komponen sidebar yang di-include oleh halaman-halaman publik
// berbasis PHP (misal: halaman/index.php).
// Menampilkan navigasi dan informasi kontak sekolah.
// ============================================================

$current = basename($_SERVER['PHP_SELF']);
function navLink($file, $icon, $label, $badge = null)
{
  global $current;
  $active = ($current === $file) ? ' active' : '';
  $bdg = $badge ? "<span class=\"nav-badge\">$badge</span>" : '';
  echo "<a href=\"$file\" class=\"nav-link$active\">
      <span class=\"nav-icon\"><i class=\"fas $icon\"></i></span>
      <span>$label</span>$bdg
    </a>";
}
$pendingCount = '';
$totalData = 0;
$totalPending = 0;
$siswaCount = 0;
$ekskulCount = 0;
try {
  global $pdo;
  if ($pdo) {
    $n = $pdo->query("SELECT COUNT(*) FROM testimoni WHERE status='nonaktif'")->fetchColumn();
    if ($n > 0) $pendingCount = $n;
    $totalPending = (int)$n;
    // Gather quick totals for sidebar
    $tables = ['agenda', 'pengumuman', 'dokumentasi', 'prestasi', 'ekstrakurikuler', 'pendaftaran_ekskul', 'karya_siswa', 'testimoni'];
    foreach ($tables as $t) {
      $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
      $totalData += (int)$c;
    }
    $siswaCount = $pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul")->fetchColumn();
    $ekskulCount = $pdo->query("SELECT COUNT(*) FROM ekstrakurikuler")->fetchColumn();
  }
} catch (Exception $e) {
}
?>
<aside class="admin-sidebar" id="adminSidebar">
  <a href="index.php" class="sidebar-brand">
    <div class="sidebar-logo">M1B</div>
    <div class="sidebar-title">
      <span class="s-name">MAN 1 Bangka</span>
      <span class="s-sub">Admin Panel</span>
    </div>
  </a>

  <nav class="sidebar-nav">
    <div class="nav-section">Utama</div>
    <?php navLink('index.php',      'fa-tachometer-alt', 'Dashboard'); ?>
    <?php navLink('agenda.php',     'fa-calendar-alt',   'Agenda Kegiatan'); ?>
    <?php navLink('media.php',      'fa-images',         'Upload Media'); ?>
    <?php navLink('pengumuman.php', 'fa-bell',           'Pengumuman'); ?>

    <div class="nav-section">Akademik & Siswa</div>
    <?php navLink('pendaftaran.php', 'fa-clipboard-list', 'Pendaftaran Siswa'); ?>
    <?php navLink('ekskul.php',     'fa-star',           'Ekstrakurikuler'); ?>
    <?php navLink('pembina.php',    'fa-chalkboard-teacher', 'Guru Pembina'); ?>
    <?php navLink('prestasi.php',   'fa-trophy',         'Prestasi Siswa'); ?>
    <?php navLink('karya.php',      'fa-palette',        'Karya Siswa'); ?>
    <?php navLink('testimoni.php',  'fa-comment-dots',   'Testimoni', $pendingCount); ?>

    <div class="nav-section">Sistem</div>
    <a href="../index.html" class="nav-link" target="_blank">
      <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
      <span>Lihat Website</span>
    </a>
    <a href="logout.php" class="nav-link nav-link-logout">
      <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
      <span>Keluar</span>
    </a>
  </nav>

  <!-- Quick Stats Widget -->
  <div class="sidebar-stats">
    <div class="sidebar-stats-title">📊 Ringkasan Data</div>
    <div class="sidebar-stats-row">
      <div class="sidebar-stat-item">
        <span class="sidebar-stat-num"><?= number_format($totalData) ?></span>
        <span class="sidebar-stat-label">Total</span>
      </div>
      <div class="sidebar-stat-item">
        <span class="sidebar-stat-num"><?= $ekskulCount ?></span>
        <span class="sidebar-stat-label">Ekskul</span>
      </div>
      <div class="sidebar-stat-item" style="position:relative;">
        <span class="sidebar-stat-num" style="color:<?= $totalPending > 0 ? '#ff6b6b' : 'var(--gold)' ?>;"><?= $totalPending ?></span>
        <span class="sidebar-stat-label">Pending</span>
        <?php if ($totalPending > 0): ?>
          <span style="position:absolute;top:2px;right:2px;width:6px;height:6px;background:#ff6b6b;border-radius:50%;animation:pulse-dot 1.5s infinite;"></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="sidebar-footer">
    <span class="sidebar-footer-dot"></span>
    ESTEFANIA &mdash; <?= date('Y') ?>
    <span class="sidebar-footer-dot"></span>
  </div>
</aside>