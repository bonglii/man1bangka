<?php
// ============================================================
// system-check.php — Halaman Unlock Sistem Admin (nama disamarkan)
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// File ini muncul saat sistem terkunci.
// User harus memasukkan MASTER UNLOCK PASSWORD untuk membuka kembali.
//
// CATATAN KEAMANAN:
//   - Nama file disamarkan menjadi "system-check.php" (bukan "unlock.php")
//     agar orang awam tidak langsung mengenali fungsinya.
//   - File flag penanda lock disamarkan menjadi ".htcache_db" agar
//     tidak mudah ditebak nama file yang perlu dihapus untuk unlock.
//
// CARA UNLOCK MANUAL (darurat / lupa password):
//   Hapus file bernama ".htcache_db" di folder admin/ lewat File Explorer.
//   (File ini tersembunyi; aktifkan "Show hidden files" di Explorer).
//
// CARA GANTI MASTER UNLOCK PASSWORD:
//   Ubah nilai konstanta UNLOCK_PASS di bawah.
// ============================================================

session_start();

define('LOCK_FILE',    __DIR__ . '/.htcache_db');
define('UNLOCK_PASS',  'kunciau'); // Ganti sesuai kebutuhan

// Jika sistem tidak terkunci, tidak perlu halaman ini
if (!file_exists(LOCK_FILE)) {
  header('Location: login.php');
  exit;
}

// Ambil info kapan terkunci (dari isi file flag)
$lockInfo = @json_decode(file_get_contents(LOCK_FILE), true) ?: [];
$lockedAt = $lockInfo['locked_at'] ?? 'tidak diketahui';
$lockIp   = $lockInfo['ip']        ?? 'tidak diketahui';

$error   = '';
$success = false;

// ------------------------------------------------------------
// PROSES UNLOCK
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = $_POST['unlock_pass'] ?? '';

  if (hash_equals(UNLOCK_PASS, $input)) {
    // Password benar — hapus flag, reset session lockout
    @unlink(LOCK_FILE);
    $_SESSION['login_attempts']     = 0;
    $_SESSION['login_last_attempt'] = 0;
    unset($_SESSION['login_csrf']);
    $success = true;
  } else {
    $error = 'Password unlock salah!';
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Sistem Terkunci — MAN 1 Bangka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
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
      background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 60%, #450a0a 100%);
    }

    .unlock-wrap {
      width: 100%;
      max-width: 460px;
      padding: 1rem;
    }

    .unlock-box {
      background: #fff;
      border-radius: 20px;
      padding: 2.5rem 2.25rem;
      box-shadow: 0 25px 50px rgba(0, 0, 0, .4);
      border-top: 6px solid #dc2626;
    }

    .lock-icon {
      width: 80px;
      height: 80px;
      background: #fee2e2;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
      font-size: 2rem;
      color: #dc2626;
    }

    h1 {
      text-align: center;
      font-size: 1.35rem;
      font-weight: 800;
      color: #991b1b;
      letter-spacing: -.3px;
      margin-bottom: .4rem;
    }

    .subtitle {
      text-align: center;
      font-size: .85rem;
      color: #6b7280;
      margin-bottom: 1.5rem;
    }

    .info-box {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 10px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      font-size: .8rem;
      color: #7f1d1d;
      line-height: 1.7;
    }

    .info-box strong {
      color: #450a0a;
    }

    .info-box .row {
      display: flex;
      justify-content: space-between;
      padding: .2rem 0;
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
      padding: .75rem .75rem .75rem 2.5rem;
      border: 1.5px solid #e5e7eb;
      border-radius: 10px;
      font-size: .9rem;
      outline: none;
      transition: .2s;
      font-family: 'Inter', sans-serif;
    }

    input:focus {
      border-color: #dc2626;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, .1);
    }

    .btn-unlock {
      width: 100%;
      padding: .85rem;
      background: #dc2626;
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
    }

    .btn-unlock:hover {
      background: #991b1b;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(220, 38, 38, .3);
    }

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

    .success {
      background: #dcfce7;
      color: #166534;
      padding: 1rem;
      border-radius: 8px;
      font-size: .85rem;
      text-align: center;
      border: 1px solid #86efac;
    }

    .success a {
      display: inline-block;
      margin-top: .75rem;
      background: #16a34a;
      color: #fff;
      padding: .5rem 1.2rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
    }

    .help {
      margin-top: 1.25rem;
      padding-top: 1.25rem;
      border-top: 1px dashed #e5e7eb;
      font-size: .75rem;
      color: #6b7280;
      line-height: 1.6;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="unlock-wrap">
    <div class="unlock-box">

      <div class="lock-icon"><i class="fas fa-lock"></i></div>
      <h1>Sistem Terkunci</h1>
      <p class="subtitle">Seluruh panel admin MAN 1 Bangka tidak dapat diakses</p>

      <?php if ($success): ?>
        <div class="success">
          <i class="fas fa-check-circle" style="font-size:1.5rem;color:#16a34a;"></i>
          <p style="margin-top:.5rem;"><strong>Sistem berhasil dibuka!</strong></p>
          <p style="margin-top:.25rem;font-size:.8rem;">Anda sekarang dapat kembali login seperti biasa.</p>
          <a href="login.php"><i class="fas fa-sign-in-alt"></i> Lanjut ke Login</a>
        </div>
      <?php else: ?>

        <div class="info-box">
          <div class="row"><span>Terkunci pada:</span><strong><?= htmlspecialchars($lockedAt) ?></strong></div>
          <div class="row"><span>IP terakhir:</span><strong><?= htmlspecialchars($lockIp) ?></strong></div>
          <div class="row"><span>Penyebab:</span><strong>3x gagal login</strong></div>
        </div>

        <?php if ($error): ?>
          <div class="error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
          <div class="form-group">
            <label>MASTER UNLOCK PASSWORD</label>
            <div class="input-wrap">
              <i class="fas fa-key"></i>
              <input type="password" name="unlock_pass" placeholder="Masukkan password unlock" required autofocus />
            </div>
          </div>
          <button type="submit" class="btn-unlock">
            <i class="fas fa-unlock"></i>
            Buka Kunci Sistem
          </button>
        </form>

        <div class="help">
          <strong>Butuh bantuan?</strong><br>
          Hubungi administrator sistem untuk mendapatkan password unlock.
        </div>

      <?php endif; ?>

    </div>
  </div>
</body>

</html>