<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollComponentModel extends Model
{
    protected $table            = 'payroll_components';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'client_id', 'scheme_id', 'nama_komponen', 'nama', 'tipe', 'jenis_nilai', 'nilai', 'is_active',
        'is_persentase', 'jenis_komponen', 'sumber_nilai', 'periode', 'sifat_kompensasi', 'is_bpjs', 'is_pph21'
    ];
    protected $useTimestamps    = true;
    protected $updatedField     = '';

    public function getByClient($clientId)
    {
        return $this->where('client_id', $clientId)
                    ->where('is_active', 1)
                    ->orderBy('tipe', 'ASC')
                    ->findAll();
    }
}
