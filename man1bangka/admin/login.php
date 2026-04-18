<?php
// ============================================================
// login.php — Halaman Login Panel Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// KEAMANAN:
//   1. Dilindungi CSRF token — form POST tanpa token valid = ditolak
//   2. Brute-force protection: maks 3 gagal login per 15 menit
//   3. hash_equals() mencegah timing-attack saat bandingkan password
//
// CARA GANTI PASSWORD:
//   Ubah nilai konstanta ADMIN_PASS di bawah.
// ============================================================

session_start();

// ------------------------------------------------------------
// SYSTEM LOCK — Cek apakah sistem dalam mode terkunci
// File tersembunyi penanda lock; namanya disamarkan agar tidak
// mudah dikenali orang luar sebagai file kontrol.
// ------------------------------------------------------------
define('LOCK_FILE', __DIR__ . '/.htcache_db');

if (file_exists(LOCK_FILE)) {
  header('Location: system-check.php');
  exit;
}

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['admin_logged_in'])) {
  header('Location: index.php');
  exit;
}

// ------------------------------------------------------------
// BYPASS LOCKOUT — DINONAKTIFKAN (production-safe)
// Uncomment blok di bawah HANYA saat development / recovery.
// Akses: login.php?reset_lock=1  → menghapus counter gagal login.
// JANGAN aktifkan di server produksi — memungkinkan bypass brute-force!
// ------------------------------------------------------------
/*
if (isset($_GET['reset_lock'])) {
  $_SESSION['login_attempts']     = 0;
  $_SESSION['login_last_attempt'] = 0;
  unset($_SESSION['login_csrf']);
  header('Location: login.php');
  exit;
}
*/

// ------------------------------------------------------------
// KONFIGURASI KREDENSIAL
// ⚠️ Password disimpan plaintext — mudah dibaca kalau file bocor.
// Untuk keamanan lebih baik, gunakan bcrypt (password_hash + password_verify).
// ------------------------------------------------------------
define('ADMIN_USER_NAME',  'admin');
define('ADMIN_PASS',       'man1bangka2026');

