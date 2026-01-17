<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table            = 'tb_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'username', 'role', 'action', 'description', 'ip_address', 'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // We handle created_at manually in helper, or enable to true
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';
}
