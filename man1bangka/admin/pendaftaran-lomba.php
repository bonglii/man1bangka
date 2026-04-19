<?php
// ============================================================
// pendaftaran-lomba.php — Pendaftaran Lomba
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman manajemen pendaftaran lomba dari halaman publik.
// Admin dapat melihat, update status, menambah manual, dan menghapus
// data pendaftaran lomba siswa.
//
// Aksi POST yang ditangani:
//   update_status      -> ubah status satu data (menunggu/diterima/ditolak)
//   hapus              -> delete by id
//   update_status_bulk -> ubah status banyak data sekaligus
//   hapus_bulk         -> delete banyak data sekaligus
//   tambah             -> tambah pendaftaran lomba manual oleh admin
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
      header('Location: pendaftaran-lomba.php?err=status_invalid');
      exit;
    }
    $pdo->prepare("UPDATE pendaftaran_lomba SET status=? WHERE id=?")->execute([$status, $id]);
    header('Location: pendaftaran-lomba.php?msg=status_updated');
    exit;
  } elseif ($action === 'hapus') {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM pendaftaran_lomba WHERE id=?")->execute([$id]);
    header('Location: pendaftaran-lomba.php?msg=hapus');
    exit;
  } elseif ($action === 'update_status_bulk') {
    $ids    = $_POST['id'] ?? [];
    $status = $_POST['status_bulk'] ?? 'menunggu';
    if (!in_array($status, ['menunggu', 'diterima', 'ditolak'])) {
      header('Location: pendaftaran-lomba.php?err=status_invalid');
      exit;
    }
    if ($ids) {
      $ph   = implode(',', array_fill(0, count($ids), '?'));
      $vals = array_merge([$status], array_map('intval', $ids));
      $pdo->prepare("UPDATE pendaftaran_lomba SET status=? WHERE id IN ($ph)")->execute($vals);
    }
    header('Location: pendaftaran-lomba.php?msg=bulk_updated&n=' . count($ids));
    exit;
  } elseif ($action === 'hapus_bulk') {
    $ids = $_POST['ids'] ?? [];
    if ($ids) {
      $ph = implode(',', array_fill(0, count($ids), '?'));
      $pdo->prepare("DELETE FROM pendaftaran_lomba WHERE id IN ($ph)")->execute(array_map('intval', $ids));
    }
    header('Location: pendaftaran-lomba.php?msg=bulk_hapus&n=' . count($ids));
    exit;
  } elseif ($action === 'tambah') {
    $nama       = trim($_POST['nama']       ?? '');
    $kelas      = trim($_POST['kelas']      ?? '');
    $nis        = trim($_POST['nis']        ?? '');
    $lomba_id   = !empty($_POST['lomba_id']) ? (int)$_POST['lomba_id'] : null;
    $nama_lomba = trim($_POST['nama_lomba'] ?? '');
    $tingkat    = trim($_POST['tingkat']    ?? '');
    $no_hp      = trim($_POST['no_hp']      ?? '');
    $status     = $_POST['status'] ?? 'menunggu';

    // Jika admin memilih lomba dari dropdown master, pakai nama lomba
    // dari tabel lomba (lebih otoritatif) & kosongkan kebutuhan ketik manual.
    if ($lomba_id) {
      try {
        $l = $pdo->prepare("SELECT nama, kategori FROM lomba WHERE id=?");
        $l->execute([$lomba_id]);
        if ($lombaRow = $l->fetch(PDO::FETCH_ASSOC)) {
          $nama_lomba = $lombaRow['nama'];
          if (!$tingkat) $tingkat = $lombaRow['kategori'];
        } else {
          $lomba_id = null; // id tidak valid, abaikan
        }
      } catch (Exception $e) {
        // tabel `lomba` belum ada — abaikan saja, lanjut simpan free text
        $lomba_id = null;
      }
    }

    if (!in_array($status, ['menunggu', 'diterima', 'ditolak'])) {
      $status = 'menunggu';
    }
    if (!$nama || !$kelas || !$nis || !$nama_lomba) {
      header('Location: pendaftaran-lomba.php?err=data_kurang');
      exit;
    }
    // Cegah pendaftar ganda untuk lomba yang sama (NIS + nama_lomba)
    $cek = $pdo->prepare("SELECT id FROM pendaftaran_lomba WHERE nis=? AND nama_lomba=?");
    $cek->execute([$nis, $nama_lomba]);
    if ($cek->fetch()) {
      header('Location: pendaftaran-lomba.php?err=sudah_daftar');
      exit;
    }
    // INSERT: coba dengan kolom lomba_id; jika belum ada kolom (migrasi belum
    // dijalankan), fallback ke query tanpa lomba_id.
    try {
      $pdo->prepare("INSERT INTO pendaftaran_lomba (lomba_id,nama,kelas,nis,nama_lomba,tingkat,no_hp,status) VALUES (?,?,?,?,?,?,?,?)")
        ->execute([$lomba_id, $nama, $kelas, $nis, $nama_lomba, $tingkat, $no_hp, $status]);
    } catch (Exception $e) {
      $pdo->prepare("INSERT INTO pendaftaran_lomba (nama,kelas,nis,nama_lomba,tingkat,no_hp,status) VALUES (?,?,?,?,?,?,?)")
        ->execute([$nama, $kelas, $nis, $nama_lomba, $tingkat, $no_hp, $status]);
    }
    header('Location: pendaftaran-lomba.php?msg=tambah');
    exit;
  }
}

