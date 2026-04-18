<?php
// ============================================================
// auth.php — Middleware Autentikasi Panel Admin
// MAN 1 Bangka | Dibuat oleh: Estefania - 2322500043 ISB Atma Luhur
// ============================================================
// File ini di-require di bagian paling atas setiap halaman admin.
// Fungsinya:
//   1. Cek system lock — kalau terkunci redirect ke system-check.php
//   2. Mulai sesi PHP
//   3. Cek apakah user sudah login
//   4. Definisikan konstanta ADMIN_USER untuk dipakai di halaman admin
//
// Penggunaan: letakkan `require 'auth.php';` sebagai baris pertama
// setiap file PHP di folder admin/.
// ============================================================

// ------------------------------------------------------------
// SYSTEM LOCK — Cek apakah seluruh sistem admin terkunci.
// Nama file flag disamarkan (prefix .ht*) agar tidak mudah dikenali
// dan mirip file konfigurasi Apache internal.
// ------------------------------------------------------------
define('LOCK_FILE', __DIR__ . '/.htcache_db');

if (file_exists(LOCK_FILE)) {
    // Hindari infinite loop: system-check.php tidak memerlukan auth.php
    header('Location: system-check.php');
    exit;
}

session_start(); // Mulai/lanjutkan sesi PHP

// Jika belum login (atau nilai session falsy), redirect ke halaman login
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit; // Wajib setelah header redirect agar kode di bawah tidak dijalankan
}

// Definisikan ADMIN_USER sebagai konstanta agar bisa dipakai di semua halaman admin
// contoh: `echo ADMIN_USER` pada topbar dashboard
define('ADMIN_USER', $_SESSION['admin_user']);
// $pdo tersedia setelah `require '../php/config.php'` di masing-masing halaman admin
