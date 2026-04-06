<?php
// ============================================================
// agenda.php — Agenda Kegiatan
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola agenda/jadwal kegiatan sekolah.
// Data ditampilkan per bulan & tahun dengan filter navigasi.
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel agenda
//   hapus       -> delete by id
//   GET ?edit   -> pre-fill form dengan data existing
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = ''; // Variabel pesan sukses dan error untuk flash message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf(); // Tolak request jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';
  if ($action === 'tambah' || $action === 'edit') {
    $judul = trim($_POST['judul'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    $tgl = $_POST['tanggal_mulai'] ?? '';
    $tgl2 = $_POST['tanggal_selesai'] ?: null;
    $lok = trim($_POST['lokasi'] ?? '');
    $kat = $_POST['kategori'] ?? 'umum';
    $warna = $_POST['warna'] ?? '#1a6b3c';
    $selesai = isset($_POST['is_selesai']) ? 1 : 0;
    $org_id  = ($_POST['organisasi_id'] ?? '') !== '' ? (int)$_POST['organisasi_id'] : null;
    $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;
    // Validasi field wajib sebelum menyimpan ke database
    if (!$judul || !$tgl) {
      $err = 'Judul dan tanggal wajib diisi.';
    } elseif ($action === 'tambah') {
      $pdo->prepare("INSERT INTO agenda (judul,deskripsi,tanggal_mulai,tanggal_selesai,lokasi,kategori,warna,is_selesai,organisasi_id,ekskul_id) VALUES (?,?,?,?,?,?,?,?,?,?)")
        ->execute([$judul, $desk, $tgl, $tgl2, $lok, $kat, $warna, $selesai, $org_id, $ekskul_id]);
      // PRG (Post-Redirect-Get): redirect setelah POST agar tidak double-submit saat refresh
      header('Location: agenda.php?msg=tambah');
      exit;
    } else {
      $id = (int)$_POST['id'];
      $pdo->prepare("UPDATE agenda SET judul=?,deskripsi=?,tanggal_mulai=?,tanggal_selesai=?,lokasi=?,kategori=?,warna=?,is_selesai=?,organisasi_id=?,ekskul_id=? WHERE id=?")
        ->execute([$judul, $desk, $tgl, $tgl2, $lok, $kat, $warna, $selesai, $org_id, $ekskul_id, $id]);
      header('Location: agenda.php?msg=edit');
      exit;
    }
  } elseif ($action === 'hapus') {
    $pdo->prepare("DELETE FROM agenda WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: agenda.php?msg=hapus');
    exit;
  }
}
// Konversi parameter ?msg= dari redirect menjadi teks pesan yang ditampilkan
if (isset($_GET['msg'])) {
  $msgMap = ['tambah' => 'Agenda berhasil ditambahkan!', 'edit' => 'Agenda diperbarui!', 'hapus' => 'Agenda dihapus.'];
  $msg = $msgMap[$_GET['msg']] ?? '';
}
// Jika ada parameter ?edit=ID, ambil data agenda untuk pre-fill form edit
$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM agenda WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}
// Ambil filter bulan & tahun dari query string, default bulan/tahun saat ini
$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));
$rows = $pdo->prepare("SELECT * FROM agenda WHERE MONTH(tanggal_mulai)=? AND YEAR(tanggal_mulai)=? ORDER BY tanggal_mulai ASC");
$rows->execute([$bulan, $tahun]);
$rows = $rows->fetchAll(PDO::FETCH_ASSOC);
$months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$isEdit = !!$edit;
$orgList   = $pdo->query("SELECT id, nama FROM organisasi ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
$ekskulList = $pdo->query("SELECT id, nama FROM ekstrakurikuler ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Agenda — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-calendar-alt"></i> Agenda Kegiatan</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Agenda</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit): ?><a href="agenda.php" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Tambah Baru</a><?php endif; ?>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div><?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>
    <div class="page-content">
      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>
      <div class="two-col" style="align-items:start;">

        <div class="form-section anim-fade-up">
          <div class="form-section-header">
            <div class="form-section-header-icon"><i class="fas fa-<?= $isEdit ? 'pen' : 'calendar-plus' ?>"></i></div>
            <div>
              <h3><?= $isEdit ? 'Edit Agenda' : 'Tambah Agenda Baru' ?></h3>
              <p>Jadwal kegiatan sekolah</p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" id="form-agenda" autocomplete="off">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
              <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>
              <div class="form-group">
                <label>Judul Agenda <span class="req">*</span></label>
                <input type="text" name="judul" value="<?= htmlspecialchars($edit['judul'] ?? '') ?>" placeholder="Contoh: Lomba Cerdas Cermat" required />
              </div>
              <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" placeholder="Keterangan singkat kegiatan..."><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group"><label>Tanggal Mulai <span class="req">*</span></label><input type="date" name="tanggal_mulai" value="<?= substr($edit['tanggal_mulai'] ?? '', 0, 10) ?>" required /></div>
                <div class="form-group"><label>Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="<?= substr($edit['tanggal_selesai'] ?? '', 0, 10) ?>" /></div>
              </div>
              <div class="form-group"><label>Lokasi</label><input type="text" name="lokasi" value="<?= htmlspecialchars($edit['lokasi'] ?? '') ?>" placeholder="Contoh: Aula MAN 1 Bangka" /></div>
              <div style="display:grid;grid-template-columns:1fr 80px;gap:.85rem;align-items:end;">
                <div class="form-group">
                  <label>Kategori</label>
                  <select name="kategori"><?php foreach (['umum' => 'Umum', 'lomba' => 'Lomba', 'ekskul' => 'Ekskul', 'organisasi' => 'Organisasi', 'seminar' => 'Seminar', 'keagamaan' => 'Keagamaan'] as $v => $l): ?>
                      <option value="<?= $v ?>" <?= ($edit['kategori'] ?? 'umum') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group"><label>Warna</label><input type="color" name="warna" value="<?= htmlspecialchars($edit['warna'] ?? '#1a6b3c') ?>" style="width:100%;" /></div>
              </div>
              <div class="form-group">
                <label>Organisasi <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                <select name="organisasi_id">
                  <option value="">— Agenda Umum Sekolah —</option>
                  <?php foreach ($orgList as $o): ?>
                    <option value="<?= $o['id'] ?>" <?= ($edit['organisasi_id'] ?? null) == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Ekstrakurikuler <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                <select name="ekskul_id">
                  <option value="">— Bukan Kegiatan Ekskul —</option>
                  <?php foreach ($ekskulList as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= ($edit['ekskul_id'] ?? null) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="highlight-box <?= ($edit['is_selesai'] ?? 0) ? 'checked' : '' ?>" id="selesai-box" style="cursor:pointer;">
                  <input type="checkbox" name="is_selesai" id="selesai_cb"
                    <?= ($edit['is_selesai'] ?? 0) ? 'checked' : '' ?>
                    style="width:18px;height:18px;flex-shrink:0;accent-color:var(--primary-mid);cursor:pointer;"
                    onchange="this.closest('.highlight-box').classList.toggle('checked',this.checked)" />
                  <span class="hb-icon">✅</span>
                  <div class="hb-text">
                    <h4>Tandai Selesai</h4>
                    <p>Kegiatan ini sudah dilaksanakan</p>
                  </div>
                </label>
              </div>
            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="form-agenda" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?></button>
            <?php if ($isEdit): ?><a href="agenda.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a><?php else: ?><button type="reset" form="form-agenda" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</button><?php endif; ?>
          </div>
        </div>

        <div class="admin-card anim-fade-up anim-delay-2">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon"><i class="fas fa-calendar-check"></i></div>
              <div>
                <div class="card-header-title">Agenda <?= $months[$bulan] ?> <?= $tahun ?></div>
                <div class="card-header-sub"><?= count($rows) ?> kegiatan</div>
              </div>
            </div>
          </div>
          <!-- Filter bulan -->
          <div style="padding:.75rem 1rem;border-bottom:1px solid var(--gray-100);">
            <form method="GET" style="display:flex;gap:.4rem;flex-wrap:wrap;" autocomplete="off">
              <select name="bulan" style="flex:1;" onchange="this.form.submit()"><?php for ($m = 1; $m <= 12; $m++): ?><option value="<?= $m ?>" <?= $m === $bulan ? 'selected' : '' ?>><?= $months[$m] ?></option><?php endfor; ?></select>
              <select name="tahun" style="width:90px;" onchange="this.form.submit()"><?php for ($y = date('Y') + 1; $y >= date('Y') - 3; $y--): ?><option value="<?= $y ?>" <?= $y === $tahun ? 'selected' : '' ?>><?= $y ?></option><?php endfor; ?></select>
              <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i></button>
            </form>
          </div>
          <div style="max-height:580px;overflow-y:auto;padding:1rem;">
            <?php if ($rows): foreach ($rows as $r): ?>
                <div class="data-item">
                  <div class="data-item-icon" style="background:<?= htmlspecialchars($r['warna']) ?>22;color:<?= htmlspecialchars($r['warna']) ?>;border-left:3px solid <?= htmlspecialchars($r['warna']) ?>;">
                    <i class="fas fa-calendar-day"></i>
                  </div>
                  <div class="data-item-body">
                    <div class="data-item-title"><?= htmlspecialchars($r['judul']) ?></div>
                    <div class="data-item-sub">
                      <?= date('d M Y', strtotime($r['tanggal_mulai'])) ?>
                      <?= $r['lokasi'] ? ' &bull; ' . htmlspecialchars($r['lokasi']) : '' ?>
                      <span class="badge badge-blue"><?= htmlspecialchars($r['kategori']) ?></span>
                      <?php if ($r['is_selesai']): ?><span class="badge badge-green">✓ Selesai</span><?php endif; ?>
                    </div>
                  </div>
                  <div class="data-item-actions">
                    <a href="?edit=<?= $r['id'] ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="btn btn-outline btn-icon btn-xs"><i class="fas fa-pen"></i></a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus agenda?')" autocomplete="off">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="hapus" /><input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button class="btn btn-ghost btn-icon btn-xs" style="color:var(--red)"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </div>
              <?php endforeach;
            else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">📅</div>
                <h4>Tidak Ada Agenda</h4>
                <p>Belum ada kegiatan pada bulan ini.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script src="assets/admin.js"></script>
</body>

</html>