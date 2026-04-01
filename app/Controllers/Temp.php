<?php
namespace App\Controllers;
use CodeIgniter\Controller;

class Temp extends Controller {
    public function index() {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('tb_iuran');
        print_r($fields);
    }
}
