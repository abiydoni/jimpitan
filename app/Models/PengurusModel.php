<?php

namespace App\Models;

use CodeIgniter\Model;

class PengurusModel extends Model
{
    protected $table            = 'tb_pengurus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama_pengurus', 'kode_tarif'];
}
