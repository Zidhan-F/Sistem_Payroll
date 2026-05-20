<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkLocationModel extends Model
{
    protected $table            = 'work_locations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'client_id', 'lokasi_kerja', 'location_code', 
        'division_id', 'department_id', 'position_id', 
        'provinsi', 'kota_kabupaten'
    ];
    protected $useTimestamps    = true;

    protected $validationRules    = [
        'lokasi_kerja'  => 'required',
        'client_id'      => 'required|is_natural_no_zero',
    ];
    
    protected $validationMessages = [
        'lokasi_kerja' => [
            'required' => 'Nama lokasi kerja wajib diisi.'
        ],
        'client_id' => [
            'required' => 'Klien wajib dipilih.',
            'is_natural_no_zero' => 'Klien tidak valid.'
        ]
    ];

    public function getFullData()
    {
        return $this->select('work_locations.*, clients.nama as nama_klien, divisions.nama as nama_divisi, departments.nama as nama_dept, positions.nama as nama_posisi')
                    ->join('clients', 'clients.id = work_locations.client_id', 'left')
                    ->join('divisions', 'divisions.id = work_locations.division_id', 'left')
                    ->join('departments', 'departments.id = work_locations.department_id', 'left')
                    ->join('positions', 'positions.id = work_locations.position_id', 'left')
                    ->findAll();
    }
}
