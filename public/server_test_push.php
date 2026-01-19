<?php
// Script Test Push Logic (Persis PushService.php)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Autoloader (Main CI usage usually, but here we try to find vendor autoload)
// Try to locate vendor/autoload.php
$paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php'
];

$autoloadFound = false;
foreach ($paths as $p) {
    if (file_exists($p)) {
        require_once $p;
        echo "Autoload loaded from: $p<br>";
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    die("CRITICAL: vendor/autoload.php NOT FOUND. Composer dependencies missing.");
}

use Minishlink\WebPush\WebPush;

echo "<h1>Jimpitan Push Service Verification</h1>";
echo "<pre>";

// 1. Simulate Root Path Detection
$fcpath = __DIR__ . '/'; // mimics FCPATH
$rootpath = dirname($fcpath) . '/'; // mimics FCPATH . '../'

echo "Simulated ROOTPATH: $rootpath\n";

// 2. OpenSSL Logic
echo "\n--- OpenSSL Config Logic ---\n";
if (DIRECTORY_SEPARATOR === '\\') {
    echo "Detected Windows. Checking openssl.cnf...\n";
    $conf = $rootpath . 'openssl.cnf';
    if (file_exists($conf)) {
        echo "  Found at $conf. Applying putenv.\n";
        putenv("OPENSSL_CONF=" . $conf);
    } else {
        echo "  NOT FOUND at $conf. (Non-critical if native working)\n";
    }
} else {
    echo "Detected Linux/Unix. Skipping manual openssl.cnf override (Using System Default).\n";
}
echo "Current OPENSSL_CONF env: " . getenv('OPENSSL_CONF') . "\n";


// 3. Key Loading Logic
echo "\n--- Key Loading Logic ---\n";
// mimic helper env() by using getenv first then file parse
$publicKey = getenv('VAPID_PUBLIC_KEY');
$privateKey = getenv('VAPID_PRIVATE_KEY');

if (empty($publicKey)) {
    echo "getenv() returned empty. Trying manual parse of .env\n";
    $envPath = $rootpath . '.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        if (preg_match('/^VAPID_PUBLIC_KEY=(.*)$/m', $envContent, $matches)) {
            $publicKey = trim($matches[1], "\"' \t\n\r\0\x0B");
            echo "  Found Public Key in file.\n";
        }
        if (preg_match('/^VAPID_PRIVATE_KEY=(.*)$/m', $envContent, $matches)) {
            $privateKey = trim($matches[1], "\"' \t\n\r\0\x0B");
            echo "  Found Private Key in file.\n";
        }
    } else {
        echo "  ERROR: .env file not found at $envPath\n";
    }
} else {
    echo "Keys found via getenv().\n";
}

$pkLen = strlen((string)$privateKey);
$pubLen = strlen((string)$publicKey);
echo "Public Key Length: $pubLen\n";
echo "Private Key Length: $pkLen\n";

if ($pkLen < 10 || $pubLen < 10) {
    die("CRITICAL: Keys are invalid/empty.");
}

$privateKey = trim((string)$privateKey, "\"' \t\n\r\0\x0B");
$publicKey  = trim((string)$publicKey, "\"' \t\n\r\0\x0B");

// 4. WebPush Initialization Test
echo "\n--- WebPush Initialization Test ---\n";
try {
    $subject = 'https://jimpitan.appsbee.my.id/';
    
    $auth = [
        'VAPID' => [
            'subject' => $subject,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
        ],
    ];
    
    // Explicitly check if we can create the object
    $webPush = new WebPush($auth);
    
    echo "WebPush Object Created SUCCESSFULLY!\n";
    echo "Default Options: " . json_encode($webPush->getDefaultOptions()) . "\n";
    echo "\n<b>RESULT: Push Logic is VALID on this server.</b>\n";
    
} catch (Exception $e) {
    echo "CRITICAL ERROR during WebPush Init:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