// ------------------------------------------------------------
// BRUTE-FORCE PROTECTION
// Simpan hitungan gagal login & timestamp di session.
// Maks 3 percobaan dalam 15 menit → lockout.
// ------------------------------------------------------------
define('MAX_ATTEMPTS',   3);
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

  // --- Cek kredensial: username + password (plaintext, pakai hash_equals untuk mencegah timing attack) ---
  if ($user === ADMIN_USER_NAME && hash_equals(ADMIN_PASS, $pass)) {
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
      // === LOCKOUT TERPICU ===
      // Buat file flag agar seluruh folder admin terkunci sampai unlock manual
      file_put_contents(LOCK_FILE, json_encode([
        'locked_at' => date('Y-m-d H:i:s'),
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'reason'    => 'Terlalu banyak percobaan login gagal'
      ], JSON_PRETTY_PRINT));

      // Redirect ke halaman unlock (nama disamarkan sebagai system-check)
      header('Location: system-check.php');
      exit;
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
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Login Admin — MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root {
      --green-deep: #0a3a2a;
      --green: #0f5132;
      --green-mid: #1a6b3c;
      --green-light: #2d8a56;
      --gold: #c9a84c;
      --gold-light: #e4c977;
      --gold-dark: #8a7132;
      --cream: #faf7f0;
      --cream-dark: #f0ebd8;
      --ink: #0b1f17;
      --muted: #6b7968;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body { height: 100%; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: var(--ink);
      background: var(--cream);
      overflow: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* ==================== SPLIT LAYOUT ==================== */
    .login-shell {
      display: grid;
      grid-template-columns: 1.15fr 1fr;
      min-height: 100vh;
      position: relative;
    }

    /* ==================== LEFT PANEL: BRAND ==================== */
    .brand-panel {
      position: relative;
      background:
        radial-gradient(ellipse at 30% 20%, rgba(201, 168, 76, .18) 0%, transparent 55%),
        radial-gradient(ellipse at 70% 85%, rgba(45, 138, 86, .25) 0%, transparent 50%),
        linear-gradient(145deg, var(--green-deep) 0%, var(--green) 45%, var(--green-mid) 100%);
      padding: 3.5rem 4rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: var(--cream);
      overflow: hidden;
    }

    /* Islamic geometric pattern overlay */
    .brand-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        radial-gradient(circle at 1px 1px, rgba(201, 168, 76, .08) 1px, transparent 0);
      background-size: 24px 24px;
      opacity: .7;
      pointer-events: none;
    }

    /* Decorative arabesque SVG */
    .ornament {
      position: absolute;
      pointer-events: none;
      opacity: .12;
      color: var(--gold);
    }
    .ornament-top    { top: -80px;   right: -80px;  width: 360px; height: 360px; animation: spin 80s linear infinite; }
    .ornament-bottom { bottom: -120px; left: -100px; width: 420px; height: 420px; animation: spin 100s linear infinite reverse; }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* Top-left: logo mark */
    .brand-head {
      position: relative;
      z-index: 2;
      display: flex;
      align-items: center;
      gap: 1rem;
      opacity: 0;
      animation: slideUp .7s .1s ease-out forwards;
    }

    .brand-mark {
      width: 58px;
      height: 58px;
      background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Playfair Display', serif;
      font-weight: 800;
      font-size: 1.3rem;
      color: var(--green-deep);
      letter-spacing: -1px;
      box-shadow:
        0 10px 30px rgba(0, 0, 0, .3),
        inset 0 1px 0 rgba(255, 255, 255, .3);
    }

    .brand-head-text span {
      display: block;
      font-family: 'JetBrains Mono', monospace;
      font-size: .68rem;
      letter-spacing: .25em;
      color: var(--gold-light);
      text-transform: uppercase;
      margin-bottom: 2px;
    }
    .brand-head-text strong {
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--cream);
      letter-spacing: .3px;
    }

    /* Center: headline */
    .brand-hero {
      position: relative;
      z-index: 2;
      max-width: 480px;
      opacity: 0;
      animation: slideUp .7s .3s ease-out forwards;
    }

    .eyebrow {
      font-family: 'JetBrains Mono', monospace;
      font-size: .7rem;
      letter-spacing: .3em;
      color: var(--gold-light);
      text-transform: uppercase;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: .75rem;
    }
    .eyebrow::before {
      content: '';
      width: 32px;
      height: 1px;
      background: var(--gold);
    }

    .brand-hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.3rem, 3.8vw, 3.2rem);
      font-weight: 600;
      line-height: 1.08;
      letter-spacing: -1.5px;
      margin-bottom: 1.25rem;
      color: var(--cream);
    }
    .brand-hero h1 em {
      font-style: italic;
      font-weight: 500;
      color: var(--gold-light);
    }

    .brand-hero p {
      font-size: .95rem;
      line-height: 1.7;
      color: rgba(250, 247, 240, .75);
      max-width: 420px;
    }

    /* Bottom: meta info */
    .brand-foot {
      position: relative;
      z-index: 2;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      opacity: 0;
      animation: slideUp .7s .5s ease-out forwards;
    }

    .brand-foot .quote {
      font-family: 'Playfair Display', serif;
      font-style: italic;
      font-size: .92rem;
      color: rgba(250, 247, 240, .65);
      max-width: 300px;
      line-height: 1.5;
      border-left: 2px solid var(--gold);
      padding-left: 1rem;
    }
    .brand-foot .quote small {
      display: block;
      font-family: 'JetBrains Mono', monospace;
      font-style: normal;
      font-size: .65rem;
      letter-spacing: .2em;
      color: var(--gold);
      margin-top: .5rem;
      text-transform: uppercase;
    }

    .brand-foot .meta {
      font-family: 'JetBrains Mono', monospace;
      font-size: .7rem;
      color: rgba(250, 247, 240, .4);
      letter-spacing: .15em;
      text-align: right;
    }
    .brand-foot .meta span {
      display: block;
      margin-top: .3rem;
      color: var(--gold-light);
    }

    /* ==================== RIGHT PANEL: FORM ==================== */
    .form-panel {
      position: relative;
      background: var(--cream);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      overflow-y: auto;
    }

    /* subtle texture on cream */
    .form-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        radial-gradient(circle at 2px 2px, rgba(11, 61, 46, .04) 1px, transparent 0);
      background-size: 32px 32px;
      pointer-events: none;
    }

    .form-card {
      position: relative;
      width: 100%;
      max-width: 420px;
      opacity: 0;
      animation: slideUp .7s .2s ease-out forwards;
    }

    /* Form label at top */
    .form-label {
      font-family: 'JetBrains Mono', monospace;
      font-size: .68rem;
      letter-spacing: .3em;
      color: var(--green-mid);
      text-transform: uppercase;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: .75rem;
    }
    .form-label::before,
    .form-label::after {
      content: '';
      flex: 1;
      height: 1px;
      background: linear-gradient(to right, transparent, rgba(11, 61, 46, .2), transparent);
    }

    .form-title {
      font-family: 'Playfair Display', serif;
      font-size: 2.1rem;
      font-weight: 600;
      letter-spacing: -.8px;
      color: var(--green-deep);
      text-align: center;
      margin-bottom: .5rem;
      line-height: 1.1;
    }
    .form-title em {
      font-style: italic;
      color: var(--gold-dark);
    }

    .form-sub {
      text-align: center;
      font-size: .88rem;
      color: var(--muted);
      margin-bottom: 2.25rem;
    }

    /* Alert box */
    .alert {
      position: relative;
      padding: 1rem 1.1rem 1rem 3rem;
      border-radius: 14px;
      font-size: .85rem;
      margin-bottom: 1.5rem;
      line-height: 1.5;
      animation: popIn .4s ease-out;
    }
    .alert-error {
      background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
      color: #991b1b;
      border: 1px solid #fecaca;
    }
    .alert-warn {
      background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
      color: #92400e;
      border: 1px solid #fde68a;
    }
    .alert i {
      position: absolute;
      left: 1.1rem;
      top: 1.1rem;
      font-size: 1.1rem;
    }
    .alert strong { font-weight: 700; }

    @keyframes popIn {
      from { opacity: 0; transform: translateY(-6px) scale(.98); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Form fields */
    .field {
      margin-bottom: 1.15rem;
    }

    .field label {
      display: block;
      font-family: 'JetBrains Mono', monospace;
      font-size: .68rem;
      font-weight: 500;
      color: var(--green-mid);
      letter-spacing: .25em;
      text-transform: uppercase;
      margin-bottom: .55rem;
    }

    .input-wrap {
      position: relative;
      background: #fff;
      border-radius: 14px;
      border: 1.5px solid #e5e7eb;
      transition: all .25s ease;
    }
    .input-wrap:focus-within {
      border-color: var(--green-mid);
      box-shadow:
        0 0 0 4px rgba(26, 107, 60, .1),
        0 8px 20px -8px rgba(11, 61, 46, .15);
      transform: translateY(-1px);
    }

    .input-wrap .icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: .95rem;
      transition: color .25s;
    }
    .input-wrap:focus-within .icon { color: var(--green-mid); }

    .input-wrap input {
      width: 100%;
      padding: 1rem 1rem 1rem 3rem;
      border: none;
      background: transparent;
      font-size: .95rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 500;
      color: var(--ink);
      outline: none;
      letter-spacing: .2px;
      border-radius: 14px;
    }
    .input-wrap input::placeholder { color: #9ca3af; font-weight: 400; }
    .input-wrap input:disabled { background: #f3f4f6; cursor: not-allowed; }

    .toggle-pass {
      position: absolute;
      right: .5rem;
      top: 50%;
      transform: translateY(-50%);
      width: 38px;
      height: 38px;
      border: none;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      border-radius: 10px;
      transition: all .2s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .toggle-pass:hover { background: #f3f4f6; color: var(--green-mid); }

    /* Submit button */
    .btn {
      width: 100%;
      padding: 1.05rem;
      border: none;
      border-radius: 14px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 700;
      font-size: .92rem;
      letter-spacing: .4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .6rem;
      position: relative;
      overflow: hidden;
      margin-top: .5rem;
      transition: all .3s cubic-bezier(.4, 0, .2, 1);
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--green) 0%, var(--green-mid) 100%);
      color: var(--cream);
      box-shadow:
        0 10px 25px -10px rgba(11, 61, 46, .5),
        inset 0 1px 0 rgba(255, 255, 255, .15);
    }
    .btn-primary::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, transparent 0%, rgba(201, 168, 76, .25) 50%, transparent 100%);
      transform: translateX(-100%);
      transition: transform .6s;
    }
    .btn-primary:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow:
        0 18px 35px -12px rgba(11, 61, 46, .6),
        inset 0 1px 0 rgba(255, 255, 255, .2);
    }
    .btn-primary:hover:not(:disabled)::before { transform: translateX(100%); }
    .btn-primary:active:not(:disabled) { transform: translateY(0); }
    .btn:disabled {
      background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
      cursor: not-allowed;
      opacity: .75;
    }

    /* Footer link */
    .form-foot {
      margin-top: 2rem;
      padding-top: 1.75rem;
      border-top: 1px dashed #d6d3c7;
      text-align: center;
      font-size: .82rem;
      color: var(--muted);
    }
    .form-foot a {
      color: var(--green-mid);
      font-weight: 600;
      text-decoration: none;
      position: relative;
      transition: color .2s;
    }
    .form-foot a::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: -2px;
      height: 1px;
      background: var(--gold);
      transform: scaleX(0);
      transform-origin: left;
      transition: transform .3s;
    }
    .form-foot a:hover { color: var(--green-deep); }
    .form-foot a:hover::after { transform: scaleX(1); }

    /* Security badges */
    @keyframes slideUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ==================== MOBILE ==================== */
    @media (max-width: 920px) {
      body { overflow: auto; }
      .login-shell { grid-template-columns: 1fr; min-height: auto; }
      .brand-panel {
        padding: 2rem 1.75rem 3rem;
        min-height: auto;
      }
      .brand-hero { margin: 2.5rem 0; }
      .brand-hero h1 { font-size: 2rem; }
      .brand-foot { flex-direction: column; gap: 1.5rem; align-items: flex-start; }
      .brand-foot .meta { text-align: left; }
      .ornament-top    { width: 260px; height: 260px; top: -60px; right: -60px; }
      .ornament-bottom { display: none; }
      .form-panel { padding: 2.5rem 1.5rem; }
    }
  </style>
