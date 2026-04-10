<?php
// ============================================================
// karya.php — Karya Siswa
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola karya siswa (tulisan, seni, dll).
// Mendukung upload file karya ke php/uploads/karya/.
//
// Aksi POST yang ditangani:
//   tambah/edit -> insert/update tabel karya_siswa + upload file
//   hapus       -> delete by id + hapus file fisik terkait
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = ''; // Flash messages sukses/error
$UPLOAD_DIR = '../php/uploads/karya/'; // Folder penyimpanan file karya siswa

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verifyCsrf(); // Tolak jika CSRF token tidak valid
  $action = $_POST['action'] ?? '';
  if ($action === 'tambah' || $action === 'edit') {
    $judul = trim($_POST['judul'] ?? '');
    $siswa = trim($_POST['siswa'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $jenis = $_POST['jenis'] ?? 'artikel';
    $desk = trim($_POST['deskripsi'] ?? '');
    $penghargaan = trim($_POST['penghargaan'] ?? '');
    $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;
    if (!$judul || !$siswa) {
      $err = 'Judul dan nama siswa wajib diisi.';
    } else {
      $url_file = '';
      if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'mp4'];
        if (!in_array($ext, $allowed)) {
          $err = 'Format tidak didukung. Video harus MP4, gambar JPG/PNG/WEBP, dokumen PDF.';
        } elseif ($_FILES['file']['size'] > 30 * 1024 * 1024) {
          $err = 'File maks 30MB.';
        } else {
          $fname = uniqid('karya_', true) . '.' . $ext;
          // Pindahkan file karya dari tmp ke folder karya/ jika upload berhasil
          if (move_uploaded_file($_FILES['file']['tmp_name'], $UPLOAD_DIR . $fname))
            $url_file = 'php/uploads/karya/' . $fname;
        }
      }
      if (!$err) {
        if ($action === 'tambah') {
          $pdo->prepare("INSERT INTO karya_siswa (judul,penulis,siswa,kelas,jenis,deskripsi,penghargaan,url_file,ekskul_id,tanggal) VALUES (?,?,?,?,?,?,?,?,?,NOW())")
            ->execute([$judul, $siswa, $siswa, $kelas, $jenis, $desk, $penghargaan, $url_file, $ekskul_id]);
          header('Location: karya.php?msg=tambah');
          exit;
        } else {
          $id = (int)$_POST['id'];
          if ($url_file) {
            // Hapus file lama dari disk sebelum menyimpan file baru
            $oldFile = $pdo->prepare("SELECT url_file FROM karya_siswa WHERE id=?");
            $oldFile->execute([$id]);
            $old = $oldFile->fetchColumn();
            if ($old && file_exists('../' . $old)) @unlink('../' . $old);

            $sql = "UPDATE karya_siswa SET judul=?,penulis=?,siswa=?,kelas=?,jenis=?,deskripsi=?,penghargaan=?,ekskul_id=?,url_file=? WHERE id=?";
            $pdo->prepare($sql)->execute([$judul, $siswa, $siswa, $kelas, $jenis, $desk, $penghargaan, $ekskul_id, $url_file, $id]);
          } else {
            $sql = "UPDATE karya_siswa SET judul=?,penulis=?,siswa=?,kelas=?,jenis=?,deskripsi=?,penghargaan=?,ekskul_id=? WHERE id=?";
            $pdo->prepare($sql)->execute([$judul, $siswa, $siswa, $kelas, $jenis, $desk, $penghargaan, $ekskul_id, $id]);
          }
          header('Location: karya.php?msg=edit');
          exit;
        }
      }
    }
  } elseif ($action === 'hapus') {
    $id = (int)$_POST['id'];
    $r = $pdo->prepare("SELECT url_file FROM karya_siswa WHERE id=?");
    $r->execute([$id]);
    $r = $r->fetch(PDO::FETCH_ASSOC);
    if ($r && $r['url_file']) @unlink('../' . $r['url_file']);
    $pdo->prepare("DELETE FROM karya_siswa WHERE id=?")->execute([$id]);
    header('Location: karya.php?msg=hapus');
    exit;
  }
}
if (isset($_GET['msg'])) {
  $msgMap = ['tambah' => 'Karya berhasil ditambahkan!', 'edit' => 'Karya berhasil diperbarui!', 'hapus' => 'Karya dihapus.'];
  $msg = $msgMap[$_GET['msg']] ?? '';
}

