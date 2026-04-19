<?php
// ============================================================
// lomba.php — Daftar Lomba (Master Data Lomba / Katalog Lomba)
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola daftar lomba yang akan/sedang
// berlangsung. Pola sama dengan ekskul.php — dipakai oleh
// pendaftaran-lomba.php sebagai master referensi.
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel lomba
//   hapus       -> delete by id
//   update_pendaftar / hapus_pendaftar -> dari view pendaftar per lomba
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf(); // Tolak jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';

  if ($action === 'tambah' || $action === 'edit') {
    $nama                 = trim($_POST['nama'] ?? '');
    $deskripsi            = trim($_POST['deskripsi'] ?? '');
    $kategori             = $_POST['kategori'] ?? 'lainnya';
    $tingkat              = $_POST['tingkat'] ?? 'sekolah';
    $penyelenggara        = trim($_POST['penyelenggara'] ?? '');
    $tempat               = trim($_POST['tempat'] ?? '');
    $tanggal_mulai        = $_POST['tanggal_mulai'] ?: null;
    $tanggal_selesai      = $_POST['tanggal_selesai'] ?: null;
    $deadline_pendaftaran = $_POST['deadline_pendaftaran'] ?: null;
    $kuota                = $_POST['kuota'] !== '' ? (int)$_POST['kuota'] : null;
    $biaya                = $_POST['biaya'] !== '' ? (int)$_POST['biaya'] : 0;
    $kontak_pic           = trim($_POST['kontak_pic'] ?? '');
    $status               = $_POST['status'] ?? 'aktif';

    // Validasi enum agar tidak bisa di-injeksi
    if (!in_array($kategori, ['akademik', 'seni', 'olahraga', 'keagamaan', 'teknologi', 'lainnya'])) $kategori = 'lainnya';
    if (!in_array($tingkat,  ['sekolah', 'kabupaten', 'provinsi', 'nasional', 'internasional']))      $tingkat  = 'sekolah';
    if (!in_array($status,   ['aktif', 'selesai', 'dibatalkan']))                                     $status   = 'aktif';

    if (!$nama) {
      $err = 'Nama lomba wajib diisi.';
    } elseif ($action === 'tambah') {
      $pdo->prepare("INSERT INTO lomba (nama,deskripsi,kategori,tingkat,penyelenggara,tempat,tanggal_mulai,tanggal_selesai,deadline_pendaftaran,kuota,biaya,kontak_pic,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
        ->execute([$nama, $deskripsi, $kategori, $tingkat, $penyelenggara, $tempat, $tanggal_mulai, $tanggal_selesai, $deadline_pendaftaran, $kuota, $biaya, $kontak_pic, $status]);
      header('Location: lomba.php?msg=tambah');
      exit;
    } else {
      $id = (int)$_POST['id'];
      $pdo->prepare("UPDATE lomba SET nama=?,deskripsi=?,kategori=?,tingkat=?,penyelenggara=?,tempat=?,tanggal_mulai=?,tanggal_selesai=?,deadline_pendaftaran=?,kuota=?,biaya=?,kontak_pic=?,status=? WHERE id=?")
        ->execute([$nama, $deskripsi, $kategori, $tingkat, $penyelenggara, $tempat, $tanggal_mulai, $tanggal_selesai, $deadline_pendaftaran, $kuota, $biaya, $kontak_pic, $status, $id]);
      header('Location: lomba.php?msg=edit');
      exit;
    }
  } elseif ($action === 'hapus') {
    $pdo->prepare("DELETE FROM lomba WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: lomba.php?msg=hapus');
    exit;
  } elseif ($action === 'update_pendaftar') {
    // Update status pendaftar yang datang dari detail view "?view=X"
    $pid    = (int)$_POST['pendaftar_id'];
    $viewId = (int)($_POST['view'] ?? 0);
    $status = $_POST['status'] ?? 'menunggu';
    if (!in_array($status, ['menunggu', 'diterima', 'ditolak'])) $status = 'menunggu';
    $pdo->prepare("UPDATE pendaftaran_lomba SET status=? WHERE id=?")->execute([$status, $pid]);
    header('Location: lomba.php?view=' . $viewId . '&msg=pendaftar');
    exit;
  } elseif ($action === 'hapus_pendaftar') {
    $viewId2 = (int)($_POST['view'] ?? 0);
    $pdo->prepare("DELETE FROM pendaftaran_lomba WHERE id=?")->execute([(int)$_POST['pendaftar_id']]);
    header('Location: lomba.php?view=' . $viewId2 . '&msg=hapus_pendaftar');
    exit;
  }
}

// Map pesan redirect
if (isset($_GET['msg'])) {
  $msgMap = [
    'tambah'          => 'Lomba berhasil ditambahkan!',
    'edit'            => 'Data lomba diperbarui!',
    'hapus'           => 'Lomba dihapus.',
    'pendaftar'       => 'Status pendaftar diperbarui.',
    'hapus_pendaftar' => 'Pendaftar dihapus.',
  ];
  $msg = $msgMap[$_GET['msg']] ?? '';
}

// Mode edit — prefill form dari data lomba yang sedang di-edit
$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM lomba WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}

