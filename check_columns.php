<?php
$db = \Config\Database::connect();
$fields = $db->getFieldNames('tb_role');
echo json_encode($fields);
