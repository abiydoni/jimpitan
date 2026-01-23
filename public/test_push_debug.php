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

// Bypass PushService for RAW result
echo "--- Menjalankan Test Kirim (Raw Mode) ---<br>";

$accessToken = $pushService->getFCMAccessToken();
echo "Access Token: " . ($accessToken ? "<span style='color:green'>Generated</span>" : "<span style='color:red'>Failed (Check JSON Key)</span>") . "<br>";

if ($accessToken) {
    foreach ($subs as $sub) {
        $token = $sub['fcm_token'];
        echo "Mengirim ke Token: " . substr($token, 0, 15) . "...<br>";
        
        $fcmUrl = 'https://fcm.googleapis.com/v1/projects/jimpitan-app-a7by777/messages:send';
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => ['title' => 'Test Debug', 'body' => 'Bismillah bunyi!'],
                'data' => ['url' => '/chat']
            ]
        ];

        $ch = curl_init($fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "HTTP Code: $httpCode<br>";
        echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";
    }
}
