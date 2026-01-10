<?php
namespace App\Controllers;
class DebugWarga extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT nikk, nama, hubungan FROM tb_warga LIMIT 50");
        echo "<pre>";
        print_r($query->getResultArray());
        echo "</pre>";
        
        echo "<h2>Distinct NIKK Count</h2>";
        $count = $db->query("SELECT COUNT(DISTINCT nikk) as c FROM tb_warga WHERE nikk != ''")->getRow()->c;
        echo $count;
    }
}
