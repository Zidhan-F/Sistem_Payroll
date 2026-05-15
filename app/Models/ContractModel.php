<?php

namespace App\Models;

use CodeIgniter\Model;

class ContractModel extends Model
{
    protected $table            = 'contracts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'employee_id', 'client_id', 'no_kontrak', 
        'tgl_mulai', 'tgl_berakhir', 'gaji_pokok', 'status_pkwt'
    ];
    protected $useTimestamps    = true;
}
