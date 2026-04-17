<?php
// ============================================================
// notif_count.php — Endpoint notifikasi real-time admin
// Letakkan di: php/notif_count.php
// Dipanggil oleh admin.js setiap 30 detik via fetch().
// ============================================================
session_start(); // Harus sebelum require config.php
require_once __DIR__ . '/config.php';

// Hanya bisa diakses jika sesi admin aktif
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store');

try {
    $pendaftaran = (int)$pdo->query("SELECT COUNT(*) FROM pendaftaran_ekskul WHERE status='menunggu'")->fetchColumn();
    $testimoni   = (int)$pdo->query("SELECT COUNT(*) FROM testimoni WHERE status='nonaktif' AND is_approved=1")->fetchColumn();

    // pesan_kontak mungkin belum ada — gunakan try-catch terpisah
    $pesan = 0;
    try {
        $pesan = (int)$pdo->query("SELECT COUNT(*) FROM pesan_kontak WHERE is_read=0")->fetchColumn();
    } catch (Exception $e) {}

    echo json_encode([
        'pendaftaran' => $pendaftaran,
        'pesan'       => $pesan,
        'testimoni'   => $testimoni,
    ]);
} catch (Exception $e) {
    echo json_encode(['pendaftaran' => 0, 'pesan' => 0, 'testimoni' => 0]);
}