// Konversi parameter ?msg= & ?err= dari redirect ke pesan tampilan
$msg = $err = '';
$msgMap = [
  'status_updated' => 'Status pendaftaran lomba diperbarui!',
  'hapus'          => 'Data pendaftaran lomba dihapus.',
  'bulk_updated'   => ($_GET['n'] ?? 0) . ' pendaftaran diperbarui.',
  'bulk_hapus'     => ($_GET['n'] ?? 0) . ' data berhasil dihapus.',
  'tambah'         => 'Pendaftaran lomba berhasil ditambahkan!',
];
$errMap = [
  'status_invalid' => 'Status tidak valid.',
  'data_kurang'    => 'Nama, kelas, NIS, dan nama lomba wajib diisi.',
  'sudah_daftar'   => 'Siswa dengan NIS tersebut sudah terdaftar di lomba ini.',
];
if (isset($_GET['msg'])) $msg = $msgMap[$_GET['msg']] ?? '';
if (isset($_GET['err'])) $err = $errMap[$_GET['err']] ?? '';

// Filters
$fTingkat = trim($_GET['tingkat'] ?? '');
$fStatus  = $_GET['status'] ?? '';
$fSearch  = trim($_GET['q'] ?? '');

// Filter tambahan: filter berdasarkan lomba master (FK)
$fLombaId = (int)($_GET['lomba_id'] ?? 0);

// Coba JOIN dengan tabel lomba master. Fallback ke SELECT biasa
// bila tabel/kolom belum ada (migrasi belum dijalankan).
$hasLombaFK = false;
try {
  $pdo->query("SELECT lomba_id FROM pendaftaran_lomba LIMIT 1");
  $pdo->query("SELECT id FROM lomba LIMIT 1");
  $hasLombaFK = true;
} catch (Exception $e) {
  $hasLombaFK = false;
}

$sql = $hasLombaFK
  ? "SELECT p.*, l.nama AS lomba_master_nama, l.tingkat AS lomba_master_tingkat, l.status AS lomba_master_status
     FROM pendaftaran_lomba p LEFT JOIN lomba l ON p.lomba_id=l.id WHERE 1=1"
  : "SELECT * FROM pendaftaran_lomba WHERE 1=1";

$params = [];
if ($fTingkat) {
  $sql     .= $hasLombaFK ? " AND p.tingkat=?" : " AND tingkat=?";
  $params[] = $fTingkat;
}
if ($fStatus) {
  $sql     .= $hasLombaFK ? " AND p.status=?" : " AND status=?";
  $params[] = $fStatus;
}
if ($fLombaId && $hasLombaFK) {
  $sql     .= " AND p.lomba_id=?";
  $params[] = $fLombaId;
}
if ($fSearch) {
  $sql   .= $hasLombaFK
    ? " AND (p.nama LIKE ? OR p.nis LIKE ? OR p.kelas LIKE ? OR p.nama_lomba LIKE ?)"
    : " AND (nama LIKE ? OR nis LIKE ? OR kelas LIKE ? OR nama_lomba LIKE ?)";
  $params = array_merge($params, ["%$fSearch%", "%$fSearch%", "%$fSearch%", "%$fSearch%"]);
}
$sql .= $hasLombaFK ? " ORDER BY p.created_at DESC" : " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalAll      = $pdo->query("SELECT COUNT(*) FROM pendaftaran_lomba")->fetchColumn();
$totalMenunggu = $pdo->query("SELECT COUNT(*) FROM pendaftaran_lomba WHERE status='menunggu'")->fetchColumn();
$totalDiterima = $pdo->query("SELECT COUNT(*) FROM pendaftaran_lomba WHERE status='diterima'")->fetchColumn();
$totalDitolak  = $pdo->query("SELECT COUNT(*) FROM pendaftaran_lomba WHERE status='ditolak'")->fetchColumn();

