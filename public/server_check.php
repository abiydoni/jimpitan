<?php
// Script Check Diagnostik Server (Aman)
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Jimpitan Server Diagnostic</h1>";
echo "<pre>";

// 1. Cek OS
echo "OS: " . PHP_OS . " (" . php_uname() . ")\n";
echo "Directory Separator: " . (DIRECTORY_SEPARATOR === '\\' ? 'Backslash (Windows)' : 'Slash (Linux/Unix)') . "\n";

// 2. Cek Root Path
$fcpath = __DIR__;
echo "FCPATH: $fcpath\n";
$rootpath = dirname($fcpath); 
echo "ROOTPATH (Guess): $rootpath\n";

// 3. Cek OpenSSL
echo "\n--- OpenSSL ---\n";
if (extension_loaded('openssl')) {
    echo "Extension: Loaded\n";
    $conf = getenv('OPENSSL_CONF');
    echo "Current OPENSSL_CONF: " . ($conf ? $conf : '(System Default)') . "\n";
    
    // Test OpenSSL Config
    $privKey = openssl_pkey_new([
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);
    if ($privKey === false) {
        echo "Generate Key Test: FAILED. Error: " . openssl_error_string() . "\n";
        echo "<b>DIAGNOSIS:</b> Konfigurasi OpenSSL server bermasalah/tidak valid.\n";
    } else {
        echo "Generate Key Test: SUCCESS. OpenSSL is working fine.\n";
    }
} else {
    echo "Extension: NOT LOADED (CRITICAL)\n";
}

// 4. Cek .env
echo "\n--- Environment (.env) ---\n";
$envPath = $rootpath . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envPath)) {
    echo ".env File: FOUND at $envPath\n";
    
    // Try to read keys manually
    $content = file_get_contents($envPath);
    preg_match('/^VAPID_PUBLIC_KEY=(.*)$/m', $content, $mPub);
    preg_match('/^VAPID_PRIVATE_KEY=(.*)$/m', $content, $mPriv);
    
    $hasPub = !empty($mPub[1]);
    $hasPriv = !empty($mPriv[1]);
    
    echo "VAPID_PUBLIC_KEY in .env: " . ($hasPub ? "FOUND (Length: ".strlen(trim($mPub[1])).")" : "MISSING") . "\n";
    echo "VAPID_PRIVATE_KEY in .env: " . ($hasPriv ? "FOUND (Length: ".strlen(trim($mPriv[1])).")" : "MISSING") . "\n";
    
    // Check getenv
    $envPub = getenv('VAPID_PUBLIC_KEY');
    echo "getenv('VAPID_PUBLIC_KEY'): " . ($envPub ? "LOADED" : "NOT LOADED (Might be normal if not using serve/spark)") . "\n";
    
} else {
    echo ".env File: NOT FOUND at $envPath\n";
}

// 5. Cek File OpenSSL Custom
$customConf = $rootpath . DIRECTORY_SEPARATOR . 'openssl.cnf';
if (file_exists($customConf)) {
    echo "\nCustom openssl.cnf found in root: YES\n";
} else {
    echo "\nCustom openssl.cnf found in root: NO\n";
}

echo "</pre>";
