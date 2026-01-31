<?php

namespace App\Models;

use CodeIgniter\Model;

class PeminjamanModel extends Model
{
    protected $table            = 'tb_peminjaman';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'barang_id', 'nama_peminjam', 'jumlah', 'jumlah_kembali',
        'tanggal_pinjam', 'tanggal_kembali', 'status', 'keterangan', 'nominal_ganti_rugi'
    ];

    // Validation
    protected $validationRules = [
        'barang_id'       => 'required|numeric',
        'nama_peminjam'   => 'required',
        'jumlah'          => 'required|numeric|greater_than[0]',
        'tanggal_pinjam'  => 'required|valid_date'
    ];
    
    protected $validationMessages = [
        'nama_peminjam' => [
            'required' => 'Nama Peminjam wajib diisi.'
        ],
        'jumlah' => [
            'required' => 'Jumlah wajib diisi.',
            'greater_than' => 'Jumlah harus lebih dari 0.'
        ]
    ];
}
