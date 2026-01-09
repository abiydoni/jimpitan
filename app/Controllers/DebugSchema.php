<?php

namespace App\Controllers;

class DebugSchema extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        echo "<h1>Table Check</h1>";
        
        echo "<h2>master_kk Data (Top 5)</h2>";
        $query = $db->query("SELECT * FROM master_kk LIMIT 5");
        echo "<pre>";
        print_r($query->getResultArray());
        echo "</pre>";
        
        die();
    }
}