// Daftar tingkat/kategori yang tersedia untuk dropdown filter — diambil dari data
$tingkatList = $pdo->query("SELECT DISTINCT tingkat FROM pendaftaran_lomba WHERE tingkat IS NOT NULL AND tingkat!='' ORDER BY tingkat")->fetchAll(PDO::FETCH_COLUMN);

// Opsi tingkat default (sesuai form publik di halaman/pendaftaran.php tab Lomba)
$tingkatOptions = ['Matematika', 'Fisika', 'Kimia', 'Biologi', 'Informatika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Seni', 'Olahraga', 'Lainnya'];

// Daftar lomba master (untuk dropdown & filter). Hanya yang akan_datang/berlangsung.
$lombaMasterList = [];
if ($hasLombaFK) {
  try {
    $lombaMasterList = $pdo->query(
      "SELECT id, nama, tingkat, status FROM lomba
       WHERE status IN ('akan_datang','berlangsung') ORDER BY tanggal_mulai ASC, nama ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) { /* abaikan */ }
}

// Info lomba master yang sedang difilter (untuk tampil di header)
$lombaFilterInfo = null;
if ($fLombaId && $hasLombaFK) {
  try {
    $s = $pdo->prepare("SELECT * FROM lomba WHERE id=?");
    $s->execute([$fLombaId]);
    $lombaFilterInfo = $s->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) { /* abaikan */ }
}

// Detail view
$detail = null;
if (isset($_GET['detail'])) {
  $d = $hasLombaFK
    ? $pdo->prepare("SELECT p.*, l.nama AS lomba_master_nama, l.tingkat AS lomba_master_tingkat FROM pendaftaran_lomba p LEFT JOIN lomba l ON p.lomba_id=l.id WHERE p.id=?")
    : $pdo->prepare("SELECT * FROM pendaftaran_lomba WHERE id=?");
  $d->execute([(int)$_GET['detail']]);
  $detail = $d->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Pendaftaran Lomba — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-trophy"></i> Pendaftaran Lomba</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Pendaftaran Lomba</div>
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

      <!-- ============================================================
           PANDUAN / WARNING — Cara Menerima Pendaftar Lomba
           Ditampilkan sebagai <details> agar bisa dibuka/tutup admin.
           Style amber mengikuti pola note di karya.php & prestasi.php.
           ============================================================ -->
      <details open style="margin-bottom:1.25rem;border:1.5px solid #fde68a;background:#fffbeb;border-radius:var(--radius-sm,10px);overflow:hidden;">
        <summary style="cursor:pointer;padding:.85rem 1rem;display:flex;align-items:center;gap:.6rem;font-weight:700;color:#92400e;list-style:none;user-select:none;">
          <i class="fas fa-exclamation-triangle" style="color:#d97706;"></i>
          <span>Panduan Sebelum Menerima Pendaftar Lomba</span>
          <span style="margin-left:auto;font-size:.72rem;font-weight:500;color:#b45309;opacity:.85;">Klik untuk buka/tutup</span>
        </summary>
        <div style="padding:0 1.25rem 1rem 1.25rem;color:#78350f;font-size:.85rem;line-height:1.65;">
          <div style="padding-top:.25rem;border-top:1px dashed #fcd34d;margin-top:.1rem;"></div>

          <p style="margin:.75rem 0 .4rem 0;"><strong style="color:#92400e;">🔍 Langkah verifikasi sebelum mengubah status ke "Diterima":</strong></p>
          <ol style="margin:.3rem 0 .75rem 1.3rem;padding:0;">
            <li>Cek identitas siswa — pastikan <b>nama, NIS, dan kelas</b> sesuai data siswa aktif sekolah.</li>
            <li>Hubungi siswa via <b>No. HP</b> yang tercantum untuk konfirmasi kesediaan mengikuti lomba.</li>
            <li>Pastikan siswa <b>tidak terdaftar di lomba lain yang bentrok jadwalnya</b> — gunakan fitur pencarian (kolom Cari) untuk mengeceknya.</li>
            <li>Koordinasikan dengan <b>guru pembina</b> atau wali kelas jika diperlukan izin khusus.</li>
          </ol>

          <p style="margin:.85rem 0 .4rem 0;"><strong style="color:#b91c1c;">⚠️ Perhatian khusus:</strong></p>
          <ul style="margin:.3rem 0 .2rem 1.3rem;padding:0;">
            <li>Status <b>"Diterima"</b> = komitmen keikutsertaan resmi. Pastikan siswa sudah konfirmasi <i>sebelum</i> mengubah status.</li>
            <li>Aksi <b>Bulk</b> (Terima/Tolak/Hapus massal) memengaruhi banyak data sekaligus — periksa baik-baik centang sebelum klik.</li>
            <li>Data yang <b>dihapus tidak dapat dikembalikan</b>. Gunakan status <b>"Ditolak"</b> jika hanya ingin menolak tanpa menghapus histori.</li>
            <li>Pendaftar yang statusnya masih <b>"Menunggu"</b> muncul di badge menu sidebar — segera proses agar tidak menumpuk.</li>
          </ul>
        </div>
      </details>

      <!-- STATS -->
      <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem;">
        <div class="stat-card stat-purple">
          <div class="stat-icon"><i class="fas fa-trophy"></i></div>
          <div>
            <div class="stat-num"><?= $totalAll ?></div>
            <div class="stat-label">Total Pendaftar Lomba</div>
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

      <?php if ($lombaFilterInfo): ?>
        <!-- Banner info: sedang memfilter pendaftar untuk 1 lomba spesifik -->
        <div class="alert" style="background:#eef2ff;border:1.5px solid #c7d2fe;color:#3730a3;padding:.85rem 1rem;display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;border-radius:var(--radius-sm,10px);">
          <i class="fas fa-filter" style="color:#4f46e5;"></i>
          <div style="flex:1;">
            <div style="font-weight:700;font-size:.88rem;">Menampilkan pendaftar untuk lomba:</div>
            <div style="font-size:.82rem;margin-top:.15rem;"><?= htmlspecialchars($lombaFilterInfo['nama']) ?> <span style="color:#6366f1;">&bull; <?= htmlspecialchars(ucfirst($lombaFilterInfo['tingkat'])) ?></span></div>
          </div>
          <a href="pendaftaran-lomba.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Hapus Filter</a>
        </div>
      <?php endif; ?>

      <div class="admin-card">
        <!-- FILTER BAR -->
        <div class="card-header" style="flex-wrap:wrap;gap:.75rem;">
          <div class="card-header-left">
            <div class="card-header-icon"><i class="fas fa-filter"></i></div>
            <div>
              <div class="card-header-title">Data Pendaftaran Lomba</div>
              <div class="card-header-sub"><?= count($rows) ?> data ditemukan</div>
            </div>
          </div>
          <form method="GET" id="filter-form" action="pendaftaran-lomba.php" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-left:auto;" autocomplete="off" onsubmit="return true;">
            <div class="search-bar" style="min-width:180px;">
              <i class="fas fa-search"></i>
              <input type="text" name="q" id="filter-q" placeholder="Cari nama / NIS / kelas / lomba..." value="<?= htmlspecialchars($fSearch) ?>" oninput="debounceFilter()" autocomplete="off" />
            </div>
            <?php if ($hasLombaFK && $lombaMasterList): ?>
              <select name="lomba_id" id="filter-lomba-id" style="width:auto;max-width:200px;" onchange="document.getElementById('filter-form').submit()">
                <option value="">Semua Event Lomba</option>
                <?php foreach ($lombaMasterList as $lm): ?>
                  <option value="<?= $lm['id'] ?>" <?= $fLombaId === (int)$lm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($lm['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
            <select name="tingkat" id="filter-tingkat" style="width:auto;" onchange="document.getElementById('filter-form').submit()">
              <option value="">Semua Kategori</option>
              <?php
              // Gabungkan kategori dari data + opsi default, unik & urut
              $allTingkat = array_unique(array_merge($tingkatList, $tingkatOptions));
              sort($allTingkat);
              foreach ($allTingkat as $t):
                if ($t === '') continue; ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $fTingkat === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="status" id="filter-status" style="width:auto;" onchange="document.getElementById('filter-form').submit()">
              <option value="">Semua Status</option>
              <option value="menunggu" <?= $fStatus === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
              <option value="diterima" <?= $fStatus === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
              <option value="ditolak"  <?= $fStatus === 'ditolak'  ? 'selected' : '' ?>>❌ Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm" style="display:none;"><i class="fas fa-search"></i></button>
            <?php if ($fSearch || $fTingkat || $fStatus || $fLombaId): ?>
              <a href="pendaftaran-lomba.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a>
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
                <th>Nama Lomba</th>
                <th>Kategori</th>
                <th>No. HP</th>
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
                      <div style="font-weight:700;color:var(--gray-900);"><?= htmlspecialchars($r['nama']) ?></div>
                      <div style="font-size:.72rem;color:var(--gray-400);">Kelas <?= htmlspecialchars($r['kelas']) ?></div>
                    </td>
                    <td style="font-size:.8rem;font-family:monospace;color:var(--gray-600);"><?= htmlspecialchars($r['nis']) ?></td>
                    <td>
                      <div style="font-size:.85rem;font-weight:600;color:var(--gray-900);"><?= htmlspecialchars($r['nama_lomba']) ?></div>
                    </td>
                    <td>
                      <?php if ($r['tingkat']): ?>
                        <span class="badge badge-blue" style="font-size:.7rem;"><?= htmlspecialchars($r['tingkat']) ?></span>
                      <?php else: ?>
                        <span style="color:var(--gray-400);font-size:.75rem;">-</span>
                      <?php endif; ?>
                    </td>
                    <td style="font-size:.78rem;color:var(--gray-500);">
                      <?php if ($r['no_hp']): ?>
                        <i class="fas fa-phone" style="width:14px;"></i> <?= htmlspecialchars($r['no_hp']) ?>
                      <?php else: ?>
                        <span style="color:var(--gray-400);">-</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <form method="POST" style="display:inline;" autocomplete="off">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_status" />
                        <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                        <select name="status" onchange="this.form.submit()" style="width:auto;font-size:.76rem;padding:.3rem .6rem;border-radius:6px;font-weight:600;border-color:transparent;" class="status-select status-<?= htmlspecialchars($r['status']) ?>">
                          <option value="menunggu" <?= $r['status'] === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                          <option value="diterima" <?= $r['status'] === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
                          <option value="ditolak"  <?= $r['status'] === 'ditolak'  ? 'selected' : '' ?>>❌ Ditolak</option>
                        </select>
                      </form>
                    </td>
                    <td style="font-size:.75rem;color:var(--gray-400);"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    <td>
                      <div class="td-actions">
                        <a href="?detail=<?= $r['id'] ?><?= $fSearch ? '&q=' . urlencode($fSearch) : '' ?><?= $fStatus ? '&status=' . urlencode($fStatus) : '' ?><?= $fTingkat ? '&tingkat=' . urlencode($fTingkat) : '' ?>" class="btn btn-ghost btn-icon btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pendaftaran lomba ini?')" autocomplete="off">
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
                  <td colspan="9">
                    <div class="empty-state">
                      <div class="empty-state-icon">🏆</div>
                      <h4>Tidak Ada Data</h4>
                      <p>Belum ada pendaftaran lomba<?= $fSearch ? ' dengan kata kunci "' . htmlspecialchars($fSearch) . '"' : '' ?>.</p>
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
            <!-- Hint saat belum ada yang dipilih -->
            <div id="bulk-hint" style="display:flex;align-items:center;gap:.5rem;">
              <span style="font-size:.78rem;color:var(--gray-400);" id="selected-count">0 dipilih</span>
              <span id="bulk-note" style="font-size:.75rem;color:var(--gray-400);display:flex;align-items:center;gap:.35rem;">
                <i class="fas fa-info-circle" style="color:#93c5fd;font-size:.8rem;"></i>
                Centang data terlebih dahulu untuk Terima / Tolak / Hapus
              </span>
            </div>
            <!-- Tombol bulk — muncul saat ada yang dicentang -->
            <div id="bulk-action-btns" style="display:none;align-items:center;gap:.5rem;flex-wrap:wrap;">
              <button class="btn btn-sm" style="background:#ccfbf1;color:#0f766e;border:1.5px solid #0f766e;" id="btn-bulk-diterima">
                <i class="fas fa-check"></i> <span id="lbl-terima">Terima</span>
              </button>
              <button class="btn btn-sm" style="background:#ffedd5;color:#c2410c;border:1.5px solid #c2410c;" id="btn-bulk-tolak">
                <i class="fas fa-times"></i> <span id="lbl-tolak">Tolak</span>
              </button>
              <button class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:1.5px solid #b91c1c;" id="btn-bulk-hapus">
                <i class="fas fa-trash"></i> Hapus
              </button>
            </div>
            <a href="?<?= http_build_query(['tingkat' => $fTingkat, 'status' => $fStatus, 'q' => $fSearch]) ?>" class="btn btn-outline btn-sm" style="margin-left:auto;">
              <i class="fas fa-sync"></i> Refresh
            </a>
          </div>
        <?php endif; ?>
      </div>

      <script>
        /* Bulk Action Scripts — HARUS di dalam .page-content agar SPA reinitPageScripts() bisa re-run */
        const _csrfToken = <?= json_encode(getCsrfToken()) ?>;
        (function initBulkActions() {
          const checkAll = document.getElementById('check-all');
          const rowChecks = document.querySelectorAll('.row-check');
          const countEl = document.getElementById('selected-count');

          function updateBulk() {
            const n = document.querySelectorAll('.row-check:checked').length;
            if (countEl) countEl.textContent = n + ' dipilih';

            // Tampilkan tombol / sembunyikan note saat ada yang dipilih
            const bulkDiv  = document.getElementById('bulk-action-btns');
            const bulkNote = document.getElementById('bulk-note');
            if (bulkDiv)  bulkDiv.style.display  = n > 0 ? 'flex' : 'none';
            if (bulkNote) bulkNote.style.display  = n > 0 ? 'none' : 'flex';

            const lblTerima = document.getElementById('lbl-terima');
            const lblTolak  = document.getElementById('lbl-tolak');
            if (lblTerima) lblTerima.textContent = n === 1 ? 'Terima' : 'Terima Semua';
            if (lblTolak)  lblTolak.textContent  = n === 1 ? 'Tolak'  : 'Tolak Semua';
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

          // Pasang event listener ke tombol bulk (bukan onclick inline)
          // sehingga disabled attribute benar-benar direspek
          const btnDiterima = document.getElementById('btn-bulk-diterima');
          const btnTolak    = document.getElementById('btn-bulk-tolak');
          const btnHapus    = document.getElementById('btn-bulk-hapus');
          if (btnDiterima) btnDiterima.addEventListener('click', () => { if (!btnDiterima.disabled) bulkAction('diterima'); });
          if (btnTolak)    btnTolak.addEventListener('click',    () => { if (!btnTolak.disabled)    bulkAction('ditolak'); });
          if (btnHapus)    btnHapus.addEventListener('click',    () => { if (!btnHapus.disabled)    bulkHapus(); });

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
            addHidden('_csrf', _csrfToken);
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
            addHidden('_csrf', _csrfToken);
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
        <h3><i class="fas fa-trophy" style="color:var(--primary-mid);margin-right:.4rem;"></i> Tambah Pendaftaran Lomba Manual</h3>
        <button class="modal-close" onclick="closeModal('modal-tambah')"><i class="fas fa-times"></i></button>
      </div>
      <form method="POST" autocomplete="off">
        <?= csrfField() ?>
        <div class="modal-body">
          <input type="hidden" name="action" value="tambah" />
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label>Nama Siswa <span class="req">*</span></label>
              <input type="text" name="nama" placeholder="Nama lengkap" required />
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
          <?php if ($hasLombaFK && $lombaMasterList): ?>
            <!-- Pilihan dari lomba master (opsional). Jika dipilih, nama_lomba
                 akan otomatis memakai data dari tabel lomba. -->
            <div class="form-group">
              <label>Pilih dari Daftar Lomba <span style="font-weight:400;color:var(--gray-400);font-size:.75rem;">(opsional — isi bila lomba sudah terdaftar di menu Data Lomba)</span></label>
              <select name="lomba_id" onchange="onPickLombaMaster(this)">
                <option value="">— Tidak dipilih / Ketik manual di bawah —</option>
                <?php foreach ($lombaMasterList as $lm): ?>
                  <option value="<?= $lm['id'] ?>" data-nama="<?= htmlspecialchars($lm['nama']) ?>" data-tingkat="<?= htmlspecialchars($lm['tingkat']) ?>">
                    <?= htmlspecialchars($lm['nama']) ?> &mdash; <?= htmlspecialchars(ucfirst($lm['tingkat'])) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>
          <div class="form-group">
            <label>Nama Lomba <span class="req">*</span></label>
            <input type="text" name="nama_lomba" id="input-nama-lomba" placeholder="Contoh: Olimpiade Sains Nasional 2025" required />
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label>Kategori / Tingkat Lomba</label>
              <select name="tingkat" id="input-tingkat-lomba">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($tingkatOptions as $t): ?>
                  <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>No. HP</label>
              <input type="tel" name="no_hp" placeholder="08xxxxxxxxxx" />
            </div>
          </div>
          <?php if ($hasLombaFK && $lombaMasterList): ?>
            <script>
              /* Auto-fill nama_lomba ketika admin memilih dari dropdown master */
              function onPickLombaMaster(sel) {
                var opt = sel.options[sel.selectedIndex];
                var nama = opt.getAttribute('data-nama') || '';
                var inputNama = document.getElementById('input-nama-lomba');
                if (nama) {
                  inputNama.value = nama;
                  inputNama.setAttribute('readonly', 'readonly');
                  inputNama.style.background = 'var(--gray-50)';
                } else {
                  inputNama.removeAttribute('readonly');
                  inputNama.style.background = '';
                }
              }
            </script>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('modal-tambah')">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: Detail Pendaftaran Lomba -->
  <?php if ($detail): ?>
    <div class="modal-backdrop open" id="modal-detail">
      <div class="modal-box">
        <div class="modal-header">
          <h3><i class="fas fa-trophy" style="color:var(--primary-mid);margin-right:.4rem;"></i> Detail Pendaftaran Lomba</h3>
          <a href="pendaftaran-lomba.php<?= $fSearch || $fStatus || $fTingkat ? '?' . http_build_query(['q' => $fSearch, 'status' => $fStatus, 'tingkat' => $fTingkat]) : '' ?>" class="modal-close"><i class="fas fa-times"></i></a>
        </div>
        <div class="modal-body">
          <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:var(--primary-light);border-radius:var(--radius-sm);">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--primary-mid);color:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.2rem;flex-shrink:0;">
              <?= strtoupper(substr($detail['nama'], 0, 1)) ?>
            </div>
            <div>
              <div style="font-weight:700;font-size:1rem;"><?= htmlspecialchars($detail['nama']) ?></div>
              <div style="font-size:.82rem;color:var(--gray-500);">Kelas <?= htmlspecialchars($detail['kelas']) ?> &bull; NIS: <?= htmlspecialchars($detail['nis']) ?></div>
              <span class="badge status-<?= htmlspecialchars($detail['status']) ?>" style="margin-top:.3rem;display:inline-flex;"><?= htmlspecialchars($detail['status']) ?></span>
            </div>
          </div>

          <div class="detail-grid">
            <div class="detail-row detail-full">
              <div class="detail-label">Nama Lomba</div>
              <div class="detail-value" style="font-weight:600;"><?= htmlspecialchars($detail['nama_lomba']) ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Kategori / Tingkat</div>
              <div class="detail-value">
                <?php if ($detail['tingkat']): ?>
                  <span class="badge badge-blue"><?= htmlspecialchars($detail['tingkat']) ?></span>
                <?php else: ?>
                  -
                <?php endif; ?>
              </div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Kelas</div>
              <div class="detail-value"><?= htmlspecialchars($detail['kelas']) ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">NIS</div>
              <div class="detail-value" style="font-family:monospace;"><?= htmlspecialchars($detail['nis']) ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">No. HP</div>
              <div class="detail-value"><?= htmlspecialchars($detail['no_hp'] ?? '-') ?: '-' ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Tanggal Daftar</div>
              <div class="detail-value"><?= date('d F Y, H:i', strtotime($detail['created_at'])) ?></div>
            </div>
            <div class="detail-row">
              <div class="detail-label">Status</div>
              <div class="detail-value"><span class="badge status-<?= htmlspecialchars($detail['status']) ?>"><?= htmlspecialchars($detail['status']) ?></span></div>
            </div>
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
              <option value="ditolak"  <?= $detail['status'] === 'ditolak'  ? 'selected' : '' ?>>❌ Ditolak</option>
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
