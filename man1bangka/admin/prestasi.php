<?php
// ============================================================
// prestasi.php — Prestasi Siswa
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola data prestasi siswa.
// Mendukung upload foto (JPG/PNG/WEBP) dan video (MP4) ke php/uploads/prestasi/.
// Fitur upload: preview gambar otomatis sebelum simpan, video format note,
//              validasi ekstensi sisi klien & server, error panel inline.
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel prestasi + upload file opsional
//   hapus       -> delete by id + hapus file fisik terkait
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = ''; // Flash messages sukses/error
$UPLOAD_DIR = '../php/uploads/prestasi/'; // Folder upload foto/video prestasi (JPG, PNG, WEBP, MP4)

// Ensure upload directory exists
// Buat folder upload jika belum ada (misal: pertama kali deploy)
if (!is_dir($UPLOAD_DIR)) @mkdir($UPLOAD_DIR, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf(); // Tolak jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';
  if ($action === 'tambah' || $action === 'edit') {
    $f = ['judul', 'siswa', 'kelas', 'jenis', 'posisi', 'tingkat', 'penyelenggara', 'tahun'];
    $v = array_map(fn($k) => trim($_POST[$k] ?? ''), $f);
    $desk = trim($_POST['deskripsi'] ?? '');
    $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;
    if (!$v[0] || !$v[1]) {
      $err = 'Judul dan nama siswa wajib.';
    } else {
      $url_file = '';
      if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4'];
        if (!in_array($ext, $allowed)) {
          $err = 'Format tidak didukung. Gunakan JPG, PNG, WEBP, atau MP4.';
        } elseif ($_FILES['file']['size'] > 30 * 1024 * 1024) {
          $err = 'File maks 30MB.';
        } else {
          $fname = uniqid('prestasi_', true) . '.' . $ext;
          // Pindahkan file dari tmp ke folder prestasi jika upload berhasil
          if (move_uploaded_file($_FILES['file']['tmp_name'], $UPLOAD_DIR . $fname))
            $url_file = 'php/uploads/prestasi/' . $fname;
        }
      }
      if (!$err) {
        if ($action === 'tambah') {
          $pdo->prepare("INSERT INTO prestasi (judul,siswa,kelas,jenis,posisi,tingkat,penyelenggara,tahun,deskripsi,url_file,ekskul_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
            ->execute(array_merge($v, [$desk, $url_file, $ekskul_id]));
          header('Location: prestasi.php?msg=tambah');
          exit;
        } else {
          $id = (int)$_POST['id'];
          if ($url_file) {
            // Hapus file lama dari disk sebelum menyimpan file baru
            $oldFile = $pdo->prepare("SELECT url_file FROM prestasi WHERE id=?");
            $oldFile->execute([$id]);
            $old = $oldFile->fetchColumn();
            if ($old && file_exists('../' . $old)) @unlink('../' . $old);

            $sql = "UPDATE prestasi SET judul=?,siswa=?,kelas=?,jenis=?,posisi=?,tingkat=?,penyelenggara=?,tahun=?,deskripsi=?,url_file=?,ekskul_id=? WHERE id=?";
            $pdo->prepare($sql)->execute(array_merge($v, [$desk, $url_file, $ekskul_id, $id]));
          } else {
            $sql = "UPDATE prestasi SET judul=?,siswa=?,kelas=?,jenis=?,posisi=?,tingkat=?,penyelenggara=?,tahun=?,deskripsi=?,ekskul_id=? WHERE id=?";
            $pdo->prepare($sql)->execute(array_merge($v, [$desk, $ekskul_id, $id]));
          }
          header('Location: prestasi.php?msg=edit');
          exit;
        }
      }
    }
  } elseif ($action === 'hapus') {
    $id = (int)$_POST['id'];
    $r = $pdo->prepare("SELECT url_file FROM prestasi WHERE id=?");
    $r->execute([$id]);
    $r = $r->fetch(PDO::FETCH_ASSOC);
    if ($r && $r['url_file']) @unlink('../' . $r['url_file']);
    $pdo->prepare("DELETE FROM prestasi WHERE id=?")->execute([$id]);
    header('Location: prestasi.php?msg=hapus');
    exit;
  }
}
if (isset($_GET['msg'])) {
  $msgMap = ['tambah' => 'Prestasi ditambahkan!', 'edit' => 'Prestasi diperbarui!', 'hapus' => 'Prestasi dihapus.'];
  $msg = $msgMap[$_GET['msg']] ?? '';
}
$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM prestasi WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}
$rows = $pdo->query("SELECT * FROM prestasi ORDER BY tahun DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$isEdit = !!$edit;
$ekskulList = $pdo->query("SELECT id, nama FROM ekstrakurikuler ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
$medals = ['sekolah' => '⭐', 'kabupaten' => '🥉', 'provinsi' => '🥈', 'nasional' => '🥇', 'internasional' => '🏆'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Prestasi — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-trophy"></i> Prestasi Siswa</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Prestasi</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit): ?><a href="prestasi.php" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Tambah Baru</a><?php endif; ?>
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
            <div class="form-section-header-icon"><i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i></div>
            <div>
              <h3><?= $isEdit ? 'Edit Prestasi' : 'Tambah Prestasi Baru' ?></h3>
              <p>Data pencapaian siswa</p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" enctype="multipart/form-data" id="form-prestasi" autocomplete="off">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
              <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>

              <div class="form-group"><label>Judul Prestasi <span class="req">*</span></label><input type="text" name="judul" value="<?= htmlspecialchars($edit['judul'] ?? '') ?>" placeholder="Contoh: Juara 1 Olimpiade Matematika" required /></div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group"><label>Nama Siswa <span class="req">*</span></label><input type="text" name="siswa" value="<?= htmlspecialchars($edit['siswa'] ?? '') ?>" required /></div>
                <div class="form-group"><label>Kelas</label><select name="kelas">
                    <option value="">-- Pilih Kelas --</option>
                    <?php $k = ['10A', '10B', '10C', '10D', '10E', '10F', '11A', '11B', '11C', '11D', '11E', '11F', '12A', '12B', '12C', '12D', '12E', '12F'];
                    foreach ($k as $kl): ?>
                      <option value="<?= $kl ?>" <?= ($edit['kelas'] ?? '') === $kl ? 'selected' : '' ?>><?= $kl ?></option>
                    <?php endforeach; ?>
                  </select></div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group"><label>Jenis</label>
                  <select name="jenis"><?php foreach (['akademik' => 'Akademik', 'olahraga' => 'Olahraga', 'seni' => 'Seni', 'keagamaan' => 'Keagamaan', 'teknologi' => 'Teknologi', 'lainnya' => 'Lainnya'] as $v => $l): ?><option value="<?= $v ?>" <?= ($edit['jenis'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
                </div>
                <div class="form-group"><label>Posisi / Penghargaan</label><input type="text" name="posisi" value="<?= htmlspecialchars($edit['posisi'] ?? '') ?>" placeholder="Juara 1, Harapan II..." /></div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr 80px;gap:.85rem;">
                <div class="form-group"><label>Tingkat</label>
                  <select name="tingkat"><?php foreach (['sekolah' => 'Sekolah', 'kabupaten' => 'Kabupaten', 'provinsi' => 'Provinsi', 'nasional' => 'Nasional', 'internasional' => 'Internasional'] as $v => $l): ?><option value="<?= $v ?>" <?= ($edit['tingkat'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
                </div>
                <div class="form-group"><label>Penyelenggara</label><input type="text" name="penyelenggara" value="<?= htmlspecialchars($edit['penyelenggara'] ?? '') ?>" /></div>
                <div class="form-group"><label>Tahun</label><input type="number" name="tahun" value="<?= $edit['tahun'] ?? date('Y') ?>" min="2010" max="2030" /></div>
              </div>

              <!-- Deskripsi -->
              <div class="form-group">
                <label>Deskripsi Prestasi</label>
                <textarea name="deskripsi" placeholder="Ceritakan singkat tentang prestasi ini, proses meraihnya, atau hal menarik lainnya..." style="min-height:100px;" autocomplete="off"><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label>Ekstrakurikuler <span style="color:var(--gray-400);font-weight:400;">(opsional — jika prestasi dari kegiatan ekskul)</span></label>
                <select name="ekskul_id">
                  <option value="">— Prestasi Akademik / Individual —</option>
                  <?php foreach ($ekskulList as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= ($edit['ekskul_id'] ?? null) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Upload foto/video -->
              <div class="form-group">
                <label>Foto / Video <span style="color:var(--gray-400);font-weight:400;">(JPG, PNG, WEBP, MP4 — maks 30MB)</span></label>

                <!-- Image Preview (tampil saat foto dipilih) -->
                <div id="prestasi-img-preview-wrap" style="display:none;margin-bottom:.75rem;border-radius:var(--radius-sm);overflow:hidden;border:2px solid var(--primary-border);background:var(--gray-50);position:relative;">
                  <img id="prestasi-img-preview" src="" alt="Preview" style="width:100%;max-height:220px;object-fit:cover;display:block;" />
                  <div style="position:absolute;top:.5rem;right:.5rem;">
                    <button type="button" onclick="resetPrestasiFile()" style="background:rgba(0,0,0,.55);color:#fff;border:none;border-radius:99px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.75rem;" title="Hapus file">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div id="prestasi-img-name-bar" style="background:rgba(0,0,0,.5);color:#fff;font-size:.72rem;padding:.35rem .75rem;position:absolute;bottom:0;left:0;right:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                </div>

                <div class="upload-file-box" id="prestasi-file-box" onclick="document.getElementById('prestasi-file-input').click()">
                  <div class="ufb-icon"><i class="fas fa-file-upload" id="prestasi-ufb-icon"></i></div>
                  <div class="ufb-text">
                    <span id="prestasi-ufb-label">Klik untuk memilih file</span>
                    <span id="prestasi-ufb-hint">JPG, PNG, WEBP, MP4 — Maks 30MB</span>
                  </div>
                  <input type="file" name="file" id="prestasi-file-input" accept=".jpg,.jpeg,.png,.webp,.mp4" style="display:none;" onchange="onPrestasiFile(this)" />
                </div>

                <!-- Note format video -->
                <div id="prestasi-video-note" style="display:none;margin-top:.6rem;padding:.75rem 1rem;border-radius:var(--radius-sm);border:1.5px solid #fde68a;background:#fffbeb;">
                  <div style="display:flex;align-items:flex-start;gap:.6rem;">
                    <i class="fas fa-info-circle" style="color:#d97706;margin-top:2px;flex-shrink:0;"></i>
                    <div style="font-size:.8rem;color:#92400e;line-height:1.6;">
                      <strong>Format Video yang Didukung:</strong><br>
                      <div style="display:flex;flex-direction:column;gap:.25rem;margin-top:.3rem;">
                        <span style="display:flex;align-items:center;gap:.4rem;">
                          <span style="width:18px;height:18px;border-radius:50%;background:#16a34a;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0;">✓</span>
                          <strong style="color:#15803d;">.MP4</strong> &mdash; Format didukung, dapat diputar di website
                        </span>
                        <span style="display:flex;align-items:center;gap:.4rem;">
                          <span style="width:18px;height:18px;border-radius:50%;background:#dc2626;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0;">✕</span>
                          <strong style="color:#dc2626;">.MOV, .AVI, .MKV, .WMV</strong> &mdash; Tidak didukung, tidak bisa diupload
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Error format -->
                <div id="prestasi-file-err" style="display:none;margin-top:.6rem;padding:.75rem 1rem;border-radius:var(--radius-sm);border:1.5px solid #fca5a5;background:#fef2f2;align-items:flex-start;gap:.6rem;">
                  <i class="fas fa-times-circle" style="color:#dc2626;margin-top:2px;flex-shrink:0;"></i>
                  <div id="prestasi-file-err-msg" style="font-size:.8rem;color:#991b1b;line-height:1.6;"></div>
                </div>

                <?php if ($isEdit && ($edit['url_file'] ?? '')): ?>
                  <div style="display:flex;align-items:center;gap:.5rem;margin-top:.6rem;padding:.6rem .85rem;background:var(--primary-light);border-radius:var(--radius-sm);font-size:.78rem;border:1px solid var(--primary-border);">
                    <i class="fas fa-paperclip" style="color:var(--primary-mid);"></i>
                    <span style="color:var(--gray-600);">File saat ini:</span>
                    <strong style="color:var(--primary);"><?= basename($edit['url_file']) ?></strong>
                    <a href="../<?= htmlspecialchars($edit['url_file']) ?>" target="_blank" class="badge badge-green" style="margin-left:auto;text-decoration:none;">
                      <i class="fas fa-eye" style="font-size:.6rem;"></i> Lihat
                    </a>
                    <span style="color:var(--gray-400);">(Upload baru untuk mengganti)</span>
                  </div>
                <?php endif; ?>
              </div>

            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="form-prestasi" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui' : 'Simpan' ?></button>
            <?php if ($isEdit): ?><a href="prestasi.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
            <?php else: ?><button type="reset" form="form-prestasi" class="btn btn-outline" onclick="resetPrestasiFile()"><i class="fas fa-redo"></i> Reset</button><?php endif; ?>
            <span style="margin-left:auto;font-size:.74rem;color:var(--gray-400);">* wajib diisi</span>
          </div>
        </div>

        <!-- LIST -->
        <div class="admin-card anim-fade-up anim-delay-2">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--purple-bg);color:var(--purple);"><i class="fas fa-trophy"></i></div>
              <div>
                <div class="card-header-title">Data Prestasi</div>
                <div class="card-header-sub"><?= count($rows) ?> prestasi</div>
              </div>
            </div>
          </div>
          <div style="max-height:680px;overflow-y:auto;padding:1rem;">
            <?php if ($rows): foreach ($rows as $r): ?>
                <div class="data-item">
                  <div style="font-size:1.5rem;flex-shrink:0;"><?= $medals[$r['tingkat']] ?? '⭐' ?></div>
                  <div class="data-item-body">
                    <div class="data-item-title"><?= htmlspecialchars($r['judul']) ?></div>
                    <div class="data-item-sub">
                      <i class="fas fa-user" style="width:12px;"></i> <?= htmlspecialchars($r['siswa']) ?> — <?= htmlspecialchars($r['kelas']) ?>
                      <span class="badge badge-<?= $r['tingkat'] === 'nasional' ? 'red' : ($r['tingkat'] === 'provinsi' ? 'purple' : 'blue') ?>"><?= htmlspecialchars($r['tingkat']) ?></span>
                      <span class="badge badge-gray"><?= $r['tahun'] ?></span>
                      <?php if ($r['url_file'] ?? ''): ?>
                        <a href="../<?= htmlspecialchars($r['url_file']) ?>" target="_blank" class="badge badge-green" style="text-decoration:none;cursor:pointer;">
                          <i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> Lihat File
                        </a>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($r['deskripsi'])): ?>
                      <div style="font-size:.72rem;color:var(--gray-400);margin-top:.25rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:260px;"><?= htmlspecialchars(substr($r['deskripsi'], 0, 80)) ?>...</div>
                    <?php endif; ?>
                  </div>
                  <div class="data-item-actions">
                    <a href="?edit=<?= $r['id'] ?>" class="btn btn-outline btn-icon btn-xs"><i class="fas fa-pen"></i></a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus prestasi ini?')" autocomplete="off">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="hapus" /><input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button class="btn btn-ghost btn-icon btn-xs" style="color:var(--red)"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </div>
              <?php endforeach;
            else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">🏆</div>
                <h4>Belum Ada Data</h4>
                <p>Tambahkan prestasi siswa melalui form.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
      <!-- end two-col -->

      <style>
        .upload-file-box {
          display: flex;
          align-items: center;
          gap: 1rem;
          padding: 1rem 1.25rem;
          border: 2px dashed var(--gray-300);
          border-radius: var(--radius-sm);
          cursor: pointer;
          transition: var(--transition);
          background: var(--gray-50);
        }

        .upload-file-box:hover {
          border-color: var(--primary-mid);
          background: var(--primary-light);
        }

        .upload-file-box.has-file {
          border-style: solid;
          border-color: var(--primary-mid);
          background: var(--primary-light);
        }

        .ufb-icon {
          width: 44px;
          height: 44px;
          border-radius: var(--radius-sm);
          background: var(--white);
          border: 1px solid var(--gray-200);
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.1rem;
          color: var(--primary-mid);
          flex-shrink: 0;
          transition: var(--transition);
        }

        .upload-file-box.has-file .ufb-icon {
          background: var(--primary-mid);
          color: #fff;
          border-color: var(--primary-mid);
        }

        .ufb-text {
          display: flex;
          flex-direction: column;
        }

        .ufb-text #prestasi-ufb-label {
          font-size: .83rem;
          font-weight: 700;
          color: var(--gray-700);
        }

        .ufb-text #prestasi-ufb-hint {
          font-size: .72rem;
          color: var(--gray-400);
          margin-top: 2px;
        }

        .upload-file-box.has-file .ufb-text #prestasi-ufb-label {
          color: var(--primary);
        }
      </style>

      <script>
        (function initPrestasiScripts() {
          window.onPrestasiFile = function(input) {
            var box = document.getElementById('prestasi-file-box');
            var lbl = document.getElementById('prestasi-ufb-label');
            var hint = document.getElementById('prestasi-ufb-hint');
            var icon = document.getElementById('prestasi-ufb-icon');
            var prev = document.getElementById('prestasi-img-preview');
            var prevW = document.getElementById('prestasi-img-preview-wrap');
            var nameBar = document.getElementById('prestasi-img-name-bar');
            var vidNote = document.getElementById('prestasi-video-note');
            var errDiv = document.getElementById('prestasi-file-err');
            var errMsg = document.getElementById('prestasi-file-err-msg');

            // Reset error
            errDiv.style.display = 'none';
            box.style.borderColor = '';

            if (!input.files || !input.files[0]) {
              resetPrestasiFile();
              return;
            }
            var f = input.files[0];
            var ext = f.name.split('.').pop().toLowerCase();
            var imgExts = ['jpg', 'jpeg', 'png', 'webp'];
            var allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4'];

            if (!allowed.includes(ext)) {
              errMsg.innerHTML = '<strong>Format .' + ext.toUpperCase() + ' tidak didukung!</strong><br>Gunakan JPG, PNG, WEBP, atau MP4.';
              errDiv.style.display = 'flex';
              box.style.borderColor = 'var(--red)';
              input.value = '';
              return;
            }

            var iconMap = {
              jpg: 'fa-file-image',
              jpeg: 'fa-file-image',
              png: 'fa-file-image',
              webp: 'fa-file-image',
              mp4: 'fa-file-video'
            };
            icon.className = 'fas ' + (iconMap[ext] || 'fa-file');
            lbl.textContent = f.name;
            hint.textContent = (f.size / 1024 / 1024).toFixed(2) + ' MB — File siap diupload ✓';
            box.classList.add('has-file');

            // Image preview
            if (imgExts.includes(ext)) {
              var reader = new FileReader();
              reader.onload = function(e) {
                prev.src = e.target.result;
                nameBar.textContent = f.name;
                prevW.style.display = 'block';
              };
              reader.readAsDataURL(f);
              vidNote.style.display = 'none';
            } else if (ext === 'mp4') {
              prevW.style.display = 'none';
              vidNote.style.display = 'block';
            }
          };

          window.resetPrestasiFile = function() {
            var input = document.getElementById('prestasi-file-input');
            var box = document.getElementById('prestasi-file-box');
            var lbl = document.getElementById('prestasi-ufb-label');
            var hint = document.getElementById('prestasi-ufb-hint');
            var icon = document.getElementById('prestasi-ufb-icon');
            var prevW = document.getElementById('prestasi-img-preview-wrap');
            var vidNote = document.getElementById('prestasi-video-note');
            var errDiv = document.getElementById('prestasi-file-err');
            if (input) input.value = '';
            if (box) {
              box.classList.remove('has-file');
              box.style.borderColor = '';
            }
            if (lbl) lbl.textContent = 'Klik untuk memilih file';
            if (hint) hint.textContent = 'JPG, PNG, WEBP, MP4 — Maks 30MB';
            if (icon) icon.className = 'fas fa-file-upload';
            if (prevW) prevW.style.display = 'none';
            if (vidNote) vidNote.style.display = 'none';
            if (errDiv) errDiv.style.display = 'none';
          };
        })();
      </script>

    </div><!-- end page-content -->
  </main>
  <script src="assets/admin.js"></script>
</body>

</html>