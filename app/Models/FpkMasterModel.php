<?php

namespace App\Models;

use CodeIgniter\Model;

class FpkMasterModel extends Model
{
    protected $table            = 'fpk_master';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nomor_fpk', 'nama_fpk', 'provinsi', 'city', 'status'
    ];
    protected $useTimestamps    = true;

    protected $validationRules    = [
        'nomor_fpk' => 'required|is_unique[fpk_master.nomor_fpk,id,{id}]',
        'nama_fpk'  => 'required',
        'provinsi'  => 'required',
        'city'      => 'required'
    ];
    protected $validationMessages = [
        'nomor_fpk' => [
            'is_unique' => 'Nomor FPK sudah terdaftar. Silakan gunakan nomor lain.'
        ]
    ];
}
