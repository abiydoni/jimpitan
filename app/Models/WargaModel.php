<?php

namespace App\Models;

use CodeIgniter\Model;

class WargaModel extends Model
{
    protected $table            = 'tb_warga';
    protected $primaryKey       = 'id_warga';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama', 'nik', 'hubungan', 'nikk', 'jenkel', 
        'tpt_lahir', 'tgl_lahir', 'alamat', 'rt', 'rw', 
        'kelurahan', 'kecamatan', 'kota', 'propinsi', 
        'negara', 'agama', 'status', 'pekerjaan', 'hp', 
        'foto', 'tgl_warga'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
