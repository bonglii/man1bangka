<?php
// ============================================================
// media.php — Upload Media (Galeri)
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman upload dan manajemen foto & video untuk halaman Galeri.
// Foto disimpan di php/uploads/foto/, video di php/uploads/video/.
//
// Aksi POST yang ditangani:
//   upload     -> upload file + insert ke tabel dokumentasi
//   hapus      -> delete record DB + hapus file fisik
//   edit_judul -> update judul & deskripsi media
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = ''; // Flash messages: sukses dan error
$UPLOAD_DIR = '../php/uploads/'; // Direktori upload relatif dari folder admin/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  // --- AKSI: Upload file baru ---
  if ($action === 'upload') {
    $judul = trim($_POST['judul'] ?? 'Dokumentasi');
    $desk = trim($_POST['deskripsi'] ?? '');
    $kat = $_POST['kategori'] ?? 'kegiatan';
    $jenis = $_POST['jenis'] ?? 'foto';
    $org_id    = ($_POST['organisasi_id'] ?? '') !== '' ? (int)$_POST['organisasi_id'] : null;
    $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
      $file = $_FILES['file'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      // Whitelist ekstensi yang diizinkan berdasarkan jenis media
      $allowed = ($jenis === 'video') ? ['mp4', 'webm'] : ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      if (!in_array($ext, $allowed)) $err = 'Format tidak diizinkan. Foto: JPG/PNG/WEBP | Video: MP4 atau WEBM saja';
      elseif ($file['size'] > 50 * 1024 * 1024) $err = 'Ukuran maks 50MB';
      else {
        $folder = ($jenis === 'video') ? 'video/' : 'foto/';
        // Generate nama file unik untuk menghindari tumpang tindih nama
        $fname = uniqid('media_', true) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $UPLOAD_DIR . $folder . $fname)) {
          $url = 'php/uploads/' . $folder . $fname;
          $pdo->prepare("INSERT INTO dokumentasi (judul,deskripsi,jenis,url_media,thumbnail,kategori,tanggal,organisasi_id,ekskul_id) VALUES (?,?,?,?,?,?,NOW(),?,?)")
            ->execute([$judul, $desk, $jenis, $url, ($jenis !== 'video' ? $url : ''), $kat, $org_id, $ekskul_id]);
          $msg = 'Media berhasil diupload!';
        } else $err = 'Gagal menyimpan file. Cek permission folder uploads.';
      }
    } else $err = 'Pilih file terlebih dahulu.';
    // --- AKSI: Hapus media ---
  } elseif ($action === 'hapus') {
    $id = (int)$_POST['id'];
    $r = $pdo->prepare("SELECT url_media FROM dokumentasi WHERE id=?");
    $r->execute([$id]);
    $r = $r->fetch(PDO::FETCH_ASSOC);
    if ($r) {
      // Hapus file fisik dari disk sebelum menghapus record database
      $fp = '../' . $r['url_media'];
      if (file_exists($fp)) @unlink($fp); // @ untuk suppress warning jika file sudah tidak ada
      $pdo->prepare("DELETE FROM dokumentasi WHERE id=?")->execute([$id]);
      $msg = 'Media dihapus.';
    }
    // --- AKSI: Edit judul/deskripsi media (tanpa re-upload file) ---
  } elseif ($action === 'edit_judul') {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE dokumentasi SET judul=?,deskripsi=? WHERE id=?")->execute([trim($_POST['judul']), trim($_POST['deskripsi']), $id]);
    $msg = 'Keterangan diperbarui.';
  }
}

