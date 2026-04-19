<?php
// ============================================================
// pesan.php — Pesan Masuk dari Form Kontak
// MAN 1 Bangka
// ============================================================
require 'auth.php';
require '../php/config.php';

$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    if ($action === 'baca' && $id) {
        $pdo->prepare("UPDATE pesan_kontak SET is_read=1 WHERE id=?")->execute([$id]);
        header('Location: pesan.php?msg=baca'); exit;
    }
    if ($action === 'baca_semua') {
        $pdo->exec("UPDATE pesan_kontak SET is_read=1 WHERE is_read=0");
        header('Location: pesan.php?msg=baca_semua'); exit;
    }
    if ($action === 'hapus' && $id) {
        $pdo->prepare("DELETE FROM pesan_kontak WHERE id=?")->execute([$id]);
        header('Location: pesan.php?msg=hapus'); exit;
    }
}

if (isset($_GET['msg'])) {
    $flashMap = [
        'baca'       => 'Pesan ditandai sudah dibaca.',
        'baca_semua' => 'Semua pesan ditandai sudah dibaca.',
        'hapus'      => 'Pesan berhasil dihapus.',
    ];
    $msg = $flashMap[$_GET['msg']] ?? '';
}

$filter = in_array($_GET['filter'] ?? '', ['belum', 'sudah']) ? $_GET['filter'] : '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 25;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM pesan_kontak";
if ($filter === 'belum') $sql .= " WHERE is_read=0";
if ($filter === 'sudah') $sql .= " WHERE is_read=1";
$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$rows = $pdo->query($sql)->fetchAll();

