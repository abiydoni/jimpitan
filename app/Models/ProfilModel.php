<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfilModel extends Model
{
    protected $table            = 'tb_profil';
    protected $primaryKey       = 'kode';
    protected $useAutoIncrement = false; // Based on structure, it might default to 1
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama', 'alamat', 'cp', 'hp', 'logo', 'gambar', 'catatan'
    ];

    // Dates
    protected $useTimestamps = false;
}
