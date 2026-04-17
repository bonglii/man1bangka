<?php
// ============================================================
// config.php — Konfigurasi Database & Helper Global
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// File ini adalah pusat konfigurasi seluruh aplikasi.
// Di-require oleh semua halaman admin dan api.php.
// Berisi: koneksi MySQLi, koneksi PDO, dan fungsi-fungsi helper.
// ============================================================

// ------------------------------------------------------------
// KONSTANTA DATABASE
// Sesuaikan nilai di bawah dengan konfigurasi MySQL Anda.
// ------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Ganti sesuai username MySQL Anda
define('DB_PASS', '');             // Ganti sesuai password MySQL Anda
define('DB_NAME', 'man1bangka');
define('DB_CHARSET', 'utf8mb4');   // utf8mb4 mendukung emoji dan karakter khusus

// ------------------------------------------------------------
// KONSTANTA SITUS
// SITE_URL digunakan untuk membangun URL absolut pada response API.
// UPLOAD_DIR & UPLOAD_URL merujuk ke folder php/uploads/ yang
//   digunakan oleh halaman admin untuk menyimpan file upload.
// ------------------------------------------------------------
define('SITE_URL',    'http://localhost/man1bangka');
define('SITE_NAME',   'MAN 1 Bangka');
// UPLOAD_DIR & UPLOAD_URL: path absolut & URL ke folder uploads.
// Digunakan oleh modul upload di panel admin.
define('UPLOAD_DIR',  __DIR__ . '/uploads/');          // man1bangka/php/uploads/
define('UPLOAD_URL',  SITE_URL . '/php/uploads/');

// ============================================================
// FUNGSI: getConnection()
// Mengembalikan koneksi MySQLi baru.
// Dipakai oleh api.php karena api menggunakan MySQLi procedural
// untuk efisiensi query loop fetch_assoc().
// Jika koneksi gagal, langsung output JSON error dan berhenti.
// ============================================================
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Cek apakah koneksi berhasil
    if ($conn->connect_error) {
        die(json_encode([
            'status'  => 'error',
            'message' => 'Koneksi database gagal: ' . $conn->connect_error
        ]));
    }

    // Set charset agar karakter Indonesia dan emoji tersimpan dengan benar
    $conn->set_charset(DB_CHARSET);
    return $conn;
}

// ============================================================
// FUNGSI: getPDO()
// Mengembalikan instance PDO (singleton) untuk panel admin.
// PDO dipilih untuk admin karena prepared statement-nya lebih
// readable dan mendukung named placeholder.
// Static variable memastikan koneksi hanya dibuat sekali
// per request (singleton pattern — hemat resource).
// ============================================================
function getPDO(): PDO {
    static $pdo = null; // Hanya diinisialisasi sekali per request

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception saat error SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch sebagai array asosiatif
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan prepared statement native (lebih aman dari SQL injection)
            ]);
        } catch (PDOException $e) {
            // Tampilkan pesan error yang ramah — bukan stack trace mentah
            die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem;">'
              . '<strong>Koneksi Database Gagal</strong><br>'
              . htmlspecialchars($e->getMessage())
              . '<br><br>Periksa konfigurasi di <code>php/config.php</code></div>');
        }
    }
    return $pdo;
}

// Buat variabel $pdo global agar langsung tersedia di setiap file admin
// tanpa perlu memanggil getPDO() secara eksplisit.
$pdo = getPDO();

// ============================================================
// FUNGSI: jsonResponse($status, $data, $message)
// Helper untuk mengirimkan response JSON terstandarisasi.
// Format response selalu:
//   { "status": "success|error", "message": "...", "data": [...] }
// Memanggil exit() setelah output agar tidak ada output lain.
// ============================================================
function jsonResponse($status, $data = [], $message = '') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // Izinkan CORS dari semua origin (frontend)
    echo json_encode([
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

// ============================================================
// FUNGSI: sanitize($input)
// Membersihkan input string dari tag HTML dan whitespace berlebih.
// Digunakan untuk mencegah XSS pada output HTML.
// PENTING: Untuk keamanan SQL tetap gunakan prepared statement —
//          sanitize() bukan pengganti prepared statement.
// ============================================================
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// ============================================================
// CSRF PROTECTION
// Semua form POST di panel admin dilindungi CSRF token.
// Token dibuat sekali per sesi dan diverifikasi setiap POST.
// ============================================================

/**
 * Ambil (atau buat) CSRF token untuk sesi ini.
 * Wajib session_start() sudah dipanggil sebelumnya.
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifikasi CSRF token dari form POST.
 * Gunakan hash_equals() agar tahan timing attack.
 * Panggil di awal blok POST setiap halaman admin.
 */
function verifyCsrf(): void {
    $token = $_POST['_csrf'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem;">'
          . '<strong>403 — Permintaan ditolak.</strong><br>'
          . 'Token keamanan tidak valid. Kembali dan coba lagi.'
          . '</div>');
    }
}

/**
 * Render hidden input CSRF token untuk disematkan di setiap form POST admin.
 * Contoh penggunaan: <?= csrfField() ?>
 */
function csrfField(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(getCsrfToken()) . '" />';
}

// ============================================================
// FUNGSI: formatTanggal($date)
function formatTanggal($date) {
    // Mapping nomor bulan ke nama bulan Bahasa Indonesia
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April',   '05' => 'Mei',       '06' => 'Juni',
        '07' => 'Juli',    '08' => 'Agustus',   '09' => 'September',
        '10' => 'Oktober', '11' => 'November',  '12' => 'Desember'
    ];

    if (!$date) return '-'; // Tanggal kosong atau null

    // Ambil 10 karakter pertama (YYYY-MM-DD), abaikan bagian waktu jika ada
    $d = explode('-', substr($date, 0, 10));
    if (count($d) < 3) return $date; // Format tidak dikenali, kembalikan apa adanya

    // Susun: hari (tanpa leading zero) + spasi + nama bulan + spasi + tahun
    return $d[2] . ' ' . ($bulan[$d[1]] ?? $d[1]) . ' ' . $d[0];
}
?>
