<?php
$db = \Config\Database::connect();
echo "TB_TARIF TABLE:\n";
$fields = $db->query("DESCRIBE tb_tarif")->getResultArray();
foreach ($fields as $field) {
    echo $field['Field'] . " - " . $field['Type'] . "\n";
}

echo "\nTB_IURAN TABLE:\n";
$fields = $db->query("DESCRIBE tb_iuran")->getResultArray();
foreach ($fields as $field) {
    echo $field['Field'] . " - " . $field['Type'] . "\n";
}
