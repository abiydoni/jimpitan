<?php

namespace App\Models;

use CodeIgniter\Model;

class KasUmumModel extends Model
{
    protected $table            = 'kas_umum';
    protected $primaryKey       = 'id_trx';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'date_trx', 'coa_code', 'desc_trx', 'reff', 'debet', 'kredit', 'timestamp'
    ];
}
