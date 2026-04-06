<?php
// ============================================================
// login.php — Halaman Login Panel Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Menampilkan form login dan memproses autentikasi admin.
// Jika sudah login, langsung redirect ke dashboard (index.php).
// Kredensial default: admin / man1bangka2026
// ============================================================

session_start(); // Mulai sesi untuk menyimpan status login

// Jika sudah login, langsung ke dashboard — tidak perlu login lagi
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = ''; // Variabel untuk menyimpan pesan error login

// Proses form login saat method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Validasi kredensial hardcoded (cocok untuk proyek akademik sederhana)
    // Untuk produksi: gunakan database + password_hash() / password_verify()
    if ($user === 'admin' && $pass === 'man1bangka2026') {
        $_SESSION['admin_logged_in'] = true;  // Tandai sebagai sudah login
        $_SESSION['admin_user']      = $user; // Simpan nama user untuk ditampilkan
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah!'; // Pesan error ditampilkan di form
    }
}
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

    * { box-sizing: border-box; margin: 0; padding: 0; }

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

    .login-wrap { width: 100%; max-width: 400px; padding: 1rem; }

    .login-box {
      background: #fff;
      border-radius: 20px;
      padding: 2.5rem 2.25rem;
      box-shadow: 0 25px 50px rgba(0, 0, 0, .25);
    }

    .login-header { text-align: center; margin-bottom: 2rem; }

    .login-logo {
      width: 72px; height: 72px;
      background: linear-gradient(135deg, var(--green), var(--green-mid));
      border-radius: 18px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.25rem;
      font-size: 1.4rem; font-weight: 800;
      color: var(--gold);
      box-shadow: 0 8px 24px rgba(11, 61, 46, .3);
      letter-spacing: -1px;
    }

    .login-header h1 { font-size: 1.35rem; font-weight: 800; color: var(--green); letter-spacing: -.3px; }
    .login-header p  { font-size: .8rem; color: #9ca3af; margin-top: .3rem; }

    .form-group { margin-bottom: 1.1rem; }

    label {
      display: block;
      font-size: .76rem; font-weight: 700;
      color: #374151;
      margin-bottom: .4rem;
      letter-spacing: .2px;
    }

    .input-wrap { position: relative; }

    .input-wrap i {
      position: absolute; left: 12px; top: 50%;
      transform: translateY(-50%);
      color: #9ca3af; font-size: .85rem;
    }

    input {
      width: 100%;
      padding: .7rem .75rem .7rem 2.5rem;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      font-size: .9rem; outline: none;
      transition: .2s;
      font-family: 'Inter', sans-serif;
    }

    input:focus {
      border-color: var(--green-mid);
      box-shadow: 0 0 0 3px rgba(26, 107, 60, .1);
    }

    .btn-login {
      width: 100%; padding: .85rem;
      background: var(--green); color: #fff;
      border: none; border-radius: 10px;
      font-size: .9rem; font-weight: 700;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
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
      background: #fef2f2; color: #dc2626;
      padding: .75rem 1rem;
      border-radius: 8px; font-size: .8rem;
      margin-bottom: 1rem;
      display: flex; align-items: center; gap: .5rem;
      border: 1px solid #fecaca;
    }

    .back-link { text-align: center; margin-top: 1.25rem; }

    .back-link a {
      color: rgba(255, 255, 255, .7);
      font-size: .8rem; text-decoration: none; transition: .2s;
    }

    .back-link a:hover { color: #fff; }

    /* Info kredensial default — untuk kemudahan pengembangan */
    .login-info {
      margin-top: 1rem; padding: .75rem 1rem;
      background: #f9fafb; border-radius: 8px;
      font-size: .75rem; color: #6b7280; text-align: center;
    }

    .login-info code {
      background: #e5e7eb; padding: .1rem .4rem;
      border-radius: 4px; font-size: .72rem;
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

      <!-- Tampilkan pesan error jika login gagal -->
      <?php if ($error): ?>
        <div class="error">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <div class="form-group">
          <label>USERNAME</label>
          <div class="input-wrap">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Masukkan username" required autocomplete="username" />
          </div>
        </div>
        <div class="form-group">
          <label>PASSWORD</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password" />
          </div>
        </div>
        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt"></i> Masuk ke Dashboard
        </button>
      </form>

      <!-- Informasi kredensial dihapus dari UI untuk keamanan produksi.
           Ganti password di admin/login.php sebelum deploy. -->

    </div>
    <div class="back-link">
      <a href="../index.html"><i class="fas fa-arrow-left"></i> Kembali ke Website</a>
    </div>
  </div>
</body>

</html>
