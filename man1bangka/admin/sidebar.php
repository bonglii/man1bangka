<?php
// ============================================================
// sidebar.php — Komponen Sidebar Panel Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Di-include oleh seluruh halaman admin untuk menampilkan
// navigasi sidebar dan widget ringkasan data.
//
// Fitur:
//   - Navigasi dengan link aktif otomatis (berdasarkan nama file)
//   - Badge merah untuk jumlah testimoni pending (belum dimoderasi)
//   - Widget statistik cepat: total data, jumlah ekskul, pending
//   - Footer dengan nama developer dan tahun
// ============================================================

// Tentukan nama file halaman saat ini untuk menandai link aktif
$current = basename($_SERVER['PHP_SELF']);

// ============================================================
// FUNGSI: navLink($file, $icon, $label, $badge)
// Menghasilkan tag <a> navigasi sidebar.
// Parameter:
//   $file  — nama file PHP tujuan (misal: 'agenda.php')
//   $icon  — class icon Font Awesome (misal: 'fa-calendar-alt')
//   $label — teks label menu
//   $badge — (opsional) angka badge merah di kanan label
// ============================================================
function navLink($file, $icon, $label, $badge = null)
{
  global $current;

  // Tambahkan class 'active' jika halaman ini sedang dibuka
  $active = ($current === $file) ? ' active' : '';

  // Render badge angka jika ada (misal: jumlah testimoni pending)
  $bdg = $badge ? "<span class=\"nav-badge\">$badge</span>" : '';

  echo "<a href=\"$file\" class=\"nav-link$active\">
        <span class=\"nav-icon\"><i class=\"fas $icon\"></i></span>
        <span>$label</span>$bdg
      </a>";
}

// ============================================================
// QUERY STATISTIK SIDEBAR
// Mengambil data ringkasan untuk ditampilkan di widget bawah sidebar.
// Semua query dibungkus try-catch agar sidebar tidak crash
// jika ada tabel yang belum ada (misal saat pertama setup).
// ============================================================
$pendingCount = ''; // Badge untuk testimoni yang belum dimoderasi
$totalData    = 0;  // Total semua record dari 8 tabel utama
$totalPending = 0;  // Angka pending (untuk warna merah jika > 0)
$siswaCount   = 0;  // Total pendaftaran ekskul (tidak dipakai di widget saat ini)
$ekskulCount  = 0;  // Total jumlah ekskul aktif

try {
  global $pdo;
  if ($pdo) {
    // Hitung testimoni yang belum dimoderasi (status='nonaktif')
    $n = $pdo->query("SELECT COUNT(*) FROM testimoni WHERE status='nonaktif'")->fetchColumn();
    if ($n > 0) $pendingCount = $n; // Hanya tampilkan badge jika ada yang pending
    $totalPending = (int)$n;

    // Hitung total record dari seluruh tabel data utama
    $tables = [
      'agenda',
      'pengumuman',
      'dokumentasi',
      'prestasi',
      'ekstrakurikuler',
      'pendaftaran_ekskul',
      'karya_siswa',
      'testimoni'
    ];
    foreach ($tables as $t) {
      $c          = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
      $totalData += (int)$c;
    }

    // Statistik tambahan untuk widget
    $siswaCount  = $pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul")->fetchColumn();
    $ekskulCount = $pdo->query("SELECT COUNT(*) FROM ekstrakurikuler")->fetchColumn();
  }
} catch (Exception $e) {
  // Abaikan error — sidebar tetap tampil meskipun query gagal
}
?>

<!-- ============================================================
     HTML: Sidebar Admin
     Menggunakan class dari admin.css.
     Sidebar dapat di-toggle (collapse) via JavaScript di admin.js.
     ============================================================ -->
<aside class="admin-sidebar" id="adminSidebar">

  <!-- Logo & nama aplikasi — klik untuk ke dashboard -->
  <a href="index.php" class="sidebar-brand">
    <div class="sidebar-logo" style="background:transparent;padding:0;overflow:hidden;">
      <img src="assets/img/logo.png" alt="MAN 1 Bangka" style="width:44px;height:44px;object-fit:contain;" />
    </div>
    <div class="sidebar-title">
      <span class="s-name">MAN 1 Bangka</span>
      <span class="s-sub">Admin Panel</span>
    </div>
  </a>

  <!-- Navigasi utama -->
  <nav class="sidebar-nav">
    <div class="nav-section">Utama</div>
    <?php navLink('index.php',      'fa-tachometer-alt',      'Dashboard'); ?>
    <?php navLink('agenda.php',     'fa-calendar-alt',        'Agenda Kegiatan'); ?>
    <?php navLink('media.php',      'fa-images',              'Upload Media'); ?>
    <?php navLink('pengumuman.php', 'fa-bell',                'Pengumuman'); ?>

    <div class="nav-section">Akademik &amp; Siswa</div>
    <?php navLink('pendaftaran.php', 'fa-clipboard-list',      'Pendaftaran Siswa'); ?>
    <?php navLink('ekskul.php',      'fa-star',                'Ekstrakurikuler'); ?>
    <?php navLink('pembina.php',     'fa-chalkboard-teacher',  'Guru Pembina'); ?>
    <?php navLink('prestasi.php',    'fa-trophy',              'Prestasi Siswa'); ?>
    <?php navLink('karya.php',       'fa-palette',             'Karya Siswa'); ?>
    <!-- Badge merah muncul jika ada testimoni yang belum dimoderasi -->
    <?php navLink('testimoni.php',   'fa-comment-dots',        'Testimoni', $pendingCount); ?>

    <div class="nav-section">Sistem</div>
    <!-- Buka website publik di tab baru -->
    <a href="../index.html" class="nav-link" target="_blank">
      <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
      <span>Lihat Website</span>
    </a>
    <a href="logout.php" class="nav-link nav-link-logout">
      <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
      <span>Keluar</span>
    </a>
  </nav>

  <!-- Widget ringkasan statistik cepat di bawah sidebar -->
  <div class="sidebar-stats">
    <div class="sidebar-stats-title">📊 Ringkasan Data</div>
    <div class="sidebar-stats-row">
      <!-- Total seluruh record -->
      <div class="sidebar-stat-item">
        <span class="sidebar-stat-num"><?= number_format($totalData) ?></span>
        <span class="sidebar-stat-label">Total</span>
      </div>
      <!-- Jumlah ekskul -->
      <div class="sidebar-stat-item">
        <span class="sidebar-stat-num"><?= $ekskulCount ?></span>
        <span class="sidebar-stat-label">Ekskul</span>
      </div>
      <!-- Jumlah pending — merah & animasi dot jika ada -->
      <div class="sidebar-stat-item" style="position:relative;">
        <span class="sidebar-stat-num" style="color:<?= $totalPending > 0 ? '#ff6b6b' : 'var(--gold)' ?>;">
          <?= $totalPending ?>
        </span>
        <span class="sidebar-stat-label">Pending</span>
        <?php if ($totalPending > 0): ?>
          <!-- Titik animasi sebagai indikator visual ada item pending -->
          <span style="position:absolute;top:2px;right:2px;width:6px;height:6px;background:#ff6b6b;border-radius:50%;animation:pulse-dot 1.5s infinite;"></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Footer sidebar: nama developer dan tahun otomatis -->
  <div class="sidebar-footer">
    <span class="sidebar-footer-dot"></span>
    ESTEFANIA &mdash; <?= date('Y') ?>
    <span class="sidebar-footer-dot"></span>
  </div>

</aside>