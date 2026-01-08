<?php
require 'public/index.php'; // This might not work in CLI without modifications.
// Better use spark evaluate if available, but let's try this.
$db = \Config\Database::connect();
$roles = $db->table('tb_role')->get()->getResultArray();
print_r($roles);
