<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nik', 'nama', 'email', 'no_rekening', 'bank_name', 'ptkp', 
        'gaji_pokok', 'position_id', 'client_id', 'tgl_masuk', 'status', 'minimum_wage_id', 'alamat'
    ];
    protected $useTimestamps    = true;

    public function getFullData()
    {
        return $this->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept, divisions.nama as nama_divisi, clients.nama as nama_klien, positions.department_id as department_id, departments.division_id as division_id, COALESCE(NULLIF(employees.alamat, \'\'), minimum_wages.nama_daerah) as alamat, minimum_wages.tipe as umr_tipe, minimum_wages.nominal as umr_nominal')
                    ->join('positions', 'positions.id = employees.position_id', 'left')
                    ->join('departments', 'departments.id = positions.department_id', 'left')
                    ->join('divisions', 'divisions.id = departments.division_id', 'left')
                    ->join('clients', 'clients.id = employees.client_id', 'left')
                    ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                    ->findAll();
    }
}
