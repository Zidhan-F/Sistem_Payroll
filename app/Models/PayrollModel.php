<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollModel extends Model
{
    protected $table            = 'payrolls';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'employee_id', 'bulan', 'tahun', 'gaji_pokok', 
        'total_tunjangan', 'total_potongan', 'take_home_pay', 'status_pembayaran'
    ];
    protected $useTimestamps    = true;
    protected $updatedField     = ''; // Only created_at is in schema
}
