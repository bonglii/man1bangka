<?php
// ============================================================
// testimoni.php — Moderasi Testimoni
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman moderasi & manajemen testimoni siswa.
// Testimoni baru (dari publik) masuk dengan status "nonaktif" hingga disetujui admin.
// Admin juga dapat menambahkan testimoni secara manual langsung dari form.
// Stat cards menampilkan Total Testimoni & Ditampilkan (2 kolom).
//
// Aksi POST yang ditangani:
//   approve -> ubah status menjadi "aktif" (ditampilkan di website)
//   reject  -> ubah status menjadi "nonaktif" (disembunyikan)
//   hapus   -> delete by id
//   tambah  -> insert testimoni manual oleh admin (langsung aktif)
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = ''; // Pesan hasil aksi (dari redirect parameter)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf(); // Tolak jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';
  // --- AKSI: Hapus testimoni ---
  if ($action === 'hapus') {
    $pdo->prepare("DELETE FROM testimoni WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: testimoni.php?msg=hapus'); exit;
  // --- AKSI: Setujui testimoni (ubah status nonaktif -> aktif) ---
  } elseif ($action === 'approve') {
    $pdo->prepare("UPDATE testimoni SET status='aktif', is_approved=1 WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: testimoni.php?msg=approve'); exit;
  } elseif ($action === 'reject') {
    $pdo->prepare("UPDATE testimoni SET status='nonaktif', is_approved=0 WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: testimoni.php?msg=reject'); exit;
  } elseif ($action === 'tambah') {
    $nama = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $kegiatan = trim($_POST['kegiatan'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $rating = min(5, max(1, (int)($_POST['rating'] ?? 5)));
    $org_id    = ($_POST['organisasi_id'] ?? '') !== '' ? (int)$_POST['organisasi_id'] : null;
    $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;
    if (!$nama || !$isi) {
      header('Location: testimoni.php?err=data_kurang'); exit;
    }
    $pdo->prepare("INSERT INTO testimoni (nama_siswa,kelas,jenis_kegiatan,nama_kegiatan,isi,rating,status,is_approved,organisasi_id,ekskul_id) VALUES (?,?,'lainnya',?,?,?,'aktif',1,?,?)")
      ->execute([$nama, $kelas, $kegiatan, $isi, $rating, $org_id, $ekskul_id]);
    header('Location: testimoni.php?msg=tambah'); exit;
  }
}

// Konversi parameter redirect ke pesan tampilan
$msgMap = [
  'hapus'   => 'Testimoni dihapus.',
  'approve' => 'Testimoni disetujui dan ditampilkan di website!',
  'reject'  => 'Testimoni ditolak.',
  'tambah'  => 'Testimoni berhasil ditambahkan!',
];
$errMap = ['data_kurang' => 'Nama dan isi wajib diisi.'];
if (isset($_GET['msg'])) $msg = $msgMap[$_GET['msg']] ?? '';
$err = isset($_GET['err']) ? ($errMap[$_GET['err']] ?? '') : '';

$rows = $pdo->query("SELECT * FROM testimoni ORDER BY is_approved ASC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$pending = array_values(array_filter($rows, fn($r) => $r['status'] !== 'aktif'));
$approved = array_values(array_filter($rows, fn($r) => $r['status'] === 'aktif'));
$nPending = count($pending);
$nApproved = count($approved);
$orgList    = $pdo->query("SELECT id, nama FROM organisasi ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
$ekskulList = $pdo->query("SELECT id, nama FROM ekstrakurikuler ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Testimoni — Admin MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css" />
</head>

<body>
  <?php include 'sidebar.php'; ?>
  <main class="admin-main">

    <header class="admin-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon"><i class="fas fa-bars"></i></button>
        <div>
          <div class="topbar-title"><i class="fas fa-comment-dots"></i> Testimoni Siswa</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Testimoni</div>
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
      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>

      <!-- Stats -->
      <div class="stat-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:1.5rem;">
        <div class="stat-card stat-teal anim-fade-up anim-delay-1">
          <div class="stat-icon"><i class="fas fa-comment-dots"></i></div>
          <div>
            <div class="stat-num"><?= count($rows) ?></div>
            <div class="stat-label">Total Testimoni</div>
          </div>
        </div>
        <div class="stat-card stat-green anim-fade-up anim-delay-2">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="stat-num"><?= $nApproved ?></div>
            <div class="stat-label">Ditampilkan</div>
          </div>
        </div>
      </div>

      <div class="two-col" style="align-items:start;">

        <!-- FORM TAMBAH MANUAL -->
        <div class="form-section anim-fade-up">
          <div class="form-section-header">
            <div class="form-section-header-icon" style="background:var(--teal-bg);color:var(--teal);">
              <i class="fas fa-plus"></i>
            </div>
            <div>
              <h3>Tambah Testimoni Manual</h3>
              <p>Masukkan testimoni langsung dari admin</p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" id="form-testimoni" autocomplete="off">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="tambah" />

              <div class="form-group">
                <label>Nama Siswa <span class="req">*</span></label>
                <div class="input-icon-wrap">
                  <i class="input-icon fas fa-user"></i>
                  <input type="text" name="nama" placeholder="Nama lengkap siswa" required autocomplete="off" />
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Kelas</label>
                  <select name="kelas" autocomplete="off">
                    <option value="">-- Pilih Kelas --</option>
                    <option>10A</option>
                    <option>10B</option>
                    <option>10C</option>
                    <option>10D</option>
                    <option>10E</option>
                    <option>10F</option>
                    <option>11A</option>
                    <option>11B</option>
                    <option>11C</option>
                    <option>11D</option>
                    <option>11E</option>
                    <option>11F</option>
                    <option>12A</option>
                    <option>12B</option>
                    <option>12C</option>
                    <option>12D</option>
                    <option>12E</option>
                    <option>12F</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Rating</label>
                  <div class="star-input-wrap" id="star-wrap">
                    <input type="hidden" name="rating" id="rating-val" value="5" />
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <span class="star-input<?= $i <= 5 ? ' active' : '' ?>" data-val="<?= $i ?>">★</span>
                    <?php endfor; ?>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Nama Kegiatan</label>
                <div class="input-icon-wrap">
                  <i class="input-icon fas fa-calendar-check"></i>
                  <input type="text" name="kegiatan" placeholder="Contoh: Ekskul Pramuka, Lomba OSN..." autocomplete="off" />
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Organisasi <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                  <select name="organisasi_id">
                    <option value="">— Pilih Organisasi —</option>
                    <?php foreach ($orgList as $o): ?>
                      <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Ekstrakurikuler <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                  <select name="ekskul_id">
                    <option value="">— Pilih Ekskul —</option>
                    <?php foreach ($ekskulList as $e): ?>
                      <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Isi Testimoni <span class="req">*</span></label>
                <textarea name="isi" placeholder="Ceritakan pengalaman siswa mengikuti kegiatan di MAN 1 Bangka..."
                  style="min-height:110px;" required autocomplete="off"></textarea>
              </div>
            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="form-testimoni" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> Tambah Testimoni
            </button>
            <button type="reset" form="form-testimoni" class="btn btn-outline" onclick="setTimeout(()=>{document.getElementById('rating-val').value=5;document.querySelectorAll('.star-input').forEach((s,i)=>s.classList.toggle('active',i<5));},0)">
              <i class="fas fa-redo"></i> Reset
            </button>
          </div>
        </div>

        <!-- LIST PANEL -->
        <div class="admin-card anim-fade-up anim-delay-2" data-tab-group="tes-group">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--teal-bg);color:var(--teal);">
                <i class="fas fa-list"></i>
              </div>
              <div>
                <div class="card-header-title">Daftar Testimoni</div>
                <div class="card-header-sub"><?= count($rows) ?> total testimoni</div>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <div style="padding:.75rem 1.25rem 0;border-bottom:2px solid var(--gray-200);">
            <div class="admin-tabs" style="border:none;margin-bottom:0;">
              <button class="admin-tab active" data-tab="tab-pending">
                ⏳ Menunggu
                <?php if ($nPending): ?>
                  <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:var(--orange);color:#fff;border-radius:99px;font-size:.65rem;font-weight:800;margin-left:.3rem;padding:0 .3rem;"><?= $nPending ?></span>
                <?php endif; ?>
              </button>
              <button class="admin-tab" data-tab="tab-approved">
                ✅ Disetujui
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:var(--primary-mid);color:#fff;border-radius:99px;font-size:.65rem;font-weight:800;margin-left:.3rem;padding:0 .3rem;"><?= $nApproved ?></span>
              </button>
            </div>
          </div>

          <!-- TAB PENDING -->
          <div id="tab-pending" class="tab-pane active" style="max-height:560px;overflow-y:auto;">
            <?php if ($pending): ?>
              <?php foreach ($pending as $r): ?>
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--gray-100);">
                  <!-- Header row -->
                  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.65rem;">
                    <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--gold-light),#fde68a);color:var(--gold-dark);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem;flex-shrink:0;border:2px solid var(--gold);">
                      <?= strtoupper(substr($r['nama_siswa'], 0, 1)) ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="font-weight:700;font-size:.87rem;color:var(--gray-900);">
                        <?= htmlspecialchars($r['nama_siswa']) ?>
                        <?php if ($r['kelas']): ?><span style="font-weight:400;color:var(--gray-400);font-size:.78rem;"> — <?= htmlspecialchars($r['kelas']) ?></span><?php endif; ?>
                      </div>
                      <div style="display:flex;align-items:center;gap:.5rem;margin-top:.15rem;">
                        <!-- Stars -->
                        <span style="color:var(--gold);font-size:.85rem;letter-spacing:1px;"><?= str_repeat('★', (int)$r['rating']) . '<span style="color:var(--gray-300);">' . str_repeat('★', 5 - (int)$r['rating']) . '</span>' ?></span>
                        <?php if ($r['nama_kegiatan'] || $r['jenis_kegiatan']): ?>
                          <span class="badge badge-blue" style="font-size:.65rem;"><?= htmlspecialchars($r['nama_kegiatan'] ?: $r['jenis_kegiatan']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <span style="font-size:.7rem;color:var(--gray-400);flex-shrink:0;"><?= date('d/m/y', strtotime($r['created_at'])) ?></span>
                  </div>
                  <!-- Isi -->
                  <div style="font-size:.82rem;color:var(--gray-600);line-height:1.6;padding:.75rem;background:var(--gray-50);border-radius:var(--radius-sm);border-left:3px solid var(--gray-200);margin-bottom:.75rem;font-style:italic;">
                    "<?= htmlspecialchars(substr($r['isi'], 0, 200)) ?><?= strlen($r['isi']) > 200 ? '...' : '' ?>"
                  </div>
                  <!-- Actions -->
                  <div style="display:flex;gap:.4rem;">
                    <form method="POST" style="display:inline;" autocomplete="off">
              <?= csrfField() ?>
                      <input type="hidden" name="action" value="approve" />
                      <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check"></i> Setujui & Tampilkan
                      </button>
                    </form>
                    <form method="POST" style="display:inline;" autocomplete="off">
              <?= csrfField() ?>
                      <input type="hidden" name="action" value="reject" />
                      <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button type="submit" class="btn btn-outline btn-sm" style="color:var(--orange);border-color:var(--orange);">
                        <i class="fas fa-ban"></i> Tolak
                      </button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus testimoni ini?')" autocomplete="off">
              <?= csrfField() ?>
                      <input type="hidden" name="action" value="hapus" />
                      <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state" style="padding:3rem 1.5rem;">
                <div class="empty-state-icon">✅</div>
                <h4>Semua Bersih!</h4>
                <p>Tidak ada testimoni yang menunggu persetujuan.</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- TAB APPROVED -->
          <div id="tab-approved" class="tab-pane" style="max-height:560px;overflow-y:auto;">
            <?php if ($approved): ?>
              <?php foreach ($approved as $r): ?>
                <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--gray-100);">
                  <div style="display:flex;align-items:flex-start;gap:.75rem;">
                    <div style="width:38px;height:38px;border-radius:50%;background:var(--primary-light);color:var(--primary-mid);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.95rem;flex-shrink:0;border:2px solid var(--primary-border);">
                      <?= strtoupper(substr($r['nama_siswa'], 0, 1)) ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                      <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.25rem;">
                        <div>
                          <span style="font-weight:700;font-size:.87rem;"><?= htmlspecialchars($r['nama_siswa']) ?></span>
                          <?php if ($r['kelas']): ?><span style="font-size:.75rem;color:var(--gray-400);"> — <?= htmlspecialchars($r['kelas']) ?></span><?php endif; ?>
                        </div>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus?')" autocomplete="off">
              <?= csrfField() ?>
                          <input type="hidden" name="action" value="hapus" />
                          <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                          <button type="submit" class="btn btn-ghost btn-icon btn-xs" style="color:var(--red);" title="Hapus">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                      <div style="font-size:.85rem;color:var(--gold);letter-spacing:1px;margin-bottom:.35rem;">
                        <?= str_repeat('★', (int)$r['rating']) ?><span style="color:var(--gray-200);"><?= str_repeat('★', 5 - (int)$r['rating']) ?></span>
                      </div>
                      <div style="font-size:.8rem;color:var(--gray-600);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                        <?= htmlspecialchars($r['isi']) ?>
                      </div>
                      <?php if ($r['nama_kegiatan']): ?>
                        <span class="badge badge-blue" style="margin-top:.4rem;font-size:.65rem;"><?= htmlspecialchars($r['nama_kegiatan']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state" style="padding:3rem 1.5rem;">
                <div class="empty-state-icon">💬</div>
                <h4>Belum Ada Testimoni</h4>
                <p>Testimoni yang disetujui akan tampil di sini dan di website.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- STAR RATING INIT — inside .page-content so SPA reinitPageScripts() re-runs it -->
      <script>
        (function initStars() {
          var wrap = document.getElementById('star-wrap');
          if (!wrap) return;
          /* Clone wrap to clear any stale listeners from previous SPA load */
          var fresh = wrap.cloneNode(true);
          wrap.parentNode.replaceChild(fresh, wrap);
          var stars = Array.from(fresh.querySelectorAll('.star-input'));
          var input = document.getElementById('rating-val');
          function applyActive(n) {
            stars.forEach(function(s, i) { s.classList.toggle('active', i < n); });
          }
          stars.forEach(function(s, idx) {
            s.addEventListener('click', function(e) {
              e.stopPropagation();
              input.value = idx + 1;
              applyActive(idx + 1);
            });
            s.addEventListener('mouseenter', function() { applyActive(idx + 1); });
            s.addEventListener('mouseleave', function() { applyActive(parseInt(input.value) || 5); });
          });
        })();
      </script>

    </div><!-- /page-content -->
  </main>

  <script src="assets/admin.js"></script>
</body>

</html>