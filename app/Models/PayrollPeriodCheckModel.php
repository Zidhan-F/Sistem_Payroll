<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollPeriodCheckModel extends Model
{
    protected $table            = 'payroll_period_checks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'period_id', 'employee_id', 'issue_type', 'issue_detail', 'is_resolved'
    ];

    public function getByPeriod($periodId)
    {
        return $this->where('period_id', $periodId)->findAll();
    }

    public function hasUnresolved($periodId)
    {
        return $this->where('period_id', $periodId)
                    ->where('is_resolved', 0)
                    ->countAllResults() > 0;
    }
}
