<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangModel extends Model
{
    protected $table            = 'tb_barang';
    protected $primaryKey       = 'kode';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['kode_brg', 'nama', 'jumlah', 'tanggal'];

    // Validation
    protected $validationRules = [
        'kode'     => 'permit_empty',
        'kode_brg' => 'required|is_unique[tb_barang.kode_brg,kode,{kode}]',
        'nama'     => 'required',
        'jumlah'   => 'numeric'
    ];
    protected $validationMessages = [
        'kode_brg' => [
            'required' => 'Kode Barang wajib diisi.',
            'is_unique' => 'Kode Barang sudah terdaftar.'
        ],
        'nama' => [
            'required' => 'Nama Barang wajib diisi.'
        ]
    ];
}
