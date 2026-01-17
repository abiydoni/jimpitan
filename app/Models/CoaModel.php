<?php

namespace App\Models;

use CodeIgniter\Model;

class CoaModel extends Model
{
    protected $table            = 'tb_coa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['code', 'name', 'jenis', 'status', 'date_update'];
}
