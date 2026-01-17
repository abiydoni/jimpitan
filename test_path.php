<?php
// Letakkan file ini di folder root (sejajar dengan index.php)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

echo "<h2>Debug Paths & Permissions</h2>";
echo "<b>FCPATH (Root Folder):</b> " . FCPATH . "<br>";

$targetDir = FCPATH . 'img/warga';
echo "<b>Target Folder:</b> " . $targetDir . "<br>";

// Chek Exists
if (file_exists($targetDir)) {
    echo "Status Folder: <span style='color:green'>ADA (Exists)</span><br>";
} else {
    echo "Status Folder: <span style='color:red'>TIDAK ADA (Not Found)</span> - Mohon buat folder 'img' lalu di dalamnya 'warga'<br>";
}

// Check Writable
if (is_writable($targetDir)) {
    echo "Izin Tulis (Writable): <span style='color:green'>YA (Bisa Upload)</span><br>";
} else {
    echo "Izin Tulis (Writable): <span style='color:red'>TIDAK (Permission Denied)</span> - Mohon ubah permission folder menjadi 755 atau 777<br>";
}

// Test Write
$testFile = $targetDir . '/test_debug.txt';
echo "<br><b>Mencoba membuat file test...</b><br>";
if (@file_put_contents($testFile, 'Tes tulis file berhasil.')) {
    echo "<span style='color:green'>BERHASIL membuat file test di: $testFile</span><br>";
    echo "Cek via browser file ini: <a href='/img/warga/test_debug.txt'>/img/warga/test_debug.txt</a><br>";
    // unlink($testFile); // Jangan hapus dulu biar user bisa cek
} else {
    echo "<span style='color:red'>GAGAL membuat file. Upload pasti error.</span><br>";
    $error = error_get_last();
    if ($error) echo "Error detail: " . $error['message'];
}

echo "<hr>";
echo "<h3>Daftar File di img/warga:</h3>";
if (is_dir($targetDir)) {
    $files = scandir($targetDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file <br>";
        }
    }
}
