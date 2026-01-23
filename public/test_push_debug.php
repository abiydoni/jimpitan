<?php
/**
 * Script Debugging Notifikasi FCM
 * Letakkan di folder public/ dan akses via browser.
 */

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
require $pathsPath;
$paths = new \Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$session = session();
$userId = $session->get('id_code');

if (!$userId) {
    die("❌ Error: Anda harus login ke aplikasi Jimpitan terlebih dahulu di tab lain.");
}

echo "<h2>FCM Debugging for User: $userId</h2>";

$db = \Config\Database::connect();
$subs = $db->table('fcm_subscriptions')->where('user_id', $userId)->get()->getResultArray();

if (empty($subs)) {
    echo "❌ Error: Tidak ada token terdaftar untuk User ID: $userId di tabel fcm_subscriptions.<br>";
    echo "Silakan buka halaman Chat dan pastikan sudah klik 'Sinkronkan'.";
    exit;
}

echo "✅ Ditemukan " . count($subs) . " perangkat terdaftar.<br><br>";

$pushService = new \App\Libraries\PushService();
echo "--- Menjalankan Test Kirim ---<br>";

// We use the first sub as a direct test
$success = $pushService->sendNotification($userId, "Test Debug Notifikasi " . date('H:i:s'), "DEBUG SYSTEM", "/chat");

if ($success) {
    echo "<h3 style='color:green'>✅ Sukses! Google FCM menerima permintaan pengiriman.</h3>";
    echo "Jika HP tetap tidak bunyi, cek:<br>";
    echo "1. Pengaturan Notifikasi Browser (Muted/Do Not Disturb).<br>";
    echo "2. Service Worker (sw.js) di DevTools Console.<br>";
} else {
    echo "<h3 style='color:red'>❌ Gagal! Cek file logs di writable/logs/ untuk detail kesalahannya.</h3>";
    echo "Biasanya masalah di file JSON Key atau koneksi server ke Google.";
}