</head>

<body>
  <div class="login-shell">

    <!-- ========== LEFT: BRAND PANEL ========== -->
    <aside class="brand-panel">

      <!-- Decorative arabesque ornaments -->
      <svg class="ornament ornament-top" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="99" stroke="currentColor" stroke-width=".5"/>
        <circle cx="100" cy="100" r="70" stroke="currentColor" stroke-width=".5"/>
        <circle cx="100" cy="100" r="40" stroke="currentColor" stroke-width=".5"/>
        <g stroke="currentColor" stroke-width=".5" fill="none">
          <path d="M100 1 L100 199 M1 100 L199 100 M29 29 L171 171 M171 29 L29 171"/>
          <path d="M100 30 L130 70 L170 100 L130 130 L100 170 L70 130 L30 100 L70 70 Z"/>
          <path d="M100 50 L120 80 L150 100 L120 120 L100 150 L80 120 L50 100 L80 80 Z"/>
        </g>
      </svg>

      <svg class="ornament ornament-bottom" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g stroke="currentColor" stroke-width=".5" fill="none">
          <circle cx="100" cy="100" r="95"/>
          <circle cx="100" cy="100" r="65"/>
          <circle cx="100" cy="100" r="35"/>
          <path d="M100 5 L115 85 L195 100 L115 115 L100 195 L85 115 L5 100 L85 85 Z"/>
          <path d="M50 50 L100 30 L150 50 L170 100 L150 150 L100 170 L50 150 L30 100 Z"/>
        </g>
      </svg>

      <!-- Top: mark + institution -->
      <div class="brand-head">
        <div class="brand-mark">M1B</div>
        <div class="brand-head-text">
          <span>Madrasah Aliyah Negeri</span>
          <strong>MAN 1 Bangka</strong>
        </div>
      </div>

      <!-- Center: headline -->
      <div class="brand-hero">
        <p class="eyebrow">Panel Administrasi</p>
        <h1>Mengelola<br>hari yang <em>bermakna.</em></h1>
        <p>Sistem manajemen konten untuk pengumuman, agenda, prestasi, dan kegiatan siswa di lingkungan madrasah.</p>
      </div>

      <!-- Bottom: quote + meta -->
      <div class="brand-foot">
        <div class="quote">
          Barangsiapa menempuh suatu jalan untuk mencari ilmu, maka Allah akan memudahkan baginya jalan menuju surga.
          <small>— HR. Muslim</small>
        </div>
        <div class="meta">
          <?= date('d · M · Y') ?>
        </div>
      </div>
    </aside>

    <!-- ========== RIGHT: FORM PANEL ========== -->
    <main class="form-panel">
      <div class="form-card">

        <p class="form-label">Otentikasi Aman</p>
        <h2 class="form-title">Selamat <em>datang</em></h2>
        <p class="form-sub">Masukkan kredensial Anda untuk melanjutkan</p>

        <?php if ($isLocked): ?>
          <div class="alert alert-warn">
            <i class="fas fa-lock"></i>
            <strong>Akun terkunci.</strong> Coba lagi dalam <strong><?= ceil($lockRemaining / 60) ?> menit</strong>.
          </div>
        <?php elseif ($error): ?>
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
          <!-- CSRF token: melindungi form dari serangan Cross-Site Request Forgery -->
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

          <div class="field">
            <label>Username</label>
            <div class="input-wrap">
              <i class="icon fas fa-user"></i>
              <input type="text" name="username" placeholder="Masukkan username Anda"
                     required autocomplete="username" <?= $isLocked ? 'disabled' : '' ?> />
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <div class="input-wrap">
              <i class="icon fas fa-key"></i>
              <input type="password" id="passInput" name="password" placeholder="••••••••••••"
                     required autocomplete="current-password" <?= $isLocked ? 'disabled' : '' ?> />
              <button type="button" class="toggle-pass" onclick="togglePass()" aria-label="Tampilkan password" <?= $isLocked ? 'disabled' : '' ?>>
                <i id="passIcon" class="fas fa-eye"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" <?= $isLocked ? 'disabled' : '' ?>>
            <i class="fas <?= $isLocked ? 'fa-lock' : 'fa-arrow-right-to-bracket' ?>"></i>
            <?= $isLocked ? 'Akun Terkunci' : 'Masuk ke Dashboard' ?>
          </button>
        </form>

        <p class="form-foot">
          <a href="../index.html"><i class="fas fa-arrow-left" style="font-size:.7em;margin-right:.3em;"></i>Kembali ke beranda website</a>
        </p>
      </div>
    </main>

  </div>

  <script>
    // Toggle password visibility
    function togglePass() {
      const input = document.getElementById('passInput');
      const icon = document.getElementById('passIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
      }
    }
  </script>
</body>

</html>