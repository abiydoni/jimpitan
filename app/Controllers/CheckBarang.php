<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class CheckBarang extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $query = $db->query("DESCRIBE tb_barang");
        $rows = $query->getResultArray();
        $fields = array_column($rows, 'Field');
        echo implode(', ', $fields);
        exit;
    }
}
