<?php
// ============================================================
// pembina.php — Guru Pembina
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola data guru pembina ekstrakurikuler.
// Data pembina digunakan oleh modul ekskul sebagai relasi foreign key.
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel kontak_pembina
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
    $jabatan = trim($_POST['jabatan'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $bidang = trim($_POST['bidang'] ?? '');
    if (!$nama) $err = 'Nama pembina wajib diisi.';
    elseif ($action === 'tambah') {
      $foto = '';
      if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
          $fname = 'pembina_' . time() . '_' . rand(100, 999) . '.' . $ext;
          $dest = '../php/uploads/foto_pembina/' . $fname;
          if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
            $foto = 'foto_pembina/' . $fname;
          }
        }
      }
      $pdo->prepare("INSERT INTO kontak_pembina (nama,jabatan,email,no_hp,bidang,foto) VALUES (?,?,?,?,?,?)")
        ->execute([$nama, $jabatan, $email, $no_hp, $bidang, $foto]);
      header('Location: pembina.php?msg=tambah');
      exit;
    } else {
      $id = (int)$_POST['id'];
      $fotoUpdate = '';
      if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
          $fname = 'pembina_' . time() . '_' . rand(100, 999) . '.' . $ext;
          $dest = '../php/uploads/foto_pembina/' . $fname;
          if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
            $fotoUpdate = $fname;
            // Delete old foto
            $stmtFoto = $pdo->prepare("SELECT foto FROM kontak_pembina WHERE id=?");
            $stmtFoto->execute([$id]);
            $oldFoto = $stmtFoto->fetchColumn();
            if ($oldFoto && file_exists('../php/uploads/' . $oldFoto)) {
              @unlink('../php/uploads/' . $oldFoto);
            }
          }
        }
      }
      if ($fotoUpdate) {
        $pdo->prepare("UPDATE kontak_pembina SET nama=?,jabatan=?,email=?,no_hp=?,bidang=?,foto=? WHERE id=?")
          ->execute([$nama, $jabatan, $email, $no_hp, $bidang, 'foto_pembina/' . $fotoUpdate, $id]);
      } else {
        $pdo->prepare("UPDATE kontak_pembina SET nama=?,jabatan=?,email=?,no_hp=?,bidang=? WHERE id=?")
          ->execute([$nama, $jabatan, $email, $no_hp, $bidang, $id]);
      }
      header('Location: pembina.php?msg=edit');
      exit;
    }
  } elseif ($action === 'hapus') {
    $id = (int)$_POST['id'];
    // Lepas relasi ekskul
    $pdo->prepare("UPDATE ekstrakurikuler SET pembina_id=NULL WHERE pembina_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM kontak_pembina WHERE id=?")->execute([$id]);
    header('Location: pembina.php?msg=hapus');
    exit;
  }
}
if (isset($_GET['msg'])) {
  $msgMap = ['tambah' => 'Guru pembina berhasil ditambahkan!', 'edit' => 'Data pembina diperbarui!', 'hapus' => 'Pembina dihapus.'];
  $msg = $msgMap[$_GET['msg']] ?? '';
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM kontak_pembina WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}
$rows = $pdo->query("SELECT k.*, COUNT(e.id) as jml_ekskul
    FROM kontak_pembina k
    LEFT JOIN ekstrakurikuler e ON e.pembina_id=k.id
    GROUP BY k.id ORDER BY k.bidang, k.nama")->fetchAll(PDO::FETCH_ASSOC);
$isEdit = !!$edit;
$bidangColors = ['Kepramukaan' => 'green', 'Keagamaan' => 'teal', 'Olahraga' => 'blue', 'Akademik' => 'purple', 'Teknologi' => 'orange', 'Seni' => 'gold', 'Koordinator Kesiswaan' => 'red'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Guru Pembina — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-chalkboard-teacher"></i> Guru Pembina</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Pembina</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit): ?><a href="pembina.php" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Tambah Baru</a><?php endif; ?>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div><?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">
      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

      <div class="two-col" style="align-items:start;">

        <!-- FORM -->
        <div class="form-section anim-fade-up">
          <div class="form-section-header">
            <div class="form-section-header-icon"><i class="fas fa-<?= $isEdit ? 'pen' : 'user-plus' ?>"></i></div>
            <div>
              <h3><?= $isEdit ? 'Edit Data Pembina' : 'Tambah Guru Pembina' ?></h3>
              <p>Data guru pembina ekskul & kegiatan</p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" id="form-pembina" autocomplete="off" enctype="multipart/form-data">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
              <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>

              <div class="form-group">
                <label>Nama Lengkap <span class="req">*</span></label>
                <div class="input-icon-wrap">
                  <i class="input-icon fas fa-user"></i>
                  <input type="text" name="nama" value="<?= htmlspecialchars($edit['nama'] ?? '') ?>"
                    placeholder="Contoh: Bpk. Ahmad Fauzi, S.Pd" required autocomplete="off" />
                </div>
              </div>

              <div class="form-group">
                <label>Jabatan</label>
                <div class="input-icon-wrap">
                  <i class="input-icon fas fa-id-badge"></i>
                  <input type="text" name="jabatan" value="<?= htmlspecialchars($edit['jabatan'] ?? '') ?>"
                    placeholder="Contoh: Pembina Pramuka" autocomplete="off" />
                </div>
              </div>

              <div class="form-group">
                <label>Bidang / Ekskul</label>
                <select name="bidang">
                  <option value="">-- Pilih Bidang --</option>
                  <?php foreach (['Kepramukaan', 'Keagamaan', 'Olahraga', 'Akademik', 'Teknologi', 'Seni', 'Koordinator Kesiswaan', 'Lainnya'] as $b): ?>
                    <option value="<?= $b ?>" <?= ($edit['bidang'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Foto Profil <span style="color:var(--gray-400);font-weight:400;">(JPG/PNG/WEBP, maks 5MB)</span></label>
                <div style="display:flex;align-items:center;gap:1rem;">
                  <div id="foto-preview-wrap" style="width:56px;height:56px;border-radius:50%;overflow:hidden;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid var(--primary-border);">
                    <?php if (!empty($edit['foto'])): ?>
                      <img src="../php/uploads/<?= htmlspecialchars($edit['foto']) ?>" id="foto-preview-img" style="width:100%;height:100%;object-fit:cover;" />
                    <?php else: ?>
                      <i class="fas fa-user" style="color:var(--primary-mid);font-size:1.4rem;" id="foto-preview-icon"></i>
                    <?php endif; ?>
                  </div>
                  <div style="flex:1;">
                    <input type="file" name="foto" id="foto-input" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewFotoPembina(this)" />
                    <button type="button" onclick="document.getElementById('foto-input').click()" class="btn btn-outline btn-sm">
                      <i class="fas fa-camera"></i> <?= !empty($edit['foto']) ? 'Ganti Foto' : 'Upload Foto' ?>
                    </button>
                    <div style="font-size:.72rem;color:var(--gray-400);margin-top:.3rem;">Foto akan tampil sebagai avatar profil lingkaran</div>
                  </div>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Email</label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-envelope"></i>
                    <input type="email" name="email" value="<?= htmlspecialchars($edit['email'] ?? '') ?>"
                      placeholder="nama@man1bangka.sch.id" autocomplete="off" />
                  </div>
                </div>
                <div class="form-group">
                  <label>No. HP / WA</label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-phone"></i>
                    <input type="tel" name="no_hp" value="<?= htmlspecialchars($edit['no_hp'] ?? '') ?>"
                      placeholder="08xxxxxxxxxx" autocomplete="off" />
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="form-pembina" class="btn btn-primary">
              <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui Data' : 'Simpan Pembina' ?>
            </button>
            <?php if ($isEdit): ?>
              <a href="pembina.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
            <?php else: ?>
              <button type="reset" form="form-pembina" class="btn btn-outline"><i class="fas fa-redo"></i> Reset</button>
            <?php endif; ?>
          </div>
        </div>

        <!-- LIST -->
        <div class="admin-card anim-fade-up anim-delay-2">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--teal-bg);color:var(--teal);"><i class="fas fa-users-cog"></i></div>
              <div>
                <div class="card-header-title">Daftar Guru Pembina</div>
                <div class="card-header-sub"><?= count($rows) ?> pembina terdaftar</div>
              </div>
            </div>
          </div>
          <div style="padding:1rem;max-height:600px;overflow-y:auto;">
            <?php if ($rows): ?>
              <?php foreach ($rows as $r): $bc = $bidangColors[$r['bidang']] ?? 'gray'; ?>
                <div class="data-item">
                  <div style="width:48px;height:48px;border-radius:50%;overflow:hidden;background:var(--primary-light);color:var(--primary-mid);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;flex-shrink:0;border:2px solid var(--primary-border);">
                    <?php if (!empty($r['foto'])): ?>
                      <img src="../php/uploads/<?= htmlspecialchars($r['foto']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="<?= htmlspecialchars($r['nama']) ?>" />
                    <?php else: ?>
                      <?= strtoupper(substr($r['nama'], 0, 1)) ?>
                    <?php endif; ?>
                  </div>
                  <div class="data-item-body">
                    <div class="data-item-title"><?= htmlspecialchars($r['nama']) ?></div>
                    <div class="data-item-sub" style="margin-top:.2rem;">
                      <?= htmlspecialchars($r['jabatan'] ?? '-') ?>
                      <?php if ($r['bidang']): ?> <span class="badge badge-<?= $bc ?>"><?= htmlspecialchars($r['bidang']) ?></span><?php endif; ?>
                      <?php if ($r['jml_ekskul'] > 0): ?><span class="badge badge-blue"><?= $r['jml_ekskul'] ?> ekskul</span><?php endif; ?>
                    </div>
                    <div style="display:flex;gap:1rem;margin-top:.35rem;font-size:.72rem;color:var(--gray-400);">
                      <?php if ($r['no_hp']): ?><span><i class="fas fa-phone" style="width:12px;margin-right:3px;"></i><?= htmlspecialchars($r['no_hp']) ?></span><?php endif; ?>
                      <?php if ($r['email']): ?><span><i class="fas fa-envelope" style="width:12px;margin-right:3px;"></i><?= htmlspecialchars($r['email']) ?></span><?php endif; ?>
                    </div>
                  </div>
                  <div class="data-item-actions">
                    <a href="?edit=<?= $r['id'] ?>" class="btn btn-outline btn-icon btn-xs" title="Edit"><i class="fas fa-pen"></i></a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pembina <?= htmlspecialchars(addslashes($r['nama']), ENT_QUOTES) ?>?')">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="hapus" />
                      <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button type="submit" class="btn btn-ghost btn-icon btn-xs" style="color:var(--red);" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">👩‍🏫</div>
                <h4>Belum Ada Pembina</h4>
                <p>Tambahkan guru pembina menggunakan form di sebelah kiri.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </main>
  <script src="assets/admin.js"></script>
  <script>
    function previewFotoPembina(input) {
      if (!input.files || !input.files[0]) return;
      const reader = new FileReader();
      reader.onload = function(e) {
        const wrap = document.getElementById('foto-preview-wrap');
        wrap.innerHTML = '<img src="' + e.target.result + '" id="foto-preview-img" style="width:100%;height:100%;object-fit:cover;"/>';
      };
      reader.readAsDataURL(input.files[0]);
    }
  </script>
</body>

</html>