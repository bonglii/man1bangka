<?php
// ============================================================
// api.php — REST API Endpoint Utama
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// PERUBAHAN (perbaikan ERD):
//   - Module 'ekskul': COALESCE(k.nama, e.nama_pembina) → k.nama
//     Kolom e.nama_pembina sudah dihapus dari tabel ekstrakurikuler.
//     Seluruh data pembina kini wajib tersedia via FK pembina_id.
// ============================================================
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? 'list';
$conn   = getConnection();

switch ($module) {

    // ----------------------------------------------------------
    // MODULE: pengumuman
    // ----------------------------------------------------------
    case 'pengumuman':
        if ($action === 'list') {
            $limit = min((int)($_GET['limit'] ?? 20), 50);
            $kat   = sanitize($_GET['kategori'] ?? '');

            $sql = "SELECT * FROM pengumuman WHERE 1=1";
            $params = [];
            $types  = '';
            if ($kat) { $sql .= " AND kategori=?"; $params[] = $kat; $types .= 's'; }
            $sql .= " ORDER BY tanggal_publish DESC LIMIT " . (int)$limit;

            $data = [];
            $stmt = $conn->prepare($sql);
            if ($params) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['tanggal_publish_format'] = formatTanggal($row['tanggal_publish']);
                $data[] = $row;
            }
            jsonResponse('success', $data);
        }

        if ($action === 'highlight') {
            $data = [];
            $res  = $conn->query("SELECT * FROM pengumuman WHERE is_highlight=1 ORDER BY tanggal_publish DESC LIMIT 3");
            while ($row = $res->fetch_assoc()) {
                $row['tanggal_publish_format'] = formatTanggal($row['tanggal_publish']);
                $data[] = $row;
            }
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: agenda
    // ----------------------------------------------------------
    case 'agenda':
        if ($action === 'list') {
            $bulan = (int)($_GET['bulan'] ?? date('m'));
            $tahun = (int)($_GET['tahun'] ?? date('Y'));

            $data = [];
            $stmt = $conn->prepare(
                "SELECT * FROM agenda
                 WHERE MONTH(tanggal_mulai)=? AND YEAR(tanggal_mulai)=?
                 ORDER BY tanggal_mulai ASC"
            );
            $stmt->bind_param('ii', $bulan, $tahun);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['tanggal_format'] = formatTanggal($row['tanggal_mulai']);
                $data[] = $row;
            }
            jsonResponse('success', $data);
        }

        if ($action === 'upcoming') {
            $data = [];
            $res  = $conn->query(
                "SELECT * FROM agenda
                 WHERE tanggal_mulai >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                   AND is_selesai = 0
                 ORDER BY tanggal_mulai ASC
                 LIMIT 5"
            );

            if (!$res || $res->num_rows === 0) {
                $res = $conn->query("SELECT * FROM agenda ORDER BY tanggal_mulai DESC LIMIT 5");
            }

            while ($row = $res->fetch_assoc()) {
                $row['tanggal_format'] = formatTanggal($row['tanggal_mulai']);
                $data[] = $row;
            }
            jsonResponse('success', $data);
        }

        if ($action === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $judul  = sanitize($_POST['judul'] ?? '');
            $desk   = sanitize($_POST['deskripsi'] ?? '');
            $tgl    = sanitize($_POST['tanggal_mulai'] ?? '');
            $tgl2   = sanitize($_POST['tanggal_selesai'] ?? '') ?: null;
            $lokasi = sanitize($_POST['lokasi'] ?? '');
            $kat    = sanitize($_POST['kategori'] ?? 'umum');
            $warna  = sanitize($_POST['warna'] ?? '#1a6b3c');
            $org_id    = ($_POST['organisasi_id'] ?? '') !== '' ? (int)$_POST['organisasi_id'] : null;
            $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;

            if (!$judul || !$tgl) jsonResponse('error', [], 'Judul dan tanggal wajib diisi');

            $stmt = $conn->prepare(
                "INSERT INTO agenda (judul, deskripsi, tanggal_mulai, tanggal_selesai, lokasi, kategori, warna, is_selesai, organisasi_id, ekskul_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)"
            );
            $stmt->bind_param('sssssssii', $judul, $desk, $tgl, $tgl2, $lokasi, $kat, $warna, $org_id, $ekskul_id);

            if ($stmt->execute()) {
                jsonResponse('success', ['id' => $conn->insert_id], 'Agenda berhasil ditambahkan');
            } else {
                jsonResponse('error', [], 'Gagal menyimpan agenda');
            }
        }
        break;

    // ----------------------------------------------------------
    // MODULE: ekskul
    // PERUBAHAN: Hapus COALESCE(k.nama, e.nama_pembina)
    //            Kolom e.nama_pembina sudah tidak ada di tabel.
    //            Gunakan k.nama langsung (bisa NULL jika belum ada pembina).
    // ----------------------------------------------------------
    case 'ekskul':
        if ($action === 'list') {
            $kat = sanitize($_GET['kategori'] ?? '');

            // SEBELUM (lama — bermasalah setelah kolom nama_pembina dihapus):
            // $sql = "SELECT e.*, COALESCE(k.nama, e.nama_pembina) AS nama_pembina, k.no_hp AS hp_pembina
            //         FROM ekstrakurikuler e
            //         LEFT JOIN kontak_pembina k ON e.pembina_id = k.id";

            // SESUDAH (baru — menggunakan k.nama langsung):
            $sql = "SELECT e.*, k.nama AS nama_pembina, k.no_hp AS hp_pembina
                    FROM ekstrakurikuler e
                    LEFT JOIN kontak_pembina k ON e.pembina_id = k.id";

            $eksParams = [];
            $eksTypes  = '';
            if ($kat) { $sql .= " WHERE e.kategori=?"; $eksParams[] = $kat; $eksTypes .= 's'; }
            $sql .= " ORDER BY e.nama ASC";

            $data = [];
            $stmtEks = $conn->prepare($sql);
            if ($eksParams) $stmtEks->bind_param($eksTypes, ...$eksParams);
            $stmtEks->execute();
            $res = $stmtEks->get_result();
            while ($row = $res->fetch_assoc()) $data[] = $row;
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: prestasi
    // ----------------------------------------------------------
    case 'prestasi':
        if ($action === 'list') {
            $tingkat = sanitize($_GET['tingkat'] ?? '');

            $sql = "SELECT * FROM prestasi";
            $presParams = [];
            $presTypes  = '';
            if ($tingkat) { $sql .= " WHERE tingkat=?"; $presParams[] = $tingkat; $presTypes .= 's'; }
            $sql .= " ORDER BY tahun DESC, tingkat DESC";

            $data = [];
            $stmtPres = $conn->prepare($sql);
            if ($presParams) $stmtPres->bind_param($presTypes, ...$presParams);
            $stmtPres->execute();
            $res = $stmtPres->get_result();
            while ($row = $res->fetch_assoc()) $data[] = $row;
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: dokumentasi
    // ----------------------------------------------------------
    case 'dokumentasi':
        if ($action === 'list' || $action === 'foto' || $action === 'video') {
            $jenis = ($action === 'foto')  ? 'foto'
                   : (($action === 'video') ? 'video'
                   : sanitize($_GET['jenis'] ?? ''));

            $kat   = sanitize($_GET['kategori'] ?? '');
            $limit = min((int)($_GET['limit'] ?? 50), 200);

            $docParams = [];
            $docTypes  = '';
            $sql = "SELECT * FROM dokumentasi WHERE 1=1";
            if ($jenis) { $sql .= " AND jenis=?";     $docParams[] = $jenis; $docTypes .= 's'; }
            if ($kat)   { $sql .= " AND kategori=?";  $docParams[] = $kat;   $docTypes .= 's'; }
            $sql .= " ORDER BY tanggal DESC LIMIT " . (int)$limit;

            $data = [];
            $stmtDoc = $conn->prepare($sql);
            if ($docParams) $stmtDoc->bind_param($docTypes, ...$docParams);
            $stmtDoc->execute();
            $res = $stmtDoc->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['tanggal_format'] = formatTanggal($row['tanggal']);
                $data[] = $row;
            }
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: karya
    // ----------------------------------------------------------
    case 'karya':
        if ($action === 'list') {
            $jenis = sanitize($_GET['jenis'] ?? '');

            $karyaParams = [];
            $karyaTypes  = '';
            $sql = "SELECT * FROM karya_siswa WHERE 1=1";
            if ($jenis) { $sql .= " AND jenis=?"; $karyaParams[] = $jenis; $karyaTypes .= 's'; }
            $sql .= " ORDER BY id DESC";

            $data = [];
            $stmtKarya = $conn->prepare($sql);
            if ($karyaParams) $stmtKarya->bind_param($karyaTypes, ...$karyaParams);
            $stmtKarya->execute();
            $res = $stmtKarya->get_result();
            while ($row = $res->fetch_assoc()) $data[] = $row;
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: arsip
    // ----------------------------------------------------------
    case 'arsip':
        if ($action === 'list') {
            $ta  = sanitize($_GET['tahun_ajaran'] ?? '');
            $sem = sanitize($_GET['semester'] ?? '');

            $arsipParams = [];
            $arsipTypes  = '';
            $sql = "SELECT * FROM arsip WHERE 1=1";
            if ($ta)  { $sql .= " AND tahun_ajaran=?"; $arsipParams[] = $ta;  $arsipTypes .= 's'; }
            if ($sem) { $sql .= " AND semester=?";     $arsipParams[] = $sem; $arsipTypes .= 's'; }
            $sql .= " ORDER BY tahun_ajaran DESC, semester DESC";

            $data = [];
            $stmtArsip = $conn->prepare($sql);
            if ($arsipParams) $stmtArsip->bind_param($arsipTypes, ...$arsipParams);
            $stmtArsip->execute();
            $res = $stmtArsip->get_result();
            while ($row = $res->fetch_assoc()) $data[] = $row;
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: organisasi
    // ----------------------------------------------------------
    case 'organisasi':
        if ($action === 'list') {
            $data = [];
            $res  = $conn->query("SELECT * FROM organisasi ORDER BY nama ASC");

            while ($row = $res->fetch_assoc()) {
                $id = $row['id'];

                $stmtAngg = $conn->prepare("SELECT * FROM anggota_organisasi WHERE organisasi_id=? ORDER BY jabatan ASC");
                $stmtAngg->bind_param('i', $id);
                $stmtAngg->execute();
                $angg = $stmtAngg->get_result();
                $row['anggota'] = [];
                while ($a = $angg->fetch_assoc()) $row['anggota'][] = $a;

                $stmtPk = $conn->prepare("SELECT * FROM program_kerja WHERE organisasi_id=? ORDER BY semester ASC");
                $stmtPk->bind_param('i', $id);
                $stmtPk->execute();
                $pk = $stmtPk->get_result();
                $row['program_kerja'] = [];
                while ($p = $pk->fetch_assoc()) $row['program_kerja'][] = $p;

                $data[] = $row;
            }
            jsonResponse('success', $data);
        }
        break;

    // ----------------------------------------------------------
    // MODULE: testimoni
    // ----------------------------------------------------------
    case 'testimoni':
        if ($action === 'list') {
            $data = [];
            $res  = $conn->query("SELECT * FROM testimoni WHERE status='aktif' ORDER BY created_at DESC LIMIT 12");
            while ($row = $res->fetch_assoc()) $data[] = $row;
            jsonResponse('success', $data);
        }

        if ($action === 'tambah' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama   = sanitize($_POST['nama_siswa'] ?? '');
            $kelas  = sanitize($_POST['kelas'] ?? '');
            $jenis  = sanitize($_POST['jenis_kegiatan'] ?? 'lainnya');
            $namKeg = sanitize($_POST['nama_kegiatan'] ?? '');
            $isi    = sanitize($_POST['isi'] ?? '');
            $rating = min(5, max(1, (int)($_POST['rating'] ?? 5)));
            $org_id    = ($_POST['organisasi_id'] ?? '') !== '' ? (int)$_POST['organisasi_id'] : null;
            $ekskul_id = ($_POST['ekskul_id'] ?? '') !== '' ? (int)$_POST['ekskul_id'] : null;

            if (!$nama || !$isi) jsonResponse('error', [], 'Nama dan isi testimoni wajib diisi');

            $stmt = $conn->prepare(
                "INSERT INTO testimoni (nama_siswa, kelas, jenis_kegiatan, nama_kegiatan, isi, rating, status, organisasi_id, ekskul_id)
                 VALUES (?, ?, ?, ?, ?, ?, 'aktif', ?, ?)"
            );
            $stmt->bind_param('sssssiii', $nama, $kelas, $jenis, $namKeg, $isi, $rating, $org_id, $ekskul_id);

            if ($stmt->execute()) {
                jsonResponse('success', [], 'Testimoni berhasil dikirim!');
            } else {
                jsonResponse('error', [], 'Gagal menyimpan testimoni');
            }
        }
        break;

    // ----------------------------------------------------------
    // MODULE: daftar_ekskul
    // ----------------------------------------------------------
    case 'daftar_ekskul':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ekskulId = (int)($_POST['ekstrakurikuler_id'] ?? 0);
            $nama     = sanitize($_POST['nama_siswa'] ?? '');
            $kelas    = sanitize($_POST['kelas'] ?? '');
            $nis      = sanitize($_POST['nis'] ?? '');
            $noHp     = sanitize($_POST['no_hp'] ?? '');
            $email    = sanitize($_POST['email'] ?? '');
            $alasan   = sanitize($_POST['alasan'] ?? '');

            if (!$ekskulId || !$nama || !$kelas || !$nis) {
                jsonResponse('error', [], 'Data tidak lengkap');
            }

            $stmtCek = $conn->prepare("SELECT id FROM pendaftaran_ekskul WHERE nis=? AND ekstrakurikuler_id=?");
            $stmtCek->bind_param('si', $nis, $ekskulId);
            $stmtCek->execute();
            $cek = $stmtCek->get_result();
            if ($cek->num_rows > 0) {
                jsonResponse('error', [], 'Anda sudah terdaftar di ekstrakurikuler ini');
            }

            $stmt = $conn->prepare(
                "INSERT INTO pendaftaran_ekskul (ekstrakurikuler_id, nama_siswa, kelas, nis, no_hp, email, alasan)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issssss', $ekskulId, $nama, $kelas, $nis, $noHp, $email, $alasan);

            if ($stmt->execute()) {
                jsonResponse('success', [], 'Pendaftaran berhasil! Menunggu konfirmasi pembina.');
            } else {
                jsonResponse('error', [], 'Gagal mendaftar');
            }
        }
        break;

    // ----------------------------------------------------------
    // MODULE: pembina
    // ----------------------------------------------------------
    case 'pembina':
        if ($action === 'list') {
            $data = [];
            $res  = $conn->query("SELECT * FROM kontak_pembina ORDER BY bidang, nama ASC");
            while ($row = $res->fetch_assoc()) $data[] = $row;
            jsonResponse('success', $data);
        }
        break;

    default:
        jsonResponse('error', [], 'Modul tidak ditemukan');
}

$conn->close();
?>
