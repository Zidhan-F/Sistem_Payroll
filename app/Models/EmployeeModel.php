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
        'employ_id', 'hari_kerja', 'denda_absen', 'division_id', 'department_id'
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
        return $this->select('employees.*, global_positions.nama as nama_posisi, global_departments.nama as nama_dept, global_divisions.nama as nama_divisi, clients.nama as nama_klien, COALESCE(NULLIF(CAST(employees.alamat AS VARCHAR(MAX)), \'\'), minimum_wages.nama_daerah) as alamat, minimum_wages.tipe as umr_tipe, minimum_wages.nominal as umr_nominal, work_locations.lokasi_kerja as nama_lokasi')
                    ->join('global_positions', 'global_positions.id = employees.position_id', 'left')
                    ->join('global_departments', 'global_departments.id = employees.department_id', 'left')
                    ->join('global_divisions', 'global_divisions.id = employees.division_id', 'left')
                    ->join('clients', 'clients.id = employees.client_id', 'left')
                    ->join('minimum_wages', 'minimum_wages.id = employees.minimum_wage_id', 'left')
                    ->join('work_locations', 'work_locations.id = employees.work_location_id', 'left')
                    ->findAll();
    }
}
