<?php
// ============================================================
// pengumuman.php — Pengumuman
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola pengumuman sekolah.
// Pengumuman dapat ditandai is_highlight untuk tampil di beranda.
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel pengumuman
//   hapus       -> delete by id
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'tambah' || $action === 'edit') {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $kat = $_POST['kategori'] ?? 'umum';
    $tp = $_POST['tanggal_publish'] ?: date('Y-m-d');
    $tb = $_POST['tanggal_berakhir'] ?: null;
    $hl = isset($_POST['is_highlight']) ? 1 : 0;
    $org_id = ($_POST['organisasi_id'] ?? '') !== '' ? (int)$_POST['organisasi_id'] : null;
    if (!$judul || !$isi) {
      $err = 'Judul dan isi wajib diisi.';
    } elseif ($action === 'tambah') {
      $pdo->prepare("INSERT INTO pengumuman (judul,isi,kategori,tanggal_publish,tanggal_berakhir,is_highlight,organisasi_id) VALUES (?,?,?,?,?,?,?)")
        ->execute([$judul, $isi, $kat, $tp, $tb, $hl, $org_id]);
      header('Location: pengumuman.php?msg=tambah');
      exit;
    } else {
      $id = (int)$_POST['id'];
      $pdo->prepare("UPDATE pengumuman SET judul=?,isi=?,kategori=?,tanggal_publish=?,tanggal_berakhir=?,is_highlight=?,organisasi_id=? WHERE id=?")
        ->execute([$judul, $isi, $kat, $tp, $tb, $hl, $org_id, $id]);
      header('Location: pengumuman.php?msg=edit');
      exit;
    }
  } elseif ($action === 'hapus') {
    $pdo->prepare("DELETE FROM pengumuman WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: pengumuman.php?msg=hapus');
    exit;
  }
}
if (isset($_GET['msg'])) {
  $msgMap = ['tambah' => 'Pengumuman berhasil ditambahkan!', 'edit' => 'Pengumuman diperbarui!', 'hapus' => 'Pengumuman dihapus.'];
  $msg = $msgMap[$_GET['msg']] ?? '';
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM pengumuman WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}
$rows = $pdo->query("SELECT * FROM pengumuman ORDER BY tanggal_publish DESC LIMIT 40")->fetchAll(PDO::FETCH_ASSOC);
$isEdit = !!$edit;
$orgList = $pdo->query("SELECT id, nama FROM organisasi ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Pengumuman — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-bell"></i> Pengumuman</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Pengumuman</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit): ?>
          <a href="pengumuman.php" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Tambah Baru</a>
        <?php endif; ?>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div>
          <?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">

      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

      <div class="two-col" style="align-items:start;">

        <!-- FORM PANEL -->
        <div class="form-section anim-fade-up">
          <div class="form-section-header">
            <div class="form-section-header-icon">
              <i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i>
            </div>
            <div>
              <h3><?= $isEdit ? 'Edit Pengumuman' : 'Tambah Pengumuman Baru' ?></h3>
              <p><?= $isEdit ? 'Perbarui data pengumuman yang ada' : 'Isi form untuk menambahkan pengumuman' ?></p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" id="form-pengumuman" autocomplete="off">
              <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
              <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>

              <div class="form-group">
                <label>Judul Pengumuman <span class="req">*</span></label>
                <input type="text" name="judul" value="<?= htmlspecialchars($edit['judul'] ?? '') ?>"
                  placeholder="Contoh: Pendaftaran Lomba OSN 2026" required />
              </div>

              <div class="form-group">
                <label>Isi Pengumuman <span class="req">*</span></label>
                <textarea name="isi" placeholder="Tulis isi pengumuman secara lengkap..." required
                  style="min-height:130px;"><?= htmlspecialchars($edit['isi'] ?? '') ?></textarea>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Kategori</label>
                  <select name="kategori">
                    <?php $cats = ['umum' => 'Umum', 'lomba' => 'Lomba', 'ekskul' => 'Ekskul', 'keagamaan' => 'Keagamaan', 'akademik' => 'Akademik', 'libur' => 'Libur/Hari Besar'];
                    foreach ($cats as $v => $l): ?>
                      <option value="<?= $v ?>" <?= ($edit['kategori'] ?? 'umum') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Tanggal Publish</label>
                  <input type="date" name="tanggal_publish" value="<?= $edit['tanggal_publish'] ?? date('Y-m-d') ?>" />
                </div>
              </div>

              <div class="form-group">
                <label>Tanggal Berakhir <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                <input type="date" name="tanggal_berakhir" value="<?= htmlspecialchars($edit['tanggal_berakhir'] ?? '') ?>" />
              </div>

              <div class="form-group">
                <label>Organisasi Pengirim <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                <select name="organisasi_id">
                  <option value="">— Dari Pihak Sekolah —</option>
                  <?php foreach ($orgList as $o): ?>
                    <option value="<?= $o['id'] ?>" <?= ($edit['organisasi_id'] ?? null) == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Highlight toggle -->
              <div class="form-group">
                <label>Tampilan Beranda</label>
                <div class="highlight-box <?= ($edit['is_highlight'] ?? 0) ? 'checked' : '' ?>" id="hl-box">
                  <input type="checkbox" name="is_highlight" id="hl_cb"
                    <?= ($edit['is_highlight'] ?? 0) ? 'checked' : '' ?>
                    style="position:absolute;opacity:0;pointer-events:none;" />
                  <span class="hb-icon">⭐</span>
                  <div class="hb-text">
                    <h4>Tampilkan sebagai Highlight</h4>
                    <p>Pengumuman akan muncul di bagian utama halaman beranda</p>
                  </div>
                  <i class="fas fa-check" id="hl-check" style="color:var(--gold);margin-left:auto;<?= ($edit['is_highlight'] ?? 0) ? '' : 'display:none' ?>"></i>
                </div>
              </div>
            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="form-pengumuman" class="btn btn-primary">
              <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?> Pengumuman
            </button>
            <?php if ($isEdit): ?>
              <a href="pengumuman.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
            <?php else: ?>
              <button type="reset" form="form-pengumuman" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</button>
            <?php endif; ?>
            <span style="margin-left:auto;font-size:.75rem;color:var(--gray-400);">* wajib diisi</span>
          </div>
        </div>

        <!-- LIST PANEL -->
        <div class="admin-card anim-fade-up anim-delay-2">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon"><i class="fas fa-list"></i></div>
              <div>
                <div class="card-header-title">Daftar Pengumuman</div>
                <div class="card-header-sub"><?= count($rows) ?> pengumuman tersimpan</div>
              </div>
            </div>
          </div>
          <div style="max-height:640px;overflow-y:auto;padding:1rem;">
            <?php if ($rows): ?>
              <?php foreach ($rows as $r): ?>
                <div class="data-item">
                  <div class="data-item-icon" style="<?= $r['is_highlight'] ? 'background:var(--gold-light);color:var(--gold-dark);' : '' ?>">
                    <i class="fas fa-<?= $r['is_highlight'] ? 'star' : 'bell' ?>"></i>
                  </div>
                  <div class="data-item-body">
                    <div class="data-item-title"><?= htmlspecialchars($r['judul']) ?></div>
                    <div class="data-item-sub">
                      <?= date('d M Y', strtotime($r['tanggal_publish'])) ?> &bull;
                      <span class="badge badge-<?= $r['kategori'] === 'lomba' ? 'red' : ($r['kategori'] === 'keagamaan' ? 'teal' : 'blue') ?>"><?= htmlspecialchars($r['kategori']) ?></span>
                      <?php if ($r['is_highlight']): ?><span class="badge badge-gold">⭐ Highlight</span><?php endif; ?>
                    </div>
                  </div>
                  <div class="data-item-actions">
                    <a href="?edit=<?= $r['id'] ?>" class="btn btn-outline btn-icon btn-xs" title="Edit"><i class="fas fa-pen"></i></a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus pengumuman ini?')" autocomplete="off">
                      <input type="hidden" name="action" value="hapus" />
                      <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button type="submit" class="btn btn-ghost btn-icon btn-xs" style="color:var(--red);" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">📢</div>
                <h4>Belum Ada Pengumuman</h4>
                <p>Tambahkan pengumuman pertama melalui form di sebelah kiri.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /two-col -->

      <!-- HIGHLIGHT INIT — MUST stay inside .page-content so SPA reinitPageScripts() re-runs it on every navigation -->
      <script>
        (function initHL() {
          var box = document.getElementById('hl-box');
          if (!box) return;
          /* Clone to wipe any stale listeners attached by previous SPA load */
          var fresh = box.cloneNode(true);
          box.parentNode.replaceChild(fresh, box);
          fresh.style.cursor = 'pointer';
          fresh.addEventListener('click', function() {
            var cb  = document.getElementById('hl_cb');
            var chk = document.getElementById('hl-check');
            var next = !cb.checked;
            cb.checked = next;
            fresh.classList.toggle('checked', next);
            chk.style.display = next ? '' : 'none';
          });
        })();
      </script>

    </div><!-- /page-content -->
  </main>

  <script src="assets/admin.js"></script>
</body>

</html>