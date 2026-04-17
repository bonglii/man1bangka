<?php
// ============================================================
// osim.php — Manajemen OSIM (Organisasi Siswa Intra Madrasah)
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Halaman CRUD untuk mengelola data OSIM/OSIS.
// Mencakup 3 sub-modul:
//   1. Organisasi     — profil, visi, misi, gambar logo
//   2. Anggota        — daftar pengurus & foto per organisasi
//   3. Program Kerja  — proker dengan status rencana/berjalan/selesai
//
// Parameter URL:
//   ?org=ID           — tampilkan detail organisasi (anggota + proker)
//   ?edit_org=ID      — form edit organisasi
//   ?edit_anggota=ID&org=ID — form edit anggota
//   ?edit_proker=ID&org=ID  — form edit program kerja
//
// Aksi POST yang ditangani:
//   org_tambah / org_edit   -> insert/update tabel organisasi
//   org_hapus               -> delete organisasi + cascade anggota & proker
//   anggota_tambah / anggota_edit -> insert/update tabel anggota_organisasi
//   anggota_hapus           -> delete anggota
//   proker_tambah / proker_edit   -> insert/update tabel program_kerja
//   proker_hapus            -> delete program kerja
//   proker_status           -> update status program kerja langsung dari tabel
//
// Seluruh operasi database menggunakan PDO prepared statement.
// Autentikasi admin dicek via require auth.php di baris pertama.
// ============================================================
require 'auth.php';
require '../php/config.php'; ?>
<?php
$msg = $err = '';

// ============================================================
// HELPER: upload foto (jpg/png/webp, maks 5 MB)
// ============================================================
function uploadFotoOsim(string $fileKey, string $prefix): string
{
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return '';
    $ext     = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed) || $_FILES[$fileKey]['size'] > 5 * 1024 * 1024) return '';
    $fname = $prefix . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
    $dest  = '../php/uploads/foto/' . $fname;
    return move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest) ? 'foto/' . $fname : '';
}

function hapusFotoOsim(string $path): void
{
    if ($path && file_exists('../php/uploads/' . $path)) @unlink('../php/uploads/' . $path);
}

