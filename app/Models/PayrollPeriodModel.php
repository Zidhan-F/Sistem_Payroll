<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollPeriodModel extends Model
{
    protected $table            = 'payroll_periods';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['client_id', 'bulan', 'tahun', 'status_cutoff', 'pay_date'];
}
