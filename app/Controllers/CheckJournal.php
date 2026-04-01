<?php
namespace App\Controllers;
use CodeIgniter\Controller;

class CheckJournal extends Controller {
    public function index() {
        $db = \Config\Database::connect();
        $res = $db->table('kas_sub')
                  ->where('sub_reff', 'RT0700001')
                  ->get()->getResultArray();
        echo "<pre>";
        print_r($res);
        echo "</pre>";
    }
}
