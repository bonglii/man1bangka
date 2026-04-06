<?php
// ============================================================
// halaman/index.php — Beranda Publik (Server-Side Rendering)
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Versi PHP (SSR) dari halaman beranda publik.
// Menampilkan data langsung dari database tanpa JavaScript fetch.
// TIDAK memerlukan login admin — halaman ini PUBLIK.
// ============================================================
require '../php/config.php'; // hanya config DB — bukan auth.php

$pengumumanList = $pdo->query(
    "SELECT * FROM pengumuman WHERE is_highlight=1
     ORDER BY tanggal_publish DESC LIMIT 3"
)->fetchAll(PDO::FETCH_ASSOC);

$agendaList = $pdo->query(
    "SELECT * FROM agenda
     WHERE tanggal_mulai >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
       AND is_selesai = 0
     ORDER BY tanggal_mulai ASC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);
if (empty($agendaList)) {
    $agendaList = $pdo->query(
        "SELECT * FROM agenda ORDER BY tanggal_mulai DESC LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);
}

$ekskulList = $pdo->query(
    "SELECT e.*, k.nama AS nama_pembina
     FROM ekstrakurikuler e
     LEFT JOIN kontak_pembina k ON e.pembina_id = k.id
     ORDER BY e.nama ASC LIMIT 6"
)->fetchAll(PDO::FETCH_ASSOC);

$prestasiList = $pdo->query(
    "SELECT * FROM prestasi ORDER BY tahun DESC, tingkat DESC LIMIT 4"
)->fetchAll(PDO::FETCH_ASSOC);

$testimoniList = $pdo->query(
    "SELECT * FROM testimoni WHERE status='aktif'
     ORDER BY created_at DESC LIMIT 4"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Beranda — MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

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
        <li><a href="../index.html" class="active"><i class="fas fa-home"></i> Beranda</a></li>
        <li><a href="pengumuman.html"><i class="fas fa-bell"></i> Pengumuman</a></li>
        <li><a href="agenda.html"><i class="fas fa-calendar"></i> Agenda</a></li>
        <li><a href="ekstrakurikuler.html"><i class="fas fa-star"></i> Ekskul</a></li>
        <li><a href="prestasi.html"><i class="fas fa-trophy"></i> Prestasi</a></li>
        <li><a href="organisasi.html"><i class="fas fa-users"></i> Organisasi</a></li>
      </ul>
    </div>
  </nav>

  <section class="hero">
    <div class="hero__content">
      <div class="hero__badge"><i class="fas fa-star"></i> Portal Resmi Kegiatan Siswa</div>
      <h1>Selamat Datang di<br><span>MAN 1 Bangka</span></h1>
      <p>Temukan informasi lengkap tentang kegiatan, prestasi, dan organisasi siswa.</p>
      <div class="hero__actions">
        <a href="ekstrakurikuler.html" class="btn btn-primary"><i class="fas fa-star"></i> Jelajahi Ekskul</a>
        <a href="pendaftaran.html" class="btn btn-outline"><i class="fas fa-pen"></i> Daftar Sekarang</a>
      </div>
    </div>
  </section>

  <main class="main-content">

    <?php if ($pengumumanList): ?>
    <section class="section">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-bell"></i> Pengumuman Terkini</h2>
        <a href="pengumuman.html" class="section-link">Lihat Semua <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="cards-grid">
        <?php foreach ($pengumumanList as $p): ?>
        <div class="card reveal">
          <div class="card__body">
            <span class="badge"><?= htmlspecialchars($p['kategori']) ?></span>
            <h3 class="card__title"><?= htmlspecialchars($p['judul']) ?></h3>
            <p class="card__text"><?= htmlspecialchars(mb_substr($p['isi'], 0, 150)) ?>...</p>
            <div class="card__meta"><i class="fas fa-calendar-alt"></i> <?= formatTanggal($p['tanggal_publish']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($agendaList): ?>
    <section class="section section-alt">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Agenda Mendatang</h2>
        <a href="agenda.html" class="section-link">Lihat Semua <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="agenda-list">
        <?php foreach ($agendaList as $a): ?>
        <div class="agenda-item reveal">
          <div class="agenda-datebox" style="background:<?= htmlspecialchars($a['warna'] ?? '#1a6b3c') ?>">
            <span class="ad-day"><?= date('d', strtotime($a['tanggal_mulai'])) ?></span>
            <span class="ad-mon"><?= formatTanggal($a['tanggal_mulai']) ?></span>
          </div>
          <div class="agenda-body">
            <h4><?= htmlspecialchars($a['judul']) ?></h4>
            <?php if ($a['lokasi']): ?><p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($a['lokasi']) ?></p><?php endif; ?>
            <span class="badge"><?= htmlspecialchars($a['kategori']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($ekskulList): ?>
    <section class="section">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-star"></i> Ekstrakurikuler</h2>
        <a href="ekstrakurikuler.html" class="section-link">Lihat Semua <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="cards-grid">
        <?php foreach ($ekskulList as $e): ?>
        <div class="card reveal">
          <div class="card__body">
            <span class="badge"><?= htmlspecialchars($e['kategori']) ?></span>
            <h3 class="card__title"><?= htmlspecialchars($e['nama']) ?></h3>
            <?php if ($e['deskripsi']): ?><p class="card__text"><?= htmlspecialchars(mb_substr($e['deskripsi'], 0, 100)) ?>...</p><?php endif; ?>
            <?php if ($e['jadwal']): ?><div class="card__meta"><i class="fas fa-clock"></i> <?= htmlspecialchars($e['jadwal']) ?></div><?php endif; ?>
            <?php if ($e['nama_pembina']): ?><div class="card__meta"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($e['nama_pembina']) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($prestasiList): ?>
    <section class="section section-alt">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-trophy"></i> Prestasi Siswa</h2>
        <a href="prestasi.html" class="section-link">Lihat Semua <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="cards-grid">
        <?php foreach ($prestasiList as $p): ?>
        <div class="card reveal">
          <div class="card__body">
            <span class="badge"><?= htmlspecialchars($p['tingkat']) ?></span>
            <h3 class="card__title"><?= htmlspecialchars($p['judul']) ?></h3>
            <div class="card__meta"><i class="fas fa-user"></i> <?= htmlspecialchars($p['siswa']) ?><?= $p['kelas'] ? ' — ' . htmlspecialchars($p['kelas']) : '' ?></div>
            <?php if ($p['posisi']): ?><div class="card__meta"><i class="fas fa-medal"></i> <?= htmlspecialchars($p['posisi']) ?></div><?php endif; ?>
            <div class="card__meta"><i class="fas fa-calendar"></i> <?= htmlspecialchars((string)$p['tahun']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($testimoniList): ?>
    <section class="section">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-comment-dots"></i> Kata Mereka</h2>
        <a href="testimoni.html" class="section-link">Lihat Semua <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="cards-grid">
        <?php foreach ($testimoniList as $t): ?>
        <div class="card reveal">
          <div class="card__body">
            <p class="card__text">"<?= htmlspecialchars($t['isi']) ?>"</p>
            <div class="card__meta" style="margin-top:.75rem;">
              <strong><?= htmlspecialchars($t['nama_siswa']) ?></strong>
              <?php if ($t['kelas']): ?> — <?= htmlspecialchars($t['kelas']) ?><?php endif; ?>
            </div>
            <div>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fas fa-star" style="color:<?= $i <= $t['rating'] ? '#f59e0b' : '#d1d5db' ?>;font-size:.8rem;"></i>
              <?php endfor; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </main>

  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-logo">M1B</div>
        <p>MAN 1 Bangka — Portal Kegiatan Siswa</p>
      </div>
      <div class="footer-links">
        <a href="../index.html">Beranda</a>
        <a href="pengumuman.html">Pengumuman</a>
        <a href="agenda.html">Agenda</a>
        <a href="ekstrakurikuler.html">Ekskul</a>
        <a href="prestasi.html">Prestasi</a>
        <a href="kontak.html">Kontak</a>
      </div>
    </div>
    <div class="footer-bottom">
      <div>© <?= date('Y') ?> MAN 1 Bangka. All rights reserved.</div>
      <div>Dikembangkan oleh <span>Estefania</span></div>
    </div>
  </footer>

  <script src="../assets/js/main.js"></script>
  <script>document.addEventListener('DOMContentLoaded', () => { initReveal(); });</script>
</body>
</html>
