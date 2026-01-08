<?php
$db = \Config\Database::connect();
echo "USERS TABLE:\n";
$usersFields = $db->query("DESCRIBE users")->getResultArray();
print_r($usersFields);

echo "\nTB_ROLE TABLE:\n";
$roleFields = $db->query("DESCRIBE tb_role")->getResultArray();
print_r($roleFields);
