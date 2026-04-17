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
$bidangColors = [
  'Kepramukaan'           => 'green',
  'Keagamaan'             => 'teal',
  'Olahraga'              => 'blue',
  'Akademik'              => 'purple',
  'Teknologi'             => 'orange',
  'Seni'                  => 'gold',
  'Koordinator Kesiswaan' => 'red',
  'Bahasa'                => 'blue',
  'Kesehatan'             => 'teal',
  'Lingkungan Hidup'      => 'green',
  'Lainnya'               => 'gray',
];
// Helper: render semua chip bidang dari string "A / B / C"
function renderBidangBadges(string $bidang, array $colors): string {
  if (!$bidang) return '';
  $parts = array_filter(array_map('trim', explode('/', $bidang)));
  $html  = '';
  foreach ($parts as $b) {
    $cls   = $colors[$b] ?? 'gray';
    $html .= '<span class="badge badge-' . $cls . '" style="margin:.1rem .15rem .1rem 0;">'
           . htmlspecialchars($b) . '</span>';
  }
  return $html;
}
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
                <input type="hidden" name="bidang" id="bidang-value"
                  value="<?= htmlspecialchars($edit['bidang'] ?? '') ?>" />

                <!-- Panduan langkah -->
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.7rem .9rem;margin-bottom:.6rem;">
                  <div style="font-size:.78rem;font-weight:700;color:#1e40af;margin-bottom:.45rem;display:flex;align-items:center;gap:.4rem;">
                    <i class="fas fa-lightbulb" style="color:#f59e0b;"></i> Cara menambah bidang:
                  </div>
                  <div style="display:flex;flex-direction:column;gap:.35rem;">
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#1e40af;">
                      <span style="background:#1e40af;color:#fff;border-radius:50%;width:18px;height:18px;min-width:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;">1</span>
                      Ketik nama bidang di kotak bawah, atau pilih dari daftar yang muncul
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#1e40af;">
                      <span style="background:#1e40af;color:#fff;border-radius:50%;width:18px;height:18px;min-width:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;">2</span>
                      Tekan <kbd style="background:#dbeafe;color:#1e40af;padding:1px 6px;border-radius:4px;font-size:.7rem;font-weight:700;border:1px solid #93c5fd;">Enter</kbd> atau klik tombol <strong>+ Tambah</strong>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#1e40af;">
                      <span style="background:#1e40af;color:#fff;border-radius:50%;width:18px;height:18px;min-width:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;">3</span>
                      Ulangi untuk bidang lain — otomatis dipisah dengan <strong>&nbsp;/&nbsp;</strong>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;color:#991b1b;margin-top:.05rem;">
                      <span style="background:#ef4444;color:#fff;border-radius:50%;width:18px;height:18px;min-width:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;">✕</span>
                      Klik chip bidang yang muncul di atas untuk <strong>menghapusnya</strong>
                    </div>
                  </div>
                </div>

                <!-- Tag chips -->
                <div id="bidang-tags" style="display:flex;flex-wrap:wrap;gap:.4rem;min-height:38px;padding:.45rem .55rem;border:1px solid var(--gray-200);border-radius:var(--radius-sm);background:#f9fafb;margin-bottom:.45rem;cursor:text;"
                  onclick="document.getElementById('bidang-input').focus()">
                </div>

                <!-- Input + tombol -->
                <div style="display:flex;gap:.4rem;align-items:center;">
                  <div class="input-icon-wrap" style="flex:1;">
                    <i class="input-icon fas fa-chalkboard"></i>
                    <input type="text" id="bidang-input"
                      placeholder="Ketik nama bidang atau pilih dari daftar..."
                      list="bidang-options" autocomplete="off" />
                  </div>
                  <button type="button" id="bidang-add-btn" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah
                  </button>
                </div>
                <datalist id="bidang-options">
                  <option value="Kepramukaan">
                  <option value="Keagamaan">
                  <option value="Olahraga">
                  <option value="Akademik">
                  <option value="Teknologi">
                  <option value="Seni">
                  <option value="Koordinator Kesiswaan">
                  <option value="Bahasa">
                  <option value="Kesehatan">
                  <option value="Lingkungan Hidup">
                  <option value="Lainnya">
                </datalist>
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
              <?php foreach ($rows as $r): ?>
                <div class="data-item">
                  <div style="width:48px;height:48px;border-radius:50%;overflow:hidden;background:var(--primary-light);color:var(--primary-mid);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;flex-shrink:0;border:2px solid var(--primary-border);">
                    <?php if (!empty($r['foto'])): ?>
                      <img src="../php/uploads/<?= htmlspecialchars($r['foto']) ?>" style="width:100%;height:100%;object-fit:cover;" alt="<?= htmlspecialchars($r['nama']) ?>" />
                    <?php else: ?>
                      <?= strtoupper(substr($r['nama'], 0, 1)) ?>
                    <?php endif; ?>
                  </div>
                  <div class="data-item-body" style="min-width:0;">
                    <div class="data-item-title"><?= htmlspecialchars($r['nama']) ?></div>
                    <?php if ($r['jabatan']): ?>
                      <div style="font-size:.78rem;color:var(--gray-500);margin-top:.15rem;">
                        <i class="fas fa-id-badge" style="width:13px;color:var(--gray-400);margin-right:3px;"></i><?= htmlspecialchars($r['jabatan']) ?>
                      </div>
                    <?php endif; ?>
                    <?php if ($r['bidang']): ?>
                      <div style="display:flex;flex-wrap:wrap;gap:.15rem;margin-top:.35rem;">
                        <?= renderBidangBadges($r['bidang'], $bidangColors) ?>
                        <?php if ($r['jml_ekskul'] > 0): ?><span class="badge badge-blue"><?= $r['jml_ekskul'] ?> ekskul</span><?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <div style="display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.35rem;font-size:.72rem;color:var(--gray-400);">
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

    /* ── Bidang multi-tag dengan pemisah " / " ── */
    (function () {
      const hiddenInput = document.getElementById('bidang-value');
      const tagsWrap    = document.getElementById('bidang-tags');
      const textInput   = document.getElementById('bidang-input');
      if (!hiddenInput || !tagsWrap || !textInput) return;

      // Warna chip per bidang
      const chipColors = {
        'Kepramukaan': '#dcfce7:#166534', 'Keagamaan': '#ccfbf1:#0f766e',
        'Olahraga': '#dbeafe:#1e40af',    'Akademik': '#ede9fe:#5b21b6',
        'Teknologi': '#ffedd5:#9a3412',   'Seni': '#fef9c3:#92400e',
        'Koordinator Kesiswaan': '#fee2e2:#991b1b',
        'Bahasa': '#e0f2fe:#0369a1',      'Kesehatan': '#d1fae5:#065f46',
      };

      // Render chips dari nilai hidden (pisah dengan " / ")
      function renderTags() {
        tagsWrap.innerHTML = '';
        const vals = hiddenInput.value
          .split('/')
          .map(v => v.trim())
          .filter(v => v.length > 0);

        vals.forEach(v => {
          const pair = chipColors[v] ? chipColors[v].split(':') : ['#f3f4f6', '#374151'];
          const chip = document.createElement('span');
          chip.style.cssText = `
            display:inline-flex;align-items:center;gap:.3rem;
            background:${pair[0]};color:${pair[1]};
            padding:.2rem .6rem;border-radius:99px;
            font-size:.75rem;font-weight:600;cursor:pointer;
            border:1px solid ${pair[1]}33;
            transition:opacity .15s;
          `;
          chip.title = 'Klik untuk hapus';
          chip.innerHTML = `${v} <i class="fas fa-times" style="font-size:.6rem;opacity:.7;"></i>`;
          chip.addEventListener('click', () => removeTag(v));
          chip.addEventListener('mouseenter', () => chip.style.opacity = '.7');
          chip.addEventListener('mouseleave', () => chip.style.opacity = '1');
          tagsWrap.appendChild(chip);
        });

        // Placeholder saat kosong
        if (vals.length === 0) {
          tagsWrap.style.border = '1px solid var(--gray-200)';
        } else {
          tagsWrap.style.border = '1.5px solid var(--primary-border)';
        }
      }

      function addBidang() {
        const val = textInput.value.trim();
        if (!val) return;

        const current = hiddenInput.value
          .split('/')
          .map(v => v.trim())
          .filter(v => v.length > 0);

        // Cegah duplikat
        if (current.map(v => v.toLowerCase()).includes(val.toLowerCase())) {
          textInput.value = '';
          textInput.focus();
          return;
        }

        current.push(val);
        hiddenInput.value = current.join(' / ');
        textInput.value = '';
        textInput.focus();
        renderTags();
      }

      function removeTag(val) {
        const current = hiddenInput.value
          .split('/')
          .map(v => v.trim())
          .filter(v => v.length > 0 && v.toLowerCase() !== val.toLowerCase());
        hiddenInput.value = current.join(' / ');
        renderTags();
      }

      // Expose ke global (tidak dipakai lagi tapi tetap ada untuk backward compat)
      window.addBidang = addBidang;

      // Tekan Enter di input
      textInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          addBidang();
        }
      });

      // Tombol Tambah — pakai mousedown bukan click
      // agar nilai input belum hilang saat blur terjadi sebelum click
      const addBtn = document.getElementById('bidang-add-btn');
      if (addBtn) {
        addBtn.addEventListener('mousedown', function(e) {
          e.preventDefault(); // Cegah blur pada input
          addBidang();
        });
      }

      // Init render dari nilai yang sudah ada (mode edit)
      renderTags();
    })();
  </script>
</body>

</html>