$totalPesan = (int)$pdo->query("SELECT COUNT(*) FROM pesan_kontak")->fetchColumn();
$belumBaca  = (int)$pdo->query("SELECT COUNT(*) FROM pesan_kontak WHERE is_read=0")->fetchColumn();
$sudahBaca  = $totalPesan - $belumBaca;
$totalPages = max(1, (int)ceil(($filter === 'belum' ? $belumBaca : ($filter === 'sudah' ? $sudahBaca : $totalPesan)) / $limit));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Pesan Masuk — Admin MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/admin.css"/>
</head>
<body>
  <?php include 'sidebar.php'; ?>
  <main class="admin-main">

    <header class="admin-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon"><i class="fas fa-bars"></i></button>
        <div>
          <div class="topbar-title">
            <i class="fas fa-envelope"></i> Pesan Masuk
            <?php if ($belumBaca > 0): ?>
              <span class="badge badge-gold" style="margin-left:.5rem;font-size:.65rem;"><?= $belumBaca ?> baru</span>
            <?php endif; ?>
          </div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Pesan Masuk</div>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div>
          <?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">

      <?php if ($msg): ?>
        <div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <!-- ============================================================
           PANDUAN / WARNING — Menangani Pesan Masuk (Privacy)
           ============================================================ -->
      <details open style="margin-bottom:1.25rem;border:1.5px solid #fde68a;background:#fffbeb;border-radius:var(--radius-sm,10px);overflow:hidden;">
        <summary style="cursor:pointer;padding:.85rem 1rem;display:flex;align-items:center;gap:.6rem;font-weight:700;color:#92400e;list-style:none;user-select:none;">
          <i class="fas fa-exclamation-triangle" style="color:#d97706;"></i>
          <span>Panduan Menangani Pesan Masuk</span>
          <span style="margin-left:auto;font-size:.72rem;font-weight:500;color:#b45309;opacity:.85;">Klik untuk buka/tutup</span>
        </summary>
        <div style="padding:0 1.25rem 1rem 1.25rem;color:#78350f;font-size:.85rem;line-height:1.65;">
          <div style="padding-top:.25rem;border-top:1px dashed #fcd34d;margin-top:.1rem;"></div>

          <p style="margin:.75rem 0 .4rem 0;"><strong style="color:#92400e;">🔍 Cara merespons pesan:</strong></p>
          <ol style="margin:.3rem 0 .75rem 1.3rem;padding:0;">
            <li>Baca pesan dengan <b>teliti</b> sebelum menghubungi pengirim — konteks penting untuk balasan yang tepat.</li>
            <li>Balas langsung via <b>email / telepon</b> pengirim yang tercantum. Sistem ini <b>tidak mengirim balasan otomatis</b>.</li>
            <li>Tandai <b>"Sudah Dibaca"</b> setelah pesan selesai ditangani agar tidak terus muncul di badge sidebar.</li>
            <li>Kalau pesan ditujukan ke unit tertentu (OSIS, ekskul, BK, dll), <b>teruskan</b> ke penanggung jawab yang relevan.</li>
          </ol>

          <p style="margin:.85rem 0 .4rem 0;"><strong style="color:#b91c1c;">🔒 Privasi data pengirim:</strong></p>
          <ul style="margin:.3rem 0 .2rem 1.3rem;padding:0;">
            <li>Pesan berisi <b>data kontak pribadi</b> (nama, email, nomor HP). Jangan disebarluaskan ke pihak yang tidak berkepentingan.</li>
            <li>Jangan balas pesan dengan <b>informasi sensitif</b> (password, data siswa lain, dll) via channel yang tidak terverifikasi.</li>
            <li>Data yang dihapus <b>tidak dapat dikembalikan</b>. Kalau belum yakin, cukup tandai "Sudah Dibaca" dulu.</li>
          </ul>
        </div>
      </details>

      <!-- Stat cards -->
      <div class="stat-grid anim-fade-up" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card stat-teal">
          <div class="stat-card-shine"></div>
          <div class="stat-icon"><i class="fas fa-envelope"></i></div>
          <div class="stat-num"><?= $totalPesan ?></div>
          <div class="stat-label">Total Pesan</div>
          <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
        <div class="stat-card stat-gold">
          <div class="stat-card-shine"></div>
          <div class="stat-icon"><i class="fas fa-envelope-open"></i></div>
          <div class="stat-num"><?= $belumBaca ?></div>
          <div class="stat-label">Belum Dibaca</div>
          <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
        <div class="stat-card stat-green">
          <div class="stat-card-shine"></div>
          <div class="stat-icon"><i class="fas fa-check-double"></i></div>
          <div class="stat-num"><?= $sudahBaca ?></div>
          <div class="stat-label">Sudah Dibaca</div>
          <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
        </div>
      </div>

      <!-- Filter + bulk action -->
      <div class="filter-bar anim-fade-up">
        <span class="filter-lbl"><i class="fas fa-filter"></i> Filter:</span>
        <a href="pesan.php" class="filter-chip <?= $filter==='' ? 'active' : '' ?>">
          <i class="fas fa-list"></i> Semua <span style="opacity:.7">(<?= $totalPesan ?>)</span>
        </a>
        <a href="pesan.php?filter=belum" class="filter-chip <?= $filter==='belum' ? 'active' : '' ?>">
          <i class="fas fa-circle" style="font-size:.45rem;"></i> Belum Dibaca
          <?php if ($belumBaca > 0): ?>
            <span style="background:#ef4444;color:#fff;border-radius:20px;padding:0 6px;font-size:.68rem;line-height:1.6;"><?= $belumBaca ?></span>
          <?php endif; ?>
        </a>
        <a href="pesan.php?filter=sudah" class="filter-chip <?= $filter==='sudah' ? 'active' : '' ?>">
          <i class="fas fa-check"></i> Sudah Dibaca <span style="opacity:.7">(<?= $sudahBaca ?>)</span>
        </a>

        <?php if ($belumBaca > 0): ?>
          <form method="POST" style="margin-left:auto;">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="baca_semua"/>
            <button type="submit" class="btn btn-sm btn-outline" onclick="return confirm('Tandai semua pesan sebagai sudah dibaca?')">
              <i class="fas fa-check-double"></i> Tandai Semua Dibaca
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Info jumlah -->
      <?php if (!empty($rows)): ?>
        <div class="bulk-info anim-fade-up">
          Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= $filter === 'belum' ? $belumBaca : ($filter === 'sudah' ? $sudahBaca : $totalPesan) ?></strong> pesan
          <?= $filter === 'belum' ? '· belum dibaca' : ($filter === 'sudah' ? '· sudah dibaca' : '') ?>
          <?php if ($totalPages > 1): ?> · Halaman <?= $page ?>/<?= $totalPages ?><?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Daftar pesan -->
      <?php if (empty($rows)): ?>
        <div class="empty-pesan anim-fade-up">
          <div class="empty-pesan-icon"><i class="far fa-envelope-open"></i></div>
          <h3>
            <?php if ($filter === 'belum'): ?>Tidak ada pesan belum dibaca
            <?php elseif ($filter === 'sudah'): ?>Tidak ada pesan sudah dibaca
            <?php else: ?>Belum ada pesan masuk
            <?php endif; ?>
          </h3>
          <p>Pesan dari form kontak halaman publik akan tampil di sini.</p>
        </div>

      <?php else: ?>
        <div class="pesan-list">
          <?php foreach ($rows as $i => $p):
            $initial = strtoupper(mb_substr($p['nama'], 0, 1));
          ?>
            <div class="pesan-card <?= $p['is_read'] ? 'read' : 'unread' ?> anim-fade-up" style="animation-delay:<?= $i * 0.04 ?>s">

              <div class="pesan-card-header">
                <div class="pesan-sender">
                  <div class="pesan-avatar"><?= $initial ?></div>
                  <div>
                    <div class="pesan-nama">
                      <?= htmlspecialchars($p['nama']) ?>
                      <?php if ($p['kelas']): ?>
                        <span class="pesan-kelas">· <?= htmlspecialchars($p['kelas']) ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="pesan-time">
                      <i class="far fa-clock"></i>
                      <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                    </div>
                  </div>
                </div>
                <div>
                  <?php if ($p['is_read']): ?>
                    <span class="badge badge-gray"><i class="fas fa-check"></i> Sudah dibaca</span>
                  <?php else: ?>
                    <span class="badge badge-gold"><i class="fas fa-circle" style="font-size:.4rem;"></i> Belum dibaca</span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="pesan-card-body">
                <div class="pesan-subjek">
                  <i class="fas fa-tag"></i>
                  <?= htmlspecialchars($p['subjek']) ?>
                </div>
                <div class="pesan-isi"><?= htmlspecialchars($p['pesan']) ?></div>

                <div class="pesan-card-footer">
                  <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                    <?php if (!$p['is_read']): ?>
                      <form method="POST" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="baca"/>
                        <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                        <button type="submit" class="btn btn-sm btn-outline">
                          <i class="fas fa-check"></i> Tandai Dibaca
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                  <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Hapus pesan dari <?= htmlspecialchars(addslashes($p['nama'])) ?>?')">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="hapus"/>
                    <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i> Hapus
                    </button>
                  </form>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <!-- Paginasi pesan -->
          <div style="display:flex;gap:.5rem;justify-content:center;padding:1.5rem 0;flex-wrap:wrap;">
            <?php if ($page > 1): ?>
              <a href="?filter=<?= $filter ?>&page=<?= $page - 1 ?>" style="padding:.45rem 1rem;border-radius:6px;background:var(--green-dark,#1a6b3c);color:#fff;text-decoration:none;font-size:.85rem;">← Sebelumnya</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <a href="?filter=<?= $filter ?>&page=<?= $p ?>"
                 style="padding:.45rem .85rem;border-radius:6px;<?= $p === $page ? 'background:#1a6b3c;color:#fff;' : 'background:#f3f4f6;color:#374151;' ?>;text-decoration:none;font-size:.85rem;">
                <?= $p ?>
              </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
              <a href="?filter=<?= $filter ?>&page=<?= $page + 1 ?>" style="padding:.45rem 1rem;border-radius:6px;background:var(--green-dark,#1a6b3c);color:#fff;text-decoration:none;font-size:.85rem;">Berikutnya →</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>

    </div>
  </main>
  <script src="assets/admin.js"></script>
</body>
</html>
