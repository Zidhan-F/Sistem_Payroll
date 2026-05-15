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
        'client_id', 'nama_komponen', 'tipe', 'jenis_nilai', 'nilai', 'is_active'
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
