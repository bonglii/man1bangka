<?php
// ============================================================
// halaman/index.php — Halaman Beranda Publik (PHP Version)
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Versi PHP dari halaman beranda yang memuat data dinamis
// langsung dari database (tidak menggunakan fetch API).
// Digunakan sebagai alternatif index.html untuk SSR (Server-Side Rendering).
// Cocok jika JavaScript dinonaktifkan di browser.
// ============================================================
require '../admin/auth.php';
require '../php/config.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Dashboard — Admin MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../admin/assets/admin.css" />
</head>

<body>
  <?php include '../admin/sidebar.php'; ?>
  <main class="admin-main">

    <!-- TOPBAR -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon" style="display:none"><i class="fas fa-bars"></i></button>
        <div>
          <div class="topbar-title"><i class="fas fa-tachometer-alt"></i> Dashboard</div>
          <div class="topbar-breadcrumb">Admin / Beranda</div>
        </div>
      </div>
      <div class="topbar-right">
        <a href="../index.html" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Lihat Website</a>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div>
          <?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">

      <!-- GREETING CARD -->
      <div class="greeting-card">
        <div class="greeting-badge"><span class="live-dot"></span> Panel Admin Aktif</div>
        <h2>Selamat datang, <?= htmlspecialchars(ADMIN_USER) ?> 👋</h2>
        <p><?= date('l, d F Y') ?> &mdash; Berikut ringkasan data website MAN 1 Bangka.</p>

        <div class="greeting-metrics">
          <?php
          $totalAll = 0;
          $tables2 = ['agenda', 'pengumuman', 'dokumentasi', 'prestasi', 'ekstrakurikuler', 'pendaftaran_ekskul', 'karya_siswa', 'testimoni'];
          foreach ($tables2 as $t2) {
            $c2 = $pdo->query("SELECT COUNT(*) FROM `$t2`")->fetchColumn();
            $totalAll += (int)$c2;
          }
          $pendCount = (int)$pdo->query("SELECT COUNT(*) FROM testimoni WHERE status='nonaktif'")->fetchColumn();
          $agendaBulan = (int)$pdo->query("SELECT COUNT(*) FROM agenda WHERE MONTH(tanggal_mulai)=MONTH(NOW()) AND YEAR(tanggal_mulai)=YEAR(NOW())")->fetchColumn();
          $ekskulTotal = (int)$pdo->query("SELECT COUNT(*) FROM ekstrakurikuler")->fetchColumn();
          ?>
          <div class="greeting-metric">
            <span class="greeting-metric-num" id="gm-total">0</span>
            <span class="greeting-metric-label">Total Data</span>
          </div>
          <div class="greeting-metric">
            <span class="greeting-metric-num" id="gm-agenda">0</span>
            <span class="greeting-metric-label">Agenda Bulan Ini</span>
          </div>
          <div class="greeting-metric">
            <span class="greeting-metric-num" id="gm-ekskul">0</span>
            <span class="greeting-metric-label">Ekstrakurikuler</span>
          </div>
          <div class="greeting-metric" style="<?= $pendCount > 0 ? 'border-color:rgba(255,107,107,.3);background:rgba(255,107,107,.1);' : '' ?>">
            <span class="greeting-metric-num" id="gm-pending" style="color:<?= $pendCount > 0 ? '#ff8080' : 'var(--gold)' ?>;">0</span>
            <span class="greeting-metric-label">Pending Review</span>
          </div>
        </div>

        <div class="greeting-ring" aria-hidden="true">
          <span class="greeting-ring-icon">🏫</span>
        </div>
      </div>

      <!-- STAT CARDS -->
      <?php
      $stats = [
        ['tbl' => 'agenda',             'label' => 'Agenda',       'icon' => 'fa-calendar-alt',     'color' => 'green',  'link' => 'agenda.php'],
        ['tbl' => 'pengumuman',         'label' => 'Pengumuman',   'icon' => 'fa-bell',              'color' => 'gold',   'link' => 'pengumuman.php'],
        ['tbl' => 'dokumentasi',        'label' => 'Media',        'icon' => 'fa-images',            'color' => 'blue',   'link' => 'media.php'],
        ['tbl' => 'prestasi',           'label' => 'Prestasi',     'icon' => 'fa-trophy',            'color' => 'purple', 'link' => 'prestasi.php'],
        ['tbl' => 'testimoni',          'label' => 'Testimoni',    'icon' => 'fa-comment-dots',      'color' => 'teal',   'link' => 'testimoni.php'],
        ['tbl' => 'ekstrakurikuler',    'label' => 'Ekskul',       'icon' => 'fa-star',              'color' => 'orange', 'link' => 'ekskul.php'],
        ['tbl' => 'pendaftaran_ekskul', 'label' => 'Pendaftaran',  'icon' => 'fa-clipboard-list',    'color' => 'indigo', 'link' => 'pendaftaran.php'],
        ['tbl' => 'karya_siswa',        'label' => 'Karya Siswa',  'icon' => 'fa-palette',           'color' => 'red',    'link' => 'karya.php'],
      ];
      ?>
      <div class="stat-grid">
        <?php $si = 0;
        foreach ($stats as $s):
          $q = $pdo->query("SELECT COUNT(*) FROM `{$s['tbl']}`");
          $count = $q ? (int)$q->fetchColumn() : 0;
          $si++;
        ?>
          <a href="<?= $s['link'] ?>" style="text-decoration:none;" title="Kelola <?= $s['label'] ?>">
            <div class="stat-card stat-<?= $s['color'] ?> anim-delay-<?= min($si, 8) ?>">
              <div class="stat-card-shine"></div>
              <div class="stat-icon"><i class="fas <?= $s['icon'] ?>"></i></div>
              <div>
                <div class="stat-num" data-count="<?= $count ?>">0</div>
                <div class="stat-label"><?= $s['label'] ?></div>
              </div>
              <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- TWO COL: Quick Add + Recent Registrations -->
      <div class="two-col" style="margin-bottom:1.5rem;">

        <!-- Quick Agenda Form -->
        <div class="admin-card">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon"><i class="fas fa-plus"></i></div>
              <div>
                <div class="card-header-title">Tambah Agenda Cepat</div>
                <div class="card-header-sub">Tambah agenda tanpa pindah halaman</div>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form id="quick-agenda" class="quick-form" autocomplete="off">
              <div class="form-group">
                <label>Judul Agenda <span class="req">*</span></label>
                <input type="text" name="judul" placeholder="Contoh: Rapat OSIS Bulanan" required />
              </div>
              <div class="row-2">
                <div class="form-group">
                  <label>Tanggal <span class="req">*</span></label>
                  <input type="date" name="tanggal_mulai" required value="<?= date('Y-m-d') ?>" />
                </div>
                <div class="form-group">
                  <label>Kategori</label>
                  <select name="kategori">
                    <option value="umum">Umum</option>
                    <option value="lomba">Lomba</option>
                    <option value="ekskul">Ekskul</option>
                    <option value="organisasi">Organisasi</option>
                    <option value="seminar">Seminar</option>
                    <option value="keagamaan">Keagamaan</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="lokasi" placeholder="Contoh: Aula MAN 1 Bangka" />
              </div>
              <div style="display:flex;gap:.5rem;align-items:center;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Agenda</button>
                <a href="agenda.php" class="btn btn-outline btn-sm">Kelola Semua</a>
              </div>
            </form>
            <div id="quick-msg"></div>
          </div>
        </div>

        <!-- Recent Pendaftaran -->
        <div class="admin-card">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fas fa-clipboard-list"></i></div>
              <div>
                <div class="card-header-title">Pendaftaran Terbaru</div>
                <div class="card-header-sub">Siswa yang baru mendaftar kegiatan</div>
              </div>
            </div>
            <a href="pendaftaran.php" class="btn btn-outline btn-sm">Lihat Semua</a>
          </div>
          <div class="card-body" style="padding:0;">
            <?php
            $pend = $pdo->query("SELECT p.nama_siswa, p.kelas, p.status, e.nama as ekskul, p.created_at
            FROM pendaftaran_ekskul p
            LEFT JOIN ekstrakurikuler e ON p.ekstrakurikuler_id = e.id
            ORDER BY p.created_at DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if ($pend): ?>
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Siswa</th>
                    <th>Ekskul</th>
                    <th>Status</th>
                    <th>Tgl</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pend as $r): ?>
                    <tr>
                      <td>
                        <div style="font-weight:700;"><?= htmlspecialchars($r['nama_siswa']) ?></div>
                        <div style="font-size:.72rem;color:var(--gray-400);"><?= htmlspecialchars($r['kelas'] ?? '') ?></div>
                      </td>
                      <td class="td-truncate" style="max-width:130px;"><?= htmlspecialchars($r['ekskul'] ?? '-') ?></td>
                      <td><span class="badge status-<?= htmlspecialchars($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                      <td style="font-size:.75rem;color:var(--gray-400);"><?= date('d/m/y', strtotime($r['created_at'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="empty-state" style="padding:2rem;">
                <div class="empty-state-icon">📋</div>
                <p>Belum ada pendaftaran masuk.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Agenda + Testimoni -->
      <div class="two-col" style="margin-bottom:1.5rem;">
        <div class="admin-card">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon"><i class="fas fa-calendar-check"></i></div>
              <div>
                <div class="card-header-title">Agenda Bulan Ini</div>
                <div class="card-header-sub"><?= date('F Y') ?></div>
              </div>
            </div>
            <a href="agenda.php" class="btn btn-outline btn-sm">Kelola</a>
          </div>
          <div class="card-body" style="padding:0;">
            <?php
            $ag = $pdo->query("SELECT judul,tanggal_mulai,kategori,warna FROM agenda
            WHERE MONTH(tanggal_mulai)=MONTH(NOW()) AND YEAR(tanggal_mulai)=YEAR(NOW())
            ORDER BY tanggal_mulai ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
            if (!$ag) {
              $ag = $pdo->query("SELECT judul,tanggal_mulai,kategori,warna FROM agenda
              ORDER BY ABS(DATEDIFF(tanggal_mulai, CURDATE())) ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
            }
            ?>
            <?php if ($ag): ?>
              <?php foreach ($ag as $a): ?>
                <div class="recent-item" style="padding:.65rem 1.4rem;">
                  <div class="recent-dot" style="background:<?= htmlspecialchars($a['warna']) ?>;box-shadow:0 0 0 2px <?= htmlspecialchars($a['warna']) ?>33;"></div>
                  <div class="recent-title"><?= htmlspecialchars($a['judul']) ?></div>
                  <span class="badge badge-gray"><?= date('d M', strtotime($a['tanggal_mulai'])) ?></span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state" style="padding:1.5rem;">
                <p>Tidak ada agenda bulan ini.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="admin-card">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--teal-bg);color:var(--teal);"><i class="fas fa-comment-dots"></i></div>
              <div>
                <div class="card-header-title">Testimoni Masuk</div>
                <div class="card-header-sub">Menunggu persetujuan</div>
              </div>
            </div>
            <a href="testimoni.php" class="btn btn-outline btn-sm">Moderasi</a>
          </div>
          <div class="card-body" style="padding:0;">
            <?php
            $tes = $pdo->query("SELECT nama_siswa, kelas, isi, rating FROM testimoni
            WHERE status='nonaktif' ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if ($tes): ?>
              <?php foreach ($tes as $t): ?>
                <div class="recent-item" style="padding:.7rem 1.4rem;align-items:flex-start;gap:.75rem;">
                  <div style="width:34px;height:34px;border-radius:50%;background:var(--teal-bg);color:var(--teal);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0;"><?= strtoupper(substr($t['nama_siswa'], 0, 1)) ?></div>
                  <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:.82rem;"><?= htmlspecialchars($t['nama_siswa']) ?> <span style="color:var(--gray-400);font-weight:400;">&mdash; <?= htmlspecialchars($t['kelas'] ?? '') ?></span></div>
                    <div style="font-size:.77rem;color:var(--gray-500);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;"><?= htmlspecialchars(substr($t['isi'], 0, 70)) ?>...</div>
                  </div>
                  <span style="color:var(--gold);font-size:.75rem;flex-shrink:0;"><?= str_repeat('★', $t['rating']) ?></span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state" style="padding:2rem;">
                <div class="empty-state-icon">✅</div>
                <p>Tidak ada testimoni yang menunggu.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Ekskul progress bars -->
      <?php
      $ekskulList = $pdo->query("SELECT e.nama, COUNT(p.id) as jumlah_pendaftar, e.kuota as max_peserta
      FROM ekstrakurikuler e
      LEFT JOIN pendaftaran_ekskul p ON p.ekstrakurikuler_id = e.id AND p.status='diterima'
      GROUP BY e.id ORDER BY jumlah_pendaftar DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
      if ($ekskulList):
      ?>
        <div class="admin-card" style="margin-bottom:1.5rem;">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--orange-bg);color:var(--orange);"><i class="fas fa-users"></i></div>
              <div>
                <div class="card-header-title">Kapasitas Ekstrakurikuler</div>
                <div class="card-header-sub">Jumlah peserta diterima per ekskul</div>
              </div>
            </div>
            <a href="ekskul.php" class="btn btn-outline btn-sm">Kelola</a>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.2rem;">
              <?php foreach ($ekskulList as $ek):
                $max = $ek['max_peserta'] > 0 ? $ek['max_peserta'] : 30;
                $pct = min(100, round(($ek['jumlah_pendaftar'] / $max) * 100));
                $color = $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f97316' : 'var(--primary-mid)');
              ?>
                <div>
                  <div class="mini-progress-label">
                    <span style="font-weight:700;color:var(--gray-800);"><?= htmlspecialchars($ek['nama']) ?></span>
                    <span style="color:<?= $color ?>;"><?= $ek['jumlah_pendaftar'] ?>/<?= $max ?></span>
                  </div>
                  <div class="mini-progress-bar">
                    <div class="mini-progress-fill" data-pct="<?= $pct ?>"
                      style="background:linear-gradient(90deg,<?= $color ?>,<?= $color ?>99);"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div><!-- /page-content -->
  </main>

  <script src="../admin/assets/admin.js"></script>
  <script>
    // ── Animated counters ────────────────────────────────────────
    function animCount(el, target, duration = 900) {
      const start = performance.now();

      function tick(now) {
        const p = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(ease * target).toLocaleString('id');
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target.toLocaleString('id');
      }
      requestAnimationFrame(tick);
    }

    // Stat cards counter
    document.querySelectorAll('.stat-num[data-count]').forEach(el => {
      const obs = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
          animCount(el, parseInt(el.dataset.count));
          obs.disconnect();
        }
      }, {
        threshold: .2
      });
      obs.observe(el);
    });

    // Greeting metrics
    const gmTargets = {
      'gm-total': <?= $totalAll ?>,
      'gm-agenda': <?= $agendaBulan ?>,
      'gm-ekskul': <?= $ekskulTotal ?>,
      'gm-pending': <?= $pendCount ?>
    };
    window.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        Object.entries(gmTargets).forEach(([id, val]) => {
          const el = document.getElementById(id);
          if (el) animCount(el, val, 1100);
        });
      }, 200);

      // Progress bars
      document.querySelectorAll('.mini-progress-fill').forEach(bar => {
        const pct = parseInt(bar.dataset.pct) || 0;
        setTimeout(() => bar.style.width = pct + '%', 400);
      });
    });

    // Quick agenda submit
    document.getElementById('quick-agenda').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = this.querySelector('[type=submit]');
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
      const fd = new FormData(this);
      const res = await fetch('php/api.php?module=agenda&action=tambah', {
        method: 'POST',
        body: fd
      });
      const d = await res.json();
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-plus"></i> Tambah Agenda';
      if (d.status === 'success') {
        showMsg('quick-msg', 'Agenda berhasil ditambahkan!', 'ok');
        this.reset();
        this.querySelector('[name=tanggal_mulai]').value = '<?= date('Y-m-d') ?>';
        setTimeout(() => location.reload(), 1500);
      } else {
        showMsg('quick-msg', d.message || 'Gagal menyimpan', 'err');
      }
    });
  </script>
</body>

</html>