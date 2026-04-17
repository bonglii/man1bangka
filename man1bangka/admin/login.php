<?php
// ============================================================
// login.php — Halaman Login Panel Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// KEAMANAN (diperkuat):
//   1. Password disimpan sebagai bcrypt hash (bukan plaintext)
//   2. Dilindungi CSRF token — form POST tanpa token valid = ditolak
//   3. Brute-force protection: maks 5 gagal login per 15 menit
//
// CARA GANTI PASSWORD:
//   Jalankan di terminal: php -r "echo password_hash('password_baru', PASSWORD_DEFAULT);"
//   Tempel hasilnya ke konstanta ADMIN_PASS_HASH di bawah.
// ============================================================

session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['admin_logged_in'])) {
  header('Location: index.php');
  exit;
}

// ------------------------------------------------------------
// KONFIGURASI KREDENSIAL
// Password hash bcrypt untuk 'man1bangka2026'
// Ganti hash ini jika ingin mengganti password.
// ------------------------------------------------------------
define('ADMIN_USER_NAME',  'admin');
define('ADMIN_PASS_HASH',  '$2y$12$YqmEV3aWVnpbF8zNQkWlMOAvPmrX3eK.1vFnjHBLxXqsj7WFrFN/e');
// Hash di atas dibuat dengan: password_hash('man1bangka2026', PASSWORD_DEFAULT, ['cost'=>12])

// ------------------------------------------------------------
// BRUTE-FORCE PROTECTION
// Simpan hitungan gagal login & timestamp di session.
// Maks 5 percobaan dalam 15 menit → lockout.
// ------------------------------------------------------------
define('MAX_ATTEMPTS',   5);
define('LOCKOUT_SECS',   15 * 60); // 15 menit

$now         = time();
$attempts    = $_SESSION['login_attempts'] ?? 0;
$lastAttempt = $_SESSION['login_last_attempt'] ?? 0;

// Reset counter jika lockout sudah lewat
if ($attempts >= MAX_ATTEMPTS && ($now - $lastAttempt) >= LOCKOUT_SECS) {
  $_SESSION['login_attempts']     = 0;
  $_SESSION['login_last_attempt'] = 0;
  $attempts = 0;
}

$isLocked = ($attempts >= MAX_ATTEMPTS);
$lockRemaining = $isLocked ? (LOCKOUT_SECS - ($now - $lastAttempt)) : 0;

$error = '';

// ------------------------------------------------------------
// PROSES FORM LOGIN
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {

  // --- Verifikasi CSRF ---
  $csrfOk = isset($_POST['_csrf'], $_SESSION['login_csrf'])
            && hash_equals($_SESSION['login_csrf'], $_POST['_csrf']);
  if (!$csrfOk) {
    http_response_code(403);
    die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem;">
         <strong>403 — Permintaan ditolak.</strong><br>Token keamanan tidak valid. <a href="login.php">Kembali</a></div>');
  }

  $user = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';

  // --- Cek kredensial: username exact-match + bcrypt verify ---
  if ($user === ADMIN_USER_NAME && password_verify($pass, ADMIN_PASS_HASH)) {
    // Login sukses — reset counter, buat session baru (session fixation prevention)
    session_regenerate_id(true);
    $_SESSION['admin_logged_in']    = true;
    $_SESSION['admin_user']         = $user;
    $_SESSION['login_attempts']     = 0;
    $_SESSION['login_last_attempt'] = 0;
    unset($_SESSION['login_csrf']);
    header('Location: index.php');
    exit;
  } else {
    // Login gagal — naikkan counter
    $_SESSION['login_attempts']     = $attempts + 1;
    $_SESSION['login_last_attempt'] = $now;
    $attempts++;
    $isLocked = ($attempts >= MAX_ATTEMPTS);

    if ($isLocked) {
      $error = 'Terlalu banyak percobaan gagal. Akun terkunci 15 menit.';
    } else {
      $remaining = MAX_ATTEMPTS - $attempts;
      $error = 'Username atau password salah! Sisa percobaan: ' . $remaining . 'x.';
    }
  }
}