$edit = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM karya_siswa WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch(PDO::FETCH_ASSOC);
}
$rows = $pdo->query("SELECT * FROM karya_siswa ORDER BY tanggal DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$isEdit = !!$edit;
$ekskulList = $pdo->query("SELECT id, nama FROM ekstrakurikuler ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);

$jenisInfo = [
  'artikel'      => ['icon' => '📝', 'label' => 'Artikel',      'color' => 'blue'],
  'karya_ilmiah' => ['icon' => '🔬', 'label' => 'Karya Ilmiah', 'color' => 'purple'],
  'poster'       => ['icon' => '🖼️', 'label' => 'Poster',       'color' => 'orange'],
  'video'        => ['icon' => '🎬', 'label' => 'Video',         'color' => 'red'],
  'puisi'        => ['icon' => '✍️', 'label' => 'Puisi',         'color' => 'teal'],
  'lainnya'      => ['icon' => '⭐', 'label' => 'Lainnya',       'color' => 'gray'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Karya Siswa — Admin MAN 1 Bangka</title>
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
          <div class="topbar-title"><i class="fas fa-palette"></i> Karya Siswa</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Karya Siswa</div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($isEdit): ?><a href="karya.php" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> Tambah Baru</a><?php endif; ?>
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

        <!-- FORM -->
        <div class="form-section anim-fade-up">
          <div class="form-section-header">
            <div class="form-section-header-icon"><i class="fas fa-<?= $isEdit ? 'pen' : 'plus' ?>"></i></div>
            <div>
              <h3><?= $isEdit ? 'Edit Karya' : 'Tambah Karya Baru' ?></h3>
              <p><?= $isEdit ? 'Perbarui informasi karya siswa' : 'Tambahkan karya atau hasil kreativitas siswa' ?></p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" enctype="multipart/form-data" id="form-karya" autocomplete="off">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="<?= $isEdit ? 'edit' : 'tambah' ?>" />
              <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>" /><?php endif; ?>

              <div class="form-group">
                <label>Judul Karya <span class="req">*</span></label>
                <input type="text" name="judul" value="<?= htmlspecialchars($edit['judul'] ?? '') ?>"
                  placeholder="Contoh: Inovasi Pupuk Organik dari Kelapa Sawit" required autocomplete="off" />
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Nama Siswa <span class="req">*</span></label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-user"></i>
                    <input type="text" name="siswa" value="<?= htmlspecialchars($edit['siswa'] ?? '') ?>"
                      placeholder="Nama lengkap" required autocomplete="off" />
                  </div>
                </div>
                <div class="form-group">
                  <label>Kelas</label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-graduation-cap"></i>
                    <select name="kelas" autocomplete="off">
                      <option value="">-- Pilih Kelas --</option>
                      <?php $kk = ['10A', '10B', '10C', '10D', '10E', '10F', '11A', '11B', '11C', '11D', '11E', '11F', '12A', '12B', '12C', '12D', '12E', '12F'];
                      foreach ($kk as $kl): ?>
                        <option value="<?= $kl ?>" <?= ($edit['kelas'] ?? '') === $kl ? 'selected' : '' ?>><?= $kl ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Jenis Karya</label>
                  <select name="jenis">
                    <?php foreach ($jenisInfo as $v => $info): ?>
                      <option value="<?= $v ?>" <?= ($edit['jenis'] ?? 'artikel') === $v ? 'selected' : '' ?>>
                        <?= $info['icon'] ?> <?= $info['label'] ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Penghargaan / Prestasi</label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-award"></i>
                    <input type="text" name="penghargaan" value="<?= htmlspecialchars($edit['penghargaan'] ?? '') ?>"
                      placeholder="Contoh: Juara 1 Nasional" autocomplete="off" />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label>Deskripsi Karya</label>
                <textarea name="deskripsi" placeholder="Jelaskan singkat tentang karya ini..." style="min-height:100px;" autocomplete="off"><?= htmlspecialchars($edit['deskripsi'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label>Ekstrakurikuler <span style="color:var(--gray-400);font-weight:400;">(opsional — jika karya dari kegiatan ekskul)</span></label>
                <select name="ekskul_id">
                  <option value="">— Karya Mandiri / Bukan dari Ekskul —</option>
                  <?php foreach ($ekskulList as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= ($edit['ekskul_id'] ?? null) == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nama']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Upload file styled -->
              <div class="form-group">
                <label>Upload File <span style="color:var(--gray-400);font-weight:400;">(PDF / Gambar / Video, maks 30MB)</span></label>
                <div class="upload-file-box" id="file-box" onclick="document.getElementById('karya-file').click()">
                  <div class="ufb-icon"><i class="fas fa-file-upload" id="ufb-icon-i"></i></div>
                  <div class="ufb-text">
                    <span id="ufb-label">Klik untuk memilih file</span>
                    <span id="ufb-hint">PDF, JPG, PNG, MP4 — Maks 30MB</span>
                  </div>
                  <input type="file" name="file" id="karya-file" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4"
                    style="display:none;" onchange="onKaryaFile(this)" />
                </div>

                <!-- Note format video - tampil otomatis jika jenis=video -->
                <div id="karya-video-note" style="display:none;margin-top:.6rem;padding:.75rem 1rem;border-radius:var(--radius-sm);border:1.5px solid #fde68a;background:#fffbeb;">
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
                        <span style="display:flex;align-items:center;gap:.4rem;">
                          <span style="width:18px;height:18px;border-radius:50%;background:#2563eb;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0;">i</span>
                          Konversi ke MP4 menggunakan <strong>HandBrake</strong> (gratis di handbrake.fr)
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Error non-mp4 -->
                <div id="karya-file-err" style="display:none;margin-top:.6rem;padding:.75rem 1rem;border-radius:var(--radius-sm);border:1.5px solid #fca5a5;background:#fef2f2;align-items:flex-start;gap:.6rem;">
                  <i class="fas fa-times-circle" style="color:#dc2626;margin-top:2px;flex-shrink:0;"></i>
                  <div id="karya-file-err-msg" style="font-size:.8rem;color:#991b1b;line-height:1.6;"></div>
                </div>

                <?php if ($isEdit && ($edit['url_file'] ?? '')): ?>
                  <div style="display:flex;align-items:center;gap:.5rem;margin-top:.6rem;padding:.6rem .85rem;background:var(--primary-light);border-radius:var(--radius-sm);font-size:.78rem;border:1px solid var(--primary-border);">
                    <i class="fas fa-paperclip" style="color:var(--primary-mid);"></i>
                    <span style="color:var(--gray-600);">File saat ini:</span>
                    <strong style="color:var(--primary);"><?= basename($edit['url_file']) ?></strong>
                    <span style="color:var(--gray-400);margin-left:.25rem;">(Upload baru untuk mengganti)</span>
                  </div>
                <?php endif; ?>
              </div>
            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="form-karya" class="btn btn-primary">
              <i class="fas fa-save"></i> <?= $isEdit ? 'Perbarui Karya' : 'Simpan Karya' ?>
            </button>
            <?php if ($isEdit): ?>
              <a href="karya.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
            <?php else: ?>
              <button type="reset" form="form-karya" class="btn btn-outline" onclick="resetKaryaFile()"><i class="fas fa-redo"></i> Reset</button>
            <?php endif; ?>
            <span style="margin-left:auto;font-size:.74rem;color:var(--gray-400);">* wajib diisi</span>
          </div>
        </div>

        <!-- LIST -->
        <div class="admin-card anim-fade-up anim-delay-2">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--orange-bg);color:var(--orange);">
                <i class="fas fa-palette"></i>
              </div>
              <div>
                <div class="card-header-title">Daftar Karya Siswa</div>
                <div class="card-header-sub"><?= count($rows) ?> karya terdaftar</div>
              </div>
            </div>
          </div>
          <div style="max-height:680px;overflow-y:auto;padding:1rem;">
            <?php if ($rows): ?>
              <?php foreach ($rows as $r): $ji = $jenisInfo[$r['jenis']] ?? $jenisInfo['lainnya']; ?>
                <div class="data-item">
                  <!-- Jenis icon -->
                  <div style="width:42px;height:42px;border-radius:10px;background:var(--<?= $ji['color'] ?>-bg,var(--gray-100));display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                    <?= $ji['icon'] ?>
                  </div>
                  <div class="data-item-body">
                    <div class="data-item-title"><?= htmlspecialchars($r['judul']) ?></div>
                    <div style="font-size:.75rem;color:var(--gray-500);margin-top:.15rem;">
                      <i class="fas fa-user" style="width:12px;margin-right:2px;"></i>
                      <?= htmlspecialchars($r['siswa'] ?? $r['penulis'] ?? '-') ?>
                      <?php if ($r['kelas']): ?>&nbsp;&mdash;&nbsp;<?= htmlspecialchars($r['kelas']) ?><?php endif; ?>
                    </div>
                    <div style="display:flex;gap:.3rem;margin-top:.4rem;flex-wrap:wrap;align-items:center;">
                      <span class="badge badge-<?= $ji['color'] ?>"><?= $ji['label'] ?></span>
                      <?php if ($r['penghargaan']): ?>
                        <span class="badge badge-gold"><i class="fas fa-award" style="font-size:.6rem;"></i> <?= htmlspecialchars($r['penghargaan']) ?></span>
                      <?php endif; ?>
                      <?php if ($r['url_file'] ?? ''): ?>
                        <a href="../<?= htmlspecialchars($r['url_file']) ?>" target="_blank"
                          class="badge badge-green" style="text-decoration:none;cursor:pointer;">
                          <i class="fas fa-external-link-alt" style="font-size:.6rem;"></i> Lihat File
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="data-item-actions">
                    <a href="?edit=<?= $r['id'] ?>" class="btn btn-outline btn-icon btn-xs" title="Edit">
                      <i class="fas fa-pen"></i>
                    </a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus karya &quot;<?= addslashes(htmlspecialchars($r['judul'])) ?>&quot;?')" autocomplete="off">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="hapus" />
                      <input type="hidden" name="id" value="<?= $r['id'] ?>" />
                      <button type="submit" class="btn btn-ghost btn-icon btn-xs" style="color:var(--red);" title="Hapus">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">🎨</div>
                <h4>Belum Ada Karya</h4>
                <p>Tambahkan karya siswa pertama melalui form di sebelah kiri.</p>
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

        .ufb-text #ufb-label {
          font-size: .83rem;
          font-weight: 700;
          color: var(--gray-700);
        }

        .ufb-text #ufb-hint {
          font-size: .72rem;
          color: var(--gray-400);
          margin-top: 2px;
        }

        .upload-file-box.has-file .ufb-text #ufb-label {
          color: var(--primary);
        }
      </style>

      <script>
        // ===== Karya Siswa Scripts — inside page-content for SPA re-init =====
        (function initKaryaScripts() {
          var jenisEl = document.querySelector('select[name="jenis"]');
          if (!jenisEl) return; // guard: not on this page

          function getSubmitBtn() {
            return document.querySelector('#form-karya button[type=submit]');
          }

          // Watch jenis dropdown
          jenisEl.addEventListener('change', function() {
            var note = document.getElementById('karya-video-note');
            var fileInput = document.getElementById('karya-file');
            if (this.value === 'video') {
              note.style.display = 'block';
              fileInput.accept = '.mp4';
              document.getElementById('ufb-hint').textContent = 'MP4 saja — Maks 30MB';
            } else {
              note.style.display = 'none';
              fileInput.accept = '.pdf,.jpg,.jpeg,.png,.webp,.mp4';
              document.getElementById('ufb-hint').textContent = 'PDF, JPG, PNG, MP4 — Maks 30MB';
              hideKaryaFileErr();
            }
          });

          // Trigger on load if edit mode with jenis=video
          if (jenisEl.value === 'video') {
            document.getElementById('karya-video-note').style.display = 'block';
            document.getElementById('karya-file').accept = '.mp4';
            document.getElementById('ufb-hint').textContent = 'MP4 saja — Maks 30MB';
          }

          window.onKaryaFile = function(input) {
            var box = document.getElementById('file-box');
            var lbl = document.getElementById('ufb-label');
            var hint = document.getElementById('ufb-hint');
            var icon = document.getElementById('ufb-icon-i');
            if (!input.files || !input.files[0]) {
              resetKaryaFile();
              return;
            }

            var f = input.files[0];
            var ext = f.name.split('.').pop().toLowerCase();
            var jenis = document.querySelector('select[name="jenis"]').value;

            if (jenis === 'video' && ext !== 'mp4') {
              showKaryaFileErr(ext);
              input.value = '';
              return;
            }
            if (jenis !== 'video' && (ext === 'mp4' || ext === 'webm' || ext === 'mov' || ext === 'avi')) {
              showKaryaFileErr(ext, 'video_wrong_jenis');
              input.value = '';
              return;
            }
            hideKaryaFileErr();

            var iconMap = {
              pdf: 'fa-file-pdf',
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
          };

          window.showKaryaFileErr = function(ext, type) {
            var errDiv = document.getElementById('karya-file-err');
            var msgDiv = document.getElementById('karya-file-err-msg');
            var msg = '';
            if (type === 'video_wrong_jenis') {
              msg = '<strong>File video tidak sesuai jenis karya yang dipilih.</strong><br>' +
                'Jika ingin upload video, ubah <strong>Jenis Karya</strong> ke <strong>Video</strong> terlebih dahulu.';
            } else {
              msg = '<strong>Format .' + ext.toUpperCase() + ' tidak dapat diupload untuk video!</strong><br>' +
                'Hanya file <strong>.MP4</strong> yang diterima.<br>' +
                '<span style="opacity:.8;">Konversi ke MP4 menggunakan <strong>HandBrake</strong> (gratis di handbrake.fr).</span>';
            }
            msgDiv.innerHTML = msg;
            errDiv.style.display = 'flex';
            document.getElementById('file-box').style.borderColor = 'var(--red)';
            var btn = getSubmitBtn();
            if (btn) btn.disabled = true;
          };

          window.hideKaryaFileErr = function() {
            document.getElementById('karya-file-err').style.display = 'none';
            document.getElementById('file-box').style.borderColor = '';
            var btn = getSubmitBtn();
            if (btn) btn.disabled = false;
          };

          window.resetKaryaFile = function() {
            document.getElementById('karya-file').value = '';
            document.getElementById('file-box').classList.remove('has-file');
            document.getElementById('file-box').style.borderColor = '';
            document.getElementById('ufb-label').textContent = 'Klik untuk memilih file';
            var jenis = document.querySelector('select[name="jenis"]').value;
            document.getElementById('ufb-hint').textContent = jenis === 'video' ?
              'MP4 saja — Maks 30MB' :
              'PDF, JPG, PNG, MP4 — Maks 30MB';
            document.getElementById('ufb-icon-i').className = 'fas fa-file-upload';
            hideKaryaFileErr();
          };
        })();
      </script>
    </div><!-- end page-content -->
  </main>
  <script src="assets/admin.js"></script>
</body>

</html>