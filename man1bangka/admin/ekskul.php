<?php
// ============================================================
// ekskul.php — Ekstrakurikuler
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola data ekstrakurikuler.
// Setiap ekskul dapat dihubungkan ke guru pembina (kontak_pembina).
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel ekstrakurikuler
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
  verifyCsrf(); // Tolak jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';
  if ($action === 'tambah' || $action === 'edit') {
    $nama = trim($_POST['nama'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    $kat = $_POST['kategori'] ?? 'lainnya';
    $jadwal = trim($_POST['jadwal'] ?? '');
    $tempat = trim($_POST['tempat'] ?? '');
    $pid = $_POST['pembina_id'] ?: null;
    $kuota = $_POST['kuota'] ?: null;
    if (!$nama) $err = 'Nama ekskul wajib diisi.';
    elseif ($action === 'tambah') {
      $pdo->prepare("INSERT INTO ekstrakurikuler (nama,deskripsi,kategori,jadwal,tempat,pembina_id,kuota) VALUES (?,?,?,?,?,?,?)")
        ->execute([$nama, $desk, $kat, $jadwal, $tempat, $pid, $kuota]);
      header('Location: ekskul.php?msg=tambah');
      exit;
    } else {
      $id = (int)$_POST['id'];
      $pdo->prepare("UPDATE ekstrakurikuler SET nama=?,deskripsi=?,kategori=?,jadwal=?,tempat=?,pembina_id=?,kuota=? WHERE id=?")
        ->execute([$nama, $desk, $kat, $jadwal, $tempat, $pid, $kuota, $id]);
      header('Location: ekskul.php?msg=edit');
      exit;
    }
  } elseif ($action === 'hapus') {
    $pdo->prepare("DELETE FROM ekstrakurikuler WHERE id=?")->execute([(int)$_POST['id']]);
    header('Location: ekskul.php?msg=hapus');
    exit;
  } elseif ($action === 'update_pendaftar') {
    $pid = (int)$_POST['pendaftar_id'];
    $status = $_POST['status'] ?? 'menunggu';
    if (!in_array($status, ['menunggu', 'diterima', 'ditolak'])) $status = 'menunggu';
    $pdo->prepare("UPDATE pendaftaran_ekskul SET status=? WHERE id=?")->execute([$status, $pid]);
    header('Location: ekskul.php?msg=pendaftar');
    exit;
  } elseif ($action === 'hapus_pendaftar') {
    $pdo->prepare("DELETE FROM pendaftaran_ekskul WHERE id=?")->execute([(int)$_POST['pendaftar_id']]);
    header('Location: ekskul.php?msg=hapus_pendaftar');
    exit;
  }
}
if (isset($_GET['msg'])) {
  $msgMap = ['tambah' => 'Ekskul berhasil ditambahkan!', 'edit' => 'Ekskul diperbarui!', 'hapus' => 'Ekskul dihapus.', 'pendaftar' => 'Status pendaftar diperbarui.', 'hapus_pendaftar' => 'Pendaftar dihapus.'];
  $msg = $msgMap[$_GET['msg']] ?? '';
}

$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM ekstrakurikuler WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}

// Ekskul terpilih untuk lihat pendaftar
$viewEkskul = null;
$pendaftar = [];
if (isset($_GET['view'])) {
  $s = $pdo->prepare("SELECT e.*,k.nama as nama_pembina FROM ekstrakurikuler e LEFT JOIN kontak_pembina k ON e.pembina_id=k.id WHERE e.id=?");
  $s->execute([(int)$_GET['view']]);
  $viewEkskul = $s->fetch(PDO::FETCH_ASSOC);
  if ($viewEkskul) {
    $pendaftar = $pdo->prepare("SELECT * FROM pendaftaran_ekskul WHERE ekstrakurikuler_id=? ORDER BY created_at DESC");
    $pendaftar->execute([(int)$_GET['view']]);
    $pendaftar = $pendaftar->fetchAll(PDO::FETCH_ASSOC);
  }
}

