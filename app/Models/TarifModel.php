<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifModel extends Model
{
    protected $table            = 'tb_tarif';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_tarif', 'nama_tarif', 'tarif', 'coa_code',
        'metode', 'icon', 'status', 'date_update'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_update';
    protected $updatedField  = 'date_update';
}
