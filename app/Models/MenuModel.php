<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'tb_menu';
    protected $primaryKey       = 'kode';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama', 'alamat_url', 'ikon', 'status', 'role_access'
    ];

    // Dates
    protected $useTimestamps = false;
}
