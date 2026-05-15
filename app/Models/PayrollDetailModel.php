<?php

namespace App\Models;

use CodeIgniter\Model;

class PayrollDetailModel extends Model
{
    protected $table            = 'payroll_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['payroll_id', 'nama_komponen', 'tipe', 'jumlah'];
}
