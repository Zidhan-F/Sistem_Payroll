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
        'gaji_pokok', 'position_id', 'client_id', 'tgl_masuk', 'status'
    ];
    protected $useTimestamps    = true;

    public function getFullData()
    {
        return $this->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept, clients.nama as nama_klien')
                    ->join('positions', 'positions.id = employees.position_id', 'left')
                    ->join('departments', 'departments.id = positions.department_id', 'left')
                    ->join('clients', 'clients.id = employees.client_id', 'left')
                    ->findAll();
    }
}
