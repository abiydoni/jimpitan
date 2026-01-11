<?php

namespace App\Models;

use CodeIgniter\Model;

class IuranModel extends Model
{
    protected $table            = 'tb_iuran';
    protected $primaryKey       = 'id_iuran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode_tarif', 'nikk', 'jenis_iuran', 
        'bulan', 'tahun', 'jumlah', 'jml_bayar', 
        'status', 'tgl_bayar', 'keterangan', 'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // Based on schema structure, tgl_bayar and created_at seem manually managed or DB defaulted
    protected $dateFormat    = 'datetime';
}