$rows = $pdo->query("SELECT e.*,k.nama as nama_pembina,
    (SELECT COUNT(*) FROM pendaftaran_ekskul p WHERE p.ekstrakurikuler_id=e.id) as total_daftar,
    (SELECT COUNT(*) FROM pendaftaran_ekskul p WHERE p.ekstrakurikuler_id=e.id AND p.status='diterima') as total_diterima,
    (SELECT COUNT(*) FROM pendaftaran_ekskul p WHERE p.ekstrakurikuler_id=e.id AND p.status='menunggu') as total_menunggu
    FROM ekstrakurikuler e LEFT JOIN kontak_pembina k ON e.pembina_id=k.id ORDER BY e.kategori,e.nama")->fetchAll(PDO::FETCH_ASSOC);

$pembinas = $pdo->query("SELECT id,nama,bidang FROM kontak_pembina ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
$isEdit = !!$edit;
$catIcon = ['olahraga' => '⚽', 'seni' => '🎨', 'akademik' => '📚', 'keagamaan' => '🕌', 'teknologi' => '💻', 'lainnya' => '🌟'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Ekstrakurikuler — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-star"></i> Ekstrakurikuler</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Ekskul</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit || $viewEkskul): ?>
          <a href="ekskul.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <?php endif; ?>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div><?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">
      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

      <?php if ($viewEkskul): ?>
        <!-- ===================== DETAIL VIEW PENDAFTAR ===================== -->
        <div class="admin-card anim-fade-up">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="font-size:1.2rem;background:var(--primary-light);">
                <?= $catIcon[$viewEkskul['kategori']] ?? '⭐' ?>
              </div>
              <div>
                <div class="card-header-title"><?= htmlspecialchars($viewEkskul['nama']) ?></div>
                <div class="card-header-sub">
                  <?= htmlspecialchars($viewEkskul['kategori']) ?>
                  <?php if ($viewEkskul['jadwal']): ?>&bull; <?= htmlspecialchars($viewEkskul['jadwal']) ?><?php endif; ?>
                  <?php if ($viewEkskul['nama_pembina']): ?>&bull; Pembina: <?= htmlspecialchars($viewEkskul['nama_pembina']) ?><?php endif; ?>
                </div>
              </div>
            </div>
            <a href="ekskul.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Tutup</a>
          </div>

          <!-- Stats row -->
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--gray-100);">
            <?php
            $total = count($pendaftar);
            $diterima = count(array_filter($pendaftar, fn($p) => $p['status'] === 'diterima'));
            $menunggu = count(array_filter($pendaftar, fn($p) => $p['status'] === 'menunggu'));
            $ditolak = $total - $diterima - $menunggu;
            foreach (
              [
                ['label' => 'Total Pendaftar', 'val' => $total, 'color' => 'var(--primary)', 'bg' => 'var(--primary-light)'],
                ['label' => 'Diterima', 'val' => $diterima, 'color' => 'var(--teal)', 'bg' => 'var(--teal-bg)'],
                ['label' => 'Menunggu', 'val' => $menunggu, 'color' => 'var(--orange)', 'bg' => 'var(--orange-bg)'],
              ] as $s
            ): ?>
              <div style="padding:1.1rem 1.4rem;background:var(--white);">
                <div style="font-size:1.8rem;font-weight:800;color:<?= $s['color'] ?>;"><?= $s['val'] ?></div>
                <div style="font-size:.72rem;font-weight:600;color:var(--gray-500);margin-top:.1rem;"><?= $s['label'] ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Table pendaftar -->
          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama Siswa</th>
                  <th>NIS</th>
                  <th>Kelas</th>
                  <th>Kontak</th>
                  <th>Alasan</th>
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
                      <td>
                        <div style="font-weight:700;"><?= htmlspecialchars($p['nama_siswa']) ?></div>
                      </td>
                      <td style="font-family:monospace;font-size:.78rem;"><?= htmlspecialchars($p['nis']) ?></td>
                      <td><?= htmlspecialchars($p['kelas']) ?></td>
                      <td style="font-size:.75rem;">
                        <?php if ($p['no_hp']): ?><div><i class="fas fa-phone" style="width:12px;color:var(--gray-400);"></i> <?= htmlspecialchars($p['no_hp']) ?></div><?php endif; ?>
                        <?php if ($p['email']): ?><div><i class="fas fa-envelope" style="width:12px;color:var(--gray-400);"></i> <?= htmlspecialchars($p['email']) ?></div><?php endif; ?>
                      </td>
                      <td style="max-width:160px;">
                        <?php if ($p['alasan']): ?>
                          <div style="font-size:.75rem;color:var(--gray-600);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?= htmlspecialchars($p['alasan']) ?></div>
                        <?php else: ?><span style="color:var(--gray-300);font-size:.75rem;">—</span><?php endif; ?>
                      </td>
                      <td>
                        <form method="POST" style="display:inline;" autocomplete="off">
              <?= csrfField() ?>
                          <input type="hidden" name="action" value="update_pendaftar" />
                          <input type="hidden" name="pendaftar_id" value="<?= $p['id'] ?>" />
                          <input type="hidden" name="<?= 'view' ?>" value="<?= (int)$_GET['view'] ?>" />
                          <select name="status" onchange="this.form.submit()" style="width:auto;font-size:.75rem;padding:.3rem .5rem;font-weight:600;border-radius:6px;cursor:pointer;" class="status-<?= htmlspecialchars($p['status']) ?>">
                            <option value="menunggu" <?= $p['status'] === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                            <option value="diterima" <?= $p['status'] === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
                            <option value="ditolak" <?= $p['status'] === 'ditolak' ? 'selected' : '' ?>>❌ Ditolak</option>
                          </select>
                        </form>
                      </td>
                      <td style="font-size:.72rem;color:var(--gray-400);"><?= date('d/m/y', strtotime($p['created_at'])) ?></td>
                      <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pendaftar ini?')" autocomplete="off">
              <?= csrfField() ?>
                          <input type="hidden" name="action" value="hapus_pendaftar" />
                          <input type="hidden" name="pendaftar_id" value="<?= $p['id'] ?>" />
                          <button type="submit" class="btn btn-ghost btn-icon btn-xs" style="color:var(--red);"><i class="fas fa-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach;
                else: ?>
                  <tr>
                    <td colspan="9">
                      <div class="empty-state" style="padding:2rem;">
                        <div class="empty-state-icon">📋</div>
                        <h4>Belum Ada Pendaftar</h4>
                        <p>Belum ada siswa yang mendaftar ke ekskul ini.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <?php if ($viewEkskul['kuota']): ?>
            <div style="padding:.75rem 1.25rem;border-top:1px solid var(--gray-100);font-size:.78rem;color:var(--gray-500);">
              <i class="fas fa-users" style="margin-right:.4rem;"></i>
              Kuota: <strong><?= $diterima ?>/<?= $viewEkskul['kuota'] ?></strong> siswa diterima
              <?php $sisa = $viewEkskul['kuota'] - $diterima; ?>
              &mdash; <span style="color:<?= $sisa > 0 ? 'var(--teal)' : 'var(--red)' ?>;font-weight:700;"><?= max(0, $sisa) ?> slot tersisa</span>
            </div>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <!-- ===================== MAIN VIEW ===================== -->
        <div class="two-col" style="align-items:start;">

          <!-- FORM -->
          <div class="form-section anim-fade-up">
            <div class="form-section-header">
              <div class="form-section-header-icon"><i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i></div>
              <div>
                <h3><?= $isEdit ? 'Edit Ekstrakurikuler' : 'Tambah Ekskul Baru' ?></h3>
                <p>Data kegiatan ekstrakurikuler</p>
              </div>
            </div>
            <div class="form-section-body">
              <form method="POST" id="form-ekskul" autocomplete="off">
              <?= csrfField() ?>
                <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>

                <div class="form-group">
                  <label>Nama Ekskul <span class="req">*</span></label>
                  <input type="text" name="nama" value="<?= htmlspecialchars($edit['nama'] ?? '') ?>" placeholder="Contoh: Pramuka" required autocomplete="off" />
                </div>
                <div class="form-group">
                  <label>Deskripsi</label>
                  <textarea name="deskripsi" placeholder="Keterangan singkat ekskul..." autocomplete="off"><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                      <?php foreach (['olahraga' => '⚽ Olahraga', 'seni' => '🎨 Seni', 'akademik' => '📚 Akademik', 'keagamaan' => '🕌 Keagamaan', 'teknologi' => '💻 Teknologi', 'lainnya' => '🌟 Lainnya'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($edit['kategori'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Kuota Anggota</label>
                    <input type="number" name="kuota" value="<?= $edit['kuota'] ?? '' ?>" placeholder="Maks peserta" min="1" autocomplete="off" />
                  </div>
                </div>
                <div class="form-group">
                  <label>Jadwal</label>
                  <input type="text" name="jadwal" value="<?= htmlspecialchars($edit['jadwal'] ?? '') ?>" placeholder="Contoh: Selasa & Kamis, 14.00–16.00" autocomplete="off" />
                </div>
                <div class="form-group">
                  <label>Tempat</label>
                  <input type="text" name="tempat" value="<?= htmlspecialchars($edit['tempat'] ?? '') ?>" placeholder="Contoh: Lapangan Utama" autocomplete="off" />
                </div>
                <div class="form-group">
                  <label>Guru Pembina</label>
                  <select name="pembina_id">
                    <option value="">-- Pilih Pembina --</option>
                    <?php foreach ($pembinas as $p): ?>
                      <option value="<?= $p['id'] ?>" <?= ($edit['pembina_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?><?= $p['bidang'] ? ' (' . $p['bidang'] . ')' : '' ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </form>
            </div>
            <div class="form-section-footer">
              <button type="submit" form="form-ekskul" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?></button>
              <?php if ($isEdit): ?><a href="ekskul.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a><?php else: ?><button type="reset" form="form-ekskul" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</button><?php endif; ?>
              <a href="pembina.php" class="btn btn-outline btn-sm" style="margin-left:auto;"><i class="fas fa-user-plus"></i> Kelola Pembina</a>
            </div>
          </div>

          <!-- EKSKUL LIST -->
          <div class="admin-card anim-fade-up anim-delay-2">
            <div class="card-header">
              <div class="card-header-left">
                <div class="card-header-icon" style="background:var(--orange-bg);color:var(--orange);"><i class="fas fa-star"></i></div>
                <div>
                  <div class="card-header-title">Daftar Ekskul</div>
                  <div class="card-header-sub"><?= count($rows) ?> ekskul aktif</div>
                </div>
              </div>
            </div>
            <div style="max-height:620px;overflow-y:auto;padding:1rem;">
              <?php if ($rows): ?>
                <?php foreach ($rows as $r): ?>
                  <div class="data-item" style="<?= $r['total_menunggu'] > 0 ? 'border-color:var(--orange-bg);' : '' ?>">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                      <?= $catIcon[$r['kategori']] ?? '⭐' ?>
                    </div>
                    <div class="data-item-body">
                      <div class="data-item-title"><?= htmlspecialchars($r['nama']) ?></div>
                      <div class="data-item-sub" style="margin-top:.2rem;">
                        <span class="badge badge-blue"><?= htmlspecialchars($r['kategori']) ?></span>
                        <?php if ($r['nama_pembina']): ?><span style="font-size:.71rem;color:var(--gray-400);"><i class="fas fa-user-tie" style="margin-right:2px;"></i><?= htmlspecialchars($r['nama_pembina']) ?></span><?php endif; ?>
                      </div>
                      <!-- Pendaftar badges -->
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
                      </div>
                    </div>
                    <div class="data-item-actions" style="flex-direction:column;gap:.3rem;">
                      <a href="?view=<?= $r['id'] ?>" class="btn btn-outline btn-sm" title="Lihat Pendaftar" style="font-size:.72rem;">
                        <i class="fas fa-users"></i> Pendaftar
                      </a>
                      <div style="display:flex;gap:.3rem;">
                        <a href="?edit=<?= $r['id'] ?>" class="btn btn-ghost btn-icon btn-xs" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus ekskul ini?')" autocomplete="off">
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
                  <div class="empty-state-icon">⭐</div>
                  <h4>Belum Ada Ekskul</h4>
                  <p>Tambahkan ekskul menggunakan form.</p>
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