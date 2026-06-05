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
        'gaji_pokok', 'position_id', 'client_id', 'tgl_masuk', 'status', 'minimum_wage_id', 'alamat',
        'tempat_lahir', 'tanggal_lahir', 'npwp', 'start_contract', 'end_contract', 'tipe_perjanjian', 'status_pernikahan', 'work_location_id', 'jumlah_anak',
        'employ_id', 'hari_kerja', 'denda_absen', 'division_id', 'department_id', 'custom_standard_days'
    ];
    protected $useTimestamps    = true;

    protected $validationRules    = [
        'nik'   => 'required|is_unique[employees.nik,id,{id}]',
        'email' => 'permit_empty|valid_email|is_unique[employees.email,id,{id}]'
    ];
    protected $validationMessages = [
        'nik' => [
            'is_unique' => 'NIK sudah terdaftar. Silakan gunakan NIK lain.'
        ],
        'email' => [
            'is_unique' => 'Email sudah terdaftar. Silakan gunakan Email lain.'
        ]
    ];

    public function getFullData()
    {
        $today = date('Y-m-d');
        return $this->select('employees.*, positions.nama as nama_posisi, departments.nama as nama_dept, divisions.nama as nama_divisi, COALESCE(employees.division_id, departments.division_id) as division_id, COALESCE(employees.department_id, positions.department_id) as department_id, clients.nama as nama_klien, COALESCE(NULLIF(CAST(employees.alamat AS VARCHAR(MAX)), \'\'), minimum_wages.nama_daerah) as alamat, minimum_wages.tipe as umr_tipe, minimum_wages.nominal as umr_nominal, work_locations.lokasi_kerja as nama_lokasi, (SELECT shift_scheme_id FROM employee_shifts WHERE employee_shifts.employee_id = employees.id AND (employee_shifts.end_date IS NULL OR employee_shifts.end_date >= \'' . $today . '\') ORDER BY employee_shifts.start_date DESC LIMIT 1) as shift_scheme_id')
                    ->join('positions', 'positions.id = employees.position_id', 'left')
                    ->join('departments', 'departments.id = COALESCE(employees.department_id, positions.department_id)', 'left')
                    ->join('divisions', 'divisions.id = COALESCE(employees.division_id, departments.division_id)', 'left')
                    ->join('clients', 'clients.id = employees.client_id', 'left')
                    ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                    ->join('work_locations', 'work_locations.id = employees.work_location_id', 'left')
                    ->findAll();
    }
}