// Ambil filter dari query string untuk tampilan daftar media
$allowed_jenis = ['foto', 'video'];
$allowed_kat   = ['kegiatan', 'prestasi', 'wisuda', 'lainnya', 'pramuka', 'olahraga'];
$filter     = in_array($_GET['jenis'] ?? '', $allowed_jenis) ? $_GET['jenis'] : '';
$kat_filter = in_array($_GET['kat']   ?? '', $allowed_kat)   ? $_GET['kat']   : '';
$sql = "SELECT * FROM dokumentasi WHERE 1=1";
if ($filter)     $sql .= " AND jenis=?"    ;
if ($kat_filter) $sql .= " AND kategori=?";
$sql .= " ORDER BY tanggal DESC";
$sqlParams = array_filter([$filter, $kat_filter]);
$stmtMedia = $pdo->prepare($sql);
$stmtMedia->execute(array_values($sqlParams));
$media = $stmtMedia->fetchAll(PDO::FETCH_ASSOC);
$total_foto = $pdo->query("SELECT COUNT(*) FROM dokumentasi WHERE jenis='foto'")->fetchColumn();
$total_video = $pdo->query("SELECT COUNT(*) FROM dokumentasi WHERE jenis='video'")->fetchColumn();
$orgList    = $pdo->query("SELECT id, nama FROM organisasi ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
$ekskulList = $pdo->query("SELECT id, nama FROM ekstrakurikuler ORDER BY nama ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Upload Media — Admin MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css" />
  <style>
    /* Upload zone improved */
    .upload-zone-v2 {
      border: 2px dashed var(--gray-300);
      border-radius: var(--radius-lg);
      background: var(--gray-50);
      cursor: pointer;
      transition: var(--transition);
      padding: 2.5rem 1.5rem;
      text-align: center;
    }

    .upload-zone-v2:hover,
    .upload-zone-v2.drag-over {
      border-color: var(--primary-mid);
      background: var(--primary-light);
    }

    .upload-zone-v2 .uz-icon {
      width: 64px;
      height: 64px;
      border-radius: var(--radius-lg);
      background: var(--primary-light);
      color: var(--primary-mid);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      margin: 0 auto 1rem;
      border: 2px solid var(--primary-border);
      transition: var(--transition);
    }

    .upload-zone-v2:hover .uz-icon {
      background: var(--primary-mid);
      color: #fff;
    }

    .upload-zone-v2 h4 {
      font-size: .95rem;
      font-weight: 700;
      color: var(--gray-700);
      margin-bottom: .3rem;
    }

    .upload-zone-v2 p {
      font-size: .78rem;
      color: var(--gray-400);
    }

    .upload-zone-v2 .uz-hint {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      margin-top: .75rem;
      padding: .3rem .85rem;
      background: var(--white);
      border: 1px solid var(--gray-200);
      border-radius: 99px;
      font-size: .72rem;
      font-weight: 600;
      color: var(--gray-500);
    }

    /* Media gallery grid */
    .media-gallery {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
      gap: 1rem;
    }

    .media-card {
      border-radius: var(--radius-sm);
      overflow: hidden;
      background: var(--gray-900);
      position: relative;
      aspect-ratio: 4/3;
      box-shadow: var(--shadow-sm);
      cursor: pointer;
      transition: transform .2s, box-shadow .2s;
    }

    .media-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-lg);
    }

    .media-card img,
    .media-card video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: .3s;
      display: block;
    }

    .media-card:hover img,
    .media-card:hover video {
      opacity: .75;
    }

    .media-card .mc-type {
      position: absolute;
      top: 7px;
      left: 7px;
      background: rgba(0, 0, 0, .65);
      color: #fff;
      font-size: .62rem;
      font-weight: 700;
      padding: .2rem .5rem;
      border-radius: 5px;
      letter-spacing: .5px;
      display: flex;
      align-items: center;
      gap: .25rem;
    }

    .media-card .mc-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(11, 61, 46, .92) 0%, transparent 50%);
      opacity: 0;
      transition: .2s;
      padding: .75rem;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      gap: .35rem;
    }

    .media-card:hover .mc-overlay {
      opacity: 1;
    }

    .media-card .mc-title {
      color: #fff;
      font-size: .75rem;
      font-weight: 600;
      line-height: 1.3;
    }

    .media-card .mc-actions {
      display: flex;
      gap: .3rem;
    }

    .media-card .mc-btn {
      width: 28px;
      height: 28px;
      border-radius: 7px;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .72rem;
      cursor: pointer;
      transition: .15s;
    }

    /* Jenis selector tabs */
    .jenis-tabs {
      display: flex;
      gap: .5rem;
      margin-bottom: 1.1rem;
    }

    .jenis-tab {
      flex: 1;
      padding: .7rem;
      border-radius: var(--radius-sm);
      border: 2px solid var(--gray-200);
      background: var(--white);
      cursor: pointer;
      transition: var(--transition);
      text-align: center;
    }

    .jenis-tab:hover {
      border-color: var(--primary-mid);
    }

    .jenis-tab.active {
      border-color: var(--primary-mid);
      background: var(--primary-light);
    }

    .jenis-tab .jt-icon {
      font-size: 1.4rem;
      display: block;
      margin-bottom: .25rem;
    }

    .jenis-tab .jt-label {
      font-size: .78rem;
      font-weight: 700;
      color: var(--gray-700);
    }

    .jenis-tab.active .jt-label {
      color: var(--primary-mid);
    }
  </style>
