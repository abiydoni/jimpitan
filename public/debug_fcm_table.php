<?php
// Letakkan di folder public/ agar bisa diakses via URL
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
require $pathsPath;
$paths = new \Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();
$rows = $db->table('fcm_subscriptions')->get()->getResultArray();

header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);
