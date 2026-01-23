<?php
require 'app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();
$rows = $db->table('fcm_subscriptions')->get()->getResultArray();

header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);