</head>

<body>
  <?php include 'sidebar.php'; ?>
  <main class="admin-main">

    <header class="admin-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon"><i class="fas fa-bars"></i></button>
        <div>
          <div class="topbar-title"><i class="fas fa-images"></i> Upload Foto & Video</div>
          <div class="topbar-breadcrumb"><a href="index.php">Dashboard</a> / Media</div>
        </div>
      </div>
      <div class="topbar-right">
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div><?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">
      <?php if ($msg): ?><div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

      <!-- Stats row -->
      <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem;">
        <div class="stat-card stat-green anim-fade-up anim-delay-1">
          <div class="stat-icon"><i class="fas fa-photo-video"></i></div>
          <div>
            <div class="stat-num"><?= $total_foto + $total_video ?></div>
            <div class="stat-label">Total Media</div>
          </div>
        </div>
        <div class="stat-card stat-blue anim-fade-up anim-delay-2">
          <div class="stat-icon"><i class="fas fa-image"></i></div>
          <div>
            <div class="stat-num"><?= $total_foto ?></div>
            <div class="stat-label">Foto</div>
          </div>
        </div>
        <div class="stat-card stat-purple anim-fade-up anim-delay-3">
          <div class="stat-icon"><i class="fas fa-film"></i></div>
          <div>
            <div class="stat-num"><?= $total_video ?></div>
            <div class="stat-label">Video</div>
          </div>
        </div>
      </div>

      <div class="two-col" style="align-items:start;">

        <!-- UPLOAD FORM -->
        <div class="form-section anim-fade-up">
          <div class="form-section-header">
            <div class="form-section-header-icon"><i class="fas fa-cloud-upload-alt"></i></div>
            <div>
              <h3>Upload Media Baru</h3>
              <p>Foto & video tampil langsung di halaman Galeri</p>
            </div>
          </div>
          <div class="form-section-body">
            <form method="POST" enctype="multipart/form-data" id="upload-form" autocomplete="off">
              <input type="hidden" name="action" value="upload" />
              <input type="hidden" name="jenis" id="jenis-val" value="foto" />

              <div class="form-group">
                <label>Judul / Keterangan <span class="req">*</span></label>
                <input type="text" name="judul" placeholder="Contoh: Lomba Futsal Antar Kelas 2026" required autocomplete="off" />
              </div>
              <div class="form-group">
                <label>Deskripsi <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                <textarea name="deskripsi" placeholder="Keterangan tambahan..." style="min-height:72px;" autocomplete="off"></textarea>
              </div>
              <div class="form-group">
                <label>Kategori</label>
                <select name="kategori">
                  <option value="kegiatan">🏫 Kegiatan Umum</option>
                  <option value="ekskul">⭐ Ekstrakurikuler</option>
                  <option value="lomba">🏆 Lomba & Prestasi</option>
                  <option value="organisasi">👥 OSIS/Organisasi</option>
                  <option value="keagamaan">🕌 Keagamaan</option>
                  <option value="lainnya">📌 Lainnya</option>
                </select>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                <div class="form-group">
                  <label>Organisasi <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                  <select name="organisasi_id">
                    <option value="">— Pilih Organisasi —</option>
                    <?php foreach ($orgList as $o): ?>
                      <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nama']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Ekstrakurikuler <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                  <select name="ekskul_id">
                    <option value="">— Pilih Ekskul —</option>
                    <?php foreach ($ekskulList as $e): ?>
                      <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nama']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Jenis tabs -->
              <div class="form-group">
                <label>Jenis Media</label>
                <div class="jenis-tabs">
                  <div class="jenis-tab active" id="tab-foto" onclick="setJenis('foto')">
                    <span class="jt-icon">📷</span>
                    <span class="jt-label">Foto / Gambar</span>
                  </div>
                  <div class="jenis-tab" id="tab-video" onclick="setJenis('video')">
                    <span class="jt-icon">🎬</span>
                    <span class="jt-label">Video</span>
                  </div>
                </div>
                <!-- Video format note -->
                <div id="video-format-note" style="display:none;margin-top:.6rem;padding:.75rem 1rem;border-radius:var(--radius-sm);border:1.5px solid #fde68a;background:#fffbeb;">
                  <div style="display:flex;align-items:flex-start;gap:.6rem;">
                    <i class="fas fa-info-circle" style="color:#d97706;margin-top:2px;flex-shrink:0;"></i>
                    <div style="font-size:.8rem;color:#92400e;line-height:1.6;">
                      <strong>Format Video yang Didukung:</strong><br>
                      <div style="display:flex;flex-direction:column;gap:.25rem;margin-top:.3rem;">
                        <span style="display:flex;align-items:center;gap:.4rem;">
                          <span style="width:18px;height:18px;border-radius:50%;background:#16a34a;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0;">✓</span>
                          <strong style="color:#15803d;">.MP4</strong> &mdash; Format didukung, video dapat diputar di website
                        </span>
                        <span style="display:flex;align-items:center;gap:.4rem;">
                          <span style="width:18px;height:18px;border-radius:50%;background:#dc2626;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0;">✕</span>
                          <strong style="color:#dc2626;">.MOV, .AVI, .MKV, .WMV</strong> &mdash; Tidak didukung, tidak bisa diupload
                        </span>
                        <span style="display:flex;align-items:center;gap:.4rem;">
                          <span style="width:18px;height:18px;border-radius:50%;background:#2563eb;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;flex-shrink:0;">i</span>
                          Konversi ke MP4 dulu menggunakan <strong>HandBrake</strong> (gratis) sebelum upload
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Upload zone -->
              <div class="form-group">
                <div class="upload-zone-v2" id="drop-zone" onclick="document.getElementById('file-input').click()">
                  <div class="uz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                  <h4>Klik atau seret file ke sini</h4>
                  <p>Lepaskan file untuk mulai upload</p>
                  <div class="uz-hint" id="uz-hint"><i class="fas fa-image"></i> JPG, PNG, WEBP &mdash; Maks 50MB</div>
                  <input type="file" name="file" id="file-input" style="display:none;" accept="image/*" onchange="onFileSelect(this)" />
                </div>
                <!-- File selected preview -->
                <div id="file-selected" style="display:none;margin-top:.75rem;padding:.85rem 1rem;background:var(--primary-light);border:1.5px solid var(--primary-border);border-radius:var(--radius-sm);display:none;align-items:center;gap:.75rem;">
                  <div style="width:40px;height:40px;border-radius:8px;background:var(--primary-mid);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;" id="file-icon"><i class="fas fa-image"></i></div>
                  <div style="flex:1;min-width:0;">
                    <div id="file-name" style="font-size:.84rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--gray-900);"></div>
                    <div id="file-size" style="font-size:.72rem;color:var(--gray-500);margin-top:1px;"></div>
                  </div>
                  <button type="button" onclick="clearFile()" class="btn btn-ghost btn-icon btn-sm" style="flex-shrink:0;color:var(--red);"><i class="fas fa-times"></i></button>
                </div>
              </div>

              <!-- Progress -->
              <div class="progress-bar-wrap" id="progress-wrap" style="display:none;margin-bottom:.75rem;">
                <div class="progress-bar-fill" id="progress-fill"></div>
              </div>
            </form>
          </div>
          <div class="form-section-footer">
            <button type="submit" form="upload-form" class="btn btn-primary" id="btn-upload">
              <i class="fas fa-upload"></i> Upload & Tampilkan di Website
            </button>
            <div style="margin-left:auto;display:flex;align-items:center;gap:.4rem;font-size:.75rem;color:var(--gold-dark);background:var(--gold-light);padding:.35rem .75rem;border-radius:99px;border:1px solid rgba(201,168,76,.3);">
              <i class="fas fa-bolt"></i> Langsung tampil di Galeri
            </div>
          </div>
        </div>

        <!-- GALLERY PANEL -->
        <div class="admin-card anim-fade-up anim-delay-2">
          <div class="card-header">
            <div class="card-header-left">
              <div class="card-header-icon" style="background:var(--blue-bg);color:var(--blue);"><i class="fas fa-th"></i></div>
              <div>
                <div class="card-header-title">Media Tersimpan</div>
                <div class="card-header-sub"><?= count($media) ?> item</div>
              </div>
            </div>
          </div>
          <!-- Filter bar -->
          <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--gray-100);display:flex;gap:.4rem;flex-wrap:wrap;">
            <a href="media.php" class="btn btn-sm <?= !$filter ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
            <a href="?jenis=foto" class="btn btn-sm <?= $filter === 'foto' ? 'btn-primary' : 'btn-outline' ?>"><i class="fas fa-image"></i> Foto</a>
            <a href="?jenis=video" class="btn btn-sm <?= $filter === 'video' ? 'btn-primary' : 'btn-outline' ?>"><i class="fas fa-film"></i> Video</a>
          </div>
          <div style="padding:1.1rem;max-height:600px;overflow-y:auto;">
            <?php if ($media): ?>
              <div class="media-gallery">
                <?php foreach ($media as $m): ?>
                  <div class="media-card">
                    <?php if ($m['jenis'] === 'video'): ?>
                      <div style="width:100%;height:100%;background:#111;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.4rem;">
                        <i class="fas fa-play-circle" style="font-size:2rem;color:rgba(255,255,255,.7);"></i>
                        <span style="font-size:.68rem;color:rgba(255,255,255,.5);padding:0 .5rem;text-align:center;overflow:hidden;max-height:2.5em;"><?= htmlspecialchars(basename($m['url_media'])) ?></span>
                      </div>
                      <div class="mc-type"><i class="fas fa-film"></i> VIDEO</div>
                    <?php else: ?>
                      <img src="../<?= htmlspecialchars($m['url_media']) ?>" alt="<?= htmlspecialchars($m['judul']) ?>"
                        onerror="this.parentElement.style.background='#e5e7eb';this.remove();" />
                    <?php endif; ?>
                    <div class="mc-overlay">
                      <div class="mc-title"><?= htmlspecialchars($m['judul']) ?></div>
                      <div class="mc-actions">
                        <button class="mc-btn" style="background:var(--blue);color:#fff;" onclick="openEditMedia(<?= $m['id'] ?>,'<?= addslashes(htmlspecialchars($m['judul'])) ?>','<?= addslashes(htmlspecialchars($m['deskripsi'] ?? '')) ?>')" title="Edit"><i class="fas fa-pen"></i></button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus media ini?')" autocomplete="off">
                          <input type="hidden" name="action" value="hapus" />
                          <input type="hidden" name="id" value="<?= $m['id'] ?>" />
                          <button type="submit" class="mc-btn" style="background:var(--red);color:#fff;" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="empty-state">
                <div class="empty-state-icon">🖼️</div>
                <h4>Belum Ada Media</h4>
                <p>Upload foto atau video pertama melalui form di sebelah kiri.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-backdrop" id="modal-edit">
      <div class="modal-box">
        <div class="modal-header">
          <h3><i class="fas fa-pen" style="color:var(--primary-mid);margin-right:.4rem;"></i> Edit Keterangan Media</h3>
          <button class="modal-close" onclick="closeModal('modal-edit')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" autocomplete="off">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit_judul" />
            <input type="hidden" name="id" id="edit-media-id" />
            <div class="form-group">
              <label>Judul <span class="req">*</span></label>
              <input type="text" name="judul" id="edit-media-judul" required autocomplete="off" />
            </div>
            <div class="form-group">
              <label>Deskripsi</label>
              <textarea name="deskripsi" id="edit-media-desc" autocomplete="off"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
          </div>
        </form>
      </div>
    </div>

  </main>
  <script src="assets/admin.js"></script>
  <script>
    function setJenis(j) {
      document.getElementById('jenis-val').value = j;
      document.getElementById('tab-foto').classList.toggle('active', j === 'foto');
      document.getElementById('tab-video').classList.toggle('active', j === 'video');
      const input = document.getElementById('file-input');
      const hint = document.getElementById('uz-hint');
      const icon = document.getElementById('file-icon')?.querySelector('i');
      const note = document.getElementById('video-format-note');
      if (j === 'video') {
        input.accept = 'video/mp4,.mp4';
        hint.innerHTML = '<i class="fas fa-film"></i> MP4 saja — Maks 50MB';
        if (icon) icon.className = 'fas fa-film';
        if (note) note.style.display = 'block';
      } else {
        input.accept = 'image/*';
        hint.innerHTML = '<i class="fas fa-image"></i> JPG, PNG, WEBP — Maks 50MB';
        if (icon) icon.className = 'fas fa-image';
        if (note) note.style.display = 'none';
      }
      clearFile();
    }

    function formatBytes(b) {
      if (b < 1024) return b + ' B';
      if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
      return (b / 1048576).toFixed(1) + ' MB';
    }

    function onFileSelect(input) {
      const f = input.files[0];
      if (!f) return;
      const jenis = document.getElementById('jenis-val').value;
      const ext = f.name.split('.').pop().toLowerCase();

      // Validate MP4 for video
      if (jenis === 'video' && ext !== 'mp4') {
        showVideoError(ext);
        input.value = '';
        return;
      }
      hideVideoError();

      const sel = document.getElementById('file-selected');
      sel.style.display = 'flex';
      document.getElementById('file-name').textContent = f.name;
      document.getElementById('file-size').textContent = formatBytes(f.size);
      document.getElementById('drop-zone').style.borderColor = 'var(--primary-mid)';
    }

    function showVideoError(ext) {
      const prev = document.getElementById('video-ext-err');
      if (prev) prev.remove();
      const err = document.createElement('div');
      err.id = 'video-ext-err';
      err.style.cssText = 'margin-top:.6rem;padding:.75rem 1rem;border-radius:8px;border:1.5px solid #fca5a5;background:#fef2f2;display:flex;align-items:flex-start;gap:.6rem;';
      err.innerHTML = '<i class="fas fa-times-circle" style="color:#dc2626;margin-top:2px;flex-shrink:0;font-size:1rem;"></i>' +
        '<div style="font-size:.8rem;color:#991b1b;line-height:1.6;">' +
        '<strong>Format .' + ext.toUpperCase() + ' tidak dapat diupload!</strong><br>' +
        'Hanya file <strong>.MP4</strong> yang diterima untuk video.<br>' +
        '<span style="opacity:.8;">Konversi ke MP4 dulu menggunakan <strong>HandBrake</strong> (gratis di handbrake.fr).</span>' +
        '</div>';
      document.getElementById('drop-zone').after(err);
      document.getElementById('drop-zone').style.borderColor = 'var(--red)';
      document.getElementById('btn-upload').disabled = true;
    }

    function hideVideoError() {
      const err = document.getElementById('video-ext-err');
      if (err) err.remove();
      document.getElementById('btn-upload').disabled = false;
    }

    function clearFile() {
      document.getElementById('file-input').value = '';
      document.getElementById('file-selected').style.display = 'none';
      document.getElementById('drop-zone').style.borderColor = '';
    }

    function openEditMedia(id, judul, desc) {
      document.getElementById('edit-media-id').value = id;
      document.getElementById('edit-media-judul').value = judul;
      document.getElementById('edit-media-desc').value = desc;
      openModal('modal-edit');
    }

    // Drag & drop
    const dz = document.getElementById('drop-zone');
    dz.addEventListener('dragover', e => {
      e.preventDefault();
      dz.classList.add('drag-over');
    });
    dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
    dz.addEventListener('drop', e => {
      e.preventDefault();
      dz.classList.remove('drag-over');
      const input = document.getElementById('file-input');
      input.files = e.dataTransfer.files;
      onFileSelect(input);
    });

    // Upload progress
    document.getElementById('upload-form').addEventListener('submit', function() {
      const w = document.getElementById('progress-wrap');
      const f = document.getElementById('progress-fill');
      const btn = document.getElementById('btn-upload');
      w.style.display = 'block';
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';
      let p = 0;
      const t = setInterval(() => {
        p = Math.min(p + Math.random() * 12, 90);
        f.style.width = p + '%';
      }, 250);
    });
  </script>
</body>

</html>