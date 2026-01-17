<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'tb_role';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'remark'];

    // Validation
    protected $validationRules = [
        'name'   => 'required|is_unique[tb_role.name,id,{id}]|alpha_dash',
        'remark' => 'required'
    ];
    protected $validationMessages = [
        'name' => [
            'required' => 'Kode Role harus diisi.',
            'is_unique' => 'Kode Role sudah ada.',
            'alpha_dash' => 'Kode Role hanya boleh huruf, angka, dash, dan underscore.'
        ],
        'remark' => [
            'required' => 'Nama Role (Remark) harus diisi.'
        ]
    ];
}