// Mode view — tampilkan pendaftar untuk lomba tertentu
// Matching pendaftaran_lomba.nama_lomba dengan lomba.nama (exact match)
$viewLomba = null;
$pendaftar = [];
if (isset($_GET['view'])) {
  $s = $pdo->prepare("SELECT * FROM lomba WHERE id=?");
  $s->execute([(int)$_GET['view']]);
  $viewLomba = $s->fetch(PDO::FETCH_ASSOC);
  if ($viewLomba) {
    $p = $pdo->prepare("SELECT * FROM pendaftaran_lomba WHERE nama_lomba=? ORDER BY created_at DESC");
    $p->execute([$viewLomba['nama']]);
    $pendaftar = $p->fetchAll(PDO::FETCH_ASSOC);
  }
}

// Data utama: semua lomba + count pendaftar (match by nama)
$rows = $pdo->query("SELECT l.*,
    (SELECT COUNT(*) FROM pendaftaran_lomba p WHERE p.nama_lomba = l.nama) as total_daftar,
    (SELECT COUNT(*) FROM pendaftaran_lomba p WHERE p.nama_lomba = l.nama AND p.status='diterima') as total_diterima,
    (SELECT COUNT(*) FROM pendaftaran_lomba p WHERE p.nama_lomba = l.nama AND p.status='menunggu') as total_menunggu
    FROM lomba l
    ORDER BY
      CASE l.status WHEN 'aktif' THEN 1 WHEN 'selesai' THEN 2 WHEN 'dibatalkan' THEN 3 END,
      l.tanggal_mulai DESC,
      l.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$isEdit = !!$edit;

// Icon per kategori untuk tampilan kartu
$catIcon     = ['akademik' => '📚', 'seni' => '🎨', 'olahraga' => '⚽', 'keagamaan' => '🕌', 'teknologi' => '💻', 'lainnya' => '🏆'];
$tingkatIcon = ['sekolah' => '🏫', 'kabupaten' => '🏘️', 'provinsi' => '🗺️', 'nasional' => '🇮🇩', 'internasional' => '🌏'];
$statusBadge = ['aktif' => 'green', 'selesai' => 'gray', 'dibatalkan' => 'red'];

// Helper: format tanggal singkat
function fmtTgl($d)
{
  if (!$d) return '-';
  $ts = strtotime($d);
  return $ts ? date('d M Y', $ts) : $d;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Daftar Lomba — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-medal"></i> Daftar Lomba</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Daftar Lomba</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit || $viewLomba): ?>
          <a href="lomba.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <?php endif; ?>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div><?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">
      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

      <details open style="margin-bottom:1.25rem;border:1.5px solid #fde68a;background:#fffbeb;border-radius:var(--radius-sm,10px);overflow:hidden;">
        <summary style="cursor:pointer;padding:.85rem 1rem;display:flex;align-items:center;gap:.6rem;font-weight:700;color:#92400e;list-style:none;user-select:none;">
          <i class="fas fa-exclamation-triangle" style="color:#d97706;"></i>
          <span>Panduan Mengelola Daftar Lomba</span>
          <span style="margin-left:auto;font-size:.72rem;font-weight:500;color:#b45309;opacity:.85;">Klik untuk buka/tutup</span>
        </summary>
        <div style="padding:0 1.25rem 1rem 1.25rem;color:#78350f;font-size:.85rem;line-height:1.65;">
          <div style="padding-top:.25rem;border-top:1px dashed #fcd34d;margin-top:.1rem;"></div>

          <p style="margin:.75rem 0 .4rem 0;"><strong style="color:#92400e;">🔍 Sebelum menambahkan / mengubah lomba:</strong></p>
          <ol style="margin:.3rem 0 .75rem 1.3rem;padding:0;">
            <li>Pastikan informasi <b>tanggal, tempat, dan penyelenggara</b> sudah dikonfirmasi resmi.</li>
            <li>Isi <b>Deadline Pendaftaran</b> agar siswa tahu batas waktu mendaftar.</li>
            <li>Isi <b>Kontak PIC</b> lengkap dengan No. HP/WA yang aktif untuk pertanyaan dari siswa.</li>
            <li>Tentukan <b>Tingkat</b> (sekolah / kabupaten / ... / internasional) dengan benar — memengaruhi poin prestasi siswa.</li>
          </ol>

          <p style="margin:.85rem 0 .4rem 0;"><strong style="color:#b91c1c;">⚠️ Perhatian khusus:</strong></p>
          <ul style="margin:.3rem 0 .2rem 1.3rem;padding:0;">
            <li>Lomba berstatus <b>"Aktif"</b> akan muncul di halaman publik dan dapat diisi siswa melalui form pendaftaran.</li>
            <li>Jangan mengubah <b>Nama Lomba</b> setelah ada pendaftar — pendaftaran yang sudah masuk dimatch berdasarkan nama.</li>
            <li>Gunakan status <b>"Selesai"</b> setelah event usai (jangan dihapus) agar histori pendaftarnya tetap ada untuk data prestasi.</li>
            <li>Status <b>"Dibatalkan"</b> dipakai bila lomba batal diadakan — tetap tersimpan agar siswa yang sudah daftar bisa dihubungi.</li>
            <li>Data yang dihapus <b>tidak dapat dikembalikan</b>. Data pendaftar yang terhubung akan "yatim" (tetap ada tapi tidak terkoneksi ke master).</li>
          </ul>
        </div>
      </details>

      <?php if ($viewLomba): ?>
        <!-- ===================== DETAIL VIEW PENDAFTAR ===================== -->
        <div class="admin-card anim-fade-up">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="font-size:1.2rem;background:var(--primary-light);">
                <?= $catIcon[$viewLomba['kategori']] ?? '🏆' ?>
              </div>
              <div>
                <div class="card-header-title"><?= htmlspecialchars($viewLomba['nama']) ?></div>
                <div class="card-header-sub">
                  <span class="badge badge-blue"><?= htmlspecialchars($viewLomba['kategori']) ?></span>
                  <span style="margin:0 .3rem;">&bull;</span>
                  <?= $tingkatIcon[$viewLomba['tingkat']] ?? '' ?> <?= htmlspecialchars(ucfirst($viewLomba['tingkat'])) ?>
                  <?php if ($viewLomba['tanggal_mulai']): ?>
                    <span style="margin:0 .3rem;">&bull;</span>
                    <i class="fas fa-calendar" style="margin-right:2px;"></i> <?= fmtTgl($viewLomba['tanggal_mulai']) ?>
                  <?php endif; ?>
                  <?php if ($viewLomba['penyelenggara']): ?>
                    <span style="margin:0 .3rem;">&bull;</span>
                    <?= htmlspecialchars($viewLomba['penyelenggara']) ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <a href="lomba.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Tutup</a>
          </div>

          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--gray-100);">
            <?php
            $total    = count($pendaftar);
            $diterima = count(array_filter($pendaftar, fn($p) => $p['status'] === 'diterima'));
            $menunggu = count(array_filter($pendaftar, fn($p) => $p['status'] === 'menunggu'));
            foreach (
              [
                ['label' => 'Total Pendaftar', 'val' => $total,    'color' => 'var(--primary)'],
                ['label' => 'Diterima',        'val' => $diterima, 'color' => 'var(--teal)'],
                ['label' => 'Menunggu',        'val' => $menunggu, 'color' => 'var(--orange)'],
              ] as $s
            ): ?>
              <div style="padding:1.1rem 1.4rem;background:var(--white);">
                <div style="font-size:1.8rem;font-weight:800;color:<?= $s['color'] ?>;"><?= $s['val'] ?></div>
                <div style="font-size:.72rem;font-weight:600;color:var(--gray-500);margin-top:.1rem;"><?= $s['label'] ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama Siswa</th>
                  <th>NIS</th>
                  <th>Kelas</th>
                  <th>No. HP</th>
                  <th>Kategori</th>
                  <th>Status</th>
                  <th>Daftar</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($pendaftar): $no = 0;
                  foreach ($pendaftar as $p): $no++; ?>
                    <tr>
                      <td style="color:var(--gray-400);font-size:.75rem;"><?= $no ?></td>
                      <td><div style="font-weight:700;"><?= htmlspecialchars($p['nama']) ?></div></td>
                      <td style="font-family:monospace;font-size:.78rem;"><?= htmlspecialchars($p['nis']) ?></td>
                      <td><?= htmlspecialchars($p['kelas']) ?></td>
                      <td style="font-size:.78rem;">
                        <?php if ($p['no_hp']): ?><i class="fas fa-phone" style="width:12px;color:var(--gray-400);"></i> <?= htmlspecialchars($p['no_hp']) ?><?php else: ?><span style="color:var(--gray-300);">—</span><?php endif; ?>
                      </td>
                      <td>
                        <?php if ($p['tingkat']): ?><span class="badge badge-blue" style="font-size:.68rem;"><?= htmlspecialchars($p['tingkat']) ?></span><?php else: ?><span style="color:var(--gray-300);font-size:.75rem;">—</span><?php endif; ?>
                      </td>
                      <td>
                        <form method="POST" style="display:inline;" autocomplete="off">
                          <?= csrfField() ?>
                          <input type="hidden" name="action" value="update_pendaftar" />
                          <input type="hidden" name="pendaftar_id" value="<?= $p['id'] ?>" />
                          <input type="hidden" name="view" value="<?= (int)$_GET['view'] ?>" />
                          <select name="status" onchange="this.form.submit()" style="width:auto;font-size:.75rem;padding:.3rem .5rem;font-weight:600;border-radius:6px;cursor:pointer;" class="status-<?= htmlspecialchars($p['status']) ?>">
                            <option value="menunggu" <?= $p['status'] === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                            <option value="diterima" <?= $p['status'] === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
                            <option value="ditolak"  <?= $p['status'] === 'ditolak'  ? 'selected' : '' ?>>❌ Ditolak</option>
                          </select>
                        </form>
                      </td>
                      <td style="font-size:.72rem;color:var(--gray-400);"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                      <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pendaftar ini?')" autocomplete="off">
                          <?= csrfField() ?>
                          <input type="hidden" name="action" value="hapus_pendaftar" />
                          <input type="hidden" name="pendaftar_id" value="<?= $p['id'] ?>" />
                          <input type="hidden" name="view" value="<?= (int)$_GET['view'] ?>" />
                          <button class="btn btn-ghost btn-icon btn-sm" style="color:var(--red);" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="9">
                      <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <h4>Belum Ada Pendaftar</h4>
                        <p>Belum ada siswa yang mendaftar lomba ini. Share info lomba ke siswa agar mereka bisa mendaftar lewat halaman publik.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div style="padding:.85rem 1.25rem;border-top:1px solid var(--gray-100);font-size:.8rem;color:var(--gray-500);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
            <span><i class="fas fa-info-circle"></i> Pendaftar di-match berdasarkan nama lomba. Untuk kelola pendaftar secara terpisah, buka <a href="pendaftaran-lomba.php" style="color:var(--primary-mid);">Pendaftaran Lomba</a>.</span>
            <a href="pendaftaran-lomba.php?q=<?= urlencode($viewLomba['nama']) ?>" class="btn btn-outline btn-sm"><i class="fas fa-external-link-alt"></i> Buka di Pendaftaran Lomba</a>
          </div>
        </div>

      <?php else: ?>
        <!-- ===================== MAIN VIEW ===================== -->
        <div class="two-col" style="align-items:start;">

          <!-- FORM -->
          <div class="form-section anim-fade-up">
            <div class="form-section-header">
              <div class="form-section-header-icon"><i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i></div>
              <div>
                <h3><?= $isEdit ? 'Edit Lomba' : 'Tambah Lomba Baru' ?></h3>
                <p>Data lomba yang akan / sedang berlangsung</p>
              </div>
            </div>
            <div class="form-section-body">
              <form method="POST" id="form-lomba" autocomplete="off">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>

                <div class="form-group">
                  <label>Nama Lomba <span class="req">*</span></label>
                  <input type="text" name="nama" value="<?= htmlspecialchars($edit['nama'] ?? '') ?>" placeholder="Contoh: Olimpiade Sains Nasional 2025" required />
                </div>
                <div class="form-group">
                  <label>Deskripsi</label>
                  <textarea name="deskripsi" placeholder="Detail lomba, bidang yang dilombakan, peserta, dsb..."><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                      <?php foreach (['akademik' => '📚 Akademik', 'seni' => '🎨 Seni', 'olahraga' => '⚽ Olahraga', 'keagamaan' => '🕌 Keagamaan', 'teknologi' => '💻 Teknologi', 'lainnya' => '🏆 Lainnya'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($edit['kategori'] ?? 'lainnya') === $v ? 'selected' : '' ?>><?= $l ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Tingkat</label>
                    <select name="tingkat">
                      <?php foreach (['sekolah' => '🏫 Sekolah', 'kabupaten' => '🏘️ Kabupaten/Kota', 'provinsi' => '🗺️ Provinsi', 'nasional' => '🇮🇩 Nasional', 'internasional' => '🌏 Internasional'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($edit['tingkat'] ?? 'sekolah') === $v ? 'selected' : '' ?>><?= $l ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Penyelenggara</label>
                    <input type="text" name="penyelenggara" value="<?= htmlspecialchars($edit['penyelenggara'] ?? '') ?>" placeholder="Contoh: Dinas Pendidikan Bangka" />
                  </div>
                  <div class="form-group">
                    <label>Tempat</label>
                    <input type="text" name="tempat" value="<?= htmlspecialchars($edit['tempat'] ?? '') ?>" placeholder="Contoh: Aula MAN 1 Bangka" />
                  </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($edit['tanggal_mulai'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($edit['tanggal_selesai'] ?? '') ?>" />
                  </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Deadline Pendaftaran</label>
                    <input type="date" name="deadline_pendaftaran" value="<?= htmlspecialchars($edit['deadline_pendaftaran'] ?? '') ?>" />
                  </div>
                  <div class="form-group">
                    <label>Kuota Peserta</label>
                    <input type="number" name="kuota" value="<?= $edit['kuota'] ?? '' ?>" placeholder="Maks peserta" min="1" />
                  </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Biaya Pendaftaran (Rp)</label>
                    <input type="number" name="biaya" value="<?= $edit['biaya'] ?? 0 ?>" placeholder="0 = gratis" min="0" step="1000" />
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                      <?php foreach (['aktif' => '✅ Aktif', 'selesai' => '🏁 Selesai', 'dibatalkan' => '❌ Dibatalkan'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($edit['status'] ?? 'aktif') === $v ? 'selected' : '' ?>><?= $l ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label>Kontak PIC / Penanggung Jawab</label>
                  <input type="text" name="kontak_pic" value="<?= htmlspecialchars($edit['kontak_pic'] ?? '') ?>" placeholder="Contoh: Bu Siti (WA: 0812-3456-7890)" />
                </div>
              </form>
            </div>
            <div class="form-section-footer">
              <button type="submit" form="form-lomba" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?></button>
              <?php if ($isEdit): ?>
                <a href="lomba.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
              <?php else: ?>
                <button type="reset" form="form-lomba" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</button>
              <?php endif; ?>
              <a href="pendaftaran-lomba.php" class="btn btn-outline btn-sm" style="margin-left:auto;"><i class="fas fa-clipboard-list"></i> Kelola Pendaftaran</a>
            </div>
          </div>

          <!-- LOMBA LIST -->
          <div class="admin-card anim-fade-up anim-delay-2">
            <div class="card-header">
              <div class="card-header-left">
                <div class="card-header-icon" style="background:#fef3c7;color:#ca8a04;"><i class="fas fa-medal"></i></div>
                <div>
                  <div class="card-header-title">Daftar Lomba</div>
                  <div class="card-header-sub"><?= count($rows) ?> lomba terdaftar</div>
                </div>
              </div>
            </div>
            <div style="max-height:680px;overflow-y:auto;padding:1rem;">
              <?php if ($rows): ?>
                <?php foreach ($rows as $r): ?>
                  <div class="data-item" style="<?= $r['status'] === 'dibatalkan' ? 'opacity:.65;' : '' ?><?= $r['total_menunggu'] > 0 ? 'border-color:var(--orange-bg);' : '' ?>">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                      <?= $catIcon[$r['kategori']] ?? '🏆' ?>
                    </div>
                    <div class="data-item-body">
                      <div class="data-item-title">
                        <?= htmlspecialchars($r['nama']) ?>
                        <span class="badge badge-<?= $statusBadge[$r['status']] ?? 'gray' ?>" style="font-size:.62rem;margin-left:.3rem;vertical-align:middle;"><?= htmlspecialchars($r['status']) ?></span>
                      </div>
                      <div class="data-item-sub" style="margin-top:.2rem;">
                        <span class="badge badge-blue"><?= htmlspecialchars($r['kategori']) ?></span>
                        <span style="font-size:.71rem;color:var(--gray-500);margin-left:.3rem;">
                          <?= $tingkatIcon[$r['tingkat']] ?? '' ?> <?= htmlspecialchars(ucfirst($r['tingkat'])) ?>
                        </span>
                        <?php if ($r['tanggal_mulai']): ?>
                          <span style="font-size:.71rem;color:var(--gray-400);margin-left:.5rem;">
                            <i class="fas fa-calendar" style="margin-right:2px;"></i><?= fmtTgl($r['tanggal_mulai']) ?>
                          </span>
                        <?php endif; ?>
                      </div>
                      <?php if ($r['penyelenggara'] || $r['tempat']): ?>
                        <div style="font-size:.7rem;color:var(--gray-400);margin-top:.25rem;">
                          <?php if ($r['penyelenggara']): ?><i class="fas fa-building" style="width:12px;"></i> <?= htmlspecialchars($r['penyelenggara']) ?><?php endif; ?>
                          <?php if ($r['penyelenggara'] && $r['tempat']): ?> &bull; <?php endif; ?>
                          <?php if ($r['tempat']): ?><i class="fas fa-map-marker-alt" style="width:12px;"></i> <?= htmlspecialchars($r['tempat']) ?><?php endif; ?>
                        </div>
                      <?php endif; ?>
                      <div style="display:flex;gap:.3rem;margin-top:.35rem;flex-wrap:wrap;">
                        <a href="?view=<?= $r['id'] ?>" class="badge badge-<?= $r['total_daftar'] > 0 ? 'green' : 'gray' ?>" style="text-decoration:none;cursor:pointer;">
                          <i class="fas fa-users" style="font-size:.6rem;"></i> <?= $r['total_daftar'] ?> pendaftar
                        </a>
                        <?php if ($r['total_menunggu'] > 0): ?>
                          <a href="?view=<?= $r['id'] ?>" class="badge badge-gold" style="text-decoration:none;">
                            ⏳ <?= $r['total_menunggu'] ?> menunggu
                          </a>
                        <?php endif; ?>
                        <?php if ($r['kuota']): ?>
                          <span class="badge badge-gray"><i class="fas fa-door-open" style="font-size:.6rem;"></i> <?= $r['total_diterima'] ?>/<?= $r['kuota'] ?></span>
                        <?php endif; ?>
                        <?php if ($r['deadline_pendaftaran']): ?>
                          <?php
                          $deadlineTs = strtotime($r['deadline_pendaftaran']);
                          $expired    = $deadlineTs && $deadlineTs < time();
                          $badgeStyle = $expired ? 'badge-red' : 'badge-blue';
                          ?>
                          <span class="badge <?= $badgeStyle ?>" style="font-size:.65rem;" title="Deadline Pendaftaran"><i class="fas fa-clock" style="font-size:.6rem;"></i> <?= fmtTgl($r['deadline_pendaftaran']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="data-item-actions" style="flex-direction:column;gap:.3rem;">
                      <a href="?view=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Lihat Pendaftar" style="font-size:.72rem;">
                        <i class="fas fa-users"></i> Pendaftar
                      </a>
                      <div style="display:flex;gap:.3rem;">
                        <a href="?edit=<?= $r['id'] ?>" class="btn btn-ghost btn-icon btn-xs" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus lomba ini? Data pendaftar yang terhubung tidak akan terhapus, tapi master data-nya hilang.')" autocomplete="off">
                          <?= csrfField() ?>
                          <input type="hidden" name="action" value="hapus" /><input type="hidden" name="id" value="<?= $r['id'] ?>" />
                          <button class="btn btn-ghost btn-icon btn-xs" style="color:var(--red);"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="empty-state">
                  <div class="empty-state-icon">🏆</div>
                  <h4>Belum Ada Lomba</h4>
                  <p>Tambahkan lomba pertama menggunakan form di sebelah kiri.</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>
  <script src="assets/admin.js"></script>
</body>

</html>
