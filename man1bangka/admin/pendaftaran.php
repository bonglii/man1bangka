<?php
// ============================================================
// pendaftaran.php — Pendaftaran Siswa
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman manajemen pendaftaran ekstrakurikuler dari halaman publik.
// Admin dapat melihat, update status, dan menghapus pendaftaran.
//
// Aksi POST yang ditangani:
//   update_status -> ubah status (pending/diterima/ditolak)
//   hapus         -> delete by id
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf(); // Tolak jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';

  if ($action === 'update_status') {
    $id     = (int)$_POST['id'];
    $status = $_POST['status'] ?? 'menunggu';
    if (!in_array($status, ['menunggu', 'diterima', 'ditolak'])) {
      header('Location: pendaftaran.php?err=status_invalid'); exit;
    }
    $pdo->prepare("UPDATE pendaftaran_ekskul SET status=? WHERE id=?")->execute([$status, $id]);
    header('Location: pendaftaran.php?msg=status_updated'); exit;

  } elseif ($action === 'hapus') {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM pendaftaran_ekskul WHERE id=?")->execute([$id]);
    header('Location: pendaftaran.php?msg=hapus'); exit;

  } elseif ($action === 'update_status_bulk') {
    $ids    = $_POST['id'] ?? [];
    $status = $_POST['status_bulk'] ?? 'menunggu';
    if (!in_array($status, ['menunggu', 'diterima', 'ditolak'])) {
      header('Location: pendaftaran.php?err=status_invalid'); exit;
    }
    if ($ids) {
      $ph = implode(',', array_fill(0, count($ids), '?'));
      $vals = array_merge([$status], array_map('intval', $ids));
      $pdo->prepare("UPDATE pendaftaran_ekskul SET status=? WHERE id IN ($ph)")->execute($vals);
    }
    header('Location: pendaftaran.php?msg=bulk_updated&n=' . count($ids)); exit;

  } elseif ($action === 'hapus_bulk') {
    $ids = $_POST['ids'] ?? [];
    if ($ids) {
      $ph = implode(',', array_fill(0, count($ids), '?'));
      $pdo->prepare("DELETE FROM pendaftaran_ekskul WHERE id IN ($ph)")->execute(array_map('intval', $ids));
    }
    header('Location: pendaftaran.php?msg=bulk_hapus&n=' . count($ids)); exit;

  } elseif ($action === 'tambah') {
    $ekskul_id = (int)($_POST['ekstrakurikuler_id'] ?? 0);
    $nama      = trim($_POST['nama_siswa'] ?? '');
    $kelas     = trim($_POST['kelas'] ?? '');
    $nis       = trim($_POST['nis'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $alasan    = trim($_POST['alasan'] ?? '');
    $status    = $_POST['status'] ?? 'menunggu';

    if (!$ekskul_id || !$nama || !$kelas || !$nis) {
      header('Location: pendaftaran.php?err=data_kurang'); exit;
    }
    $cek = $pdo->prepare("SELECT id FROM pendaftaran_ekskul WHERE nis=? AND ekstrakurikuler_id=?");
    $cek->execute([$nis, $ekskul_id]);
    if ($cek->fetch()) {
      header('Location: pendaftaran.php?err=sudah_daftar'); exit;
    }
    $pdo->prepare("INSERT INTO pendaftaran_ekskul (ekstrakurikuler_id,nama_siswa,kelas,nis,no_hp,email,alasan,status) VALUES (?,?,?,?,?,?,?,?)")
      ->execute([$ekskul_id, $nama, $kelas, $nis, $no_hp, $email, $alasan, $status]);
    header('Location: pendaftaran.php?msg=tambah'); exit;
  }
}

// Konversi parameter ?msg= & ?err= dari redirect ke pesan tampilan
$msg = $err = '';
$msgMap = [
  'status_updated' => 'Status pendaftaran diperbarui!',
  'hapus'          => 'Data pendaftaran dihapus.',
  'bulk_updated'   => ($_GET['n'] ?? 0) . ' pendaftaran diperbarui.',
  'bulk_hapus'     => ($_GET['n'] ?? 0) . ' data berhasil dihapus.',
  'tambah'         => 'Pendaftaran berhasil ditambahkan!',
];
$errMap = [
  'status_invalid' => 'Status tidak valid.',
  'data_kurang'    => 'Ekskul, nama siswa, kelas, dan NIS wajib diisi.',
  'sudah_daftar'   => 'Siswa dengan NIS tersebut sudah terdaftar di ekskul ini.',
];
if (isset($_GET['msg'])) $msg = $msgMap[$_GET['msg']] ?? '';
if (isset($_GET['err'])) $err = $errMap[$_GET['err']] ?? '';

// Filters
$fEkskul  = (int)($_GET['ekskul'] ?? 0);
$fStatus  = $_GET['status'] ?? '';
$fSearch  = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, e.nama as nama_ekskul, e.kategori as kat_ekskul
        FROM pendaftaran_ekskul p
        LEFT JOIN ekstrakurikuler e ON p.ekstrakurikuler_id = e.id
        WHERE 1=1";
$params = [];
if ($fEkskul) {
  $sql .= " AND p.ekstrakurikuler_id=?";
  $params[] = $fEkskul;
}
if ($fStatus) {
  $sql .= " AND p.status=?";
  $params[] = $fStatus;
}
if ($fSearch) {
  $sql .= " AND (p.nama_siswa LIKE ? OR p.nis LIKE ? OR p.kelas LIKE ?)";
  $params = array_merge($params, ["%$fSearch%", "%$fSearch%", "%$fSearch%"]);
}
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalAll      = $pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul")->fetchColumn();
$totalMenunggu = $pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul WHERE status='menunggu'")->fetchColumn();
$totalDiterima = $pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul WHERE status='diterima'")->fetchColumn();
$totalDitolak  = $pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul WHERE status='ditolak'")->fetchColumn();

// All ekskul for dropdown
$ekskulList = $pdo->query("SELECT id, nama FROM ekstrakurikuler ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

// Detail view
$detail = null;
if (isset($_GET['detail'])) {
  $d = $pdo->prepare("SELECT p.*, e.nama as nama_ekskul, e.jadwal, e.tempat, e.kategori as kat_ekskul
        FROM pendaftaran_ekskul p LEFT JOIN ekstrakurikuler e ON p.ekstrakurikuler_id = e.id
        WHERE p.id=?");
  $d->execute([(int)$_GET['detail']]);
  $detail = $d->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Pendaftaran Siswa — Admin MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="assets/admin.css" />
</head>

<body>
  <?php include 'sidebar.php'; ?>
  <main class="admin-main">

    <header class="admin-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon"><i class="fas fa-bars"></i></button>
        <div>
          <div class="topbar-title"><i class="fas fa-clipboard-list"></i> Pendaftaran Siswa</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Pendaftaran</div>
        </div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-primary btn-sm" onclick="openModal('modal-tambah')">
          <i class="fas fa-plus"></i> Tambah Manual
        </button>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div>
          <?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">

      <?php if ($msg): ?>
        <div class="alert alert-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="alert alert-err"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <!-- STATS -->
      <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem;">
        <div class="stat-card stat-green">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div>
            <div class="stat-num"><?= $totalAll ?></div>
            <div class="stat-label">Total Pendaftar</div>
          </div>
        </div>
        <div class="stat-card stat-gold">
          <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
          <div>
            <div class="stat-num"><?= $totalMenunggu ?></div>
            <div class="stat-label">Menunggu</div>
          </div>
        </div>
        <div class="stat-card stat-teal">
          <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="stat-num"><?= $totalDiterima ?></div>
            <div class="stat-label">Diterima</div>
          </div>
        </div>
        <div class="stat-card stat-red">
          <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
          <div>
            <div class="stat-num"><?= $totalDitolak ?></div>
            <div class="stat-label">Ditolak</div>
          </div>
        </div>
      </div>

      <div class="admin-card">
        <!-- FILTER BAR -->
        <div class="card-header" style="flex-wrap:wrap;gap:.75rem;">
          <div class="card-header-left">
            <div class="card-header-icon"><i class="fas fa-filter"></i></div>
            <div>
              <div class="card-header-title">Data Pendaftaran</div>
              <div class="card-header-sub"><?= count($rows) ?> data ditemukan</div>
            </div>
          </div>
          <form method="GET" id="filter-form" action="pendaftaran.php" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-left:auto;" autocomplete="off" onsubmit="return true;">
            <div class="search-bar" style="min-width:180px;">
              <i class="fas fa-search"></i>
              <input type="text" name="q" id="filter-q" placeholder="Cari nama / NIS / kelas..." value="<?= htmlspecialchars($fSearch) ?>" oninput="debounceFilter()" autocomplete="off" />
            </div>
            <select name="ekskul" id="filter-ekskul" style="width:auto;" onchange="document.getElementById('filter-form').submit()">
              <option value="">Semua Ekskul</option>
              <?php foreach ($ekskulList as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $fEkskul == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nama']) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="status" id="filter-status" style="width:auto;" onchange="document.getElementById('filter-form').submit()">
              <option value="">Semua Status</option>
              <option value="menunggu" <?= $fStatus === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
              <option value="diterima" <?= $fStatus === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
              <option value="ditolak" <?= $fStatus === 'ditolak' ? 'selected' : '' ?>>❌ Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm" style="display:none;"><i class="fas fa-search"></i></button>
            <?php if ($fSearch || $fEkskul || $fStatus): ?>
              <a href="pendaftaran.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a>
            <?php endif; ?>
          </form>
        </div>

        <!-- TABLE -->
        <!-- Hidden bulk form (outside table to avoid nested form issues) -->
        <form method="POST" id="bulk-form" autocomplete="off" style="display:none;">
              <?= csrfField() ?>
          <input type="hidden" name="action" value="hapus_bulk" />
          <div id="bulk-ids-container"></div>
        </form>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th style="width:36px;"><input type="checkbox" id="check-all" style="width:auto;cursor:pointer;" /></th>
                <th>Siswa</th>
                <th>NIS</th>
                <th>Ekskul</th>
                <th>Kontak</th>
                <th>Status</th>
                <th>Daftar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($rows): ?>
                <?php foreach ($rows as $r): ?>
                  <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $r['id'] ?>" class="row-check" style="width:auto;cursor:pointer;" /></td>
                    <td>
                      <div style="font-weight:700;color:var(--gray-900);"><?= htmlspecialchars($r['nama_siswa']) ?></div>
                      <div style="font-size:.72rem;color:var(--gray-400);">Kelas <?= htmlspecialchars($r['kelas']) ?></div>
                    </td>
                    <td style="font-size:.8rem;font-family:monospace;color:var(--gray-600);"><?= htmlspecialchars($r['nis']) ?></td>
                    <td>
                      <div style="font-size:.82rem;font-weight:600;"><?= htmlspecialchars($r['nama_ekskul'] ?? '-') ?></div>
                      <?php if ($r['kat_ekskul']): ?><span class="badge badge-blue" style="font-size:.65rem;"><?= htmlspecialchars($r['kat_ekskul']) ?></span><?php endif; ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--gray-500);">
                      <?php if ($r['no_hp']): ?><div><i class="fas fa-phone" style="width:14px;"></i> <?= htmlspecialchars($r['no_hp']) ?></div><?php endif; ?>
                      <?php if ($r['email']): ?><div><i class="fas fa-envelope" style="width:14px;"></i> <?= htmlspecialchars($r['email']) ?></div><?php endif; ?>
                    </td>
                    <td>
                      <form method="POST" style="display:inline;" autocomplete="off">
              <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_status" />
                        <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                        <select name="status" onchange="this.form.submit()" style="width:auto;font-size:.76rem;padding:.3rem .6rem;border-radius:6px;font-weight:600;border-color:transparent;" class="status-select status-<?= htmlspecialchars($r['status']) ?>">
                          <option value="menunggu" <?= $r['status'] === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                          <option value="diterima" <?= $r['status'] === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
                          <option value="ditolak" <?= $r['status'] === 'ditolak' ? 'selected' : '' ?>>❌ Ditolak</option>
                        </select>
                      </form>
                    </td>
                    <td style="font-size:.75rem;color:var(--gray-400);"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    <td>
                      <div class="td-actions">
                        <a href="?detail=<?= $r['id'] ?><?= $fSearch ? '&q=' . urlencode($fSearch) : '' ?><?= $fStatus ? '&status=' . urlencode($fStatus) : '' ?><?= $fEkskul ? '&ekskul=' . (int)$fEkskul : '' ?>" class="btn btn-ghost btn-icon btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pendaftaran ini?')" autocomplete="off">
              <?= csrfField() ?>
                          <input type="hidden" name="action" value="hapus" />
                          <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                          <button type="submit" class="btn btn-ghost btn-icon btn-sm" style="color:var(--red);" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8">
                    <div class="empty-state">
                      <div class="empty-state-icon">📋</div>
                      <h4>Tidak Ada Data</h4>
                      <p>Belum ada pendaftaran<?= $fSearch ? ' dengan kata kunci "' . htmlspecialchars($fSearch) . '"' : '' ?>.</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- BULK ACTIONS -->
        <?php if ($rows): ?>
          <div style="padding:.85rem 1.25rem;border-top:1px solid var(--gray-100);display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <span style="font-size:.78rem;color:var(--gray-400);" id="selected-count">0 dipilih</span>
            <button onclick="bulkAction('diterima')" class="btn btn-outline btn-sm" style="color:var(--teal);border-color:var(--teal);" id="btn-bulk-diterima" disabled>
              <i class="fas fa-check"></i> <span id="lbl-terima">Terima Semua</span>
            </button>
            <button onclick="bulkAction('ditolak')" class="btn btn-outline btn-sm" style="color:var(--orange);border-color:var(--orange);" id="btn-bulk-tolak" disabled>
              <i class="fas fa-times"></i> <span id="lbl-tolak">Tolak Semua</span>
            </button>
            <button onclick="bulkHapus()" class="btn btn-outline btn-sm" style="color:var(--red);border-color:var(--red);" id="btn-bulk-hapus" disabled>
              <i class="fas fa-trash"></i> Hapus Dipilih
            </button>
            <a href="?<?= http_build_query(['ekskul' => $fEkskul, 'status' => $fStatus, 'q' => $fSearch]) ?>" class="btn btn-outline btn-sm" style="margin-left:auto;">
              <i class="fas fa-sync"></i> Refresh
            </a>
          </div>
        <?php endif; ?>
      </div>

      <script>
        /* Bulk Action Scripts — HARUS di dalam .page-content agar SPA reinitPageScripts() bisa re-run */
        (function initBulkActions() {
          const checkAll = document.getElementById('check-all');
          const rowChecks = document.querySelectorAll('.row-check');
          const bulkBtns = ['btn-bulk-diterima', 'btn-bulk-tolak', 'btn-bulk-hapus'];
          const countEl = document.getElementById('selected-count');

          function updateBulk() {
            const n = document.querySelectorAll('.row-check:checked').length;
            if (countEl) countEl.textContent = n + ' dipilih';
            bulkBtns.forEach(id => {
              const b = document.getElementById(id);
              if (b) b.disabled = (n === 0);
            });
            const lblTerima = document.getElementById('lbl-terima');
            const lblTolak = document.getElementById('lbl-tolak');
            if (lblTerima) lblTerima.textContent = n === 1 ? 'Terima' : 'Terima Semua';
            if (lblTolak) lblTolak.textContent = n === 1 ? 'Tolak' : 'Tolak Semua';
          }

          if (checkAll) {
            checkAll.addEventListener('change', () => {
              rowChecks.forEach(c => c.checked = checkAll.checked);
              updateBulk();
            });
          }
          rowChecks.forEach(c => c.addEventListener('change', () => {
            if (checkAll) checkAll.checked = [...rowChecks].every(c => c.checked);
            updateBulk();
          }));

          window.bulkAction = function(status) {
            const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
            if (!ids.length) return;
            if (!confirm('Update ' + ids.length + ' data menjadi "' + status + '"?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            const addHidden = (n, v) => {
              const i = document.createElement('input');
              i.type = 'hidden';
              i.name = n;
              i.value = v;
              form.appendChild(i);
            };
            addHidden('action', 'update_status_bulk');
            addHidden('status_bulk', status);
            ids.forEach(id => addHidden('id[]', id));
            document.body.appendChild(form);
            form.submit();
          };

          window.bulkHapus = function() {
            const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
            if (!ids.length) return;
            if (!confirm('Hapus ' + ids.length + ' data yang dipilih?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            const addHidden = (n, v) => {
              const i = document.createElement('input');
              i.type = 'hidden';
              i.name = n;
              i.value = v;
              form.appendChild(i);
            };
            addHidden('action', 'hapus_bulk');
            ids.forEach(id => addHidden('ids[]', id));
            document.body.appendChild(form);
            form.submit();
          };

          let filterTimer = null;
          window.debounceFilter = function() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(() => {
              const f = document.getElementById('filter-form');
              if (f) f.submit();
            }, 500);
          };
        })();
      </script>

    </div>
  </main>

  <!-- MODAL: Tambah Manual -->
  <div class="modal-backdrop" id="modal-tambah">
    <div class="modal-box">
      <div class="modal-header">
        <h3><i class="fas fa-user-plus" style="color:var(--primary-mid);margin-right:.4rem;"></i> Tambah Pendaftaran Manual</h3>
        <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="fas fa-times"></i></button>
      </div>
      <form method="POST" autocomplete="off">
              <?= csrfField() ?>
        <div class="modal-body">
          <input type="hidden" name="action" value="tambah" />
          <div class="form-group">
            <label>Ekstrakurikuler <span class="req">*</span></label>
            <select name="ekstrakurikuler_id" required>
              <option value="">-- Pilih Ekskul --</option>
              <?php foreach ($ekskulList as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label>Nama Siswa <span class="req">*</span></label>
              <input type="text" name="nama_siswa" placeholder="Nama lengkap" required />
            </div>
            <div class="form-group">
              <label>NIS <span class="req">*</span></label>
              <input type="text" name="nis" placeholder="Nomor Induk Siswa" required />
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label>Kelas <span class="req">*</span></label>
              <select name="kelas" required>
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
              <label>Status Awal</label>
              <select name="status">
                <option value="menunggu">⏳ Menunggu</option>
                <option value="diterima">✅ Diterima</option>
                <option value="ditolak">❌ Ditolak</option>
              </select>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label>No. HP</label>
              <input type="tel" name="no_hp" placeholder="08xxxxxxxxxx" />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="siswa@email.com" />
            </div>
          </div>
          <div class="form-group">
            <label>Alasan Mendaftar</label>
            <textarea name="alasan" placeholder="Tuliskan alasan atau motivasi mendaftar..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('modal-tambah')">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Detail Pendaftaran -->
  <?php if ($detail): ?>
    <div class="modal-backdrop open" id="modal-detail">
      <div class="modal-box">
        <div class="modal-header">
          <h3><i class="fas fa-user" style="color:var(--primary-mid);margin-right:.4rem;"></i> Detail Pendaftaran</h3>
          <a href="pendaftaran.php<?= $fSearch || $fStatus || $fEkskul ? '?' . http_build_query(['q' => $fSearch, 'status' => $fStatus, 'ekskul' => $fEkskul]) : '' ?>" class="modal-close"><i class="fas fa-times"></i></a>
        </div>
        <div class="modal-body">
          <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:var(--primary-light);border-radius:var(--radius-sm);">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--primary-mid);color:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;flex-shrink:0;">
              <?= strtoupper(substr($detail['nama_siswa'], 0, 1)) ?>
            </div>
            <div>
              <div style="font-weight:700;font-size:1rem;"><?= htmlspecialchars($detail['nama_siswa']) ?></div>
              <div style="font-size:.82rem;color:var(--gray-500);">Kelas <?= htmlspecialchars($detail['kelas']) ?> &bull; NIS: <?= htmlspecialchars($detail['nis']) ?></div>
              <span class="badge status-<?= htmlspecialchars($detail['status']) ?>" style="margin-top:.3rem;display:inline-flex;"><?= htmlspecialchars($detail['status']) ?></span>
            </div>
          </div>

          <div class="detail-grid">
            <div class="detail-row">
              <div class="detail-label">Ekskul Dipilih</div>
              <div class="detail-value"><?= htmlspecialchars($detail['nama_ekskul'] ?? '-') ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Kategori Ekskul</div>
              <div class="detail-value"><span class="badge badge-blue"><?= htmlspecialchars($detail['kat_ekskul'] ?? '-') ?></span></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Jadwal Ekskul</div>
              <div class="detail-value"><?= htmlspecialchars($detail['jadwal'] ?? '-') ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Tempat Ekskul</div>
              <div class="detail-value"><?= htmlspecialchars($detail['tempat'] ?? '-') ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">No. HP</div>
              <div class="detail-value"><?= htmlspecialchars($detail['no_hp'] ?? '-') ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Email</div>
              <div class="detail-value"><?= htmlspecialchars($detail['email'] ?? '-') ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Tanggal Daftar</div>
              <div class="detail-value"><?= date('d F Y, H:i', strtotime($detail['created_at'])) ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Status</div>
              <div class="detail-value"><span class="badge status-<?= htmlspecialchars($detail['status']) ?>"><?= htmlspecialchars($detail['status']) ?></span></div>
            </div>
            <?php if ($detail['alasan']): ?>
              <div class="detail-row detail-full">
                <div class="detail-label">Alasan Mendaftar</div>
                <div class="detail-value" style="background:var(--gray-50);padding:.75rem;border-radius:var(--radius-sm);line-height:1.6;font-style:italic;">"<?= htmlspecialchars($detail['alasan']) ?>"</div>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <!-- Quick status update -->
          <form method="POST" style="display:flex;gap:.5rem;align-items:center;flex:1;" autocomplete="off">
              <?= csrfField() ?>
            <input type="hidden" name="action" value="update_status" />
            <input type="hidden" name="id" value="<?= $detail['id'] ?>" />
            <select name="status" style="width:auto;font-size:.82rem;">
              <option value="menunggu" <?= $detail['status'] === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
              <option value="diterima" <?= $detail['status'] === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
              <option value="ditolak" <?= $detail['status'] === 'ditolak' ? 'selected' : '' ?>>❌ Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Update Status</button>
          </form>
          <form method="POST" onsubmit="return confirm('Hapus data ini?')" autocomplete="off">
              <?= csrfField() ?>
            <input type="hidden" name="action" value="hapus" />
            <input type="hidden" name="id" value="<?= $detail['id'] ?>" />
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <script src="assets/admin.js"></script>
</body>

</html>