// ============================================================
// HANDLE POST ACTIONS
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action  = $_POST['action'] ?? '';
    $backOrg = (int)($_POST['org_id'] ?? 0);

    // ---- ORGANISASI: Tambah ----
    if ($action === 'org_tambah') {
        $nama  = trim($_POST['nama'] ?? '');
        $desk  = trim($_POST['deskripsi'] ?? '');
        $visi  = trim($_POST['visi'] ?? '');
        $misi  = trim($_POST['misi'] ?? '');
        if (!$nama) { $err = 'Nama organisasi wajib diisi.'; }
        else {
            $gambar = uploadFotoOsim('gambar', 'org');
            $pdo->prepare("INSERT INTO organisasi (nama, deskripsi, visi, misi, gambar) VALUES (?,?,?,?,?)")
                ->execute([$nama, $desk, $visi, $misi, $gambar]);
            header('Location: osim.php?msg=org_tambah'); exit;
        }
    }

    // ---- ORGANISASI: Edit ----
    elseif ($action === 'org_edit') {
        $id    = (int)$_POST['id'];
        $nama  = trim($_POST['nama'] ?? '');
        $desk  = trim($_POST['deskripsi'] ?? '');
        $visi  = trim($_POST['visi'] ?? '');
        $misi  = trim($_POST['misi'] ?? '');
        if (!$nama) { $err = 'Nama organisasi wajib diisi.'; }
        else {
            $gambarBaru = uploadFotoOsim('gambar', 'org');
            if ($gambarBaru) {
                $old = $pdo->prepare("SELECT gambar FROM organisasi WHERE id=?");
                $old->execute([$id]);
                hapusFotoOsim($old->fetchColumn() ?: '');
                $pdo->prepare("UPDATE organisasi SET nama=?,deskripsi=?,visi=?,misi=?,gambar=? WHERE id=?")
                    ->execute([$nama, $desk, $visi, $misi, $gambarBaru, $id]);
            } else {
                $pdo->prepare("UPDATE organisasi SET nama=?,deskripsi=?,visi=?,misi=? WHERE id=?")
                    ->execute([$nama, $desk, $visi, $misi, $id]);
            }
            header('Location: osim.php?msg=org_edit'); exit;
        }
    }

    // ---- ORGANISASI: Hapus ----
    elseif ($action === 'org_hapus') {
        $id  = (int)$_POST['id'];
        $old = $pdo->prepare("SELECT gambar FROM organisasi WHERE id=?");
        $old->execute([$id]);
        hapusFotoOsim($old->fetchColumn() ?: '');
        // Hapus anggota foto
        $angs = $pdo->prepare("SELECT foto FROM anggota_organisasi WHERE organisasi_id=?");
        $angs->execute([$id]);
        foreach ($angs->fetchAll(PDO::FETCH_COLUMN) as $f) hapusFotoOsim($f);
        // Cascade delete (anggota & proker ikut terhapus via query langsung)
        $pdo->prepare("DELETE FROM anggota_organisasi WHERE organisasi_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM program_kerja WHERE organisasi_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM organisasi WHERE id=?")->execute([$id]);
        header('Location: osim.php?msg=org_hapus'); exit;
    }

    // ---- ANGGOTA: Tambah ----
    elseif ($action === 'anggota_tambah') {
        $orgId   = (int)($_POST['organisasi_id'] ?? 0);
        $nama    = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $kelas   = trim($_POST['kelas'] ?? '');
        if (!$nama || !$jabatan || !$orgId) { $err = 'Nama dan jabatan wajib diisi.'; }
        else {
            $foto = uploadFotoOsim('foto', 'anggota');
            $pdo->prepare("INSERT INTO anggota_organisasi (organisasi_id, nama, jabatan, kelas, foto) VALUES (?,?,?,?,?)")
                ->execute([$orgId, $nama, $jabatan, $kelas, $foto]);
            header("Location: osim.php?org=$orgId&tab=anggota&msg=anggota_tambah"); exit;
        }
    }

    // ---- ANGGOTA: Edit ----
    elseif ($action === 'anggota_edit') {
        $id      = (int)$_POST['id'];
        $orgId   = (int)($_POST['organisasi_id'] ?? 0);
        $nama    = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $kelas   = trim($_POST['kelas'] ?? '');
        if (!$nama || !$jabatan) { $err = 'Nama dan jabatan wajib diisi.'; }
        else {
            $fotoBaru = uploadFotoOsim('foto', 'anggota');
            if ($fotoBaru) {
                $old = $pdo->prepare("SELECT foto FROM anggota_organisasi WHERE id=?");
                $old->execute([$id]);
                hapusFotoOsim($old->fetchColumn() ?: '');
                $pdo->prepare("UPDATE anggota_organisasi SET nama=?,jabatan=?,kelas=?,foto=? WHERE id=?")
                    ->execute([$nama, $jabatan, $kelas, $fotoBaru, $id]);
            } else {
                $pdo->prepare("UPDATE anggota_organisasi SET nama=?,jabatan=?,kelas=? WHERE id=?")
                    ->execute([$nama, $jabatan, $kelas, $id]);
            }
            header("Location: osim.php?org=$orgId&tab=anggota&msg=anggota_edit"); exit;
        }
    }

    // ---- ANGGOTA: Hapus ----
    elseif ($action === 'anggota_hapus') {
        $id    = (int)$_POST['id'];
        $orgId = (int)($_POST['organisasi_id'] ?? 0);
        $old   = $pdo->prepare("SELECT foto FROM anggota_organisasi WHERE id=?");
        $old->execute([$id]);
        hapusFotoOsim($old->fetchColumn() ?: '');
        $pdo->prepare("DELETE FROM anggota_organisasi WHERE id=?")->execute([$id]);
        header("Location: osim.php?org=$orgId&tab=anggota&msg=anggota_hapus"); exit;
    }

    // ---- PROGRAM KERJA: Tambah ----
    elseif ($action === 'proker_tambah') {
        $orgId    = (int)($_POST['organisasi_id'] ?? 0);
        $nama     = trim($_POST['nama_program'] ?? '');
        $desk     = trim($_POST['deskripsi'] ?? '');
        $semester = in_array($_POST['semester'] ?? '', ['ganjil', 'genap']) ? $_POST['semester'] : 'ganjil';
        $status   = in_array($_POST['status'] ?? '', ['rencana', 'berjalan', 'selesai']) ? $_POST['status'] : 'rencana';
        if (!$nama || !$orgId) { $err = 'Nama program wajib diisi.'; }
        else {
            $pdo->prepare("INSERT INTO program_kerja (organisasi_id, nama_program, deskripsi, semester, status) VALUES (?,?,?,?,?)")
                ->execute([$orgId, $nama, $desk, $semester, $status]);
            header("Location: osim.php?org=$orgId&tab=proker&msg=proker_tambah"); exit;
        }
    }

    // ---- PROGRAM KERJA: Edit ----
    elseif ($action === 'proker_edit') {
        $id       = (int)$_POST['id'];
        $orgId    = (int)($_POST['organisasi_id'] ?? 0);
        $nama     = trim($_POST['nama_program'] ?? '');
        $desk     = trim($_POST['deskripsi'] ?? '');
        $semester = in_array($_POST['semester'] ?? '', ['ganjil', 'genap']) ? $_POST['semester'] : 'ganjil';
        $status   = in_array($_POST['status'] ?? '', ['rencana', 'berjalan', 'selesai']) ? $_POST['status'] : 'rencana';
        if (!$nama) { $err = 'Nama program wajib diisi.'; }
        else {
            $pdo->prepare("UPDATE program_kerja SET nama_program=?,deskripsi=?,semester=?,status=? WHERE id=?")
                ->execute([$nama, $desk, $semester, $status, $id]);
            header("Location: osim.php?org=$orgId&tab=proker&msg=proker_edit"); exit;
        }
    }

    // ---- PROGRAM KERJA: Hapus ----
    elseif ($action === 'proker_hapus') {
        $id    = (int)$_POST['id'];
        $orgId = (int)($_POST['organisasi_id'] ?? 0);
        $pdo->prepare("DELETE FROM program_kerja WHERE id=?")->execute([$id]);
        header("Location: osim.php?org=$orgId&tab=proker&msg=proker_hapus"); exit;
    }

    // ---- PROGRAM KERJA: Update Status (inline) ----
    elseif ($action === 'proker_status') {
        $id     = (int)$_POST['id'];
        $orgId  = (int)($_POST['organisasi_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['rencana', 'berjalan', 'selesai']) ? $_POST['status'] : 'rencana';
        $pdo->prepare("UPDATE program_kerja SET status=? WHERE id=?")->execute([$status, $id]);
        header("Location: osim.php?org=$orgId&tab=proker&msg=proker_status"); exit;
    }
}

// ============================================================
// PESAN REDIRECT
// ============================================================
$msgMap = [
    'org_tambah'     => 'Organisasi berhasil ditambahkan!',
    'org_edit'       => 'Organisasi berhasil diperbarui!',
    'org_hapus'      => 'Organisasi dihapus.',
    'anggota_tambah' => 'Anggota berhasil ditambahkan!',
    'anggota_edit'   => 'Anggota berhasil diperbarui!',
    'anggota_hapus'  => 'Anggota dihapus.',
    'proker_tambah'  => 'Program kerja berhasil ditambahkan!',
    'proker_edit'    => 'Program kerja berhasil diperbarui!',
    'proker_hapus'   => 'Program kerja dihapus.',
    'proker_status'  => 'Status program kerja diperbarui.',
];
if (isset($_GET['msg'])) $msg = $msgMap[$_GET['msg']] ?? '';

// ============================================================
// STATE: mode halaman
// ============================================================
$activeOrgId  = isset($_GET['org'])          ? (int)$_GET['org']          : 0;
$editOrgId    = isset($_GET['edit_org'])     ? (int)$_GET['edit_org']     : 0;
$editAnggId   = isset($_GET['edit_anggota']) ? (int)$_GET['edit_anggota'] : 0;
$editProkerId = isset($_GET['edit_proker'])  ? (int)$_GET['edit_proker']  : 0;
$activeTab    = $_GET['tab'] ?? 'anggota'; // tab default di halaman detail

// ============================================================
// QUERY DATA
// ============================================================
// Semua organisasi
$orgList = $pdo->query("SELECT o.*,
    (SELECT COUNT(*) FROM anggota_organisasi WHERE organisasi_id=o.id) as total_anggota,
    (SELECT COUNT(*) FROM program_kerja WHERE organisasi_id=o.id) as total_proker,
    (SELECT COUNT(*) FROM program_kerja WHERE organisasi_id=o.id AND status='berjalan') as proker_berjalan
    FROM organisasi o ORDER BY o.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Detail organisasi yang sedang dibuka
$activeOrg = null;
$anggotaList = [];
$prokerList  = [];
if ($activeOrgId) {
    $s = $pdo->prepare("SELECT * FROM organisasi WHERE id=?");
    $s->execute([$activeOrgId]);
    $activeOrg = $s->fetch(PDO::FETCH_ASSOC);
    if ($activeOrg) {
        $s2 = $pdo->prepare("SELECT * FROM anggota_organisasi WHERE organisasi_id=? ORDER BY id ASC");
        $s2->execute([$activeOrgId]);
        $anggotaList = $s2->fetchAll(PDO::FETCH_ASSOC);

        $s3 = $pdo->prepare("SELECT * FROM program_kerja WHERE organisasi_id=? ORDER BY semester, status, nama_program");
        $s3->execute([$activeOrgId]);
        $prokerList = $s3->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Data untuk form edit
$editOrg     = null;
$editAngg    = null;
$editProker  = null;
if ($editOrgId) {
    $s = $pdo->prepare("SELECT * FROM organisasi WHERE id=?");
    $s->execute([$editOrgId]);
    $editOrg = $s->fetch(PDO::FETCH_ASSOC);
}
if ($editAnggId) {
    $s = $pdo->prepare("SELECT * FROM anggota_organisasi WHERE id=?");
    $s->execute([$editAnggId]);
    $editAngg = $s->fetch(PDO::FETCH_ASSOC);
    if ($editAngg && !$activeOrgId) $activeOrgId = (int)$editAngg['organisasi_id'];
}
if ($editProkerId) {
    $s = $pdo->prepare("SELECT * FROM program_kerja WHERE id=?");
    $s->execute([$editProkerId]);
    $editProker = $s->fetch(PDO::FETCH_ASSOC);
    if ($editProker && !$activeOrgId) $activeOrgId = (int)$editProker['organisasi_id'];
}

// Stat cards utama
$totalOrg    = count($orgList);
$totalAngg   = (int)$pdo->query("SELECT COUNT(*) FROM anggota_organisasi")->fetchColumn();
$totalProker = (int)$pdo->query("SELECT COUNT(*) FROM program_kerja")->fetchColumn();
$prokerAktif = (int)$pdo->query("SELECT COUNT(*) FROM program_kerja WHERE status='berjalan'")->fetchColumn();

// Label helper
$statusProkerLabel = ['rencana' => '📋 Rencana', 'berjalan' => '🔄 Berjalan', 'selesai' => '✅ Selesai'];
$statusProkerColor = ['rencana' => 'badge-gray', 'berjalan' => 'badge-gold', 'selesai' => 'badge-green'];
$semesterLabel     = ['ganjil' => 'Ganjil', 'genap' => 'Genap'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>OSIM — Admin MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/admin.css" />
  <style>
    /* ---- OSIM-specific styles ---- */

    /* ============================================================
       CUSTOM FILE UPLOAD ZONE
       ============================================================ */
    .file-upload-wrap { display: flex; flex-direction: column; gap: .55rem; }

    .file-upload-zone {
      position: relative;
      border: 2px dashed var(--gray-200);
      border-radius: 12px;
      background: var(--gray-50);
      transition: border-color .2s, background .2s;
      overflow: hidden;
    }
    .file-upload-zone:hover,
    .file-upload-zone.drag-over {
      border-color: var(--primary-mid);
      background: var(--primary-light);
    }
    .file-upload-zone.has-file {
      border-style: solid;
      border-color: var(--primary-border);
      background: #f0fdf4;
    }

    /* Label adalah seluruh area klik */
    .file-upload-trigger {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: .85rem 1rem;
      cursor: pointer;
      width: 100%;
    }

    /* Ikon besar di kiri */
    .file-upload-icon-box {
      width: 46px; height: 46px; flex-shrink: 0;
      border-radius: 10px;
      background: var(--white);
      border: 1.5px solid var(--gray-200);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.35rem;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
      transition: transform .18s;
    }
    .file-upload-zone:hover .file-upload-icon-box,
    .file-upload-zone.drag-over .file-upload-icon-box {
      transform: scale(1.08);
      border-color: var(--primary-border);
    }

    /* Teks panduan & steps */
    .file-upload-guide { flex: 1; min-width: 0; }
    .file-upload-title {
      font-size: .8rem; font-weight: 700;
      color: var(--gray-700); margin-bottom: .32rem;
    }
    .file-upload-steps {
      display: flex; flex-direction: column; gap: .18rem;
    }
    .file-upload-step {
      display: flex; align-items: center; gap: .38rem;
      font-size: .69rem; color: var(--gray-500); line-height: 1.3;
    }
    .file-upload-step-num {
      display: inline-flex; align-items: center; justify-content: center;
      width: 15px; height: 15px; border-radius: 50%;
      background: var(--primary-mid); color: #fff;
      font-size: .58rem; font-weight: 800; flex-shrink: 0;
    }

    /* Input file asli — tersembunyi */
    .file-upload-zone input[type="file"] {
      position: absolute; inset: 0;
      opacity: 0; width: 100%; height: 100%;
      cursor: pointer; font-size: 0;
    }

    /* Preview setelah file dipilih */
    .file-upload-preview {
      display: none;
      align-items: center; gap: .75rem;
      padding: .65rem 1rem;
      background: #f0fdf4;
      border-top: 1px solid var(--primary-border);
    }
    .file-upload-preview.show { display: flex; }
    .file-upload-preview img {
      width: 44px; height: 44px; border-radius: 8px;
      object-fit: cover;
      border: 2px solid var(--primary-border);
      flex-shrink: 0;
    }
    .file-upload-preview-info { flex: 1; min-width: 0; }
    .file-upload-preview-name {
      font-size: .75rem; font-weight: 700;
      color: var(--gray-800);
      overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .file-upload-preview-size {
      font-size: .67rem; color: var(--gray-400); margin-top: .1rem;
    }
    .file-upload-clear {
      background: none; border: none; cursor: pointer;
      color: var(--red); font-size: .72rem; font-weight: 700;
      padding: .25rem .5rem; border-radius: 6px;
      transition: background .15s;
      display: flex; align-items: center; gap: .25rem;
      flex-shrink: 0;
    }
    .file-upload-clear:hover { background: #fee2e2; }

    /* Info foto saat ini (mode edit) */
    .file-current-info {
      display: flex; align-items: center; gap: .65rem;
      padding: .55rem 1rem;
      background: var(--white);
      border-bottom: 1px dashed var(--gray-200);
      font-size: .72rem; color: var(--gray-500);
    }
    .file-current-info img {
      width: 36px; height: 36px; border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-border);
      flex-shrink: 0;
    }
    .file-current-info-img-rect img {
      border-radius: 8px;
    }
    .org-hero {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 60%, #2d8a55 100%);
      border-radius: var(--radius-lg);
      padding: 1.75rem 1.75rem 1.4rem;
      color: #fff;
      position: relative;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }
    .org-hero::before {
      content: '';
      position: absolute;
      top: -40px; right: -40px;
      width: 180px; height: 180px;
      border-radius: 50%;
      background: rgba(255,255,255,.07);
    }
    .org-hero::after {
      content: '';
      position: absolute;
      bottom: -20px; left: 40%;
      width: 120px; height: 120px;
      border-radius: 50%;
      background: rgba(255,255,255,.05);
    }
    .org-hero-logo {
      width: 64px; height: 64px;
      border-radius: 14px;
      object-fit: cover;
      border: 3px solid rgba(255,255,255,.3);
      flex-shrink: 0;
    }
    .org-hero-avatar {
      width: 64px; height: 64px;
      border-radius: 14px;
      background: rgba(255,255,255,.15);
      border: 3px solid rgba(255,255,255,.3);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem; font-weight: 900; color: #fff;
      flex-shrink: 0;
    }
    .org-hero-stat {
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 10px;
      padding: .6rem 1rem;
      text-align: center;
      min-width: 80px;
      backdrop-filter: blur(4px);
    }
    .org-hero-stat-num { font-size: 1.4rem; font-weight: 800; line-height: 1; }
    .org-hero-stat-label { font-size: .64rem; opacity: .75; margin-top: .2rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }

    /* Org card on main list */
    .org-card {
      background: var(--white);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-lg);
      overflow: hidden;
      transition: var(--transition);
      box-shadow: var(--shadow-sm);
      display: flex; flex-direction: column;
    }
    .org-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-border);
    }
    .org-card-banner {
      height: 68px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
      position: relative;
    }
    .org-card-banner-dot {
      position: absolute; top: 12px; right: 14px;
      width: 44px; height: 44px; border-radius: 50%;
      background: rgba(255,255,255,.1);
    }
    .org-card-avatar {
      position: absolute; bottom: -18px; left: 18px;
      width: 52px; height: 52px; border-radius: 12px;
      border: 3px solid var(--white);
      box-shadow: 0 2px 10px rgba(0,0,0,.12);
      overflow: hidden;
      background: var(--primary-light);
      color: var(--primary-mid);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: 1.1rem;
    }
    .org-card-body { padding: 1.3rem 1.1rem .85rem; }
    .org-card-name { font-weight: 800; font-size: .92rem; color: var(--gray-900); margin-top: .35rem; }
    .org-card-desc {
      font-size: .75rem; color: var(--gray-500); margin-top: .3rem; line-height: 1.5;
      overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    }
    .org-card-stats {
      display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .75rem;
    }
    .org-card-footer {
      padding: .75rem 1.1rem;
      border-top: 1px solid var(--gray-100);
      background: var(--gray-50);
      display: flex; gap: .5rem; align-items: center;
    }

    /* Anggota card grid */
    .anggota-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
      gap: 1rem;
      padding: 1.25rem;
    }
    .anggota-card {
      background: var(--white);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-lg);
      padding: 1.1rem .85rem .75rem;
      text-align: center;
      transition: var(--transition);
      position: relative;
    }
    .anggota-card:hover {
      border-color: var(--primary-border);
      box-shadow: var(--shadow-sm);
      transform: translateY(-2px);
    }
    .anggota-avatar {
      width: 58px; height: 58px; border-radius: 50%;
      border: 3px solid var(--primary-border);
      object-fit: cover; margin: 0 auto .6rem;
      display: block;
    }
    .anggota-avatar-placeholder {
      width: 58px; height: 58px; border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-light), #d1fae5);
      border: 3px solid var(--primary-border);
      color: var(--primary-mid);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; font-size: 1.2rem; margin: 0 auto .6rem;
    }
    .anggota-name { font-weight: 700; font-size: .82rem; color: var(--gray-900); }
    /* Tombol aksi selalu terlihat di bawah card */
    .anggota-actions {
      display: flex; gap: .35rem; justify-content: center;
      margin-top: .55rem; padding-top: .5rem;
      border-top: 1px solid var(--gray-100);
    }
    /* Edit form highlight saat mode edit aktif */
    .tab-add-panel.editing {
      scroll-margin-top: 80px;
    }
    @keyframes editPulse {
      0%   { box-shadow: 0 0 0 0 rgba(234,179,8,.45); }
      70%  { box-shadow: 0 0 0 8px rgba(234,179,8,0); }
      100% { box-shadow: 0 0 0 0 rgba(234,179,8,0); }
    }
    .tab-add-panel.editing.highlight-anim {
      animation: editPulse .7s ease 2;
    }

    /* Proker table rows with left-border status indicator */
    .proker-rencana  td:first-child { border-left: 3px solid var(--gray-300); }
    .proker-berjalan td:first-child { border-left: 3px solid var(--gold); }
    .proker-selesai  td:first-child { border-left: 3px solid var(--teal); }

    /* Inline add-form panel inside tab */
    .tab-add-panel {
      background: var(--gray-50);
      border-bottom: 1px solid var(--gray-200);
      padding: 1.1rem 1.25rem;
    }
    .tab-add-panel.editing {
      background: #fffbf0;
      border-bottom-color: #fde68a;
    }
    .tab-add-title {
      font-size: .78rem; font-weight: 700; color: var(--gray-700);
      display: flex; align-items: center; gap: .45rem;
      margin-bottom: .85rem;
    }
    .tab-add-title i { color: var(--primary-mid); font-size: .75rem; }
    .tab-add-title.editing i { color: var(--gold-dark); }
  </style>
