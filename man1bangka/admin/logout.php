<?php
// ============================================================
// logout.php — Proses Logout Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// Menghancurkan seluruh data sesi dan mengarahkan kembali
// ke halaman login. Dipanggil dari link "Keluar" di sidebar.
// ============================================================

session_start();    // Mulai sesi yang ada agar bisa dihancurkan
session_destroy();  // Hapus semua data sesi (termasuk admin_logged_in)

// Redirect ke halaman login setelah logout
header('Location: login.php');
exit; // Hentikan eksekusi agar tidak ada output setelah redirect