// Generate CSRF token baru untuk form (jika belum ada atau sudah terpakai)
if (empty($_SESSION['login_csrf'])) {
  $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['login_csrf'];
?>
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Login Admin — MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --green: #0B3D2E;
      --green-mid: #1a6b3c;
      --gold: #C9A84C;
      --cream: #FAF7F0;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #0B3D2E 0%, #1a6b3c 60%, #0f5132 100%);
      background-image:
        radial-gradient(circle at 20% 50%, rgba(201, 168, 76, .12) 0%, transparent 60%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, .05) 0%, transparent 50%),
        linear-gradient(135deg, #0B3D2E 0%, #1a6b3c 60%, #0f5132 100%);
    }

    .login-wrap {
      width: 100%;
      max-width: 400px;
      padding: 1rem;
    }

    .login-box {
      background: #fff;
      border-radius: 20px;
      padding: 2.5rem 2.25rem;
      box-shadow: 0 25px 50px rgba(0, 0, 0, .25);
    }

    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-logo {
      width: 72px;
      height: 72px;
      background: linear-gradient(135deg, var(--green), var(--green-mid));
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
      font-size: 1.4rem;
      font-weight: 800;
      color: var(--gold);
      box-shadow: 0 8px 24px rgba(11, 61, 46, .3);
      letter-spacing: -1px;
    }

    .login-header h1 {
      font-size: 1.35rem;
      font-weight: 800;
      color: var(--green);
      letter-spacing: -.3px;
    }

    .login-header p {
      font-size: .8rem;
      color: #9ca3af;
      margin-top: .3rem;
    }

    .form-group {
      margin-bottom: 1.1rem;
    }

    label {
      display: block;
      font-size: .76rem;
      font-weight: 700;
      color: #374151;
      margin-bottom: .4rem;
      letter-spacing: .2px;
    }

    .input-wrap {
      position: relative;
    }

    .input-wrap i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: .85rem;
    }

    input {
      width: 100%;
      padding: .7rem .75rem .7rem 2.5rem;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      font-size: .9rem;
      outline: none;
      transition: .2s;
      font-family: 'Inter', sans-serif;
    }

    input:focus {
      border-color: var(--green-mid);
      box-shadow: 0 0 0 3px rgba(26, 107, 60, .1);
    }

    .btn-login {
      width: 100%;
      padding: .85rem;
      background: var(--green);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: .9rem;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      transition: .2s;
      font-family: 'Inter', sans-serif;
      letter-spacing: .2px;
    }

    .btn-login:hover {
      background: var(--green-mid);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(11, 61, 46, .3);
    }

    /* Kotak pesan error login */
    .error {
      background: #fef2f2;
      color: #dc2626;
      padding: .75rem 1rem;
      border-radius: 8px;
      font-size: .8rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: .5rem;
      border: 1px solid #fecaca;
    }

    .back-link {
      text-align: center;
      margin-top: 1.25rem;
    }

    .back-link a {
      color: rgba(255, 255, 255, .7);
      font-size: .8rem;
      text-decoration: none;
      transition: .2s;
    }

    .back-link a:hover {
      color: #fff;
    }

    /* Info kredensial default — untuk kemudahan pengembangan */
    .login-info {
      margin-top: 1rem;
      padding: .75rem 1rem;
      background: #f9fafb;
      border-radius: 8px;
      font-size: .75rem;
      color: #6b7280;
      text-align: center;
    }

    .login-info code {
      background: #e5e7eb;
      padding: .1rem .4rem;
      border-radius: 4px;
      font-size: .72rem;
    }
  </style>
</head>

<body>
  <div class="login-wrap">
    <div class="login-box">

      <div class="login-header">
        <div class="login-logo">M1B</div>
        <h1>Selamat Datang</h1>
        <p>Panel Admin MAN 1 Bangka</p>
      </div>

      <!-- Tampilkan pesan lockout atau error login -->
      <?php if ($isLocked): ?>
        <div class="error" style="background:#fef3c7;color:#92400e;border-color:#fcd34d;">
          <i class="fas fa-lock"></i>
          Akun terkunci. Coba lagi dalam
          <strong><?= ceil($lockRemaining / 60) ?> menit</strong>.
        </div>
      <?php elseif ($error): ?>
        <div class="error">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <!-- CSRF token: melindungi form dari serangan Cross-Site Request Forgery -->
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
        <div class="form-group">
          <label>USERNAME</label>
          <div class="input-wrap">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Masukkan username"
                   required autocomplete="username" <?= $isLocked ? 'disabled' : '' ?> />
          </div>
        </div>
        <div class="form-group">
          <label>PASSWORD</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Masukkan password"
                   required autocomplete="current-password" <?= $isLocked ? 'disabled' : '' ?> />
          </div>
        </div>
        <button type="submit" class="btn-login" <?= $isLocked ? 'disabled style="opacity:.5;cursor:not-allowed;"' : '' ?>>
          <i class="fas <?= $isLocked ? 'fa-lock' : 'fa-sign-in-alt' ?>"></i>
          <?= $isLocked ? 'Akun Terkunci' : 'Masuk ke Dashboard' ?>
        </button>
      </form>

      <!-- Password diubah? Lihat instruksi di komentar login.php baris atas. -->

    </div>
    <div class="back-link">
      <a href="../index.html"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
    </div>
  </div>
</body>

</html>