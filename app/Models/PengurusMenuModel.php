<?php

namespace App\Models;

use CodeIgniter\Model;

class PengurusMenuModel extends Model
{
    protected $table            = 'tb_pengurus_menu';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_pengurus', 'kode_menu', 'tipe_akses', 'akses_tarif'];
}