</head>

<body>
  <?php include 'sidebar.php'; ?>
  <main class="admin-main">

    <header class="admin-topbar">
      <div class="topbar-left">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon"><i class="fas fa-bars"></i></button>
        <div>
          <div class="topbar-title">
            <i class="fas fa-users-cog"></i>
            <?php if ($activeOrg): ?>
              OSIM — <span style="color:var(--primary-mid);"><?= htmlspecialchars($activeOrg['nama']) ?></span>
            <?php elseif ($editOrg): ?>
              Edit Organisasi
            <?php else: ?>
              Organisasi Siswa Intra Madrasah
            <?php endif; ?>
          </div>
          <div class="topbar-breadcrumb">
            <a href="index.php">Dashboard</a> /
            <?php if ($activeOrg || $editOrg): ?>
              <a href="osim.php">OSIM</a> /
              <?= $activeOrg ? htmlspecialchars($activeOrg['nama']) : 'Edit Organisasi' ?>
            <?php else: ?>
              OSIM
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="topbar-right">
        <?php if ($activeOrg || $editOrg): ?>
          <a href="osim.php" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        <?php endif; ?>
        <div class="topbar-admin">
          <div class="topbar-admin-avatar"><?= strtoupper(substr(ADMIN_USER, 0, 2)) ?></div>
          <?= htmlspecialchars(ADMIN_USER) ?>
        </div>
      </div>
    </header>

    <div class="page-content">

      <?php if ($msg): ?>
        <div class="alert alert-ok anim-fade-up"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="alert alert-err anim-fade-up"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <?php /* ====================================================
             HALAMAN DETAIL ORGANISASI
             ==================================================== */ if ($activeOrg): ?>

        <!-- Hero Banner -->
        <div class="org-hero anim-fade-up">
          <div style="display:flex;align-items:flex-start;gap:1.1rem;position:relative;z-index:1;">
            <?php if ($activeOrg['gambar']): ?>
              <img src="../php/uploads/<?= htmlspecialchars($activeOrg['gambar']) ?>" class="org-hero-logo" alt="Logo" />
            <?php else: ?>
              <div class="org-hero-avatar"><?= strtoupper(substr($activeOrg['nama'], 0, 1)) ?></div>
            <?php endif; ?>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.7rem;font-weight:700;opacity:.65;text-transform:uppercase;letter-spacing:.8px;margin-bottom:.25rem;">
                Organisasi Siswa
              </div>
              <h2 style="font-size:1.15rem;font-weight:800;margin:0 0 .35rem;line-height:1.2;">
                <?= htmlspecialchars($activeOrg['nama']) ?>
              </h2>
              <?php if ($activeOrg['deskripsi']): ?>
                <p style="font-size:.78rem;opacity:.8;line-height:1.5;margin:0;max-width:500px;
                  overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                  <?= htmlspecialchars($activeOrg['deskripsi']) ?>
                </p>
              <?php endif; ?>
            </div>
            <a href="?edit_org=<?= $activeOrg['id'] ?>" class="btn btn-sm"
              style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);flex-shrink:0;backdrop-filter:blur(4px);">
              <i class="fas fa-pen"></i> Edit
            </a>
          </div>
          <!-- Stat mini -->
          <div style="display:flex;gap:.65rem;margin-top:1.1rem;flex-wrap:wrap;position:relative;z-index:1;">
            <?php
            $prokerSelesai  = count(array_filter($prokerList, fn($p) => $p['status'] === 'selesai'));
            $prokerBerjalan = count(array_filter($prokerList, fn($p) => $p['status'] === 'berjalan'));
            $prokerRencana  = count(array_filter($prokerList, fn($p) => $p['status'] === 'rencana'));
            foreach ([
              ['num' => count($anggotaList), 'label' => 'Anggota Pengurus', 'icon' => 'fa-users'],
              ['num' => count($prokerList),  'label' => 'Program Kerja',    'icon' => 'fa-tasks'],
              ['num' => $prokerBerjalan,      'label' => 'Sedang Berjalan',  'icon' => 'fa-spinner'],
              ['num' => $prokerSelesai,       'label' => 'Selesai',          'icon' => 'fa-check-circle'],
            ] as $s): ?>
              <div class="org-hero-stat">
                <div class="org-hero-stat-num"><?= $s['num'] ?></div>
                <div class="org-hero-stat-label"><i class="fas <?= $s['icon'] ?>" style="margin-right:.2rem;font-size:.58rem;"></i><?= $s['label'] ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tabs -->
        <div class="admin-card anim-fade-up anim-delay-1" data-tab-group="osim-detail">
          <div style="padding:.75rem 1.25rem 0;border-bottom:2px solid var(--gray-200);">
            <div class="admin-tabs" style="border:none;margin-bottom:0;">
              <button class="admin-tab <?= $activeTab === 'anggota' ? 'active' : '' ?>" data-tab="tab-anggota">
                <i class="fas fa-id-badge" style="margin-right:.35rem;font-size:.78rem;"></i>
                Anggota Pengurus
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:var(--primary-mid);color:#fff;border-radius:99px;font-size:.63rem;font-weight:800;margin-left:.35rem;padding:0 .3rem;">
                  <?= count($anggotaList) ?>
                </span>
              </button>
              <button class="admin-tab <?= $activeTab === 'proker' ? 'active' : '' ?>" data-tab="tab-proker">
                <i class="fas fa-clipboard-list" style="margin-right:.35rem;font-size:.78rem;"></i>
                Program Kerja
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:var(--teal);color:#fff;border-radius:99px;font-size:.63rem;font-weight:800;margin-left:.35rem;padding:0 .3rem;">
                  <?= count($prokerList) ?>
                </span>
              </button>
            </div>
          </div>

          <!-- ===== TAB ANGGOTA ===== -->
          <div id="tab-anggota" class="tab-pane <?= $activeTab === 'anggota' ? 'active' : '' ?>">

            <!-- Form add/edit anggota -->
            <?php $isEditAngg = !!$editAngg; ?>
            <div id="form-anggota-panel" class="tab-add-panel <?= $isEditAngg ? 'editing' : '' ?>">
              <div class="tab-add-title <?= $isEditAngg ? 'editing' : '' ?>">
                <i class="fas fa-<?= $isEditAngg ? 'pen' : 'user-plus' ?>"></i>
                <?= $isEditAngg ? 'Edit Data Anggota — ' . htmlspecialchars($editAngg['nama']) : 'Tambah Anggota Pengurus' ?>
              </div>
              <form method="POST" enctype="multipart/form-data" autocomplete="off" id="form-anggota">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="<?= $isEditAngg ? 'anggota_edit' : 'anggota_tambah' ?>" />
                <input type="hidden" name="organisasi_id" value="<?= $activeOrgId ?>" />
                <?php if ($isEditAngg): ?><input type="hidden" name="id" value="<?= $editAngg['id'] ?>" /><?php endif; ?>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                  <div class="form-group" style="margin:0;">
                    <label>Nama Lengkap <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                      <i class="input-icon fas fa-user"></i>
                      <input type="text" name="nama" value="<?= htmlspecialchars($editAngg['nama'] ?? '') ?>"
                        placeholder="Nama lengkap anggota" required autocomplete="off" />
                    </div>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Jabatan <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                      <i class="input-icon fas fa-id-card"></i>
                      <input type="text" name="jabatan" value="<?= htmlspecialchars($editAngg['jabatan'] ?? '') ?>"
                        placeholder="Contoh: Ketua OSIS" required autocomplete="off" />
                    </div>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Kelas</label>
                    <select name="kelas">
                      <option value="">-- Pilih Kelas --</option>
                      <?php foreach (['10A','10B','10C','10D','10E','10F','11A','11B','11C','11D','11E','11F','12A','12B','12C','12D','12E','12F'] as $k): ?>
                        <option value="<?= $k ?>" <?= ($editAngg['kelas'] ?? '') === $k ? 'selected' : '' ?>><?= $k ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div style="display:flex;align-items:flex-end;gap:.75rem;flex-wrap:wrap;">
                  <div class="form-group" style="margin:0;flex:1;min-width:220px;">
                    <label>Foto Profil</label>
                    <div class="file-upload-zone" id="zone-foto-anggota">
                      <?php if ($isEditAngg && $editAngg['foto']): ?>
                        <div class="file-current-info">
                          <img src="../php/uploads/<?= htmlspecialchars($editAngg['foto']) ?>" alt="Foto saat ini" />
                          <span>📎 Foto saat ini tersimpan — lewati jika tidak ingin mengganti</span>
                        </div>
                      <?php endif; ?>
                      <label class="file-upload-trigger" for="input-foto-anggota">
                        <div class="file-upload-icon-box">🖼️</div>
                        <div class="file-upload-guide">
                          <div class="file-upload-title">Klik atau seret foto ke sini</div>
                          <div class="file-upload-steps">
                            <div class="file-upload-step">
                              <span class="file-upload-step-num">1</span>
                              Pilih file JPG, PNG, atau WebP
                            </div>
                            <div class="file-upload-step">
                              <span class="file-upload-step-num">2</span>
                              Pastikan ukuran <strong>di bawah 5 MB</strong>
                            </div>
                            <div class="file-upload-step">
                              <span class="file-upload-step-num">3</span>
                              Foto akan terpotong otomatis berbentuk lingkaran
                            </div>
                          </div>
                        </div>
                      </label>
                      <input type="file" id="input-foto-anggota" name="foto" accept="image/*"
                        onchange="handleFileUpload(this,'zone-foto-anggota','prev-foto-anggota')" />
                      <div class="file-upload-preview" id="prev-foto-anggota">
                        <img src="" alt="Preview" />
                        <div class="file-upload-preview-info">
                          <div class="file-upload-preview-name"></div>
                          <div class="file-upload-preview-size"></div>
                        </div>
                        <button type="button" class="file-upload-clear"
                          onclick="clearFileUpload('input-foto-anggota','zone-foto-anggota','prev-foto-anggota')">
                          <i class="fas fa-times"></i> Hapus
                        </button>
                      </div>
                    </div>
                  </div>
                  <div style="display:flex;gap:.5rem;padding-bottom:1px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i class="fas fa-save"></i> <?= $isEditAngg ? 'Perbarui' : 'Tambah Anggota' ?>
                    </button>
                    <?php if ($isEditAngg): ?>
                      <a href="?org=<?= $activeOrgId ?>&tab=anggota" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Batal
                      </a>
                    <?php else: ?>
                      <button type="reset" form="form-anggota" class="btn btn-outline btn-sm">
                        <i class="fas fa-redo"></i> Reset
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </form>
            </div>

            <!-- Anggota grid -->
            <?php if ($anggotaList): ?>
              <div class="anggota-grid">
                <?php foreach ($anggotaList as $a): ?>
                  <div class="anggota-card anim-fade-up">
                    <!-- Avatar -->
                    <?php if ($a['foto']): ?>
                      <img src="../php/uploads/<?= htmlspecialchars($a['foto']) ?>" class="anggota-avatar" alt="Foto" />
                    <?php else: ?>
                      <div class="anggota-avatar-placeholder"><?= strtoupper(substr($a['nama'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="anggota-name"><?= htmlspecialchars($a['nama']) ?></div>
                    <span class="badge badge-blue" style="margin-top:.4rem;display:inline-block;font-size:.65rem;">
                      <?= htmlspecialchars($a['jabatan']) ?>
                    </span>
                    <?php if ($a['kelas']): ?>
                      <div style="font-size:.7rem;color:var(--gray-400);margin-top:.3rem;">
                        <i class="fas fa-graduation-cap" style="font-size:.6rem;"></i> Kelas <?= htmlspecialchars($a['kelas']) ?>
                      </div>
                    <?php endif; ?>
                    <!-- Action buttons — always visible -->
                    <div class="anggota-actions">
                      <a href="?org=<?= $activeOrgId ?>&edit_anggota=<?= $a['id'] ?>&tab=anggota#form-anggota-panel"
                        class="btn btn-outline btn-xs" style="font-size:.68rem;padding:.3rem .6rem;" title="Edit">
                        <i class="fas fa-pen"></i> Edit
                      </a>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus anggota ini?')" autocomplete="off">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="anggota_hapus" />
                        <input type="hidden" name="id" value="<?= $a['id'] ?>" />
                        <input type="hidden" name="organisasi_id" value="<?= $activeOrgId ?>" />
                        <button type="submit" class="btn btn-ghost btn-xs"
                          style="color:var(--red);font-size:.68rem;padding:.3rem .6rem;" title="Hapus">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="empty-state" style="padding:3rem;">
                <div class="empty-state-icon">👤</div>
                <h4>Belum Ada Anggota</h4>
                <p>Tambahkan anggota pengurus menggunakan form di atas.</p>
              </div>
            <?php endif; ?>

          </div><!-- /tab-anggota -->

          <!-- ===== TAB PROGRAM KERJA ===== -->
          <div id="tab-proker" class="tab-pane <?= $activeTab === 'proker' ? 'active' : '' ?>">

            <!-- Progress bar proker -->
            <?php if (count($prokerList) > 0):
              $pct = round($prokerSelesai / count($prokerList) * 100); ?>
              <div style="padding:.85rem 1.25rem;background:var(--gray-50);border-bottom:1px solid var(--gray-200);display:flex;align-items:center;gap:1rem;">
                <div style="flex:1;">
                  <div style="display:flex;justify-content:space-between;font-size:.72rem;font-weight:600;color:var(--gray-600);margin-bottom:.35rem;">
                    <span>Progress Penyelesaian Proker</span>
                    <span style="color:var(--primary-mid);"><?= $prokerSelesai ?>/<?= count($prokerList) ?> selesai (<?= $pct ?>%)</span>
                  </div>
                  <div style="height:6px;background:var(--gray-200);border-radius:99px;overflow:hidden;">
                    <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,var(--primary-mid),var(--teal));border-radius:99px;transition:width .5s ease;"></div>
                  </div>
                </div>
                <div style="display:flex;gap:.4rem;flex-shrink:0;">
                  <span class="badge badge-gray" style="font-size:.64rem;">📋 <?= $prokerRencana ?> rencana</span>
                  <span class="badge badge-gold" style="font-size:.64rem;">🔄 <?= $prokerBerjalan ?> berjalan</span>
                  <span class="badge badge-green" style="font-size:.64rem;">✅ <?= $prokerSelesai ?> selesai</span>
                </div>
              </div>
            <?php endif; ?>

            <!-- Form add/edit proker -->
            <?php $isEditProker = !!$editProker; ?>
            <div class="tab-add-panel <?= $isEditProker ? 'editing' : '' ?>">
              <div class="tab-add-title <?= $isEditProker ? 'editing' : '' ?>">
                <i class="fas fa-<?= $isEditProker ? 'pen' : 'plus' ?>"></i>
                <?= $isEditProker ? 'Edit Program Kerja — ' . htmlspecialchars($editProker['nama_program']) : 'Tambah Program Kerja Baru' ?>
              </div>
              <form method="POST" autocomplete="off" id="form-proker">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="<?= $isEditProker ? 'proker_edit' : 'proker_tambah' ?>" />
                <input type="hidden" name="organisasi_id" value="<?= $activeOrgId ?>" />
                <?php if ($isEditProker): ?><input type="hidden" name="id" value="<?= $editProker['id'] ?>" /><?php endif; ?>

                <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:.75rem;margin-bottom:.65rem;">
                  <div class="form-group" style="margin:0;">
                    <label>Nama Program <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                      <i class="input-icon fas fa-tasks"></i>
                      <input type="text" name="nama_program"
                        value="<?= htmlspecialchars($editProker['nama_program'] ?? '') ?>"
                        placeholder="Contoh: Masa Orientasi Siswa Baru" required autocomplete="off" />
                    </div>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Semester</label>
                    <select name="semester">
                      <option value="ganjil" <?= ($editProker['semester'] ?? 'ganjil') === 'ganjil' ? 'selected' : '' ?>>📅 Ganjil</option>
                      <option value="genap"  <?= ($editProker['semester'] ?? '') === 'genap'  ? 'selected' : '' ?>>📅 Genap</option>
                    </select>
                  </div>
                  <div class="form-group" style="margin:0;">
                    <label>Status Awal</label>
                    <select name="status">
                      <option value="rencana"  <?= ($editProker['status'] ?? 'rencana') === 'rencana'  ? 'selected' : '' ?>>📋 Rencana</option>
                      <option value="berjalan" <?= ($editProker['status'] ?? '') === 'berjalan' ? 'selected' : '' ?>>🔄 Berjalan</option>
                      <option value="selesai"  <?= ($editProker['status'] ?? '') === 'selesai'  ? 'selected' : '' ?>>✅ Selesai</option>
                    </select>
                  </div>
                </div>
                <div style="display:flex;align-items:flex-end;gap:.75rem;">
                  <div class="form-group" style="margin:0;flex:1;">
                    <label>Deskripsi <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                    <textarea name="deskripsi" placeholder="Uraian singkat program kerja..." rows="2"
                      style="resize:none;" autocomplete="off"><?= htmlspecialchars($editProker['deskripsi'] ?? '') ?></textarea>
                  </div>
                  <div style="display:flex;gap:.5rem;padding-bottom:1px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i class="fas fa-save"></i> <?= $isEditProker ? 'Perbarui' : 'Tambah' ?>
                    </button>
                    <?php if ($isEditProker): ?>
                      <a href="?org=<?= $activeOrgId ?>&tab=proker" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Batal
                      </a>
                    <?php else: ?>
                      <button type="reset" form="form-proker" class="btn btn-outline btn-sm">
                        <i class="fas fa-redo"></i> Reset
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </form>
            </div>

            <!-- Tabel proker -->
            <div class="table-wrap">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th style="width:36px;">#</th>
                    <th>Nama Program</th>
                    <th style="width:90px;">Semester</th>
                    <th>Deskripsi</th>
                    <th style="width:140px;">Status</th>
                    <th style="width:80px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($prokerList): $no = 0;
                    foreach ($prokerList as $p): $no++;
                      $rowClass = 'proker-' . $p['status']; ?>
                      <tr class="<?= $rowClass ?>">
                        <td style="color:var(--gray-400);font-size:.74rem;font-weight:600;"><?= $no ?></td>
                        <td>
                          <div style="font-weight:700;font-size:.84rem;color:var(--gray-900);"><?= htmlspecialchars($p['nama_program']) ?></div>
                        </td>
                        <td>
                          <span class="badge badge-blue" style="font-size:.64rem;"><?= $semesterLabel[$p['semester']] ?></span>
                        </td>
                        <td>
                          <div style="font-size:.75rem;color:var(--gray-500);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;max-width:220px;">
                            <?= $p['deskripsi'] ? htmlspecialchars($p['deskripsi']) : '<em style="color:var(--gray-300);">—</em>' ?>
                          </div>
                        </td>
                        <td>
                          <form method="POST" style="display:inline;" autocomplete="off">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="proker_status" />
                            <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                            <input type="hidden" name="organisasi_id" value="<?= $activeOrgId ?>" />
                            <select name="status" onchange="this.form.submit()"
                              style="font-size:.73rem;padding:.3rem .55rem;border-radius:20px;font-weight:700;cursor:pointer;
                              border:1.5px solid <?= $p['status']==='berjalan' ? 'var(--gold)' : ($p['status']==='selesai' ? 'var(--teal)' : 'var(--gray-300)') ?>;
                              color:<?= $p['status']==='berjalan' ? 'var(--gold-dark)' : ($p['status']==='selesai' ? 'var(--teal)' : 'var(--gray-500)') ?>;
                              background:<?= $p['status']==='berjalan' ? '#fffbeb' : ($p['status']==='selesai' ? '#f0fdfa' : 'var(--gray-50)') ?>;">
                              <option value="rencana"  <?= $p['status']==='rencana'  ? 'selected' : '' ?>>📋 Rencana</option>
                              <option value="berjalan" <?= $p['status']==='berjalan' ? 'selected' : '' ?>>🔄 Berjalan</option>
                              <option value="selesai"  <?= $p['status']==='selesai'  ? 'selected' : '' ?>>✅ Selesai</option>
                            </select>
                          </form>
                        </td>
                        <td>
                          <div class="td-actions">
                            <a href="?org=<?= $activeOrgId ?>&edit_proker=<?= $p['id'] ?>&tab=proker"
                              class="btn btn-ghost btn-icon btn-xs" title="Edit"><i class="fas fa-pen"></i></a>
                            <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Hapus program kerja ini?')" autocomplete="off">
                              <?= csrfField() ?>
                              <input type="hidden" name="action" value="proker_hapus" />
                              <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                              <input type="hidden" name="organisasi_id" value="<?= $activeOrgId ?>" />
                              <button type="submit" class="btn btn-ghost btn-icon btn-xs"
                                style="color:var(--red);" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach;
                  else: ?>
                    <tr>
                      <td colspan="6">
                        <div class="empty-state" style="padding:2.5rem;">
                          <div class="empty-state-icon">📋</div>
                          <h4>Belum Ada Program Kerja</h4>
                          <p>Tambahkan program kerja menggunakan form di atas.</p>
                        </div>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

          </div><!-- /tab-proker -->

        </div><!-- /admin-card tabs -->

      <?php /* ====================================================
             FORM EDIT ORGANISASI
             ==================================================== */ elseif ($editOrg): ?>

        <div style="max-width:700px;margin:0 auto;">
          <div class="form-section anim-fade-up">
            <div class="form-section-header">
              <div class="form-section-header-icon" style="background:var(--white);">
                <i class="fas fa-pen" style="color:var(--primary-mid);"></i>
              </div>
              <div>
                <h3>Edit Organisasi</h3>
                <p><?= htmlspecialchars($editOrg['nama']) ?></p>
              </div>
            </div>
            <div class="form-section-body">
              <form method="POST" enctype="multipart/form-data" id="form-org-edit" autocomplete="off">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="org_edit" />
                <input type="hidden" name="id" value="<?= $editOrg['id'] ?>" />

                <div class="form-group">
                  <label>Nama Organisasi <span class="req">*</span></label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-sitemap"></i>
                    <input type="text" name="nama" value="<?= htmlspecialchars($editOrg['nama']) ?>"
                      required autocomplete="off" />
                  </div>
                </div>
                <div class="form-group">
                  <label>Deskripsi</label>
                  <textarea name="deskripsi" style="min-height:80px;"
                    autocomplete="off"><?= htmlspecialchars($editOrg['deskripsi'] ?? '') ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Visi</label>
                    <textarea name="visi" style="min-height:100px;"
                      placeholder="Visi organisasi..." autocomplete="off"><?= htmlspecialchars($editOrg['visi'] ?? '') ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Misi</label>
                    <textarea name="misi" style="min-height:100px;"
                      placeholder="Misi organisasi..." autocomplete="off"><?= htmlspecialchars($editOrg['misi'] ?? '') ?></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label>Logo / Gambar</label>
                  <div class="file-upload-zone" id="zone-gambar-edit">
                    <?php if ($editOrg['gambar']): ?>
                      <div class="file-current-info file-current-info-img-rect">
                        <img src="../php/uploads/<?= htmlspecialchars($editOrg['gambar']) ?>" alt="Logo saat ini" />
                        <span>📎 Logo saat ini tersimpan — lewati jika tidak ingin mengganti</span>
                      </div>
                    <?php endif; ?>
                    <label class="file-upload-trigger" for="input-gambar-edit">
                      <div class="file-upload-icon-box">🏷️</div>
                      <div class="file-upload-guide">
                        <div class="file-upload-title">Klik atau seret logo ke sini</div>
                        <div class="file-upload-steps">
                          <div class="file-upload-step">
                            <span class="file-upload-step-num">1</span>
                            Pilih file JPG, PNG, atau WebP
                          </div>
                          <div class="file-upload-step">
                            <span class="file-upload-step-num">2</span>
                            Ukuran file <strong>maksimal 5 MB</strong>
                          </div>
                          <div class="file-upload-step">
                            <span class="file-upload-step-num">3</span>
                            Disarankan resolusi persegi (misal 500×500 px)
                          </div>
                        </div>
                      </div>
                    </label>
                    <input type="file" id="input-gambar-edit" name="gambar" accept="image/*"
                      onchange="handleFileUpload(this,'zone-gambar-edit','prev-gambar-edit')" />
                    <div class="file-upload-preview" id="prev-gambar-edit">
                      <img src="" alt="Preview" style="border-radius:8px;" />
                      <div class="file-upload-preview-info">
                        <div class="file-upload-preview-name"></div>
                        <div class="file-upload-preview-size"></div>
                      </div>
                      <button type="button" class="file-upload-clear"
                        onclick="clearFileUpload('input-gambar-edit','zone-gambar-edit','prev-gambar-edit')">
                        <i class="fas fa-times"></i> Hapus
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="form-section-footer">
              <button type="submit" form="form-org-edit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
              </button>
              <a href="osim.php" class="btn btn-outline"><i class="fas fa-times"></i> Batal</a>
            </div>
          </div>
        </div>

      <?php /* ====================================================
             HALAMAN UTAMA — List + Form Tambah
             ==================================================== */ else: ?>

        <!-- Stat Cards -->
        <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.75rem;">
          <?php foreach ([
            ['num' => $totalOrg,    'label' => 'Organisasi',      'icon' => 'fa-sitemap',  'class' => 'stat-teal',   'delay' => 1],
            ['num' => $totalAngg,   'label' => 'Total Anggota',   'icon' => 'fa-users',    'class' => 'stat-green',  'delay' => 2],
            ['num' => $totalProker, 'label' => 'Total Proker',    'icon' => 'fa-tasks',    'class' => 'stat-blue',   'delay' => 3],
            ['num' => $prokerAktif, 'label' => 'Proker Berjalan', 'icon' => 'fa-bolt',     'class' => 'stat-orange', 'delay' => 4],
          ] as $sc): ?>
            <div class="stat-card <?= $sc['class'] ?> anim-fade-up anim-delay-<?= $sc['delay'] ?>">
              <div class="stat-card-shine"></div>
              <div class="stat-icon"><i class="fas <?= $sc['icon'] ?>"></i></div>
              <div class="stat-num"><?= $sc['num'] ?></div>
              <div class="stat-label"><?= $sc['label'] ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="two-col" style="align-items:start;">

          <!-- FORM TAMBAH -->
          <div class="form-section anim-fade-up">
            <div class="form-section-header">
              <div class="form-section-header-icon">
                <i class="fas fa-plus" style="color:var(--primary-mid);"></i>
              </div>
              <div>
                <h3>Tambah Organisasi Baru</h3>
                <p>Daftarkan OSIM atau OSIS baru ke sistem</p>
              </div>
            </div>
            <div class="form-section-body">
              <form method="POST" enctype="multipart/form-data" id="form-org-tambah" autocomplete="off">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="org_tambah" />

                <div class="form-group">
                  <label>Nama Organisasi <span class="req">*</span></label>
                  <div class="input-icon-wrap">
                    <i class="input-icon fas fa-sitemap"></i>
                    <input type="text" name="nama" placeholder="Contoh: OSIS MAN 1 Bangka"
                      required autocomplete="off" />
                  </div>
                </div>
                <div class="form-group">
                  <label>Deskripsi</label>
                  <textarea name="deskripsi" rows="3"
                    placeholder="Gambaran umum organisasi..." autocomplete="off"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;">
                  <div class="form-group">
                    <label>Visi</label>
                    <textarea name="visi" rows="3"
                      placeholder="Visi organisasi..." autocomplete="off"></textarea>
                  </div>
                  <div class="form-group">
                    <label>Misi</label>
                    <textarea name="misi" rows="3"
                      placeholder="Misi organisasi..." autocomplete="off"></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label>Logo Organisasi <span style="color:var(--gray-400);font-weight:400;">(opsional)</span></label>
                  <div class="file-upload-zone" id="zone-gambar-tambah">
                    <label class="file-upload-trigger" for="input-gambar-tambah">
                      <div class="file-upload-icon-box">🏷️</div>
                      <div class="file-upload-guide">
                        <div class="file-upload-title">Klik atau seret logo ke sini</div>
                        <div class="file-upload-steps">
                          <div class="file-upload-step">
                            <span class="file-upload-step-num">1</span>
                            Pilih file JPG, PNG, atau WebP
                          </div>
                          <div class="file-upload-step">
                            <span class="file-upload-step-num">2</span>
                            Ukuran file <strong>maksimal 5 MB</strong>
                          </div>
                          <div class="file-upload-step">
                            <span class="file-upload-step-num">3</span>
                            Disarankan resolusi persegi (misal 500×500 px)
                          </div>
                        </div>
                      </div>
                    </label>
                    <input type="file" id="input-gambar-tambah" name="gambar" accept="image/*"
                      onchange="handleFileUpload(this,'zone-gambar-tambah','prev-gambar-tambah')" />
                    <div class="file-upload-preview" id="prev-gambar-tambah">
                      <img src="" alt="Preview" style="border-radius:8px;" />
                      <div class="file-upload-preview-info">
                        <div class="file-upload-preview-name"></div>
                        <div class="file-upload-preview-size"></div>
                      </div>
                      <button type="button" class="file-upload-clear"
                        onclick="clearFileUpload('input-gambar-tambah','zone-gambar-tambah','prev-gambar-tambah')">
                        <i class="fas fa-times"></i> Hapus
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="form-section-footer">
              <button type="submit" form="form-org-tambah" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Simpan Organisasi
              </button>
              <button type="reset" form="form-org-tambah" class="btn btn-outline">
                <i class="fas fa-redo"></i> Reset
              </button>
            </div>
          </div>

          <!-- DAFTAR ORGANISASI — card-based -->
          <div class="anim-fade-up anim-delay-2">
            <div style="font-size:.72rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.75rem;padding-left:.1rem;">
              <i class="fas fa-list" style="margin-right:.35rem;"></i> <?= $totalOrg ?> Organisasi Terdaftar
            </div>

            <?php if ($orgList): ?>
              <div style="display:flex;flex-direction:column;gap:1rem;">
                <?php foreach ($orgList as $o): ?>
                  <div class="org-card">
                    <!-- Banner gradient -->
                    <div class="org-card-banner">
                      <div class="org-card-banner-dot"></div>
                      <!-- Avatar on banner -->
                      <div class="org-card-avatar">
                        <?php if ($o['gambar']): ?>
                          <img src="../php/uploads/<?= htmlspecialchars($o['gambar']) ?>"
                            style="width:100%;height:100%;object-fit:cover;" alt="Logo" />
                        <?php else: ?>
                          <?= strtoupper(substr($o['nama'], 0, 1)) ?>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="org-card-body">
                      <div class="org-card-name" style="margin-top:1.2rem;"><?= htmlspecialchars($o['nama']) ?></div>
                      <?php if ($o['deskripsi']): ?>
                        <div class="org-card-desc"><?= htmlspecialchars($o['deskripsi']) ?></div>
                      <?php endif; ?>
                      <div class="org-card-stats">
                        <span class="badge badge-blue" style="font-size:.64rem;">
                          <i class="fas fa-users" style="font-size:.58rem;margin-right:.2rem;"></i>
                          <?= $o['total_anggota'] ?> Anggota
                        </span>
                        <span class="badge badge-<?= $o['proker_berjalan'] > 0 ? 'gold' : 'gray' ?>" style="font-size:.64rem;">
                          <i class="fas fa-tasks" style="font-size:.58rem;margin-right:.2rem;"></i>
                          <?= $o['total_proker'] ?> Proker
                          <?php if ($o['proker_berjalan'] > 0): ?>
                            <span style="margin-left:.2rem;opacity:.8;">· <?= $o['proker_berjalan'] ?> berjalan</span>
                          <?php endif; ?>
                        </span>
                      </div>
                    </div>

                    <div class="org-card-footer">
                      <a href="?org=<?= $o['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;">
                        <i class="fas fa-cog"></i> Kelola
                      </a>
                      <a href="?edit_org=<?= $o['id'] ?>" class="btn btn-outline btn-sm btn-icon" title="Edit">
                        <i class="fas fa-pen"></i>
                      </a>
                      <form method="POST" style="display:inline;"
                        onsubmit="return confirm('Hapus organisasi ini beserta semua anggota dan program kerjanya?')"
                        autocomplete="off">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="org_hapus" />
                        <input type="hidden" name="id" value="<?= $o['id'] ?>" />
                        <button type="submit" class="btn btn-ghost btn-sm btn-icon"
                          style="color:var(--red);" title="Hapus">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="admin-card">
                <div class="empty-state" style="padding:3.5rem 2rem;">
                  <div class="empty-state-icon">🏫</div>
                  <h4>Belum Ada Organisasi</h4>
                  <p>Tambahkan organisasi menggunakan form di sebelah kiri.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>

        </div><!-- /two-col -->

      <?php endif; ?>

    </div><!-- /page-content -->
  </main>

  <script src="assets/admin.js"></script>
  <script>
    /* ============================================================
       CUSTOM FILE UPLOAD ZONE — handler
       ============================================================ */
    function fmtFileSize(bytes) {
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function handleFileUpload(input, zoneId, previewId) {
      const zone    = document.getElementById(zoneId);
      const preview = document.getElementById(previewId);
      if (!input.files || !input.files[0]) return;

      const file = input.files[0];
      const MAX  = 5 * 1024 * 1024; // 5 MB

      // Validasi ukuran
      if (file.size > MAX) {
        showToast('File terlalu besar! Maksimal 5 MB.', 'err');
        input.value = '';
        return;
      }
      // Validasi tipe
      if (!file.type.startsWith('image/')) {
        showToast('Hanya file gambar (JPG/PNG/WebP) yang diperbolehkan.', 'err');
        input.value = '';
        return;
      }

      // Tampilkan preview
      const reader = new FileReader();
      reader.onload = e => {
        const img  = preview.querySelector('img');
        const name = preview.querySelector('.file-upload-preview-name');
        const size = preview.querySelector('.file-upload-preview-size');
        img.src    = e.target.result;
        name.textContent = file.name;
        size.textContent = '✅ ' + fmtFileSize(file.size) + ' — siap diupload';
        preview.classList.add('show');
        zone.classList.add('has-file');
      };
      reader.readAsDataURL(file);
    }

    function clearFileUpload(inputId, zoneId, previewId) {
      const input   = document.getElementById(inputId);
      const zone    = document.getElementById(zoneId);
      const preview = document.getElementById(previewId);
      input.value = '';
      preview.classList.remove('show');
      zone.classList.remove('has-file', 'drag-over');
    }

    // Drag & drop untuk semua upload zone
    document.querySelectorAll('.file-upload-zone').forEach(zone => {
      const input = zone.querySelector('input[type="file"]');
      if (!input) return;

      zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.classList.add('drag-over');
      });
      zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
      zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
          input.files = e.dataTransfer.files;
          input.dispatchEvent(new Event('change'));
        }
      });
    });

    // Auto-scroll & highlight form edit anggota ketika URL punya ?edit_anggota=
    (function () {
      const params = new URLSearchParams(location.search);
      if (params.has('edit_anggota')) {
        const panel = document.getElementById('form-anggota-panel');
        if (panel) {
          setTimeout(() => {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            panel.classList.add('highlight-anim');
            panel.addEventListener('animationend', () => panel.classList.remove('highlight-anim'), { once: true });
          }, 120);
          setTimeout(() => {
            const firstInput = panel.querySelector('input[name="nama"]');
            if (firstInput) firstInput.focus();
          }, 450);
        }
      }
    })();
  </script>
</body>

</html>
