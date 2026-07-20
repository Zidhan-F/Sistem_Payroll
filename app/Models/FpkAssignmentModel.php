<?php

namespace App\Models;

use CodeIgniter\Model;

class FpkAssignmentModel extends Model
{
    protected $table            = 'fpk_assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'fpk_id', 'employee_id', 'nik', 'nama_karyawan',
        'nomor_fpk', 'nama_fpk', 'provinsi', 'city',
        'tanggal_penempatan', 'user_submit', 'status'
    ];
    protected $useTimestamps    = true;

    protected $validationRules    = [
        'fpk_id'      => 'required|is_natural_no_zero',
        'employee_id' => 'required|is_natural_no_zero',
        'nik'         => 'required',
        'nomor_fpk'   => 'required'
    ];
}
