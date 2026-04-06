<?php
// ============================================================
// auth.php — Middleware Autentikasi Panel Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// File ini di-require di bagian paling atas setiap halaman admin.
// Fungsinya: memastikan hanya pengguna yang sudah login yang
// dapat mengakses halaman admin.
//
// Cara kerja:
//   1. Mulai sesi PHP (session_start)
//   2. Cek apakah session 'admin_logged_in' bernilai true
//   3. Jika TIDAK → redirect ke login.php dan hentikan eksekusi
//   4. Jika YA   → definisikan ADMIN_USER dan lanjutkan halaman
//
// Penggunaan: letakkan `require 'auth.php';` sebagai baris pertama
// setiap file PHP di folder admin/.
// ============================================================

session_start(); // Mulai/lanjutkan sesi PHP

// Jika belum login, redirect ke halaman login dan hentikan eksekusi
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit; // Wajib setelah header redirect agar kode di bawah tidak dijalankan
}

// Definisikan ADMIN_USER sebagai konstanta agar bisa dipakai di semua halaman admin
// contoh: `echo ADMIN_USER` pada topbar dashboard
define('ADMIN_USER', $_SESSION['admin_user']);
// $pdo tersedia setelah `require '../php/config.php'` di masing-masing halaman admin
