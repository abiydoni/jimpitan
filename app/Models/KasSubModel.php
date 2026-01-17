<?php

namespace App\Models;

use CodeIgniter\Model;

class KasSubModel extends Model
{
    protected $table            = 'kas_sub';
    protected $primaryKey       = 'id_trx';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false; // Assuming 'timestamp' is auto-updated by DB or we manage it manually. Column name is 'timestamp' not 'created_at'
    // protected $createdField     = 'created_at'; 
    // protected $updatedField     = 'updated_at';
    protected $allowedFields    = [
        'date_trx', 'coa_code', 'desc_trx', 'reff', 'debet', 'kredit', 'timestamp'
    ];
}
